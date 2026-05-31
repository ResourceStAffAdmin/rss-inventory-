<?php

declare(strict_types=1);

/** @var array<int, array<string, string|int|float>> $categories */
/** @var array<string,string> $filters */
/** @var string|null $notice */
/** @var string|null $errorMessage */
/** @var bool $isModalOpen */
/** @var array<string,string> $formValues */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
$categoryIcon = static function (string $name): string {
    $normalized = strtolower($name);
    $iconKeywords = [
        'monitor.svg' => ['monitor', 'display', 'screen'],
        'cable.svg' => ['cable', 'adapter', 'connector'],
        'networking.svg' => ['network', 'router', 'switch', 'wifi'],
        'peripheral.svg' => ['peripheral', 'keyboard', 'mouse', 'input'],
        'storage.svg' => ['storage', 'drive', 'disk'],
        'accessory.svg' => ['accessor', 'stand', 'hub', 'organizer'],
    ];

    foreach ($iconKeywords as $icon => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return $icon;
            }
        }
    }

    return 'inventory.svg';
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
    }
    .category-cell {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 42px;
        white-space: nowrap;
    }
    .category-art {
        width: 40px;
        height: 40px;
        border: 1px solid #dbe7f5;
        border-radius: 10px;
        background: #f7fbff;
        object-fit: contain;
        flex: 0 0 40px;
        box-shadow: 0 5px 12px rgba(15, 23, 42, 0.08);
    }
    .category-name {
        font-weight: 600;
        color: #0f172a;
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
        width: min(560px, 100%);
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
    .modal-textarea,
    .modal-select {
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
</style>

<section class="module-shell">
    <article class="ui-panel module-header">
        <div class="title-block">
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">Categories</h2>
            <p class="panel-subtitle">Organize items into categories for faster search and reporting.</p>
        </div>
        <div class="module-actions">
            <form class="module-filters" method="get" action="<?= htmlspecialchars($buildUrl('/categories'), ENT_QUOTES, 'UTF-8') ?>">
                <input
                    class="module-search"
                    type="search"
                    name="q"
                    placeholder="Search categories..."
                    value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <button class="btn btn-outline" type="submit">Search</button>
                <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/categories'), ENT_QUOTES, 'UTF-8') ?>">Reset</a>
            </form>
            <button class="btn" id="openCategoryModal" type="button">+ New Category</button>
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
                    <th>Category</th>
                    <th>Description</th>
                    <th>Items</th>
                    <th>Stock Value</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($categories === []): ?>
                    <tr>
                        <td colspan="6" class="empty-state">No categories yet. Add one to organize your inventory.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $isActive = (int) $category['is_active'] === 1;
                        $categoryName = (string) $category['name'];
                        $iconUrl = $buildUrl('/images/category-icons/' . $categoryIcon($categoryName));
                        ?>
                        <tr>
                            <td>
                                <span class="category-cell">
                                    <img
                                        class="category-art"
                                        src="<?= htmlspecialchars($iconUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        alt=""
                                        aria-hidden="true"
                                    >
                                    <span class="category-name"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </td>
                            <td><?= htmlspecialchars((string) $category['description'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $category['item_count'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>PHP <?= htmlspecialchars(number_format((float) $category['stock_value'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge <?= $isActive ? 'success' : 'neutral' ?>">
                                    <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn" type="button" title="Edit">✎</button>
                                    <button class="icon-btn" type="button" title="Delete">🗑</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span>Showing <?= count($categories) ?> categories</span>
            <div class="pagination-controls">
                <button class="page-btn" type="button">Prev</button>
                <button class="page-btn active" type="button">1</button>
                <button class="page-btn" type="button">2</button>
                <button class="page-btn" type="button">Next</button>
            </div>
        </div>
    </article>
</section>

<div id="categoryModal" class="modal-backdrop<?= $isModalOpen ? ' open' : '' ?>" aria-hidden="true">
    <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="categoryTitle">
        <h3 id="categoryTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">New Category</h3>
        <p class="panel-subtitle" style="margin-top:0;">Create a category to group your inventory items.</p>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/categories'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
            <label class="modal-field">
                <span class="modal-label">Category Name *</span>
                <input class="modal-input" type="text" name="name" required maxlength="120" value="<?= htmlspecialchars($formValues['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="modal-field">
                <span class="modal-label">Description</span>
                <textarea class="modal-textarea" name="description"><?= htmlspecialchars($formValues['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <label class="modal-field">
                <span class="modal-label">Status</span>
                <select class="modal-select" name="is_active">
                    <option value="1"<?= (($formValues['is_active'] ?? '1') === '1') ? ' selected' : '' ?>>Active</option>
                    <option value="0"<?= (($formValues['is_active'] ?? '1') === '0') ? ' selected' : '' ?>>Inactive</option>
                </select>
            </label>
            <div class="modal-actions">
                <button class="btn btn-outline" id="closeCategoryModal" type="button">Cancel</button>
                <button class="btn" type="submit">Save Category</button>
            </div>
        </form>
    </article>
</div>

<script>
(() => {
    const modal = document.getElementById('categoryModal');
    const openBtn = document.getElementById('openCategoryModal');
    const closeBtn = document.getElementById('closeCategoryModal');

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
