<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use PDO;
use PDOException;

final class UiController
{
    public function login(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/');
        }

        $next = trim((string) ($_GET['next'] ?? ''));

        View::render('auth/login', [
            'pageTitle' => 'Login',
            'next' => $next,
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
            'formValues' => [
                'username' => (string) ($_GET['username'] ?? ''),
            ],
        ], 'layouts/auth');
    }

    public function authenticate(): void
    {
        $pdo = Database::connection();

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $next = trim((string) ($_POST['next'] ?? ''));

        if ($username === '' || $password === '') {
            $this->redirect($this->loginErrorUrl('Username and password are required.', $username, $next));
        }

        $statement = $pdo->prepare(
            'SELECT id, fname, lname, username, password, role, status
             FROM employees
             WHERE username = :username
             LIMIT 1'
        );
        $statement->execute([':username' => $username]);
        $employee = $statement->fetch(PDO::FETCH_ASSOC);

        if ($employee === false) {
            $this->redirect($this->loginErrorUrl('Invalid credentials.', $username, $next));
        }

        if ((string) ($employee['status'] ?? '') !== 'active' || (string) ($employee['role'] ?? '') !== 'internal') {
            $this->redirect($this->loginErrorUrl('Access is restricted to internal employees.', $username, $next));
        }

        if ($this->passwordMatches($password, $employee['password'] ?? null) === false) {
            $this->redirect($this->loginErrorUrl('Invalid credentials.', $username, $next));
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['auth_employee_id'] = (int) $employee['id'];
        $_SESSION['auth_employee_name'] = $this->employeeFullName($employee);
        $_SESSION['auth_employee_role'] = (string) $employee['role'];

        $redirectPath = $this->normalizeRedirectPath($next);
        $this->redirect($redirectPath);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->redirect('/login');
    }

    public function dashboard(): void
    {
        $pdo = Database::connection();

        View::render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'currentRoute' => '/',
            'kpis' => $this->dashboardKpis($pdo),
            'recentActivities' => $this->recentActivities($pdo),
            'lowStockItems' => $this->lowStockItems($pdo),
            'outOfStockItems' => $this->outOfStockItems($pdo),
        ]);
    }

    public function products(): void
    {
        $pdo = Database::connection();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'category_id' => trim((string) ($_GET['filter_category_id'] ?? '')),
            'status' => trim((string) ($_GET['filter_status'] ?? '')),
        ];
        $totalRows = $this->productRowCount($pdo, $filters);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $totalPages);

        $viewProductId = trim((string) ($_GET['view_product_id'] ?? ''));
        $editProductId = trim((string) ($_GET['edit_product_id'] ?? ''));
        $viewProduct = $editProductId === '' && $viewProductId !== '' && ctype_digit($viewProductId)
            ? $this->productDetail($pdo, (int) $viewProductId)
            : null;
        $editProduct = $editProductId !== '' && ctype_digit($editProductId)
            ? $this->productDetail($pdo, (int) $editProductId)
            : null;

        View::render('products/index', [
            'pageTitle' => 'Products',
            'currentRoute' => '/products',
            'description' => 'Add, update, and track items with stock levels and reorder points.',
            'tableHeaders' => ['Item', 'SKU', 'Category', 'Qty', 'Unit', 'Reorder Point', 'Supplier', 'Status', 'Actions'],
            'tableRows' => $this->productRows($pdo, $filters, $page, $perPage),
            'moduleKpis' => $this->productsKpis($pdo),
            'searchPlaceholder' => 'Search items or SKU...',
            'notice' => isset($_GET['created'])
                ? 'New item saved successfully.'
                : (isset($_GET['updated']) ? 'Item updated successfully.' : null),
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
            'isModalOpen' => isset($_GET['openNewItem']) || isset($_GET['openModal']),
            'viewProduct' => $viewProduct,
            'editProduct' => $editProduct,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_rows' => $totalRows,
                'total_pages' => $totalPages,
            ],
            'categories' => $this->categoryOptions($pdo),
            'suppliers' => $this->supplierOptions($pdo),
            'filters' => $filters,
            'formValues' => [
                'sku' => (string) ($_GET['sku'] ?? ''),
                'name' => (string) ($_GET['name'] ?? ''),
                'category_id' => (string) ($_GET['category_id'] ?? ''),
                'supplier_id' => (string) ($_GET['supplier_id'] ?? ''),
                'unit_of_measure' => (string) ($_GET['unit_of_measure'] ?? 'pcs'),
                'reorder_level' => (string) ($_GET['reorder_level'] ?? '0'),
                'price' => (string) ($_GET['price'] ?? $_GET['cost_price'] ?? $_GET['sell_price'] ?? '0'),
                'description' => (string) ($_GET['description'] ?? ''),
            ],
        ]);
    }

    public function newProduct(): void
    {
        $this->redirect('/products?openNewItem=1');
    }

    public function createProduct(): void
    {
        $pdo = Database::connection();

        $sku = trim((string) ($_POST['sku'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $categoryId = trim((string) ($_POST['category_id'] ?? ''));
        $supplierId = trim((string) ($_POST['supplier_id'] ?? ''));
        $unit = trim((string) ($_POST['unit_of_measure'] ?? 'pcs'));
        $reorderLevel = trim((string) ($_POST['reorder_level'] ?? '0'));
        $price = trim((string) ($_POST['price'] ?? $_POST['cost_price'] ?? $_POST['sell_price'] ?? '0'));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($sku === '' || $name === '') {
            $this->redirectWithProductFormError('SKU and Name are required.', [
                'sku' => $sku,
                'name' => $name,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
                'unit_of_measure' => $unit,
                'reorder_level' => $reorderLevel,
                'price' => $price,
                'description' => $description,
            ]);
        }

        if ($reorderLevel !== '' && (!is_numeric($reorderLevel) || (float) $reorderLevel < 0)) {
            $this->redirectWithProductFormError('Reorder point must be 0 or higher.', [
                'sku' => $sku,
                'name' => $name,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
                'unit_of_measure' => $unit,
                'reorder_level' => $reorderLevel,
                'price' => $price,
                'description' => $description,
            ]);
        }

        try {
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
                    :supplier_id,
                    :unit,
                    :cost_price,
                    :sell_price,
                    :reorder_level,
                    1
                )'
            );

            $statement->execute([
                ':sku' => $sku,
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':category_id' => $categoryId !== '' ? (int) $categoryId : null,
                ':supplier_id' => $supplierId !== '' ? (int) $supplierId : null,
                ':unit' => $unit !== '' ? $unit : 'pcs',
                ':cost_price' => is_numeric($price) ? (float) $price : 0.0,
                ':sell_price' => is_numeric($price) ? (float) $price : 0.0,
                ':reorder_level' => is_numeric($reorderLevel) ? (float) $reorderLevel : 0.0,
            ]);
        } catch (PDOException $exception) {
            $this->redirectWithProductFormError('Unable to save item. SKU may already exist.', [
                'sku' => $sku,
                'name' => $name,
                'category_id' => $categoryId,
                'supplier_id' => $supplierId,
                'unit_of_measure' => $unit,
                'reorder_level' => $reorderLevel,
                'price' => $price,
                'description' => $description,
            ]);
        }

        $this->redirect('/products?created=1');
    }

    public function updateProduct(): void
    {
        $pdo = Database::connection();

        $id = trim((string) ($_POST['id'] ?? ''));
        $sku = trim((string) ($_POST['sku'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $categoryId = trim((string) ($_POST['category_id'] ?? ''));
        $supplierId = trim((string) ($_POST['supplier_id'] ?? ''));
        $unit = trim((string) ($_POST['unit_of_measure'] ?? 'pcs'));
        $reorderLevel = trim((string) ($_POST['reorder_level'] ?? '0'));
        $price = trim((string) ($_POST['price'] ?? $_POST['cost_price'] ?? $_POST['sell_price'] ?? '0'));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($id === '' || !ctype_digit($id)) {
            $this->redirect('/products?error=' . rawurlencode('Invalid product selected.'));
        }

        if ($sku === '' || $name === '') {
            $this->redirectWithProductEditError((int) $id, 'SKU and Name are required.');
        }

        if ($reorderLevel !== '' && (!is_numeric($reorderLevel) || (float) $reorderLevel < 0)) {
            $this->redirectWithProductEditError((int) $id, 'Reorder point must be 0 or higher.');
        }

        try {
            $statement = $pdo->prepare(
                'UPDATE products
                 SET sku = :sku,
                     name = :name,
                     description = :description,
                     category_id = :category_id,
                     preferred_supplier_id = :supplier_id,
                     unit_of_measure = :unit,
                     cost_price = :cost_price,
                     sell_price = :sell_price,
                     reorder_level = :reorder_level,
                     is_active = 1
                 WHERE id = :id'
            );
            $statement->execute([
                ':id' => (int) $id,
                ':sku' => $sku,
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':category_id' => $categoryId !== '' ? (int) $categoryId : null,
                ':supplier_id' => $supplierId !== '' ? (int) $supplierId : null,
                ':unit' => $unit !== '' ? $unit : 'pcs',
                ':cost_price' => is_numeric($price) ? (float) $price : 0.0,
                ':sell_price' => is_numeric($price) ? (float) $price : 0.0,
                ':reorder_level' => is_numeric($reorderLevel) ? (float) $reorderLevel : 0.0,
            ]);
        } catch (PDOException $exception) {
            $this->redirectWithProductEditError((int) $id, 'Unable to update item. SKU may already exist.');
        }

        $this->redirect('/products?updated=1');
    }

    public function createCategory(): void
    {
        $pdo = Database::connection();

        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $isActive = (string) ($_POST['is_active'] ?? '1');

        if ($name === '') {
            $this->redirectWithCategoryError('Category name is required.', [
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive,
            ]);
        }

        try {
            $statement = $pdo->prepare(
                'INSERT INTO categories (name, description, is_active) VALUES (:name, :description, :is_active)'
            );
            $statement->execute([
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':is_active' => $isActive === '0' ? 0 : 1,
            ]);
        } catch (PDOException $exception) {
            $this->redirectWithCategoryError('Unable to save category. Name may already exist.', [
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive,
            ]);
        }

        $this->redirect('/categories?created=1');
    }

    public function updateCategory(): void
    {
        $pdo = Database::connection();

        $id = trim((string) ($_POST['id'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $isActive = (string) ($_POST['is_active'] ?? '1');

        if ($id === '' || !ctype_digit($id)) {
            $this->redirect('/categories?error=' . rawurlencode('Invalid category selected.'));
        }

        if ($name === '') {
            $this->redirectWithCategoryEditError((int) $id, 'Category name is required.');
        }

        try {
            $statement = $pdo->prepare(
                'UPDATE categories
                 SET name = :name,
                     description = :description,
                     is_active = :is_active
                 WHERE id = :id'
            );
            $statement->execute([
                ':id' => (int) $id,
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':is_active' => $isActive === '0' ? 0 : 1,
            ]);
        } catch (PDOException $exception) {
            $this->redirectWithCategoryEditError((int) $id, 'Unable to update category. Name may already exist.');
        }

        $this->redirect('/categories?updated=1');
    }

    public function deleteCategory(): void
    {
        $pdo = Database::connection();
        $id = trim((string) ($_POST['id'] ?? ''));

        if ($id === '' || !ctype_digit($id)) {
            $this->redirect('/categories?error=' . rawurlencode('Invalid category selected.'));
        }

        $pdo->beginTransaction();

        try {
            $clearProducts = $pdo->prepare('UPDATE products SET category_id = NULL WHERE category_id = :id');
            $clearProducts->execute([':id' => (int) $id]);

            $deactivate = $pdo->prepare('UPDATE categories SET is_active = 0 WHERE id = :id');
            $deactivate->execute([':id' => (int) $id]);

            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            $this->redirect('/categories?error=' . rawurlencode('Unable to delete category.'));
        }

        $this->redirect('/categories?deleted=1');
    }

    public function createSupplier(): void
    {
        $pdo = Database::connection();

        $supplierCode = trim((string) ($_POST['supplier_code'] ?? ''));
        $companyName = trim((string) ($_POST['company_name'] ?? ''));
        $contactName = trim((string) ($_POST['contact_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $isActive = (string) ($_POST['is_active'] ?? '1');

        if ($supplierCode === '' || $companyName === '') {
            $this->redirectWithSupplierError('Supplier code and name are required.', [
                'supplier_code' => $supplierCode,
                'company_name' => $companyName,
                'contact_name' => $contactName,
                'email' => $email,
                'phone' => $phone,
                'is_active' => $isActive,
            ]);
        }

        try {
            $statement = $pdo->prepare(
                'INSERT INTO suppliers (supplier_code, company_name, contact_name, email, phone, is_active)
                 VALUES (:supplier_code, :company_name, :contact_name, :email, :phone, :is_active)'
            );
            $statement->execute([
                ':supplier_code' => $supplierCode,
                ':company_name' => $companyName,
                ':contact_name' => $contactName !== '' ? $contactName : null,
                ':email' => $email !== '' ? $email : null,
                ':phone' => $phone !== '' ? $phone : null,
                ':is_active' => $isActive === '0' ? 0 : 1,
            ]);
        } catch (PDOException $exception) {
            $this->redirectWithSupplierError('Unable to save supplier. Code may already exist.', [
                'supplier_code' => $supplierCode,
                'company_name' => $companyName,
                'contact_name' => $contactName,
                'email' => $email,
                'phone' => $phone,
                'is_active' => $isActive,
            ]);
        }

        $this->redirect('/suppliers?created=1');
    }

    public function updateSupplier(): void
    {
        $pdo = Database::connection();

        $id = trim((string) ($_POST['id'] ?? ''));
        $supplierCode = trim((string) ($_POST['supplier_code'] ?? ''));
        $companyName = trim((string) ($_POST['company_name'] ?? ''));
        $contactName = trim((string) ($_POST['contact_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $isActive = (string) ($_POST['is_active'] ?? '1');

        if ($id === '' || !ctype_digit($id)) {
            $this->redirect('/suppliers?error=' . rawurlencode('Invalid supplier selected.'));
        }

        if ($supplierCode === '' || $companyName === '') {
            $this->redirectWithSupplierEditError((int) $id, 'Supplier code and name are required.');
        }

        try {
            $statement = $pdo->prepare(
                'UPDATE suppliers
                 SET supplier_code = :supplier_code,
                     company_name = :company_name,
                     contact_name = :contact_name,
                     email = :email,
                     phone = :phone,
                     is_active = :is_active
                 WHERE id = :id'
            );
            $statement->execute([
                ':id' => (int) $id,
                ':supplier_code' => $supplierCode,
                ':company_name' => $companyName,
                ':contact_name' => $contactName !== '' ? $contactName : null,
                ':email' => $email !== '' ? $email : null,
                ':phone' => $phone !== '' ? $phone : null,
                ':is_active' => $isActive === '0' ? 0 : 1,
            ]);
        } catch (PDOException $exception) {
            $this->redirectWithSupplierEditError((int) $id, 'Unable to update supplier. Code may already exist.');
        }

        $this->redirect('/suppliers?updated=1');
    }

    public function deleteSupplier(): void
    {
        $pdo = Database::connection();
        $id = trim((string) ($_POST['id'] ?? ''));

        if ($id === '' || !ctype_digit($id)) {
            $this->redirect('/suppliers?error=' . rawurlencode('Invalid supplier selected.'));
        }

        $pdo->beginTransaction();

        try {
            $clearProducts = $pdo->prepare('UPDATE products SET preferred_supplier_id = NULL WHERE preferred_supplier_id = :id');
            $clearProducts->execute([':id' => (int) $id]);

            $deactivate = $pdo->prepare('UPDATE suppliers SET is_active = 0 WHERE id = :id');
            $deactivate->execute([':id' => (int) $id]);

            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            $this->redirect('/suppliers?error=' . rawurlencode('Unable to delete supplier.'));
        }

        $this->redirect('/suppliers?deleted=1');
    }

    public function createStockMovement(): void
    {
        $pdo = Database::connection();

        $movement = trim((string) ($_POST['movement'] ?? ''));
        $productId = trim((string) ($_POST['product_id'] ?? ''));
        $quantity = trim((string) ($_POST['quantity'] ?? ''));
        $newQuantity = trim((string) ($_POST['new_quantity'] ?? ''));
        $reason = trim((string) ($_POST['reason'] ?? ''));

        $openModal = match ($movement) {
            'stock-out' => 'stock-out',
            'adjust' => 'adjust',
            default => 'stock-in',
        };

        if ($productId === '' || !ctype_digit($productId)) {
            $this->redirectWithStockError('Select a valid item.', $openModal, [
                'product_id' => $productId,
                'quantity' => $quantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
            ]);
        }

        $productCheck = $pdo->prepare('SELECT 1 FROM products WHERE id = :id');
        $productCheck->execute([':id' => (int) $productId]);
        if ($productCheck->fetchColumn() === false) {
            $this->redirectWithStockError('Selected item no longer exists.', $openModal, [
                'product_id' => $productId,
                'quantity' => $quantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
            ]);
        }

        if ($movement === 'adjust') {
            if ($newQuantity === '' || !is_numeric($newQuantity) || (float) $newQuantity < 0) {
                $this->redirectWithStockError('New quantity must be 0 or higher.', $openModal, [
                    'product_id' => $productId,
                    'new_quantity' => $newQuantity,
                    'reason' => $reason,
                ]);
            }
            if ($reason === '') {
                $this->redirectWithStockError('Please provide a reason for the adjustment.', $openModal, [
                    'product_id' => $productId,
                    'new_quantity' => $newQuantity,
                    'reason' => $reason,
                ]);
            }
        } else {
            if ($quantity === '' || !is_numeric($quantity) || (float) $quantity <= 0) {
                $this->redirectWithStockError('Quantity must be greater than 0.', $openModal, [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'reason' => $reason,
                ]);
            }
        }

        $locationId = $this->defaultLocationId($pdo);
        if ($locationId <= 0) {
            $this->redirectWithStockError('No location found. Please create a location first.', $openModal, [
                'product_id' => $productId,
                'quantity' => $quantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
            ]);
        }

        $userId = $this->defaultUserId($pdo);
        if ($userId <= 0) {
            $this->redirectWithStockError('No user found. Please create a user first.', $openModal, [
                'product_id' => $productId,
                'quantity' => $quantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
            ]);
        }
        $currentQty = $this->currentQuantity($pdo, $locationId, (int) $productId);

        $movementType = 'PO_RECEIVE';
        $movementQty = (float) $quantity;
        $nextQty = $currentQty + $movementQty;
        $reasonNote = $reason !== '' ? $reason : null;
        $sourceLocation = null;
        $destinationLocation = null;

        if ($movement === 'stock-out') {
            if ($movementQty > $currentQty) {
                $this->redirectWithStockError('Cannot stock out more than current quantity.', $openModal, [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }

            $movementType = 'SO_SHIP';
            $nextQty = $currentQty - $movementQty;
            $sourceLocation = $locationId;
        } elseif ($movement === 'adjust') {
            $movementType = 'ADJUSTMENT';
            $nextQty = (float) $newQuantity;
            $movementQty = abs($nextQty - $currentQty);
            if ($movementQty <= 0) {
                $this->redirectWithStockError('Adjustment must change the quantity.', $openModal, [
                    'product_id' => $productId,
                    'new_quantity' => $newQuantity,
                    'reason' => $reason,
                ]);
            }
        } else {
            $destinationLocation = $locationId;
        }

        $pdo->beginTransaction();

        try {
            $balanceStatement = $pdo->prepare(
                'INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
                 VALUES (:location_id, :product_id, :quantity_on_hand, 0)
                 ON DUPLICATE KEY UPDATE quantity_on_hand = :quantity_on_hand_update, updated_at = CURRENT_TIMESTAMP'
            );
            $balanceStatement->execute([
                ':location_id' => $locationId,
                ':product_id' => (int) $productId,
                ':quantity_on_hand' => $nextQty,
                ':quantity_on_hand_update' => $nextQty,
            ]);

            $movementStatement = $pdo->prepare(
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
                    :movement_type,
                    :product_id,
                    :source_location_id,
                    :destination_location_id,
                    :quantity,
                    NULL,
                    'MANUAL',
                    NULL,
                    :reason,
                    :moved_by,
                    NOW(),
                    :previous_stock,
                    :new_stock,
                    'COMPLETED'
                )"
            );
            $movementStatement->execute([
                ':movement_type' => $movementType,
                ':product_id' => (int) $productId,
                ':source_location_id' => $sourceLocation,
                ':destination_location_id' => $destinationLocation,
                ':quantity' => $movementQty,
                ':reason' => $reasonNote,
                ':moved_by' => $userId,
                ':previous_stock' => $currentQty,
                ':new_stock' => $nextQty,
            ]);

            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            $debug = filter_var((string) getenv('APP_DEBUG'), FILTER_VALIDATE_BOOL);
            $message = $debug ? 'Unable to save stock movement. ' . $exception->getMessage() : 'Unable to save stock movement.';
            $this->redirectWithStockError($message, $openModal, [
                'product_id' => $productId,
                'quantity' => $quantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
            ]);
        }

        $this->redirect('/stock?created=1');
    }

    public function categories(): void
    {
        $pdo = Database::connection();

        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
        ];
        $editCategoryId = trim((string) ($_GET['edit_category_id'] ?? ''));
        $editCategory = $editCategoryId !== '' && ctype_digit($editCategoryId)
            ? $this->categoryDetail($pdo, (int) $editCategoryId)
            : null;

        View::render('categories/index', [
            'pageTitle' => 'Categories',
            'currentRoute' => '/categories',
            'description' => 'Organize items into categories for faster search and reporting.',
            'categories' => $this->categoryRows($pdo, $filters),
            'filters' => $filters,
            'notice' => isset($_GET['created'])
                ? 'Category saved successfully.'
                : (isset($_GET['updated'])
                    ? 'Category updated successfully.'
                    : (isset($_GET['deleted']) ? 'Category deleted successfully.' : null)),
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
            'isModalOpen' => isset($_GET['openNewCategory']),
            'editCategory' => $editCategory,
            'formValues' => [
                'name' => (string) ($_GET['name'] ?? ''),
                'description' => (string) ($_GET['description'] ?? ''),
                'is_active' => (string) ($_GET['is_active'] ?? '1'),
            ],
        ]);
    }

    public function stock(): void
    {
        $pdo = Database::connection();

        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'type' => trim((string) ($_GET['filter_type'] ?? '')),
        ];
        $movementId = trim((string) ($_GET['movement_id'] ?? ''));
        $viewMovement = $movementId !== '' && ctype_digit($movementId)
            ? $this->stockMovementDetail($pdo, (int) $movementId)
            : null;

        View::render('stock/index', [
            'pageTitle' => 'Stock Management',
            'currentRoute' => '/stock',
            'description' => 'Record stock in, stock out, and adjustments with full traceability.',
            'movements' => $this->stockRows($pdo, $filters),
            'viewMovement' => $viewMovement,
            'filters' => $filters,
            'products' => $this->productOptions($pdo),
            'notice' => isset($_GET['created']) ? 'Stock movement saved.' : null,
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
            'openModal' => (string) ($_GET['open'] ?? ''),
            'formValues' => [
                'product_id' => (string) ($_GET['product_id'] ?? ''),
                'quantity' => (string) ($_GET['quantity'] ?? ''),
                'new_quantity' => (string) ($_GET['new_quantity'] ?? ''),
                'reason' => (string) ($_GET['reason'] ?? ''),
            ],
        ]);
    }

    public function purchaseOrders(): void
    {
        $pdo = Database::connection();

        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['filter_status'] ?? '')),
        ];

        View::render('purchase_orders/index', [
            'pageTitle' => 'Purchase Orders',
            'currentRoute' => '/stock',
            'orders' => $this->purchaseOrderRows($pdo, $filters),
            'kpis' => $this->purchaseOrderKpis($pdo),
            'filters' => $filters,
            'suppliers' => $this->supplierOptions($pdo),
            'products' => $this->purchaseOrderProductOptions($pdo),
            'notice' => isset($_GET['created'])
                ? 'Purchase order created.'
                : (isset($_GET['sent'])
                    ? 'Purchase order marked as sent.'
                    : (isset($_GET['received'])
                        ? 'Purchase order received and stock updated.'
                        : (isset($_GET['cancelled']) ? 'Purchase order cancelled.' : null))),
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
            'isModalOpen' => isset($_GET['openNewPo']),
            'formValues' => [
                'supplier_id' => (string) ($_GET['supplier_id'] ?? ''),
                'order_date' => (string) ($_GET['order_date'] ?? date('Y-m-d')),
                'expected_date' => (string) ($_GET['expected_date'] ?? ''),
                'notes' => (string) ($_GET['notes'] ?? ''),
            ],
        ]);
    }

    public function createPurchaseOrder(): void
    {
        $pdo = Database::connection();

        $supplierId = trim((string) ($_POST['supplier_id'] ?? ''));
        $orderDate = trim((string) ($_POST['order_date'] ?? ''));
        $expectedDate = trim((string) ($_POST['expected_date'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        if ($supplierId === '' || !ctype_digit($supplierId)) {
            $this->redirectWithPurchaseOrderError('Select a supplier.', [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
            ]);
        }

        $supplierCheck = $pdo->prepare('SELECT 1 FROM suppliers WHERE id = :id AND is_active = 1');
        $supplierCheck->execute([':id' => (int) $supplierId]);
        if ($supplierCheck->fetchColumn() === false) {
            $this->redirectWithPurchaseOrderError('Selected supplier is not active.', [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
            ]);
        }

        if ($orderDate === '' || $this->isValidDate($orderDate) === false) {
            $this->redirectWithPurchaseOrderError('Order date is required.', [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
            ]);
        }

        if ($expectedDate !== '' && $this->isValidDate($expectedDate) === false) {
            $this->redirectWithPurchaseOrderError('Expected date must be a valid date.', [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
            ]);
        }

        $items = $this->postedPurchaseOrderItems();
        if ($items === []) {
            $this->redirectWithPurchaseOrderError('Add at least one item.', [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
            ]);
        }

        $locationId = $this->defaultLocationId($pdo);
        $userId = $this->defaultUserId($pdo);
        if ($locationId <= 0 || $userId <= 0) {
            $this->redirectWithPurchaseOrderError('A default location and user are required before creating purchase orders.', [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
            ]);
        }

        $pdo->beginTransaction();

        try {
            $poNumber = $this->nextPurchaseOrderNumber($pdo);

            $orderStatement = $pdo->prepare(
                'INSERT INTO purchase_orders (
                    po_number,
                    supplier_id,
                    location_id,
                    status,
                    order_date,
                    expected_date,
                    notes,
                    created_by
                ) VALUES (
                    :po_number,
                    :supplier_id,
                    :location_id,
                    "DRAFT",
                    :order_date,
                    :expected_date,
                    :notes,
                    :created_by
                )'
            );
            $orderStatement->execute([
                ':po_number' => $poNumber,
                ':supplier_id' => (int) $supplierId,
                ':location_id' => $locationId,
                ':order_date' => $orderDate,
                ':expected_date' => $expectedDate !== '' ? $expectedDate : null,
                ':notes' => $notes !== '' ? $notes : null,
                ':created_by' => $userId,
            ]);

            $purchaseOrderId = (int) $pdo->lastInsertId();
            $itemStatement = $pdo->prepare(
                'INSERT INTO purchase_order_items (
                    purchase_order_id,
                    product_id,
                    ordered_qty,
                    unit_cost,
                    notes
                ) VALUES (
                    :purchase_order_id,
                    :product_id,
                    :ordered_qty,
                    :unit_cost,
                    :notes
                )'
            );

            foreach ($items as $item) {
                $itemStatement->execute([
                    ':purchase_order_id' => $purchaseOrderId,
                    ':product_id' => $item['product_id'],
                    ':ordered_qty' => $item['quantity'],
                    ':unit_cost' => $item['unit_cost'],
                    ':notes' => $item['notes'] !== '' ? $item['notes'] : null,
                ]);
            }

            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            $debug = filter_var((string) getenv('APP_DEBUG'), FILTER_VALIDATE_BOOL);
            $message = $debug ? 'Unable to create purchase order. ' . $exception->getMessage() : 'Unable to create purchase order.';
            $this->redirectWithPurchaseOrderError($message, [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
            ]);
        }

        $this->redirect('/purchase-orders?created=1');
    }

    public function sendPurchaseOrder(): void
    {
        $pdo = Database::connection();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->redirect('/purchase-orders?error=' . rawurlencode('Purchase order not found.'));
        }

        $statement = $pdo->prepare("UPDATE purchase_orders SET status = 'SENT' WHERE id = :id AND status = 'DRAFT'");
        $statement->execute([':id' => $id]);

        if ($statement->rowCount() === 0) {
            $this->redirect('/purchase-orders?error=' . rawurlencode('Only draft purchase orders can be sent.'));
        }

        $this->redirect('/purchase-orders?sent=1');
    }

    public function receivePurchaseOrder(): void
    {
        $pdo = Database::connection();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->redirect('/purchase-orders?error=' . rawurlencode('Purchase order not found.'));
        }

        $userId = $this->defaultUserId($pdo);
        if ($userId <= 0) {
            $this->redirect('/purchase-orders?error=' . rawurlencode('A default user is required before receiving purchase orders.'));
        }

        $pdo->beginTransaction();

        try {
            $orderStatement = $pdo->prepare(
                "SELECT id, location_id, status
                 FROM purchase_orders
                 WHERE id = :id
                 FOR UPDATE"
            );
            $orderStatement->execute([':id' => $id]);
            $order = $orderStatement->fetch(PDO::FETCH_ASSOC);

            if ($order === false) {
                throw new \RuntimeException('Purchase order not found.');
            }

            if (!in_array((string) $order['status'], ['DRAFT', 'SENT', 'PARTIALLY_RECEIVED'], true)) {
                throw new \RuntimeException('This purchase order cannot be received.');
            }

            $itemStatement = $pdo->prepare(
                'SELECT id, product_id, ordered_qty, received_qty, unit_cost
                 FROM purchase_order_items
                 WHERE purchase_order_id = :purchase_order_id
                 FOR UPDATE'
            );
            $itemStatement->execute([':purchase_order_id' => $id]);
            $items = $itemStatement->fetchAll(PDO::FETCH_ASSOC);

            $balanceStatement = $pdo->prepare(
                'INSERT INTO inventory_balances (location_id, product_id, quantity_on_hand, quantity_reserved)
                 VALUES (:location_id, :product_id, :quantity_on_hand, 0)
                 ON DUPLICATE KEY UPDATE quantity_on_hand = :quantity_on_hand_update, updated_at = CURRENT_TIMESTAMP'
            );
            $itemUpdateStatement = $pdo->prepare(
                'UPDATE purchase_order_items
                 SET received_qty = :received_qty
                 WHERE id = :id'
            );
            $movementStatement = $pdo->prepare(
                "INSERT INTO inventory_movements (
                    movement_type,
                    product_id,
                    destination_location_id,
                    quantity,
                    previous_stock,
                    new_stock,
                    unit_cost,
                    reference_type,
                    reference_id,
                    reason,
                    moved_by,
                    moved_at,
                    status
                ) VALUES (
                    'PO_RECEIVE',
                    :product_id,
                    :destination_location_id,
                    :quantity,
                    :previous_stock,
                    :new_stock,
                    :unit_cost,
                    'PURCHASE_ORDER',
                    :reference_id,
                    'Purchase order receipt',
                    :moved_by,
                    NOW(),
                    'COMPLETED'
                )"
            );

            $receivedSomething = false;
            $locationId = (int) $order['location_id'];

            foreach ($items as $item) {
                $orderedQty = (float) $item['ordered_qty'];
                $receivedQty = (float) $item['received_qty'];
                $remainingQty = $orderedQty - $receivedQty;

                if ($remainingQty <= 0) {
                    continue;
                }

                $productId = (int) $item['product_id'];
                $previousStock = $this->currentQuantity($pdo, $locationId, $productId);
                $newStock = $previousStock + $remainingQty;

                $balanceStatement->execute([
                    ':location_id' => $locationId,
                    ':product_id' => $productId,
                    ':quantity_on_hand' => $newStock,
                    ':quantity_on_hand_update' => $newStock,
                ]);

                $itemUpdateStatement->execute([
                    ':received_qty' => $orderedQty,
                    ':id' => (int) $item['id'],
                ]);

                $movementStatement->execute([
                    ':product_id' => $productId,
                    ':destination_location_id' => $locationId,
                    ':quantity' => $remainingQty,
                    ':previous_stock' => $previousStock,
                    ':new_stock' => $newStock,
                    ':unit_cost' => (float) $item['unit_cost'],
                    ':reference_id' => $id,
                    ':moved_by' => $userId,
                ]);

                $receivedSomething = true;
            }

            if (!$receivedSomething) {
                throw new \RuntimeException('There are no remaining quantities to receive.');
            }

            $statusStatement = $pdo->prepare("UPDATE purchase_orders SET status = 'RECEIVED' WHERE id = :id");
            $statusStatement->execute([':id' => $id]);

            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            $debug = filter_var((string) getenv('APP_DEBUG'), FILTER_VALIDATE_BOOL);
            $message = $debug ? $exception->getMessage() : 'Unable to receive purchase order.';
            $this->redirect('/purchase-orders?error=' . rawurlencode($message));
        }

        $this->redirect('/purchase-orders?received=1');
    }

    public function cancelPurchaseOrder(): void
    {
        $pdo = Database::connection();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->redirect('/purchase-orders?error=' . rawurlencode('Purchase order not found.'));
        }

        $statement = $pdo->prepare(
            "UPDATE purchase_orders
             SET status = 'CANCELLED'
             WHERE id = :id
               AND status IN ('DRAFT', 'SENT')"
        );
        $statement->execute([':id' => $id]);

        if ($statement->rowCount() === 0) {
            $this->redirect('/purchase-orders?error=' . rawurlencode('Only draft or sent purchase orders can be cancelled.'));
        }

        $this->redirect('/purchase-orders?cancelled=1');
    }

    public function lowStock(): void
    {
        $this->renderModule(
            pageTitle: 'Low Stock',
            currentRoute: '/low-stock',
            description: 'Monitor items below reorder level and prioritize restocking.',
            tableHeaders: ['Item', 'SKU', 'Category', 'Qty', 'Reorder', 'Supplier', 'Status'],
            tableRows: [],
            actions: [],
            moduleKpis: [],
            searchPlaceholder: null
        );
    }

    public function suppliers(): void
    {
        $pdo = Database::connection();

        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
        ];
        $editSupplierId = trim((string) ($_GET['edit_supplier_id'] ?? ''));
        $editSupplier = $editSupplierId !== '' && ctype_digit($editSupplierId)
            ? $this->supplierDetail($pdo, (int) $editSupplierId)
            : null;

        View::render('suppliers/index', [
            'pageTitle' => 'Suppliers',
            'currentRoute' => '/suppliers',
            'description' => 'Manage supplier profiles and link them to items.',
            'suppliers' => $this->supplierRows($pdo, $filters),
            'filters' => $filters,
            'notice' => isset($_GET['created'])
                ? 'Supplier saved successfully.'
                : (isset($_GET['updated'])
                    ? 'Supplier updated successfully.'
                    : (isset($_GET['deleted']) ? 'Supplier deleted successfully.' : null)),
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
            'isModalOpen' => isset($_GET['openNewSupplier']),
            'editSupplier' => $editSupplier,
            'formValues' => [
                'supplier_code' => (string) ($_GET['supplier_code'] ?? ''),
                'company_name' => (string) ($_GET['company_name'] ?? ''),
                'contact_name' => (string) ($_GET['contact_name'] ?? ''),
                'email' => (string) ($_GET['email'] ?? ''),
                'phone' => (string) ($_GET['phone'] ?? ''),
                'is_active' => (string) ($_GET['is_active'] ?? '1'),
            ],
        ]);
    }

    public function history(): void
    {
        $this->renderModule(
            pageTitle: 'Inventory History',
            currentRoute: '/history',
            description: 'Track important actions performed on inventory items.',
            tableHeaders: ['Date', 'Action', 'Item', 'Qty', 'User'],
            tableRows: [],
            actions: [],
            moduleKpis: [],
            searchPlaceholder: null
        );
    }

    public function users(): void
    {
        $pdo = Database::connection();
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
        ];

        View::render('users/index', [
            'pageTitle' => 'Users & Roles',
            'currentRoute' => '/users',
            'description' => 'Search the employee directory without loading the full list up front.',
            'filters' => $filters,
            'tableRows' => $this->employeeRows($pdo, $filters),
        ]);
    }

    public function employeeSearch(): void
    {
        $pdo = Database::connection();
        $query = trim((string) ($_GET['q'] ?? ''));
        $activeOnly = ((string) ($_GET['active_only'] ?? '1')) !== '0';

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->employeeSearchOptions($pdo, $query, $activeOnly), JSON_THROW_ON_ERROR);
        exit;
    }

    public function showEmployeeTransactions(): void
    {
        $pdo = Database::connection();
        $id = (int) ($_GET['id'] ?? 0);
        $employee = $this->employeeProfile($pdo, $id);

        if ($employee === null) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        View::render('users/show', [
            'pageTitle' => 'Employee Inventory Activity',
            'currentRoute' => '/users',
            'employee' => $employee,
            'summary' => $this->employeeTransactionSummary($pdo, $id),
            'transactions' => $this->employeeTransactionRows($pdo, $id),
        ]);
    }

    public function reports(): void
    {
        $this->renderModule(
            pageTitle: 'Reports',
            currentRoute: '/reports',
            description: 'Generate basic inventory summaries and movement reports.',
            tableHeaders: ['Report', 'Description', 'Last Generated', 'Status'],
            tableRows: [],
            actions: [],
            moduleKpis: [],
            searchPlaceholder: null
        );
    }

    public function accountability(): void
    {
        $pdo = Database::connection();

        View::render('accountability/index', [
            'pageTitle' => 'Asset Accountability',
            'currentRoute' => '/accountability',
            'description' => 'Track employee-issued equipment and generate printable accountability forms.',
            'assignments' => $this->assetAssignmentRows($pdo),
            'notice' => isset($_GET['created'])
                ? 'Accountability assignment saved successfully.'
                : (isset($_GET['returned']) ? 'Assignment marked as returned.' : null),
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
        ]);
    }

    public function newAccountability(): void
    {
        $pdo = Database::connection();
        $this->ensureAccountabilityAttachmentColumns($pdo);

        $prefillProductId = trim((string) ($_GET['product_id'] ?? ''));
        $prefillItem = null;
        $selectedEmployee = null;
        if ($prefillProductId !== '' && ctype_digit($prefillProductId)) {
            $prefillItem = $this->accountabilityProductDefault($pdo, (int) $prefillProductId);
        }
        $selectedEmployeeId = trim((string) ($_GET['employee_id'] ?? ''));
        if ($selectedEmployeeId !== '' && ctype_digit($selectedEmployeeId)) {
            $selectedEmployee = $this->employeeOptionById($pdo, (int) $selectedEmployeeId);
        }

        View::render('accountability/new', [
            'pageTitle' => 'New Asset Assignment',
            'currentRoute' => '/accountability',
            'products' => $this->accountabilityProductOptions($pdo),
            'prefillItem' => $prefillItem,
            'selectedEmployee' => $selectedEmployee,
            'errorMessage' => isset($_GET['error']) ? (string) $_GET['error'] : null,
            'formValues' => [
                'employee_id' => (string) ($_GET['employee_id'] ?? ''),
                'assigned_date' => (string) ($_GET['assigned_date'] ?? date('Y-m-d')),
                'returned_date' => (string) ($_GET['returned_date'] ?? ''),
                'notes' => (string) ($_GET['notes'] ?? ''),
            ],
        ]);
    }

    public function createAccountability(): void
    {
        $pdo = Database::connection();
        $this->ensureAccountabilityAttachmentColumns($pdo);

        $employeeId = trim((string) ($_POST['employee_id'] ?? ''));
        $assignedDate = trim((string) ($_POST['assigned_date'] ?? ''));
        $returnedDate = trim((string) ($_POST['returned_date'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $attachment = null;

        if ($employeeId === '' || !ctype_digit($employeeId)) {
            $this->redirectWithAccountabilityError('Select an active employee.', [
                'employee_id' => $employeeId,
                'assigned_date' => $assignedDate,
                'returned_date' => $returnedDate,
                'notes' => $notes,
            ]);
        }

        if ($assignedDate === '' || $this->isValidDate($assignedDate) === false) {
            $this->redirectWithAccountabilityError('Assigned date is required.', [
                'employee_id' => $employeeId,
                'assigned_date' => $assignedDate,
                'returned_date' => $returnedDate,
                'notes' => $notes,
            ]);
        }

        if ($returnedDate !== '' && $this->isValidDate($returnedDate) === false) {
            $this->redirectWithAccountabilityError('Returned date must be a valid date.', [
                'employee_id' => $employeeId,
                'assigned_date' => $assignedDate,
                'returned_date' => $returnedDate,
                'notes' => $notes,
            ]);
        }

        $employeeCheck = $pdo->prepare("SELECT 1 FROM employees WHERE id = :id AND status = 'active'");
        $employeeCheck->execute([':id' => (int) $employeeId]);
        if ($employeeCheck->fetchColumn() === false) {
            $this->redirectWithAccountabilityError('Selected employee is not active.', [
                'employee_id' => $employeeId,
                'assigned_date' => $assignedDate,
                'returned_date' => $returnedDate,
                'notes' => $notes,
            ]);
        }

        $items = $this->postedAssignmentItems();
        if ($items === []) {
            $this->redirectWithAccountabilityError('Add at least one equipment item.', [
                'employee_id' => $employeeId,
                'assigned_date' => $assignedDate,
                'returned_date' => $returnedDate,
                'notes' => $notes,
            ]);
        }

        try {
            $attachment = $this->uploadedAccountabilityAttachment();
        } catch (\RuntimeException $exception) {
            $this->redirectWithAccountabilityError($exception->getMessage(), [
                'employee_id' => $employeeId,
                'assigned_date' => $assignedDate,
                'returned_date' => $returnedDate,
                'notes' => $notes,
            ]);
        }

        $pdo->beginTransaction();

        try {
            $assignmentStatement = $pdo->prepare(
                'INSERT INTO asset_assignments (
                    employee_id,
                    assigned_date,
                    returned_date,
                    status,
                    notes,
                    attachment_path,
                    attachment_name,
                    attachment_mime,
                    attachment_size
                ) VALUES (
                    :employee_id,
                    :assigned_date,
                    :returned_date,
                    :status,
                    :notes,
                    :attachment_path,
                    :attachment_name,
                    :attachment_mime,
                    :attachment_size
                )'
            );
            $assignmentStatement->execute([
                ':employee_id' => (int) $employeeId,
                ':assigned_date' => $assignedDate,
                ':returned_date' => $returnedDate !== '' ? $returnedDate : null,
                ':status' => $returnedDate !== '' ? 'RETURNED' : 'ACTIVE',
                ':notes' => $notes !== '' ? $notes : null,
                ':attachment_path' => $attachment['path'] ?? null,
                ':attachment_name' => $attachment['name'] ?? null,
                ':attachment_mime' => $attachment['mime'] ?? null,
                ':attachment_size' => $attachment['size'] ?? null,
            ]);

            $assignmentId = (int) $pdo->lastInsertId();
            $itemStatement = $pdo->prepare(
                'INSERT INTO asset_assignment_items (
                    asset_assignment_id,
                    product_id,
                    equipment_name,
                    description,
                    serial_number,
                    reason,
                    quantity,
                    returned_at
                ) VALUES (
                    :asset_assignment_id,
                    :product_id,
                    :equipment_name,
                    :description,
                    :serial_number,
                    :reason,
                    :quantity,
                    :returned_at
                )'
            );

            foreach ($items as $item) {
                $itemStatement->execute([
                    ':asset_assignment_id' => $assignmentId,
                    ':product_id' => $item['product_id'],
                    ':equipment_name' => $item['equipment_name'],
                    ':description' => $item['description'],
                    ':serial_number' => $item['serial_number'],
                    ':reason' => $item['reason'],
                    ':quantity' => $item['quantity'],
                    ':returned_at' => $returnedDate !== '' ? $returnedDate . ' 00:00:00' : null,
                ]);
            }

            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            if ($attachment !== null) {
                $this->deletePublicUpload((string) $attachment['path']);
            }
            $debug = filter_var((string) getenv('APP_DEBUG'), FILTER_VALIDATE_BOOL);
            $message = $debug ? 'Unable to save assignment. ' . $exception->getMessage() : 'Unable to save assignment.';
            $this->redirectWithAccountabilityError($message, [
                'employee_id' => $employeeId,
                'assigned_date' => $assignedDate,
                'returned_date' => $returnedDate,
                'notes' => $notes,
            ]);
        }

        $this->redirect('/accountability/' . $assignmentId . '?created=1');
    }

    public function showAccountability(): void
    {
        $pdo = Database::connection();
        $this->ensureAccountabilityAttachmentColumns($pdo);

        $id = (int) ($_GET['id'] ?? 0);
        $assignment = $this->assetAssignment($pdo, $id);

        if ($assignment === null) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        View::render('accountability/show', [
            'pageTitle' => 'Accountability Details',
            'currentRoute' => '/accountability',
            'assignment' => $assignment,
            'items' => $this->assetAssignmentItems($pdo, $id),
            'notice' => isset($_GET['created']) ? 'Accountability assignment saved successfully.' : null,
        ]);
    }

    public function printAccountability(): void
    {
        $pdo = Database::connection();
        $this->ensureAccountabilityAttachmentColumns($pdo);

        $id = (int) ($_GET['id'] ?? 0);
        $assignment = $this->assetAssignment($pdo, $id);

        if ($assignment === null) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        View::render('accountability/print', [
            'pageTitle' => 'Equipment Accountability Form',
            'assignment' => $assignment,
            'items' => $this->assetAssignmentItems($pdo, $id),
        ], 'layouts/print');
    }

    public function returnAccountability(): void
    {
        $pdo = Database::connection();
        $id = (int) ($_POST['id'] ?? 0);
        $returnedDate = trim((string) ($_POST['returned_date'] ?? date('Y-m-d')));

        if ($id <= 0 || $this->isValidDate($returnedDate) === false) {
            $this->redirect('/accountability?error=' . rawurlencode('Unable to mark assignment as returned.'));
        }

        $statement = $pdo->prepare(
            "UPDATE asset_assignments
             SET status = 'RETURNED', returned_date = :returned_date
             WHERE id = :id"
        );
        $statement->execute([
            ':id' => $id,
            ':returned_date' => $returnedDate,
        ]);

        $itemStatement = $pdo->prepare(
            'UPDATE asset_assignment_items
             SET returned_at = COALESCE(returned_at, :returned_at)
             WHERE asset_assignment_id = :id'
        );
        $itemStatement->execute([
            ':id' => $id,
            ':returned_at' => $returnedDate . ' 00:00:00',
        ]);

        $this->redirect('/accountability?returned=1');
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    private function assetAssignmentRows(PDO $pdo): array
    {
        $statement = $pdo->query(
            'SELECT
                aa.id,
                aa.assigned_date,
                aa.returned_date,
                aa.status,
                e.fname,
                e.lname,
                e.company,
                COUNT(aai.id) AS item_count
             FROM asset_assignments aa
             INNER JOIN employees e ON e.id = aa.employee_id
             LEFT JOIN asset_assignment_items aai ON aai.asset_assignment_id = aa.id
             GROUP BY aa.id, aa.assigned_date, aa.returned_date, aa.status, e.fname, e.lname, e.company
             ORDER BY aa.assigned_date DESC, aa.id DESC'
        );

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'employee' => $this->employeeFullName($row),
                'department' => ((string) ($row['company'] ?? '')) !== '' ? (string) $row['company'] : '-',
                'item_count' => (int) $row['item_count'],
                'assigned_date' => (string) $row['assigned_date'],
                'returned_date' => (string) ($row['returned_date'] ?? ''),
                'status' => (string) $row['status'],
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, string|int>|null
     */
    private function assetAssignment(PDO $pdo, int $id): ?array
    {
        $statement = $pdo->prepare(
            'SELECT
                aa.id,
                aa.employee_id,
                aa.assigned_date,
                aa.returned_date,
                aa.status,
                aa.notes,
                aa.attachment_path,
                aa.attachment_name,
                aa.attachment_mime,
                aa.attachment_size,
                e.fname,
                e.lname,
                e.email,
                e.position,
                e.company
             FROM asset_assignments aa
             INNER JOIN employees e ON e.id = aa.employee_id
             WHERE aa.id = :id'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'employee_id' => (int) $row['employee_id'],
            'employee' => $this->employeeFullName($row),
            'email' => (string) ($row['email'] ?? ''),
            'position' => (string) ($row['position'] ?? ''),
            'department' => (string) ($row['company'] ?? ''),
            'assigned_date' => (string) $row['assigned_date'],
            'returned_date' => (string) ($row['returned_date'] ?? ''),
            'status' => (string) $row['status'],
            'notes' => (string) ($row['notes'] ?? ''),
            'attachment_path' => (string) ($row['attachment_path'] ?? ''),
            'attachment_name' => (string) ($row['attachment_name'] ?? ''),
            'attachment_mime' => (string) ($row['attachment_mime'] ?? ''),
            'attachment_size' => $row['attachment_size'] !== null ? (int) $row['attachment_size'] : 0,
        ];
    }

    /**
     * @return array<int, array<string, string|int|float|null>>
     */
    private function assetAssignmentItems(PDO $pdo, int $assignmentId): array
    {
        $statement = $pdo->prepare(
            'SELECT
                aai.id,
                aai.product_id,
                aai.equipment_name,
                aai.description,
                aai.serial_number,
                aai.reason,
                aai.quantity,
                p.sku
             FROM asset_assignment_items aai
             LEFT JOIN products p ON p.id = aai.product_id
             WHERE aai.asset_assignment_id = :assignment_id
             ORDER BY aai.id ASC'
        );
        $statement->execute([':assignment_id' => $assignmentId]);

        $items = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $items[] = [
                'id' => (int) $row['id'],
                'product_id' => $row['product_id'] !== null ? (int) $row['product_id'] : null,
                'sku' => (string) ($row['sku'] ?? ''),
                'equipment_name' => (string) $row['equipment_name'],
                'description' => (string) ($row['description'] ?? ''),
                'serial_number' => (string) ($row['serial_number'] ?? ''),
                'reason' => (string) ($row['reason'] ?? ''),
                'quantity' => (float) $row['quantity'],
            ];
        }

        return $items;
    }

    private function ensureAccountabilityAttachmentColumns(PDO $pdo): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $statement = $pdo->prepare(
            'SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "asset_assignments"
               AND COLUMN_NAME = "attachment_path"'
        );
        $statement->execute();
        if ((int) $statement->fetchColumn() > 0) {
            $checked = true;
            return;
        }

        $pdo->exec(
            'ALTER TABLE asset_assignments
                ADD COLUMN attachment_path VARCHAR(255) NULL AFTER notes,
                ADD COLUMN attachment_name VARCHAR(255) NULL AFTER attachment_path,
                ADD COLUMN attachment_mime VARCHAR(120) NULL AFTER attachment_name,
                ADD COLUMN attachment_size BIGINT UNSIGNED NULL AFTER attachment_mime'
        );
        $checked = true;
    }

    /**
     * @return array{path:string,name:string,mime:string,size:int}|null
     */
    private function uploadedAccountabilityAttachment(): ?array
    {
        if (!isset($_FILES['attachment']) || !is_array($_FILES['attachment'])) {
            return null;
        }

        $file = $_FILES['attachment'];
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Unable to upload attachment. Please try a smaller file.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $originalName = trim((string) ($file['name'] ?? ''));
        $size = (int) ($file['size'] ?? 0);
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('Attachment upload was not received.');
        }
        if ($size <= 0 || $size > 10 * 1024 * 1024) {
            throw new \RuntimeException('Attachment must be 10 MB or smaller.');
        }

        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException('Attachment must be a PDF, JPG, PNG, or WebP file.');
        }

        $mimeByExtension = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        $mime = $mimeByExtension[$extension] ?? 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedMime = finfo_file($finfo, $tmpName);
                finfo_close($finfo);
                if (is_string($detectedMime) && $detectedMime !== '') {
                    $mime = $detectedMime;
                }
            }
        }

        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
        if (!in_array($mime, $allowedMimes, true)) {
            throw new \RuntimeException('Attachment file type is not allowed.');
        }

        $targetDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'accountability';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to prepare attachment upload folder.');
        }

        $safeName = 'assignment_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $safeName;
        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new \RuntimeException('Unable to save attachment.');
        }

        return [
            'path' => '/uploads/accountability/' . $safeName,
            'name' => $originalName !== '' ? basename($originalName) : $safeName,
            'mime' => $mime,
            'size' => $size,
        ];
    }

    private function deletePublicUpload(string $publicPath): void
    {
        if ($publicPath === '' || !str_starts_with($publicPath, '/uploads/accountability/')) {
            return;
        }

        $absolutePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $publicPath);
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }

    /**
     * @return array<int, array{id:int,name:string,description:string}>
     */
    private function accountabilityProductOptions(PDO $pdo): array
    {
        $statement = $pdo->query(
            'SELECT
                p.id,
                p.name,
                COALESCE(c.name, "-") AS category_name,
                COALESCE(SUM(ib.quantity_on_hand), 0) AS quantity_on_hand
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1
             GROUP BY p.id, p.name, c.name
             ORDER BY p.name ASC'
        );

        $products = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $products[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'description' => (string) $row['category_name'] . ' - Qty: ' . number_format((float) $row['quantity_on_hand'], 0),
            ];
        }

        return $products;
    }

    /**
     * @return array{product_id:int,equipment_name:string,description:string}|null
     */
    private function accountabilityProductDefault(PDO $pdo, int $productId): ?array
    {
        $statement = $pdo->prepare(
            'SELECT
                p.id,
                p.name,
                COALESCE(c.name, "") AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $productId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'product_id' => (int) $row['id'],
            'equipment_name' => (string) $row['name'],
            'description' => (string) ($row['category_name'] ?? ''),
        ];
    }

    /**
     * @return array<int, array{product_id:int|null,equipment_name:string,description:string|null,serial_number:string|null,reason:string|null,quantity:float}>
     */
    private function postedAssignmentItems(): array
    {
        $productIds = $_POST['item_product_id'] ?? [];
        $equipmentNames = $_POST['item_equipment_name'] ?? [];
        $descriptions = $_POST['item_description'] ?? [];
        $serialNumbers = $_POST['item_serial_number'] ?? [];
        $reasons = $_POST['item_reason'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];

        if (!is_array($equipmentNames)) {
            return [];
        }

        $items = [];
        $count = count($equipmentNames);
        for ($index = 0; $index < $count; $index++) {
            $equipmentName = trim((string) ($equipmentNames[$index] ?? ''));
            $productId = trim((string) (is_array($productIds) ? ($productIds[$index] ?? '') : ''));
            $description = trim((string) (is_array($descriptions) ? ($descriptions[$index] ?? '') : ''));
            $serialNumber = trim((string) (is_array($serialNumbers) ? ($serialNumbers[$index] ?? '') : ''));
            $reason = trim((string) (is_array($reasons) ? ($reasons[$index] ?? '') : ''));
            $quantity = trim((string) (is_array($quantities) ? ($quantities[$index] ?? '1') : '1'));

            if ($equipmentName === '' && $description === '' && $serialNumber === '' && $reason === '' && $productId === '') {
                continue;
            }

            if ($equipmentName === '') {
                continue;
            }

            $items[] = [
                'product_id' => $productId !== '' && ctype_digit($productId) ? (int) $productId : null,
                'equipment_name' => $equipmentName,
                'description' => $description !== '' ? $description : null,
                'serial_number' => $serialNumber !== '' ? $serialNumber : null,
                'reason' => $reason !== '' ? $reason : null,
                'quantity' => $quantity !== '' && is_numeric($quantity) && (float) $quantity > 0 ? (float) $quantity : 1.0,
            ];
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function employeeFullName(array $row): string
    {
        $fullName = trim((string) ($row['fname'] ?? '') . ' ' . (string) ($row['lname'] ?? ''));

        return $fullName !== '' ? $fullName : 'Employee #' . (string) ($row['id'] ?? '');
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $parsed instanceof \DateTimeImmutable && $parsed->format('Y-m-d') === $date;
    }

    /**
     * @param array<int, string> $tableHeaders
     * @param array<int, array<int, string>> $tableRows
     */
    private function renderModule(
        string $pageTitle,
        string $currentRoute,
        string $description,
        array $tableHeaders,
        array $tableRows,
        array $actions,
        array $moduleKpis,
        ?string $searchPlaceholder,
        ?string $notice = null
    ): void {
        View::render('modules/index', [
            'pageTitle' => $pageTitle,
            'currentRoute' => $currentRoute,
            'description' => $description,
            'tableHeaders' => $tableHeaders,
            'tableRows' => $tableRows,
            'actions' => $actions,
            'moduleKpis' => $moduleKpis,
            'searchPlaceholder' => $searchPlaceholder,
            'notice' => $notice,
        ]);
    }

    /**
     * @param array<string, string> $filters
     * @return array<int, array<string, string|int>>
     */
    private function productRows(PDO $pdo, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $sumExpr = 'COALESCE(SUM(ib.quantity_on_hand), 0)';
        $sql = 'SELECT
                p.id,
                p.name AS item_name,
                COALESCE(c.name, "-") AS category_name,
                p.sku,
                p.description,
                p.category_id,
                p.preferred_supplier_id,
                ' . $sumExpr . ' AS quantity_on_hand,
                p.unit_of_measure,
                COALESCE(p.cost_price, 0) AS cost_price,
                COALESCE(p.sell_price, 0) AS sell_price,
                COALESCE(p.reorder_level, 0) AS reorder_level,
                COALESCE(s.company_name, "-") AS supplier_name,
                CASE
                    WHEN ' . $sumExpr . ' <= 0 THEN "Out of Stock"
                    WHEN ' . $sumExpr . ' <= COALESCE(p.reorder_level, 0) THEN "Low Stock"
                    ELSE "In Stock"
                END AS stock_status
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN suppliers s ON s.id = p.preferred_supplier_id
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1';

        $params = [];
        $this->appendProductFilters($sql, $params, $filters);

        $sql .= ' GROUP BY p.id, c.name, s.company_name';

        $statusHaving = $this->productStatusHaving($sumExpr, $filters);
        if ($statusHaving !== '') {
            $sql .= ' HAVING ' . $statusHaving;
        }
        $offset = max(0, ($page - 1) * $perPage);
        $limit = max(1, $perPage);
        $sql .= ' ORDER BY p.name ASC LIMIT ' . $limit . ' OFFSET ' . $offset;

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'item' => (string) $row['item_name'],
                'category' => (string) $row['category_name'],
                'category_id' => $row['category_id'] !== null ? (int) $row['category_id'] : '',
                'sku' => (string) $row['sku'],
                'description' => (string) ($row['description'] ?? ''),
                'qty' => number_format((float) $row['quantity_on_hand'], 0),
                'unit' => (string) $row['unit_of_measure'],
                'reorder' => number_format((float) $row['reorder_level'], 0),
                'supplier' => (string) $row['supplier_name'],
                'supplier_id' => $row['preferred_supplier_id'] !== null ? (int) $row['preferred_supplier_id'] : '',
                'price' => number_format((float) $row['cost_price'], 2, '.', ''),
                'status' => (string) $row['stock_status'],
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, string> $filters
     */
    private function productRowCount(PDO $pdo, array $filters = []): int
    {
        $sumExpr = 'COALESCE(SUM(ib.quantity_on_hand), 0)';
        $sql = 'SELECT COUNT(*)
            FROM (
                SELECT p.id
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN suppliers s ON s.id = p.preferred_supplier_id
                LEFT JOIN inventory_balances ib ON ib.product_id = p.id
                WHERE p.is_active = 1';

        $params = [];
        $this->appendProductFilters($sql, $params, $filters);
        $sql .= ' GROUP BY p.id, p.reorder_level';

        $statusHaving = $this->productStatusHaving($sumExpr, $filters);
        if ($statusHaving !== '') {
            $sql .= ' HAVING ' . $statusHaving;
        }

        $sql .= ') AS filtered_products';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    /**
     * @param array<string, string> $filters
     * @param array<string, mixed> $params
     */
    private function appendProductFilters(string &$sql, array &$params, array $filters): void
    {
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $terms = preg_split('/\s+/', $search) ?: [];
            foreach ($terms as $index => $term) {
                $term = trim($term);
                if ($term === '') {
                    continue;
                }

                $nameParam = ':q' . $index . '_name';
                $skuParam = ':q' . $index . '_sku';
                $descriptionParam = ':q' . $index . '_description';
                $categoryParam = ':q' . $index . '_category';
                $supplierParam = ':q' . $index . '_supplier';
                $unitParam = ':q' . $index . '_unit';
                $sql .= ' AND (
                    p.name LIKE ' . $nameParam . '
                    OR p.sku LIKE ' . $skuParam . '
                    OR COALESCE(p.description, "") LIKE ' . $descriptionParam . '
                    OR COALESCE(c.name, "") LIKE ' . $categoryParam . '
                    OR COALESCE(s.company_name, "") LIKE ' . $supplierParam . '
                    OR p.unit_of_measure LIKE ' . $unitParam . '
                )';
                $likeTerm = '%' . $term . '%';
                $params[$nameParam] = $likeTerm;
                $params[$skuParam] = $likeTerm;
                $params[$descriptionParam] = $likeTerm;
                $params[$categoryParam] = $likeTerm;
                $params[$supplierParam] = $likeTerm;
                $params[$unitParam] = $likeTerm;
            }
        }

        $categoryId = trim((string) ($filters['category_id'] ?? ''));
        if ($categoryId !== '' && ctype_digit($categoryId)) {
            $sql .= ' AND p.category_id = :category_id';
            $params[':category_id'] = (int) $categoryId;
        }
    }

    /**
     * @param array<string, string> $filters
     */
    private function productStatusHaving(string $sumExpr, array $filters): string
    {
        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($status === 'out') {
            return $sumExpr . ' <= 0';
        }
        if ($status === 'low') {
            return $sumExpr . ' > 0 AND ' . $sumExpr . ' <= COALESCE(p.reorder_level, 0)';
        }
        if ($status === 'in') {
            return $sumExpr . ' > COALESCE(p.reorder_level, 0)';
        }

        return '';
    }

    /**
     * @return array<string, string|int|float>|null
     */
    private function productDetail(PDO $pdo, int $id): ?array
    {
        $sumExpr = 'COALESCE(SUM(ib.quantity_on_hand), 0)';
        $statement = $pdo->prepare(
            'SELECT
                p.id,
                p.name,
                p.sku,
                p.description,
                p.category_id,
                COALESCE(c.name, "-") AS category_name,
                p.preferred_supplier_id,
                COALESCE(s.company_name, "-") AS supplier_name,
                p.unit_of_measure,
                COALESCE(p.cost_price, 0) AS cost_price,
                COALESCE(p.reorder_level, 0) AS reorder_level,
                ' . $sumExpr . ' AS quantity_on_hand,
                CASE
                    WHEN ' . $sumExpr . ' <= 0 THEN "Out of Stock"
                    WHEN ' . $sumExpr . ' <= COALESCE(p.reorder_level, 0) THEN "Low Stock"
                    ELSE "In Stock"
                END AS stock_status
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN suppliers s ON s.id = p.preferred_supplier_id
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.id = :id AND p.is_active = 1
             GROUP BY p.id, c.name, s.company_name
             LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'sku' => (string) $row['sku'],
            'description' => (string) ($row['description'] ?? ''),
            'category_id' => $row['category_id'] !== null ? (int) $row['category_id'] : '',
            'category_name' => (string) $row['category_name'],
            'supplier_id' => $row['preferred_supplier_id'] !== null ? (int) $row['preferred_supplier_id'] : '',
            'supplier_name' => (string) $row['supplier_name'],
            'unit_of_measure' => (string) $row['unit_of_measure'],
            'price' => number_format((float) $row['cost_price'], 2, '.', ''),
            'reorder_level' => number_format((float) $row['reorder_level'], 0, '.', ''),
            'quantity_on_hand' => number_format((float) $row['quantity_on_hand'], 0),
            'status' => (string) $row['stock_status'],
        ];
    }

    /**
     * @return array<string, string|int>|null
     */
    private function categoryDetail(PDO $pdo, int $id): ?array
    {
        $statement = $pdo->prepare(
            'SELECT id, name, description, COALESCE(is_active, 1) AS is_active
             FROM categories
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'description' => (string) ($row['description'] ?? ''),
            'is_active' => (int) $row['is_active'],
        ];
    }

    /**
     * @return array<string, string|int>|null
     */
    private function supplierDetail(PDO $pdo, int $id): ?array
    {
        $statement = $pdo->prepare(
            'SELECT id, supplier_code, company_name, contact_name, email, phone, is_active
             FROM suppliers
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'supplier_code' => (string) $row['supplier_code'],
            'company_name' => (string) $row['company_name'],
            'contact_name' => (string) ($row['contact_name'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'phone' => (string) ($row['phone'] ?? ''),
            'is_active' => (int) $row['is_active'],
        ];
    }

    /**
     * @return array<string, string|int|float>|null
     */
    private function stockMovementDetail(PDO $pdo, int $id): ?array
    {
        $statement = $pdo->prepare(
            'SELECT
                m.id,
                DATE_FORMAT(m.moved_at, "%Y-%m-%d %H:%i") AS moved_at,
                m.movement_type,
                m.quantity,
                m.previous_stock,
                m.new_stock,
                m.status,
                m.reason,
                m.reference_type,
                m.reference_id,
                p.id AS product_id,
                p.name AS item_name,
                COALESCE(u.full_name, "System") AS user_name
             FROM inventory_movements m
             INNER JOIN products p ON p.id = m.product_id
             LEFT JOIN users u ON u.id = m.moved_by
             WHERE m.id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'date' => (string) $row['moved_at'],
            'reference' => 'MOV-' . str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT),
            'item' => (string) $row['item_name'],
            'product_id' => (int) $row['product_id'],
            'type' => $this->movementLabel((string) $row['movement_type']),
            'qty' => (float) $row['quantity'],
            'previous' => (float) ($row['previous_stock'] ?? 0),
            'new' => (float) ($row['new_stock'] ?? 0),
            'user' => (string) $row['user_name'],
            'status' => (string) ($row['status'] ?? 'COMPLETED'),
            'reason' => trim((string) ($row['reason'] ?? '')) !== '' ? (string) $row['reason'] : '-',
            'source' => trim((string) ($row['reference_type'] ?? '')) !== ''
                ? (string) $row['reference_type']
                : '-',
            'source_id' => $row['reference_id'] !== null ? (int) $row['reference_id'] : '-',
        ];
    }

    /**
     * @return array<int, array{label:string,value:string}>
     */
    private function productsKpis(PDO $pdo): array
    {
        $totalItems = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();

        $totalStockValue = (float) $pdo->query(
            'SELECT COALESCE(SUM(ib.quantity_on_hand * p.cost_price), 0)
             FROM products p
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1'
        )->fetchColumn();

        $lowStock = (int) $pdo->query(
            'SELECT COUNT(*)
             FROM (
                SELECT p.id
                FROM products p
                LEFT JOIN inventory_balances ib ON ib.product_id = p.id
                WHERE p.is_active = 1
                GROUP BY p.id, p.reorder_level
                HAVING COALESCE(SUM(ib.quantity_on_hand), 0) > 0
                   AND COALESCE(SUM(ib.quantity_on_hand), 0) <= p.reorder_level
             ) AS low_stock'
        )->fetchColumn();

        $outOfStock = (int) $pdo->query(
            'SELECT COUNT(*)
             FROM (
                SELECT p.id
                FROM products p
                LEFT JOIN inventory_balances ib ON ib.product_id = p.id
                WHERE p.is_active = 1
                GROUP BY p.id
                HAVING COALESCE(SUM(ib.quantity_on_hand), 0) <= 0
             ) AS out_stock'
        )->fetchColumn();

        return [
            ['label' => 'Total Items', 'value' => number_format($totalItems)],
            ['label' => 'Low Stock', 'value' => number_format($lowStock)],
            ['label' => 'Out of Stock', 'value' => number_format($outOfStock)],
            ['label' => 'Total Stock Value', 'value' => 'PHP ' . number_format($totalStockValue, 2)],
        ];
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function categoryOptions(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC');
        return array_map(
            static fn (array $row): array => ['id' => (int) $row['id'], 'name' => (string) $row['name']],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function supplierOptions(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT id, company_name FROM suppliers WHERE is_active = 1 ORDER BY company_name ASC');
        return array_map(
            static fn (array $row): array => ['id' => (int) $row['id'], 'name' => (string) $row['company_name']],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @param array<string, string> $filters
     * @return array<int, array<string, string|int|float>>
     */
    private function categoryRows(PDO $pdo, array $filters = []): array
    {
        $sql = 'SELECT
                c.id,
                c.name,
                c.description,
                COALESCE(c.is_active, 1) AS is_active,
                COUNT(DISTINCT p.id) AS item_count,
                COALESCE(SUM(ib.quantity_on_hand * p.cost_price), 0) AS stock_value
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
            LEFT JOIN inventory_balances ib ON ib.product_id = p.id
            WHERE COALESCE(c.is_active, 1) = 1';

        $params = [];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $sql .= ' AND (c.name LIKE :q OR c.description LIKE :q)';
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= ' GROUP BY c.id ORDER BY c.name ASC';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'description' => (string) ($row['description'] ?? ''),
                'item_count' => (int) $row['item_count'],
                'stock_value' => (float) $row['stock_value'],
                'is_active' => (int) $row['is_active'],
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, string> $filters
     * @return array<int, array<string, string|int>>
     */
    private function supplierRows(PDO $pdo, array $filters = []): array
    {
        $sql = 'SELECT
                s.id,
                s.supplier_code,
                s.company_name,
                s.contact_name,
                s.phone,
                s.is_active,
                COUNT(p.id) AS linked_items
            FROM suppliers s
            LEFT JOIN products p ON p.preferred_supplier_id = s.id AND p.is_active = 1
            WHERE s.is_active = 1';

        $params = [];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $sql .= ' AND (s.company_name LIKE :q OR s.supplier_code LIKE :q)';
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= ' GROUP BY s.id ORDER BY s.company_name ASC';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'supplier_code' => (string) $row['supplier_code'],
                'company_name' => (string) $row['company_name'],
                'contact_name' => (string) ($row['contact_name'] ?? ''),
                'phone' => (string) ($row['phone'] ?? ''),
                'linked_items' => (int) $row['linked_items'],
                'is_active' => (int) $row['is_active'],
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, string> $filters
     * @return array<int, array<string, string|int>>
     */
    private function employeeRows(PDO $pdo, array $filters = []): array
    {
        $query = trim((string) ($filters['q'] ?? ''));
        if ($query === '') {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT id, fname, lname, email, position, status, company
             FROM employees
             WHERE fname LIKE :q1
                OR lname LIKE :q2
                OR CONCAT_WS(" ", fname, lname) LIKE :q3
                OR email LIKE :q4
                OR position LIKE :q5
                OR company LIKE :q6
             ORDER BY lname ASC, fname ASC
             LIMIT 50'
        );
        $searchTerm = '%' . $query . '%';
        $statement->execute([
            ':q1' => $searchTerm,
            ':q2' => $searchTerm,
            ':q3' => $searchTerm,
            ':q4' => $searchTerm,
            ':q5' => $searchTerm,
            ':q6' => $searchTerm,
        ]);

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $fullName = trim((string) ($row['fname'] ?? '') . ' ' . (string) ($row['lname'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Employee #' . (string) $row['id'];
            }

            $status = strtolower((string) ($row['status'] ?? ''));
            $statusLabel = $status === '' ? '-' : ucfirst($status);

            $rows[] = [
                'id' => (int) $row['id'],
                'name' => $fullName,
                'email' => ((string) ($row['email'] ?? '')) !== '' ? (string) $row['email'] : '-',
                'position' => ((string) ($row['position'] ?? '')) !== '' ? (string) $row['position'] : '-',
                'company' => ((string) ($row['company'] ?? '')) !== '' ? (string) $row['company'] : '-',
                'status' => $statusLabel,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, string|int>|null
     */
    private function employeeProfile(PDO $pdo, int $employeeId): ?array
    {
        $statement = $pdo->prepare(
            'SELECT id, fname, lname, email, contact, position, status, company
             FROM employees
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $employeeId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => $this->employeeFullName($row),
            'email' => (string) ($row['email'] ?? ''),
            'contact' => (string) ($row['contact'] ?? ''),
            'position' => (string) ($row['position'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'company' => (string) ($row['company'] ?? ''),
        ];
    }

    /**
     * @return array{active_assignments:string,total_items:string,returned_items:string}
     */
    private function employeeTransactionSummary(PDO $pdo, int $employeeId): array
    {
        $statement = $pdo->prepare(
            'SELECT
                SUM(CASE WHEN aa.status = "ACTIVE" THEN 1 ELSE 0 END) AS active_assignments,
                COUNT(aai.id) AS total_items,
                SUM(CASE WHEN aai.returned_at IS NOT NULL THEN 1 ELSE 0 END) AS returned_items
             FROM asset_assignments aa
             LEFT JOIN asset_assignment_items aai ON aai.asset_assignment_id = aa.id
             WHERE aa.employee_id = :employee_id'
        );
        $statement->execute([':employee_id' => $employeeId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'active_assignments' => number_format((int) ($row['active_assignments'] ?? 0)),
            'total_items' => number_format((int) ($row['total_items'] ?? 0)),
            'returned_items' => number_format((int) ($row['returned_items'] ?? 0)),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function employeeTransactionRows(PDO $pdo, int $employeeId): array
    {
        $statement = $pdo->prepare(
            'SELECT
                event_date,
                event_type,
                assignment_id,
                equipment_name,
                description,
                serial_number,
                quantity,
                reason,
                assignment_status
             FROM (
                SELECT
                    aa.assigned_date AS event_date,
                    "ASSIGNED" AS event_type,
                    aa.id AS assignment_id,
                    aai.equipment_name,
                    aai.description,
                    aai.serial_number,
                    aai.quantity,
                    aai.reason,
                    aa.status AS assignment_status
                FROM asset_assignments aa
                INNER JOIN asset_assignment_items aai ON aai.asset_assignment_id = aa.id
                WHERE aa.employee_id = :employee_id_assigned

                UNION ALL

                SELECT
                    DATE(aai.returned_at) AS event_date,
                    "RETURNED" AS event_type,
                    aa.id AS assignment_id,
                    aai.equipment_name,
                    aai.description,
                    aai.serial_number,
                    aai.quantity,
                    aai.reason,
                    aa.status AS assignment_status
                FROM asset_assignments aa
                INNER JOIN asset_assignment_items aai ON aai.asset_assignment_id = aa.id
                WHERE aa.employee_id = :employee_id_returned
                  AND aai.returned_at IS NOT NULL
             ) AS employee_events
             ORDER BY event_date DESC, assignment_id DESC, equipment_name ASC'
        );
        $statement->execute([
            ':employee_id_assigned' => $employeeId,
            ':employee_id_returned' => $employeeId,
        ]);

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rows[] = [
                'date' => (string) ($row['event_date'] ?? ''),
                'type' => (string) $row['event_type'],
                'reference' => 'ACC-' . str_pad((string) $row['assignment_id'], 6, '0', STR_PAD_LEFT),
                'item' => (string) $row['equipment_name'],
                'details' => trim((string) ($row['description'] ?? '')) !== '' ? (string) $row['description'] : '-',
                'serial' => trim((string) ($row['serial_number'] ?? '')) !== '' ? (string) $row['serial_number'] : '-',
                'qty' => number_format((float) ($row['quantity'] ?? 0), 0),
                'reason' => trim((string) ($row['reason'] ?? '')) !== '' ? (string) $row['reason'] : 'N/A',
                'status' => (string) ($row['assignment_status'] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{id:int,name:string,meta:string}>
     */
    private function employeeSearchOptions(PDO $pdo, string $query, bool $activeOnly = true): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $sql = 'SELECT id, fname, lname, position, company
                FROM employees
                WHERE (
                    fname LIKE :q1
                    OR lname LIKE :q2
                    OR CONCAT_WS(" ", fname, lname) LIKE :q3
                    OR email LIKE :q4
                    OR position LIKE :q5
                    OR company LIKE :q6
                )';
        if ($activeOnly) {
            $sql .= " AND status = 'active'";
        }
        $sql .= ' ORDER BY lname ASC, fname ASC LIMIT 12';

        $statement = $pdo->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $statement->execute([
            ':q1' => $searchTerm,
            ':q2' => $searchTerm,
            ':q3' => $searchTerm,
            ':q4' => $searchTerm,
            ':q5' => $searchTerm,
            ':q6' => $searchTerm,
        ]);

        $employees = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $meta = trim((string) ($row['position'] ?? ''));
            $company = trim((string) ($row['company'] ?? ''));
            if ($company !== '') {
                $meta = $meta !== '' ? $meta . ' - ' . $company : $company;
            }

            $employees[] = [
                'id' => (int) $row['id'],
                'name' => $this->employeeFullName($row),
                'meta' => $meta,
            ];
        }

        return $employees;
    }

    /**
     * @return array{id:int,name:string,meta:string}|null
     */
    private function employeeOptionById(PDO $pdo, int $employeeId): ?array
    {
        $statement = $pdo->prepare(
            'SELECT id, fname, lname, position, company
             FROM employees
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $employeeId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $meta = trim((string) ($row['position'] ?? ''));
        $company = trim((string) ($row['company'] ?? ''));
        if ($company !== '') {
            $meta = $meta !== '' ? $meta . ' - ' . $company : $company;
        }

        return [
            'id' => (int) $row['id'],
            'name' => $this->employeeFullName($row),
            'meta' => $meta,
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array<int, array<string, string|int|float>>
     */
    private function stockRows(PDO $pdo, array $filters = []): array
    {
        $sql = 'SELECT
                m.id,
                DATE_FORMAT(m.moved_at, "%Y-%m-%d %H:%i") AS moved_at,
                m.movement_type,
                m.quantity,
                m.previous_stock,
                m.new_stock,
                m.status,
                p.name AS item_name,
                COALESCE(u.full_name, "System") AS user_name
            FROM inventory_movements m
            INNER JOIN products p ON p.id = m.product_id
            LEFT JOIN users u ON u.id = m.moved_by
            WHERE 1=1';

        $params = [];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $sql .= ' AND (p.name LIKE :q OR m.id LIKE :q)';
            $params[':q'] = '%' . $search . '%';
        }

        $type = trim((string) ($filters['type'] ?? ''));
        if ($type !== '') {
            $sql .= ' AND m.movement_type = :movement_type';
            $params[':movement_type'] = $type;
        }

        $sql .= ' ORDER BY m.moved_at DESC, m.id DESC';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'date' => (string) $row['moved_at'],
                'reference' => 'MOV-' . str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT),
                'item' => (string) $row['item_name'],
                'type' => $this->movementLabel((string) $row['movement_type']),
                'qty' => (float) $row['quantity'],
                'previous' => (float) ($row['previous_stock'] ?? 0),
                'new' => (float) ($row['new_stock'] ?? 0),
                'user' => (string) $row['user_name'],
                'status' => (string) ($row['status'] ?? 'COMPLETED'),
                'tone' => $this->movementTone((string) $row['movement_type']),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function productOptions(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT id, name FROM products WHERE is_active = 1 ORDER BY name ASC');
        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
            ],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<int, array<string, string|int|float>>
     */
    private function purchaseOrderRows(PDO $pdo, array $filters = []): array
    {
        $sql = 'SELECT
                po.id,
                po.po_number,
                po.status,
                po.order_date,
                po.expected_date,
                s.company_name AS supplier,
                COALESCE(SUM(poi.ordered_qty), 0) AS ordered_qty,
                COALESCE(SUM(poi.received_qty), 0) AS received_qty,
                COALESCE(SUM(poi.ordered_qty * poi.unit_cost), 0) AS total_cost,
                COUNT(poi.id) AS line_count
            FROM purchase_orders po
            INNER JOIN suppliers s ON s.id = po.supplier_id
            LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
            WHERE 1=1';

        $params = [];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $sql .= ' AND (po.po_number LIKE :q OR s.company_name LIKE :q)';
            $params[':q'] = '%' . $search . '%';
        }

        $status = strtoupper(trim((string) ($filters['status'] ?? '')));
        if ($status !== '' && in_array($status, ['DRAFT', 'SENT', 'PARTIALLY_RECEIVED', 'RECEIVED', 'CANCELLED'], true)) {
            $sql .= ' AND po.status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' GROUP BY po.id, s.company_name ORDER BY po.order_date DESC, po.id DESC';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $rows = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $orderedQty = (float) $row['ordered_qty'];
            $receivedQty = (float) $row['received_qty'];
            $remainingQty = max(0, $orderedQty - $receivedQty);

            $rows[] = [
                'id' => (int) $row['id'],
                'po_number' => (string) $row['po_number'],
                'supplier' => (string) $row['supplier'],
                'status' => (string) $row['status'],
                'status_label' => ucwords(strtolower(str_replace('_', ' ', (string) $row['status']))),
                'order_date' => (string) $row['order_date'],
                'expected_date' => (string) ($row['expected_date'] ?? ''),
                'line_count' => (int) $row['line_count'],
                'ordered_qty' => number_format($orderedQty, 0),
                'received_qty' => number_format($receivedQty, 0),
                'remaining_qty' => number_format($remainingQty, 0),
                'total_cost' => 'PHP ' . number_format((float) $row['total_cost'], 2),
                'can_send' => (string) $row['status'] === 'DRAFT',
                'can_receive' => in_array((string) $row['status'], ['DRAFT', 'SENT', 'PARTIALLY_RECEIVED'], true) && $remainingQty > 0,
                'can_cancel' => in_array((string) $row['status'], ['DRAFT', 'SENT'], true),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{label:string,value:string}>
     */
    private function purchaseOrderKpis(PDO $pdo): array
    {
        $openOrders = (int) $pdo->query(
            "SELECT COUNT(*) FROM purchase_orders WHERE status IN ('DRAFT', 'SENT', 'PARTIALLY_RECEIVED')"
        )->fetchColumn();
        $receivedOrders = (int) $pdo->query("SELECT COUNT(*) FROM purchase_orders WHERE status = 'RECEIVED'")->fetchColumn();
        $openValue = (float) $pdo->query(
            "SELECT COALESCE(SUM((poi.ordered_qty - poi.received_qty) * poi.unit_cost), 0)
             FROM purchase_orders po
             INNER JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
             WHERE po.status IN ('DRAFT', 'SENT', 'PARTIALLY_RECEIVED')"
        )->fetchColumn();

        return [
            ['label' => 'Open POs', 'value' => number_format($openOrders)],
            ['label' => 'Received POs', 'value' => number_format($receivedOrders)],
            ['label' => 'Open PO Value', 'value' => 'PHP ' . number_format($openValue, 2)],
        ];
    }

    /**
     * @return array<int, array{id:int,name:string,cost_price:string}>
     */
    private function purchaseOrderProductOptions(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT id, name, cost_price FROM products WHERE is_active = 1 ORDER BY name ASC');
        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'cost_price' => number_format((float) ($row['cost_price'] ?? 0), 2, '.', ''),
            ],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<int, array{product_id:int,quantity:float,unit_cost:float,notes:string}>
     */
    private function postedPurchaseOrderItems(): array
    {
        $productIds = $_POST['item_product_id'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $unitCosts = $_POST['item_unit_cost'] ?? [];
        $notes = $_POST['item_notes'] ?? [];

        if (!is_array($productIds) || !is_array($quantities) || !is_array($unitCosts)) {
            return [];
        }

        $items = [];
        foreach ($productIds as $index => $productId) {
            $productId = trim((string) $productId);
            $quantity = trim((string) ($quantities[$index] ?? ''));
            $unitCost = trim((string) ($unitCosts[$index] ?? ''));
            $note = trim((string) ($notes[$index] ?? ''));

            if ($productId === '' && $quantity === '' && $unitCost === '') {
                continue;
            }

            if ($productId === '' || !ctype_digit($productId) || $quantity === '' || !is_numeric($quantity) || (float) $quantity <= 0) {
                return [];
            }

            if ($unitCost === '' || !is_numeric($unitCost) || (float) $unitCost < 0) {
                return [];
            }

            $items[] = [
                'product_id' => (int) $productId,
                'quantity' => (float) $quantity,
                'unit_cost' => (float) $unitCost,
                'notes' => $note,
            ];
        }

        return $items;
    }

    private function nextPurchaseOrderNumber(PDO $pdo): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $statement = $pdo->prepare(
            'SELECT po_number
             FROM purchase_orders
             WHERE po_number LIKE :prefix
             ORDER BY po_number DESC
             LIMIT 1'
        );
        $statement->execute([':prefix' => $prefix . '%']);
        $latest = (string) ($statement->fetchColumn() ?: '');
        $sequence = 1;

        if ($latest !== '') {
            $lastDash = strrpos($latest, '-');
            if ($lastDash !== false) {
                $sequence = ((int) substr($latest, $lastDash + 1)) + 1;
            }
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<string, string> $fields
     */
    private function redirectWithPurchaseOrderError(string $message, array $fields): void
    {
        $query = http_build_query(array_merge(['error' => $message, 'openNewPo' => '1'], $fields));
        $this->redirect('/purchase-orders?' . $query);
    }

    /**
     * @param array<string, string> $fields
     */
    private function redirectWithCategoryError(string $message, array $fields): void
    {
        $query = http_build_query(array_merge(['error' => $message, 'openNewCategory' => '1'], $fields));
        $this->redirect('/categories?' . $query);
    }

    private function redirectWithCategoryEditError(int $categoryId, string $message): void
    {
        $query = http_build_query([
            'error' => $message,
            'edit_category_id' => $categoryId,
        ]);
        $this->redirect('/categories?' . $query);
    }

    /**
     * @param array<string, string> $fields
     */
    private function redirectWithSupplierError(string $message, array $fields): void
    {
        $query = http_build_query(array_merge(['error' => $message, 'openNewSupplier' => '1'], $fields));
        $this->redirect('/suppliers?' . $query);
    }

    private function redirectWithSupplierEditError(int $supplierId, string $message): void
    {
        $query = http_build_query([
            'error' => $message,
            'edit_supplier_id' => $supplierId,
        ]);
        $this->redirect('/suppliers?' . $query);
    }

    /**
     * @param array<string, string> $fields
     */
    private function redirectWithStockError(string $message, string $openModal, array $fields): void
    {
        $query = http_build_query(array_merge(['error' => $message, 'open' => $openModal], $fields));
        $this->redirect('/stock?' . $query);
    }

    /**
     * @param array<string, string> $fields
     */
    private function redirectWithAccountabilityError(string $message, array $fields): void
    {
        $query = http_build_query(array_merge(['error' => $message], $fields));
        $this->redirect('/accountability/new?' . $query);
    }

    private function defaultLocationId(PDO $pdo): int
    {
        return (int) $pdo->query('SELECT id FROM locations ORDER BY id ASC LIMIT 1')->fetchColumn();
    }

    private function defaultUserId(PDO $pdo): int
    {
        return (int) $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1')->fetchColumn();
    }

    private function currentQuantity(PDO $pdo, int $locationId, int $productId): float
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

    /**
     * @param array<string, string> $fields
     */
    private function redirectWithProductFormError(string $message, array $fields): void
    {
        $query = http_build_query(array_merge(['error' => $message, 'openNewItem' => '1'], $fields));
        $this->redirect('/products?' . $query);
    }

    private function redirectWithProductEditError(int $productId, string $message): void
    {
        $query = http_build_query([
            'error' => $message,
            'edit_product_id' => $productId,
        ]);
        $this->redirect('/products?' . $query);
    }

    private function loginErrorUrl(string $message, string $username, string $next): string
    {
        $query = http_build_query([
            'error' => $message,
            'username' => $username,
            'next' => $next,
        ]);

        return '/login?' . $query;
    }

    private function normalizeRedirectPath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '://')) {
            return '/';
        }

        return $path;
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['auth_employee_id'])
            && (int) $_SESSION['auth_employee_id'] > 0
            && (string) ($_SESSION['auth_employee_role'] ?? '') === 'internal';
    }

    private function passwordMatches(string $input, ?string $stored): bool
    {
        if ($stored === null || $stored === '') {
            return false;
        }

        $hashPrefixes = ['$2y$', '$2a$', '$2b$', '$argon2i$', '$argon2id$'];
        foreach ($hashPrefixes as $prefix) {
            if (str_starts_with($stored, $prefix)) {
                return password_verify($input, $stored);
            }
        }

        return hash_equals($stored, $input);
    }

    private function buildUrl(string $path): string
    {
        return function_exists('app_url') ? app_url($path) : $path;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $this->buildUrl($path));
        exit;
    }

    /**
     * @return array<int, array{label:string,value:string,subtext:string,icon:string}>
     */
    private function dashboardKpis(PDO $pdo): array
    {
        $totalItems = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
        $totalStockValue = (float) $pdo->query(
            'SELECT COALESCE(SUM(ib.quantity_on_hand * p.cost_price), 0)
             FROM products p
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1'
        )->fetchColumn();

        $lowStockItems = (int) $pdo->query(
            'SELECT COUNT(*)
             FROM products p
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1
               AND COALESCE(ib.quantity_on_hand, 0) > 0
               AND COALESCE(ib.quantity_on_hand, 0) <= COALESCE(p.reorder_level, 0)'
        )->fetchColumn();

        $outOfStockItems = (int) $pdo->query(
            'SELECT COUNT(*)
             FROM products p
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1
               AND COALESCE(ib.quantity_on_hand, 0) <= 0'
        )->fetchColumn();

        $pendingPurchaseOrders = (int) $pdo->query(
            "SELECT COUNT(*) FROM purchase_orders WHERE status IN ('DRAFT', 'SENT', 'PARTIALLY_RECEIVED')"
        )->fetchColumn();

        $pendingRequests = (int) $pdo->query(
            "SELECT COUNT(*) FROM sales_orders WHERE status IN ('DRAFT', 'CONFIRMED', 'PACKED', 'SHIPPED')"
        )->fetchColumn();

        return [
            ['label' => 'Total Items', 'value' => number_format($totalItems), 'subtext' => 'All active SKUs', 'icon' => '▦'],
            ['label' => 'Total Stock Value', 'value' => 'PHP ' . number_format($totalStockValue, 2), 'subtext' => 'Across all locations', 'icon' => '▤'],
            ['label' => 'Low Stock Items', 'value' => number_format($lowStockItems), 'subtext' => 'Below reorder point', 'icon' => '⚠'],
            ['label' => 'Out of Stock Items', 'value' => number_format($outOfStockItems), 'subtext' => 'Needs restock', 'icon' => '⛔'],
            ['label' => 'Pending POs', 'value' => number_format($pendingPurchaseOrders), 'subtext' => 'Awaiting receipt', 'icon' => '⧗'],
            ['label' => 'Pending Requests', 'value' => number_format($pendingRequests), 'subtext' => 'Awaiting fulfillment', 'icon' => '✦'],
        ];
    }

    /**
     * @return array<int, array{date:string,action:string,item:string,reference:string,qty:string,user:string,tone:string}>
     */
    private function recentActivities(PDO $pdo): array
    {
        $statement = $pdo->query(
            'SELECT
                m.id,
                DATE_FORMAT(m.moved_at, "%Y-%m-%d %H:%i") AS activity_date,
                m.movement_type,
                m.reference_type,
                m.reference_id,
                p.name AS item,
                m.quantity,
                COALESCE(u.full_name, "System") AS user_name
             FROM inventory_movements m
             INNER JOIN products p ON p.id = m.product_id
             LEFT JOIN users u ON u.id = m.moved_by
             WHERE p.is_active = 1
             ORDER BY m.moved_at DESC, m.id DESC
             LIMIT 4'
        );

        $activities = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $reference = 'MOV-' . str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT);
            if ($row['reference_type'] !== 'MANUAL' && $row['reference_id'] !== null) {
                $reference = $row['reference_type'] . '-' . $row['reference_id'];
            }

            $activities[] = [
                'date' => (string) $row['activity_date'],
                'action' => $this->movementLabel((string) $row['movement_type']),
                'item' => (string) $row['item'],
                'reference' => (string) $reference,
                'qty' => $this->movementQuantity((string) $row['movement_type'], (string) $row['quantity']),
                'user' => (string) $row['user_name'],
                'tone' => $this->movementTone((string) $row['movement_type']),
            ];
        }

        return $activities;
    }

    private function movementTone(string $movementType): string
    {
        return match ($movementType) {
            'PO_RECEIVE', 'TRANSFER_IN' => 'success',
            'SO_SHIP', 'TRANSFER_OUT' => 'danger',
            'ADJUSTMENT' => 'warning',
            default => 'info',
        };
    }

    /**
     * @return array<int, array{item:string,sku:string,qty:string,reorder:string}>
     */
    private function lowStockItems(PDO $pdo): array
    {
        $statement = $pdo->query(
            'SELECT
                p.name AS item,
                p.sku,
                COALESCE(ib.quantity_on_hand, 0) AS quantity_on_hand,
                COALESCE(p.reorder_level, 0) AS reorder_level
             FROM products p
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1
               AND COALESCE(ib.quantity_on_hand, 0) > 0
               AND COALESCE(ib.quantity_on_hand, 0) <= COALESCE(p.reorder_level, 0)
             ORDER BY quantity_on_hand ASC, p.name ASC
             LIMIT 5'
        );

        return array_map(
            static fn (array $row): array => [
                'item' => (string) $row['item'],
                'sku' => (string) $row['sku'],
                'qty' => (string) $row['quantity_on_hand'],
                'reorder' => (string) $row['reorder_level'],
            ],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<int, array{item:string,sku:string,qty:string,reorder:string}>
     */
    private function outOfStockItems(PDO $pdo): array
    {
        $statement = $pdo->query(
            'SELECT
                p.name AS item,
                p.sku,
                COALESCE(ib.quantity_on_hand, 0) AS quantity_on_hand,
                COALESCE(p.reorder_level, 0) AS reorder_level
             FROM products p
             LEFT JOIN inventory_balances ib ON ib.product_id = p.id
             WHERE p.is_active = 1
               AND COALESCE(ib.quantity_on_hand, 0) <= 0
             ORDER BY p.name ASC
             LIMIT 5'
        );

        return array_map(
            static fn (array $row): array => [
                'item' => (string) $row['item'],
                'sku' => (string) $row['sku'],
                'qty' => (string) $row['quantity_on_hand'],
                'reorder' => (string) $row['reorder_level'],
            ],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    private function movementLabel(string $movementType): string
    {
        return match ($movementType) {
            'PO_RECEIVE' => 'Stock In',
            'SO_SHIP' => 'Stock Out',
            'ADJUSTMENT' => 'Adjusted',
            'TRANSFER_OUT' => 'Transfer Out',
            'TRANSFER_IN' => 'Transfer In',
            'RESERVE' => 'Reserve',
            'RELEASE' => 'Release',
            default => $movementType,
        };
    }

    private function movementQuantity(string $movementType, string $quantity): string
    {
        $formatted = number_format((float) $quantity, 0);

        return match ($movementType) {
            'PO_RECEIVE', 'TRANSFER_IN' => '+' . $formatted,
            'SO_SHIP', 'TRANSFER_OUT' => '-' . $formatted,
            'ADJUSTMENT' => $formatted,
            'RESERVE', 'RELEASE' => $formatted,
            default => $formatted,
        };
    }
}
