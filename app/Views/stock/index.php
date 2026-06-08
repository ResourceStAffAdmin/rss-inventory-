<?php

declare(strict_types=1);

/** @var array<int, array<string, string|int|float>> $movements */
/** @var array<string,string|int|float>|null $viewMovement */
/** @var array<string,string> $filters */
/** @var array<int, array{id:int,name:string,sku:string}> $products */
/** @var string|null $notice */
/** @var string|null $errorMessage */
/** @var string $openModal */
/** @var array<string,string> $formValues */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
$stockUrl = static function (array $overrides = []) use ($buildUrl, $filters): string {
    $params = [];
    if (($filters['q'] ?? '') !== '') {
        $params['q'] = $filters['q'];
    }
    if (($filters['type'] ?? '') !== '') {
        $params['filter_type'] = $filters['type'];
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
            continue;
        }
        $params[$key] = (string) $value;
    }

    $query = http_build_query($params);
    return $buildUrl('/stock' . ($query !== '' ? '?' . $query : ''));
};
?>
<style>
    .module-shell {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .module-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .title-block {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .module-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .module-filters {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
    }
    .module-search,
    .module-select,
    .module-input {
        border: 1px solid #e7edf4;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        color: #4b5563;
        outline: none;
        background: #fafcff;
    }
    .module-search {
        min-width: 220px;
    }
    .module-input {
        max-width: 140px;
    }
    .module-notice {
        margin-top: 8px;
        font-size: 12px;
        color: #0c4a6e;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 8px 10px;
    }
    .module-error {
        margin-top: 8px;
        font-size: 12px;
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 8px 10px;
    }
    .table-actions {
        display: inline-flex;
        gap: 6px;
    }
    .icon-btn {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        border-radius: 10px;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 10px;
        color: #64748b;
        font-size: 12px;
    }
    .pagination-controls {
        display: inline-flex;
        gap: 6px;
        align-items: center;
    }
    .page-btn {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        cursor: pointer;
        color: #475569;
    }
    .page-btn.active {
        background: #eaf2ff;
        border-color: #c7dafc;
        color: #2563eb;
        font-weight: 600;
    }
    .empty-state {
        text-align: center;
        font-size: 12px;
        color: #94a3b8;
        padding: 18px 0;
    }
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 12px;
    }
    .modal-backdrop.open {
        display: flex;
    }
    .modal-card {
        width: min(600px, 100%);
        max-height: 92vh;
        overflow: auto;
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5eaf0;
        padding: 16px;
    }
    .modal-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 10px;
    }
    .modal-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }
    .modal-input,
    .modal-select,
    .modal-textarea {
        border: 1px solid #dce5ef;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
    }
    .modal-textarea {
        min-height: 88px;
        resize: vertical;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 12px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }
    .detail-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px;
        background: #f8fafc;
    }
    .detail-item.full {
        grid-column: 1 / -1;
    }
    .detail-label {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .detail-value {
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }
    @media (max-width: 800px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="module-shell">
    <article class="ui-panel module-header">
        <div class="title-block">
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">Stock Management</h2>
            <p class="panel-subtitle">Record stock in, stock out, and adjustments with full traceability.</p>
        </div>
        <div class="module-actions">
            <button class="btn btn-success" id="openStockIn" type="button">Stock In</button>
            <button class="btn btn-danger" id="openStockOut" type="button">Stock Out</button>
            <button class="btn btn-info" id="openAdjust" type="button">Adjust</button>
            <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/purchase-orders?openNewPo=1'), ENT_QUOTES, 'UTF-8') ?>">Create PO</a>
        </div>
        <?php if ($notice !== null && $notice !== ''): ?>
            <p class="module-notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
            <p class="module-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </article>

    <article class="ui-panel">
        <form class="module-filters" method="get" action="<?= htmlspecialchars($buildUrl('/stock'), ENT_QUOTES, 'UTF-8') ?>">
            <input
                class="module-search"
                type="search"
                name="q"
                placeholder="Search items or reference..."
                value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
            <select class="module-select" name="filter_type">
                <option value="">All Types</option>
                <option value="PO_RECEIVE"<?= (($filters['type'] ?? '') === 'PO_RECEIVE') ? ' selected' : '' ?>>Stock In</option>
                <option value="SO_SHIP"<?= (($filters['type'] ?? '') === 'SO_SHIP') ? ' selected' : '' ?>>Stock Out</option>
                <option value="ADJUSTMENT"<?= (($filters['type'] ?? '') === 'ADJUSTMENT') ? ' selected' : '' ?>>Adjustment</option>
            </select>
            <button class="btn btn-outline" type="submit">Filter</button>
            <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/stock'), ENT_QUOTES, 'UTF-8') ?>">Reset</a>
        </form>

        <div class="table-wrap" style="margin-top:12px;">
            <table class="table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Previous Stock</th>
                    <th>New Stock</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($movements === []): ?>
                    <tr>
                        <td colspan="10" class="empty-state">No stock movements yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movements as $movement): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $movement['date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $movement['reference'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $movement['item'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= htmlspecialchars((string) $movement['tone'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $movement['type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars(number_format((float) $movement['qty'], 0), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(number_format((float) $movement['previous'], 0), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(number_format((float) $movement['new'], 0), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $movement['user'], ENT_QUOTES, 'UTF-8') ?></td>
                            <?php
                            $statusClass = ((string) $movement['status'] === 'COMPLETED') ? 'success' : 'neutral';
                            $statusLabel = ucfirst(strtolower((string) $movement['status']));
                            ?>
                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a
                                        class="icon-btn"
                                        href="<?= htmlspecialchars($stockUrl(['movement_id' => (int) $movement['id']]), ENT_QUOTES, 'UTF-8') ?>"
                                        title="View movement"
                                        aria-label="View movement"
                                    >👁</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span>Showing <?= count($movements) ?> movements</span>
        </div>
    </article>
</section>

<?php if ($viewMovement !== null): ?>
    <?php
    $movementStatusClass = ((string) $viewMovement['status'] === 'COMPLETED') ? 'success' : 'neutral';
    $movementStatusLabel = ucfirst(strtolower((string) $viewMovement['status']));
    ?>
    <div class="modal-backdrop open" aria-hidden="false">
        <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="movementTitle">
            <h3 id="movementTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">Stock Movement</h3>
            <p class="panel-subtitle" style="margin-top:0;"><?= htmlspecialchars((string) $viewMovement['reference'], ENT_QUOTES, 'UTF-8') ?></p>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Date</span>
                    <span class="detail-value"><?= htmlspecialchars((string) $viewMovement['date'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Type</span>
                    <span class="detail-value"><?= htmlspecialchars((string) $viewMovement['type'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item full">
                    <span class="detail-label">Item</span>
                    <span class="detail-value"><?= htmlspecialchars((string) $viewMovement['item'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $viewMovement['sku'], ENT_QUOTES, 'UTF-8') ?>)</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Quantity</span>
                    <span class="detail-value"><?= htmlspecialchars(number_format((float) $viewMovement['qty'], 0), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="badge <?= $movementStatusClass ?>"><?= htmlspecialchars($movementStatusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Previous Stock</span>
                    <span class="detail-value"><?= htmlspecialchars(number_format((float) $viewMovement['previous'], 0), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">New Stock</span>
                    <span class="detail-value"><?= htmlspecialchars(number_format((float) $viewMovement['new'], 0), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">User</span>
                    <span class="detail-value"><?= htmlspecialchars((string) $viewMovement['user'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Source</span>
                    <span class="detail-value"><?= htmlspecialchars((string) $viewMovement['source'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="detail-item full">
                    <span class="detail-label">Reason</span>
                    <span class="detail-value"><?= htmlspecialchars((string) $viewMovement['reason'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <div class="modal-actions">
                <a class="btn btn-outline" href="<?= htmlspecialchars($stockUrl(['movement_id' => null]), ENT_QUOTES, 'UTF-8') ?>">Close</a>
            </div>
        </article>
    </div>
<?php endif; ?>

<div id="stockInModal" class="modal-backdrop" aria-hidden="true">
    <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="stockInTitle">
        <h3 id="stockInTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">Stock In</h3>
        <p class="panel-subtitle" style="margin-top:0;">Add items into inventory.</p>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/stock'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
            <input type="hidden" name="movement" value="stock-in">
            <label class="modal-field">
                <span class="modal-label">Item *</span>
                <select class="modal-select" name="product_id" required>
                    <option value="">Select item</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int) $product['id'] ?>"<?= (($formValues['product_id'] ?? '') === (string) $product['id']) ? ' selected' : '' ?>>
                            <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="modal-field">
                <span class="modal-label">Quantity *</span>
                <input class="modal-input" type="number" name="quantity" min="1" step="1" required value="<?= htmlspecialchars($formValues['quantity'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="modal-field">
                <span class="modal-label">Reason</span>
                <textarea class="modal-textarea" name="reason"><?= htmlspecialchars($formValues['reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <div class="modal-actions">
                <button class="btn btn-outline" data-close="stock-in" type="button">Cancel</button>
                <button class="btn" type="submit">Save</button>
            </div>
        </form>
    </article>
</div>

<div id="stockOutModal" class="modal-backdrop" aria-hidden="true">
    <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="stockOutTitle">
        <h3 id="stockOutTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">Stock Out</h3>
        <p class="panel-subtitle" style="margin-top:0;">Remove items from inventory.</p>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/stock'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
            <input type="hidden" name="movement" value="stock-out">
            <label class="modal-field">
                <span class="modal-label">Item *</span>
                <select class="modal-select" name="product_id" required>
                    <option value="">Select item</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int) $product['id'] ?>"<?= (($formValues['product_id'] ?? '') === (string) $product['id']) ? ' selected' : '' ?>>
                            <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="modal-field">
                <span class="modal-label">Quantity *</span>
                <input class="modal-input" type="number" name="quantity" min="1" step="1" required value="<?= htmlspecialchars($formValues['quantity'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="modal-field">
                <span class="modal-label">Reason</span>
                <textarea class="modal-textarea" name="reason"><?= htmlspecialchars($formValues['reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <div class="modal-actions">
                <button class="btn btn-outline" data-close="stock-out" type="button">Cancel</button>
                <button class="btn" type="submit">Save</button>
            </div>
        </form>
    </article>
</div>

<div id="adjustModal" class="modal-backdrop" aria-hidden="true">
    <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="adjustTitle">
        <h3 id="adjustTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">Adjust Stock</h3>
        <p class="panel-subtitle" style="margin-top:0;">Update quantity and record the reason.</p>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/stock'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
            <input type="hidden" name="movement" value="adjust">
            <label class="modal-field">
                <span class="modal-label">Item *</span>
                <select class="modal-select" name="product_id" required>
                    <option value="">Select item</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int) $product['id'] ?>"<?= (($formValues['product_id'] ?? '') === (string) $product['id']) ? ' selected' : '' ?>>
                            <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="modal-field">
                <span class="modal-label">New Quantity *</span>
                <input class="modal-input" type="number" name="new_quantity" min="0" step="1" required value="<?= htmlspecialchars($formValues['new_quantity'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="modal-field">
                <span class="modal-label">Reason *</span>
                <textarea class="modal-textarea" name="reason" required><?= htmlspecialchars($formValues['reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <div class="modal-actions">
                <button class="btn btn-outline" data-close="adjust" type="button">Cancel</button>
                <button class="btn" type="submit">Save</button>
            </div>
        </form>
    </article>
</div>

<script>
(() => {
    const modals = {
        'stock-in': document.getElementById('stockInModal'),
        'stock-out': document.getElementById('stockOutModal'),
        'adjust': document.getElementById('adjustModal'),
    };

    const openModal = (key) => {
        if (modals[key]) {
            modals[key].classList.add('open');
        }
    };

    const closeModal = (key) => {
        if (modals[key]) {
            modals[key].classList.remove('open');
        }
    };

    const openStockIn = document.getElementById('openStockIn');
    const openStockOut = document.getElementById('openStockOut');
    const openAdjust = document.getElementById('openAdjust');

    if (openStockIn) {
        openStockIn.addEventListener('click', () => openModal('stock-in'));
    }
    if (openStockOut) {
        openStockOut.addEventListener('click', () => openModal('stock-out'));
    }
    if (openAdjust) {
        openAdjust.addEventListener('click', () => openModal('adjust'));
    }

    document.querySelectorAll('[data-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const key = button.getAttribute('data-close');
            if (key) {
                closeModal(key);
            }
        });
    });

    Object.entries(modals).forEach(([key, modal]) => {
        if (!modal) {
            return;
        }
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal(key);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        Object.keys(modals).forEach((key) => closeModal(key));
    });

    const openParam = <?= json_encode($openModal) ?>;
    if (openParam) {
        openModal(openParam);
    }
})();
</script>
