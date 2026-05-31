<?php

declare(strict_types=1);

/** @var string $description */
/** @var array<int, string> $tableHeaders */
/** @var array<int, array<string, string|int>> $tableRows */
/** @var array<int, array{label:string, value:string}> $moduleKpis */
/** @var string|null $searchPlaceholder */
/** @var string|null $notice */
/** @var string|null $errorMessage */
/** @var bool $isModalOpen */
/** @var array<int, array{id:int,name:string}> $categories */
/** @var array<int, array{id:int,name:string}> $suppliers */
/** @var array<string,string> $filters */
/** @var array<string,string> $formValues */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
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
    .module-search {
        border: 1px solid #e7edf4;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        color: #4b5563;
        outline: none;
        background: #fafcff;
        min-width: 220px;
    }
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
    .module-input {
        max-width: 120px;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }
    .summary-card {
        background: #fff;
        border: 1px solid #e7edf4;
        border-radius: 16px;
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eaf2ff;
        color: #2563eb;
        font-size: 14px;
        flex-shrink: 0;
    }
    .summary-card .label {
        color: #64748b;
        font-size: 12px;
        margin-bottom: 6px;
    }
    .summary-card .value {
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1;
    }
    .module-table .table-wrap {
        overflow-x: auto;
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
        width: min(760px, 100%);
        max-height: 92vh;
        overflow: auto;
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e5eaf0;
        padding: 16px;
    }
    .modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .modal-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .modal-field.full {
        grid-column: 1 / -1;
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
    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 900px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
        .module-header {
            flex-direction: column;
            align-items: stretch;
        }
        .module-actions {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }
        .module-filters {
            width: 100%;
        }
        .module-search,
        .module-select,
        .module-input,
        .module-actions .btn {
            width: 100%;
            max-width: none;
        }
        .table-actions {
            flex-wrap: nowrap;
        }
    }
    @media (max-width: 800px) {
        .modal-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="module-shell">
    <article class="ui-panel module-header">
        <div class="title-block">
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;"><?= htmlspecialchars($pageTitle ?? 'Products', ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="panel-subtitle"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="module-actions">
            <form class="module-filters" method="get" action="<?= htmlspecialchars($buildUrl('/products'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($searchPlaceholder !== null): ?>
                    <input
                        class="module-search"
                        type="search"
                        name="q"
                        placeholder="<?= htmlspecialchars($searchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                        value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php endif; ?>
                <select class="module-select" name="filter_category_id">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"<?= (($filters['category_id'] ?? '') === (string) $category['id']) ? ' selected' : '' ?>>
                            <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="module-select" name="filter_status">
                    <option value=""<?= (($filters['status'] ?? '') === '') ? ' selected' : '' ?>>All statuses</option>
                    <option value="in"<?= (($filters['status'] ?? '') === 'in') ? ' selected' : '' ?>>In Stock</option>
                    <option value="low"<?= (($filters['status'] ?? '') === 'low') ? ' selected' : '' ?>>Low Stock</option>
                    <option value="out"<?= (($filters['status'] ?? '') === 'out') ? ' selected' : '' ?>>Out of Stock</option>
                </select>
                <button class="btn btn-outline" type="submit">Filter</button>
                <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/products'), ENT_QUOTES, 'UTF-8') ?>">Reset</a>
            </form>
            <button class="btn" id="openNewItemModal" type="button">+ New Item</button>
        </div>
        <?php if ($notice !== null && $notice !== ''): ?>
            <p class="module-notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
            <p class="module-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </article>

    <?php if ($moduleKpis !== []): ?>
        <section class="summary-grid">
            <?php
            $kpiIcons = [
                'Total Items' => '▦',
                'Low Stock' => '⚠',
                'Out of Stock' => '⛔',
                'Total Stock Value' => '▤',
            ];
            ?>
            <?php foreach ($moduleKpis as $kpi): ?>
                <article class="summary-card">
                    <span class="summary-icon"><?= htmlspecialchars($kpiIcons[$kpi['label']] ?? '▣', ENT_QUOTES, 'UTF-8') ?></span>
                    <div>
                        <div class="label"><?= htmlspecialchars($kpi['label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="value"><?= htmlspecialchars($kpi['value'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <article class="ui-panel module-table">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <?php foreach ($tableHeaders as $header): ?>
                        <th><?= htmlspecialchars($header, ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php if ($tableRows === []): ?>
                    <tr>
                        <td colspan="9" class="empty-state">No products yet. Add your first item to get started.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tableRows as $row): ?>
                        <?php
                        $status = (string) $row['status'];
                        $statusClass = 'success';
                        if ($status === 'Low Stock') {
                            $statusClass = 'warning';
                        }
                        if ($status === 'Out of Stock') {
                            $statusClass = 'danger';
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $row['item'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['category'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['qty'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['reorder'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['supplier'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a
                                        class="icon-btn"
                                        href="<?= htmlspecialchars($buildUrl('/accountability/new?product_id=' . (int) $row['id']), ENT_QUOTES, 'UTF-8') ?>"
                                        title="Assign to employee"
                                        aria-label="Assign to employee"
                                    >↗</a>
                                    <button class="icon-btn" type="button" title="View">👁</button>
                                    <button class="icon-btn" type="button" title="Edit">✎</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span>Showing <?= count($tableRows) ?> items</span>
            <div class="pagination-controls">
                <button class="page-btn" type="button">Prev</button>
                <button class="page-btn active" type="button">1</button>
                <button class="page-btn" type="button">2</button>
                <button class="page-btn" type="button">3</button>
                <button class="page-btn" type="button">Next</button>
            </div>
        </div>
    </article>
</section>

<div id="newItemModal" class="modal-backdrop<?= $isModalOpen ? ' open' : '' ?>" aria-hidden="true">
    <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="newItemTitle">
        <h3 id="newItemTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">New Product</h3>
        <p class="panel-subtitle" style="margin-top:0;">Create an item and save it to your local inventory database.</p>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/products'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
            <div class="modal-grid">
                <label class="modal-field">
                    <span class="modal-label">SKU *</span>
                    <input class="modal-input" type="text" name="sku" required maxlength="60" value="<?= htmlspecialchars($formValues['sku'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Name *</span>
                    <input class="modal-input" type="text" name="name" required maxlength="191" value="<?= htmlspecialchars($formValues['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Category</span>
                    <select class="modal-select" name="category_id">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"<?= (($formValues['category_id'] ?? '') === (string) $category['id']) ? ' selected' : '' ?>>
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="modal-field">
                    <span class="modal-label">Supplier</span>
                    <select class="modal-select" name="supplier_id">
                        <option value="">Select supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"<?= (($formValues['supplier_id'] ?? '') === (string) $supplier['id']) ? ' selected' : '' ?>>
                                <?= htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="modal-field">
                    <span class="modal-label">Unit</span>
                    <input class="modal-input" type="text" name="unit_of_measure" maxlength="20" value="<?= htmlspecialchars($formValues['unit_of_measure'] ?? 'pcs', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Reorder Level</span>
                    <input class="modal-input" type="number" step="0.001" min="0" name="reorder_level" value="<?= htmlspecialchars($formValues['reorder_level'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Price</span>
                    <input class="modal-input" type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars($formValues['price'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field full">
                    <span class="modal-label">Description</span>
                    <textarea class="modal-textarea" name="description"><?= htmlspecialchars($formValues['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
            </div>

            <div class="modal-actions">
                <button class="btn btn-outline" id="closeNewItemModal" type="button">Cancel</button>
                <button class="btn" type="submit">Save Item</button>
            </div>
        </form>
    </article>
</div>

<script>
(() => {
    const modal = document.getElementById('newItemModal');
    const openBtn = document.getElementById('openNewItemModal');
    const closeBtn = document.getElementById('closeNewItemModal');

    if (!modal || !openBtn || !closeBtn) {
        return;
    }

    const openModal = () => modal.classList.add('open');
    const closeModal = () => modal.classList.remove('open');

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });
})();
</script>
