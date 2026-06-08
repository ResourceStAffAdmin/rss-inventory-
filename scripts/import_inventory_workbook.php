<?php

declare(strict_types=1);

use App\Core\Database;

require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Database.php';

$root = dirname(__DIR__);
\App\Core\Env::load($root . '/.env');
applyImportDatabaseOverrides();

$workbookPath = $argv[1] ?? ($root . '/docs/Inventory - (1).xlsx');
$mode = $argv[2] ?? '--preview';

if (!is_file($workbookPath)) {
    fwrite(STDERR, "Workbook not found: {$workbookPath}\n");
    exit(1);
}

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "PHP ZipArchive extension is required to read .xlsx files.\n");
    exit(1);
}

$rows = readFirstWorksheet($workbookPath);
$records = normalizeInventoryRows($rows);

if ($mode !== '--import') {
    echo "Workbook: {$workbookPath}\n";
    echo 'Rows found: ' . count($records) . "\n\n";
    foreach (array_slice($records, 0, 20) as $index => $record) {
        echo str_pad((string) ($index + 1), 3, ' ', STR_PAD_LEFT) . ' ';
        echo json_encode($record, JSON_UNESCAPED_SLASHES) . "\n";
    }
    if (count($records) > 20) {
        echo '... ' . (count($records) - 20) . " more rows\n";
    }
    exit(0);
}

$pdo = Database::connection();
$locationId = (int) $pdo->query('SELECT id FROM locations ORDER BY id ASC LIMIT 1')->fetchColumn();
$userId = (int) $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1')->fetchColumn();

if ($locationId <= 0) {
    fwrite(STDERR, "No location exists. Run migrations/seeders first.\n");
    exit(1);
}

if ($userId <= 0) {
    fwrite(STDERR, "No user exists. Run migrations/seeders first.\n");
    exit(1);
}

$stats = [
    'created_categories' => 0,
    'deactivated_categories' => 0,
    'deactivated_products' => 0,
    'inserted_products' => 0,
    'updated_products' => 0,
    'stock_balances_upserted' => 0,
    'stock_movements_created' => 0,
    'skipped' => 0,
];

$pdo->beginTransaction();

try {
    deactivateLegacyImportData($pdo, legacyDetailedSkus($rows), $stats);

    foreach ($records as $record) {
        if ($record['name'] === '') {
            $stats['skipped']++;
            continue;
        }

        $categoryId = null;
        if ($record['category'] !== '') {
            $categoryId = ensureCategory($pdo, $record['category'], $stats);
        }

        $existing = findProduct($pdo, $record['sku']);

        if ($existing === null) {
            $statement = $pdo->prepare(
                'INSERT INTO products (
                    sku,
                    name,
                    description,
                    category_id,
                    preferred_supplier_id,
                    unit_of_measure,
                    cost_price,
                    sell_price,
                    reorder_level,
                    is_active
                ) VALUES (
                    :sku,
                    :name,
                    :description,
                    :category_id,
                    NULL,
                    :unit,
                    0,
                    0,
                    :reorder_level,
                    1
                )'
            );
            $statement->execute([
                ':sku' => $record['sku'],
                ':name' => $record['name'],
                ':description' => $record['description'] !== '' ? $record['description'] : null,
                ':category_id' => $categoryId,
                ':unit' => $record['unit'],
                ':reorder_level' => $record['reorder_level'],
            ]);
            $productId = (int) $pdo->lastInsertId();
            $previousQty = 0.0;
            $stats['inserted_products']++;
        } else {
            $productId = (int) $existing['id'];
            $previousQty = currentQuantity($pdo, $locationId, $productId);
            $statement = $pdo->prepare(
                'UPDATE products
                 SET name = :name,
                     description = :description,
                     category_id = :category_id,
                     unit_of_measure = :unit,
                     reorder_level = :reorder_level,
                     is_active = 1
                 WHERE id = :id'
            );
            $statement->execute([
                ':id' => $productId,
                ':name' => $record['name'],
                ':description' => $record['description'] !== '' ? $record['description'] : null,
                ':category_id' => $categoryId,
                ':unit' => $record['unit'],
                ':reorder_level' => $record['reorder_level'],
            ]);
            $stats['updated_products']++;
        }

        upsertBalance($pdo, $locationId, $productId, $record['quantity']);
        $stats['stock_balances_upserted']++;

        if (abs($record['quantity'] - $previousQty) > 0.0001) {
            insertAdjustmentMovement($pdo, $productId, $locationId, $userId, $previousQty, $record['quantity']);
            $stats['stock_movements_created']++;
        }
    }

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

echo json_encode($stats, JSON_PRETTY_PRINT) . "\n";

/**
 * @return array<int, array<int, string>>
 */
function readFirstWorksheet(string $path): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException("Unable to open workbook: {$path}");
    }

    $sharedStrings = readSharedStrings($zip);
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml === false) {
        $zip->close();
        throw new RuntimeException('Unable to read first worksheet.');
    }

    $sheet = simplexml_load_string($sheetXml);
    if ($sheet === false) {
        $zip->close();
        throw new RuntimeException('Unable to parse worksheet XML.');
    }

    $rows = [];
    foreach ($sheet->sheetData->row as $row) {
        $rowIndex = ((int) $row['r']) - 1;
        $values = [];
        foreach ($row->c as $cell) {
            $reference = (string) $cell['r'];
            $columnIndex = columnIndexFromReference($reference);
            $type = (string) $cell['t'];
            $rawValue = isset($cell->v) ? (string) $cell->v : '';
            if ($type === 's') {
                $value = $sharedStrings[(int) $rawValue] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string) ($cell->is->t ?? '');
            } else {
                $value = $rawValue;
            }
            $values[$columnIndex] = trim($value);
        }
        if ($values !== []) {
            $max = max(array_keys($values));
            $rows[$rowIndex] = [];
            for ($i = 0; $i <= $max; $i++) {
                $rows[$rowIndex][$i] = $values[$i] ?? '';
            }
        }
    }

    $zip->close();
    ksort($rows);

    return array_values($rows);
}

function applyImportDatabaseOverrides(): void
{
    $mapping = [
        'IMPORT_DB_HOST' => 'DB_HOST',
        'IMPORT_DB_PORT' => 'DB_PORT',
        'IMPORT_DB_NAME' => 'DB_NAME',
        'IMPORT_DB_USER' => 'DB_USER',
        'IMPORT_DB_PASS' => 'DB_PASS',
        'IMPORT_DB_CHARSET' => 'DB_CHARSET',
    ];

    foreach ($mapping as $source => $target) {
        $value = getenv($source);
        if ($value === false) {
            continue;
        }

        putenv(sprintf('%s=%s', $target, $value));
        $_ENV[$target] = $value;
        $_SERVER[$target] = $value;
    }
}

/**
 * @return array<int, string>
 */
function readSharedStrings(ZipArchive $zip): array
{
    $xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($xml === false) {
        return [];
    }

    $shared = simplexml_load_string($xml);
    if ($shared === false) {
        return [];
    }

    $strings = [];
    foreach ($shared->si as $stringItem) {
        $parts = [];
        if (isset($stringItem->t)) {
            $parts[] = (string) $stringItem->t;
        }
        foreach ($stringItem->r as $run) {
            $parts[] = (string) $run->t;
        }
        $strings[] = trim(implode('', $parts));
    }

    return $strings;
}

function columnIndexFromReference(string $reference): int
{
    preg_match('/^[A-Z]+/i', $reference, $matches);
    $letters = strtoupper($matches[0] ?? 'A');
    $index = 0;
    for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
        $index = ($index * 26) + (ord($letters[$i]) - 64);
    }

    return $index - 1;
}

/**
 * @param array<int, array<int, string>> $rows
 * @return array<int, array{sku:string,name:string,category:string,description:string,unit:string,reorder_level:float,quantity:float}>
 */
function normalizeInventoryRows(array $rows): array
{
    $currentStockRecords = normalizeCurrentStockRows($rows);
    if ($currentStockRecords !== []) {
        return $currentStockRecords;
    }

    $headerRowIndex = findHeaderRowIndex($rows);
    if ($headerRowIndex === null) {
        throw new RuntimeException('Could not find a header row in the workbook.');
    }

    $headers = [];
    foreach ($rows[$headerRowIndex] as $index => $header) {
        $key = normalizeHeader($header);
        if ($key !== '') {
            $headers[$key] = $index;
        }
    }

    $records = [];
    for ($i = $headerRowIndex + 1, $count = count($rows); $i < $count; $i++) {
        $row = $rows[$i];
        if (emptyNonHeadingRow($row)) {
            continue;
        }

        $name = firstColumnValue($row, $headers, ['item', 'items', 'name', 'product', 'productname', 'description']);
        $sku = firstColumnValue($row, $headers, ['sku', 'stockkeepingunit', 'itemcode', 'code', 'assetcode', 'barcode', 'serial', 'serialnumber']);
        $category = firstColumnValue($row, $headers, ['category', 'type', 'classification']);
        $quantity = numericValue(firstColumnValue($row, $headers, ['quantity', 'qty', 'stock', 'onhand', 'quantityonhand']), 0.0);
        $unit = firstColumnValue($row, $headers, ['unit', 'uom', 'unitofmeasure']);
        $reorder = numericValue(firstColumnValue($row, $headers, ['reorder', 'reorderlevel', 'reorderpoint']), 0.0);

        if ($name === '') {
            $name = firstNonEmptyValue($row);
        }

        if ($sku === '') {
            $sku = generateSku($name, $i + 1);
        }

        $descriptionParts = [];
        foreach ($row as $value) {
            $value = trim((string) $value);
            if ($value !== '' && $value !== $name && $value !== $sku && !in_array($value, $descriptionParts, true)) {
                $descriptionParts[] = $value;
            }
        }

        $records[] = [
            'sku' => substr($sku, 0, 60),
            'name' => substr($name, 0, 191),
            'category' => substr($category !== '' ? broadCategoryForItem($category) : broadCategoryForItem($name), 0, 120),
            'description' => implode(' | ', $descriptionParts),
            'unit' => substr($unit !== '' ? $unit : 'pcs', 0, 20),
            'reorder_level' => $reorder,
            'quantity' => $quantity,
        ];
    }

    return $records;
}

/**
 * @param array<int, array<int, string>> $rows
 * @return array<int, array{sku:string,name:string,category:string,description:string,unit:string,reorder_level:float,quantity:float}>
 */
function normalizeCurrentStockRows(array $rows): array
{
    $idColumn = null;
    $nameColumn = null;
    $stockColumn = null;
    $headerRow = null;

    foreach ($rows as $rowIndex => $row) {
        $headers = [];
        foreach ($row as $columnIndex => $header) {
            $key = normalizeHeader((string) $header);
            if ($key !== '') {
                $headers[$key] = $columnIndex;
            }
        }

        if (
            array_key_exists('id', $headers)
            && array_key_exists('category', $headers)
            && array_key_exists('availablestocks', $headers)
        ) {
            $idColumn = $headers['id'];
            $nameColumn = $headers['category'];
            $stockColumn = $headers['availablestocks'];
            $headerRow = $rowIndex;
            break;
        }
    }

    if ($idColumn === null || $nameColumn === null || $stockColumn === null || $headerRow === null) {
        return [];
    }

    $records = [];

    for ($i = $headerRow + 1, $count = count($rows); $i < $count; $i++) {
        $row = $rows[$i];
        $sku = trim((string) ($row[$idColumn] ?? ''));
        $name = trim((string) ($row[$nameColumn] ?? ''));
        if ($sku === '' || $name === '') {
            continue;
        }

        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        $quantity = numericValue((string) ($row[$stockColumn] ?? ''), 0.0);

        $records[] = [
            'sku' => substr($sku, 0, 60),
            'name' => substr($name, 0, 191),
            'category' => substr(broadCategoryForItem($name), 0, 120),
            'description' => '',
            'unit' => 'pcs',
            'reorder_level' => 0.0,
            'quantity' => $quantity,
        ];
    }

    return $records;
}

/**
 * @param array<int, array<int, string>> $rows
 * @return array<int, string>
 */
function legacyDetailedSkus(array $rows): array
{
    $itemColumn = null;
    $headerRow = null;

    foreach ($rows as $rowIndex => $row) {
        foreach ($row as $columnIndex => $value) {
            if (normalizeHeader((string) $value) === 'item') {
                $itemColumn = $columnIndex;
                $headerRow = $rowIndex;
                break 2;
            }
        }
    }

    if ($itemColumn === null || $headerRow === null) {
        return [];
    }

    $skus = [];
    for ($i = $headerRow + 1, $count = count($rows); $i < $count; $i++) {
        $name = trim((string) ($rows[$i][$itemColumn] ?? ''));
        if ($name !== '') {
            $name = preg_replace('/\s+/', ' ', $name) ?? $name;
            $skus[] = substr(generateSku($name, $i + 1), 0, 60);
        }
    }

    return array_values(array_unique($skus));
}

function broadCategoryForItem(string $name): string
{
    $upperName = strtoupper($name);

    if (str_contains($upperName, 'MONITOR')) {
        return 'Displays';
    }

    if ($upperName === 'LAPTOP' || $upperName === 'PC' || str_contains($upperName, 'LAPTOP MAC')) {
        return 'Computers';
    }

    if (str_contains($upperName, 'MOUSE') || str_contains($upperName, 'KEYBOARD') || str_contains($upperName, 'HEADSET') || str_contains($upperName, 'WEBCAM')) {
        return 'Peripherals';
    }

    if (str_contains($upperName, 'UPS') || str_contains($upperName, 'POWER') || str_contains($upperName, 'WIFI') || str_contains($upperName, 'NETWORK')) {
        return 'Power & Network';
    }

    if (str_contains($upperName, 'HDMI') || str_contains($upperName, 'USB') || str_contains($upperName, 'DP') || str_contains($upperName, 'ADAPTER') || str_contains($upperName, 'CONVERTER')) {
        return 'Cables & Adapters';
    }

    return 'Accessories';
}

/**
 * @param array<int, array<int, string>> $rows
 */
function findHeaderRowIndex(array $rows): ?int
{
    foreach ($rows as $index => $row) {
        $headers = array_map('normalizeHeader', $row);
        $hits = 0;
        foreach (['item', 'items', 'name', 'product', 'description', 'sku', 'code', 'quantity', 'qty'] as $candidate) {
            if (in_array($candidate, $headers, true)) {
                $hits++;
            }
        }
        if ($hits >= 1 && count(array_filter($headers)) >= 2) {
            return $index;
        }
    }

    return null;
}

function normalizeHeader(string $value): string
{
    return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($value))) ?? '';
}

/**
 * @param array<int, string> $row
 */
function emptyNonHeadingRow(array $row): bool
{
    foreach ($row as $value) {
        if (trim((string) $value) !== '') {
            return false;
        }
    }

    return true;
}

/**
 * @param array<int, string> $row
 * @param array<string, int> $headers
 * @param array<int, string> $candidates
 */
function firstColumnValue(array $row, array $headers, array $candidates): string
{
    foreach ($candidates as $candidate) {
        if (array_key_exists($candidate, $headers)) {
            return trim((string) ($row[$headers[$candidate]] ?? ''));
        }
    }

    return '';
}

/**
 * @param array<int, string> $row
 */
function firstNonEmptyValue(array $row): string
{
    foreach ($row as $value) {
        $value = trim((string) $value);
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function numericValue(string $value, float $default): float
{
    $clean = preg_replace('/[^0-9.\-]/', '', $value) ?? '';

    return is_numeric($clean) ? (float) $clean : $default;
}

function generateSku(string $name, int $rowNumber): string
{
    $base = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $name) ?? '');
    $base = trim($base, '-');
    if ($base === '') {
        $base = 'ITEM';
    }

    return substr($base, 0, 45) . '-' . str_pad((string) $rowNumber, 4, '0', STR_PAD_LEFT);
}

/**
 * @param array<string, int> $stats
 */
function ensureCategory(PDO $pdo, string $name, array &$stats): int
{
    $statement = $pdo->prepare('SELECT id FROM categories WHERE name = :name LIMIT 1');
    $statement->execute([':name' => $name]);
    $id = $statement->fetchColumn();

    if ($id !== false) {
        $statement = $pdo->prepare('UPDATE categories SET is_active = 1 WHERE id = :id');
        $statement->execute([':id' => (int) $id]);

        return (int) $id;
    }

    $statement = $pdo->prepare('INSERT INTO categories (name, is_active) VALUES (:name, 1)');
    $statement->execute([':name' => $name]);
    $stats['created_categories']++;

    return (int) $pdo->lastInsertId();
}

/**
 * @return array<int, string>
 */
function broadCategoryNames(): array
{
    return [
        'Computers',
        'Displays',
        'Peripherals',
        'Cables & Adapters',
        'Power & Network',
        'Accessories',
    ];
}

/**
 * @param array<int, string> $legacySkus
 * @param array<string, int> $stats
 */
function deactivateLegacyImportData(PDO $pdo, array $legacySkus, array &$stats): void
{
    $categoryPlaceholders = implode(', ', array_fill(0, count(broadCategoryNames()), '?'));
    $statement = $pdo->prepare("UPDATE categories SET is_active = 0 WHERE name NOT IN ({$categoryPlaceholders}) AND is_active = 1");
    $statement->execute(broadCategoryNames());
    $stats['deactivated_categories'] = $statement->rowCount();

    if ($legacySkus === []) {
        return;
    }

    $skuPlaceholders = implode(', ', array_fill(0, count($legacySkus), '?'));
    $statement = $pdo->prepare("UPDATE products SET is_active = 0 WHERE sku IN ({$skuPlaceholders}) AND is_active = 1");
    $statement->execute($legacySkus);
    $stats['deactivated_products'] = $statement->rowCount();
}

/**
 * @return array<string, mixed>|null
 */
function findProduct(PDO $pdo, string $sku): ?array
{
    $statement = $pdo->prepare('SELECT id, sku FROM products WHERE sku = :sku LIMIT 1');
    $statement->execute([':sku' => $sku]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
}

function currentQuantity(PDO $pdo, int $locationId, int $productId): float
{
    $statement = $pdo->prepare(
        'SELECT quantity_on_hand FROM inventory_balances WHERE location_id = :location_id AND product_id = :product_id'
    );
    $statement->execute([
        ':location_id' => $locationId,
        ':product_id' => $productId,
    ]);
    $value = $statement->fetchColumn();

    return $value !== false ? (float) $value : 0.0;
}

function upsertBalance(PDO $pdo, int $locationId, int $productId, float $quantity): void
{
    $statement = $pdo->prepare(
        'INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
         VALUES (:location_id, :product_id, :quantity, 0)
         ON DUPLICATE KEY UPDATE quantity_on_hand = :quantity_update, updated_at = CURRENT_TIMESTAMP'
    );
    $statement->execute([
        ':location_id' => $locationId,
        ':product_id' => $productId,
        ':quantity' => $quantity,
        ':quantity_update' => $quantity,
    ]);
}

function insertAdjustmentMovement(
    PDO $pdo,
    int $productId,
    int $locationId,
    int $userId,
    float $previousQty,
    float $newQty
): void {
    $statement = $pdo->prepare(
        "INSERT INTO inventory_movements (
            movement_type,
            product_id,
            source_location_id,
            destination_location_id,
            quantity,
            unit_cost,
            reference_type,
            reference_id,
            reason,
            moved_by,
            moved_at,
            previous_stock,
            new_stock,
            status
        ) VALUES (
            'ADJUSTMENT',
            :product_id,
            NULL,
            :location_id,
            :quantity,
            NULL,
            'MANUAL',
            NULL,
            'Imported from Inventory - (1).xlsx',
            :moved_by,
            NOW(),
            :previous_stock,
            :new_stock,
            'COMPLETED'
        )"
    );
    $statement->execute([
        ':product_id' => $productId,
        ':location_id' => $locationId,
        ':quantity' => abs($newQty - $previousQty),
        ':moved_by' => $userId,
        ':previous_stock' => $previousQty,
        ':new_stock' => $newQty,
    ]);
}
