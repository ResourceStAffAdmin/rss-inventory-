<?php

declare(strict_types=1);

/** @var array<int, array<string, string|int>> $suppliers */
/** @var array<string,string> $filters */
/** @var string|null $notice */
/** @var string|null $errorMessage */
/** @var bool $isModalOpen */
/** @var array<string,string> $formValues */
/** @var array<string,string|int>|null $editSupplier */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
$supplierUrl = static function (array $overrides = []) use ($buildUrl, $filters): string {
    $params = [];
    if (($filters['q'] ?? '') !== '') {
        $params['q'] = $filters['q'];
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
            continue;
        }
        $params[$key] = (string) $value;
    }

    $query = http_build_query($params);
    return $buildUrl('/suppliers' . ($query !== '' ? '?' . $query : ''));
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
        align-items: center;
    }
    .table-actions form {
        margin: 0;
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
        width: min(620px, 100%);
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
    .modal-select {
        border: 1px solid #dce5ef;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 12px;
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
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">Suppliers</h2>
            <p class="panel-subtitle">Manage supplier profiles and link them to items.</p>
        </div>
        <div class="module-actions">
            <form class="module-filters" method="get" action="<?= htmlspecialchars($buildUrl('/suppliers'), ENT_QUOTES, 'UTF-8') ?>">
                <input
                    class="module-search"
                    type="search"
                    name="q"
                    placeholder="Search suppliers..."
                    value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <button class="btn btn-outline" type="submit">Search</button>
                <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/suppliers'), ENT_QUOTES, 'UTF-8') ?>">Reset</a>
            </form>
            <button class="btn" id="openSupplierModal" type="button">+ New Supplier</button>
        </div>
        <?php if ($notice !== null && $notice !== ''): ?>
            <p class="module-notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
            <p class="module-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </article>

    <article class="ui-panel">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Supplier</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Linked Items</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($suppliers === []): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No suppliers yet. Add one to track purchase sources.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <?php $isActive = (int) $supplier['is_active'] === 1; ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $supplier['supplier_code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $supplier['company_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $supplier['contact_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $supplier['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $supplier['linked_items'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge <?= $isActive ? 'success' : 'neutral' ?>">
                                    <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a
                                        class="icon-btn"
                                        href="<?= htmlspecialchars($supplierUrl(['edit_supplier_id' => (int) $supplier['id']]), ENT_QUOTES, 'UTF-8') ?>"
                                        title="Edit supplier"
                                        aria-label="Edit supplier"
                                    >✎</a>
                                    <form
                                        method="post"
                                        action="<?= htmlspecialchars($buildUrl('/suppliers/' . (int) $supplier['id'] . '/delete'), ENT_QUOTES, 'UTF-8') ?>"
                                        onsubmit="return confirm('Delete this supplier? Products using it will have their supplier cleared.');"
                                    >
                                        <button class="icon-btn" type="submit" title="Delete supplier" aria-label="Delete supplier">🗑</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span>Showing <?= count($suppliers) ?> suppliers</span>
        </div>
    </article>
</section>

<div id="supplierModal" class="modal-backdrop<?= $isModalOpen ? ' open' : '' ?>" aria-hidden="true">
    <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="supplierTitle">
        <h3 id="supplierTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">New Supplier</h3>
        <p class="panel-subtitle" style="margin-top:0;">Create a supplier profile for sourcing items.</p>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/suppliers'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
            <div class="modal-grid">
                <label class="modal-field">
                    <span class="modal-label">Supplier Code *</span>
                    <input class="modal-input" type="text" name="supplier_code" required maxlength="40" value="<?= htmlspecialchars($formValues['supplier_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Company Name *</span>
                    <input class="modal-input" type="text" name="company_name" required maxlength="150" value="<?= htmlspecialchars($formValues['company_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Contact Name</span>
                    <input class="modal-input" type="text" name="contact_name" maxlength="120" value="<?= htmlspecialchars($formValues['contact_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Email</span>
                    <input class="modal-input" type="email" name="email" maxlength="191" value="<?= htmlspecialchars($formValues['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Phone</span>
                    <input class="modal-input" type="text" name="phone" maxlength="40" value="<?= htmlspecialchars($formValues['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Status</span>
                    <select class="modal-select" name="is_active">
                        <option value="1"<?= (($formValues['is_active'] ?? '1') === '1') ? ' selected' : '' ?>>Active</option>
                        <option value="0"<?= (($formValues['is_active'] ?? '1') === '0') ? ' selected' : '' ?>>Inactive</option>
                    </select>
                </label>
            </div>
            <div class="modal-actions">
                <button class="btn btn-outline" id="closeSupplierModal" type="button">Cancel</button>
                <button class="btn" type="submit">Save Supplier</button>
            </div>
        </form>
    </article>
</div>

<?php if ($editSupplier !== null): ?>
    <div class="modal-backdrop open" aria-hidden="false">
        <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editSupplierTitle">
            <h3 id="editSupplierTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">Edit Supplier</h3>
            <p class="panel-subtitle" style="margin-top:0;">Update supplier details and status.</p>

            <form
                method="post"
                action="<?= htmlspecialchars($buildUrl('/suppliers/' . (int) $editSupplier['id'] . '/update'), ENT_QUOTES, 'UTF-8') ?>"
                style="margin-top:12px;"
            >
                <div class="modal-grid">
                    <label class="modal-field">
                        <span class="modal-label">Supplier Code *</span>
                        <input class="modal-input" type="text" name="supplier_code" required maxlength="40" value="<?= htmlspecialchars((string) $editSupplier['supplier_code'], ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Company Name *</span>
                        <input class="modal-input" type="text" name="company_name" required maxlength="150" value="<?= htmlspecialchars((string) $editSupplier['company_name'], ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Contact Name</span>
                        <input class="modal-input" type="text" name="contact_name" maxlength="120" value="<?= htmlspecialchars((string) $editSupplier['contact_name'], ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Email</span>
                        <input class="modal-input" type="email" name="email" maxlength="191" value="<?= htmlspecialchars((string) $editSupplier['email'], ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Phone</span>
                        <input class="modal-input" type="text" name="phone" maxlength="40" value="<?= htmlspecialchars((string) $editSupplier['phone'], ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Status</span>
                        <select class="modal-select" name="is_active">
                            <option value="1"<?= ((int) $editSupplier['is_active'] === 1) ? ' selected' : '' ?>>Active</option>
                            <option value="0"<?= ((int) $editSupplier['is_active'] === 0) ? ' selected' : '' ?>>Inactive</option>
                        </select>
                    </label>
                </div>
                <div class="modal-actions">
                    <a class="btn btn-outline" href="<?= htmlspecialchars($supplierUrl(['edit_supplier_id' => null]), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
                    <button class="btn" type="submit">Update Supplier</button>
                </div>
            </form>
        </article>
    </div>
<?php endif; ?>

<script>
(() => {
    const modal = document.getElementById('supplierModal');
    const openBtn = document.getElementById('openSupplierModal');
    const closeBtn = document.getElementById('closeSupplierModal');

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
