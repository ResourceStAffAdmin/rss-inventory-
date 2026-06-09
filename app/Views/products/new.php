<?php

declare(strict_types=1);

/** @var array<int, array{id:int,name:string}> $categories */
/** @var array<int, array{id:int,name:string}> $suppliers */
/** @var array<string,string> $formValues */
/** @var string|null $errorMessage */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
?>
<style>
    .form-shell {
        max-width: 980px;
        margin: 0 auto;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-field.full {
        grid-column: 1 / -1;
    }
    .form-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }
    .form-input,
    .form-select,
    .form-textarea {
        border: 1px solid #dce5ef;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
    }
    .form-textarea {
        min-height: 96px;
        resize: vertical;
    }
    .form-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-top: 12px;
    }
    .btn {
        border-radius: 999px;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .btn.primary {
        background: #57c4ff;
        border-color: #57c4ff;
        color: #fff;
    }
    .btn.alt {
        background: #fff;
        border-color: #dce5ef;
        color: #475569;
    }
    .error-box {
        font-size: 12px;
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 8px 10px;
        margin-top: 10px;
    }
    @media (max-width: 900px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="ui-panel form-shell">
    <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">New Product</h2>
    <p class="panel-subtitle">Create an item and save it to your local inventory database.</p>

    <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
        <div class="error-box"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($buildUrl('/products'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
        <div class="form-grid">
            <label class="form-field">
                <span class="form-label">Product ID *</span>
                <input class="form-input" type="text" name="sku" required maxlength="60" value="<?= htmlspecialchars($formValues['sku'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="form-field">
                <span class="form-label">Name *</span>
                <input class="form-input" type="text" name="name" required maxlength="191" value="<?= htmlspecialchars($formValues['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="form-field">
                <span class="form-label">Category</span>
                <select class="form-select" name="category_id">
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"<?= (($formValues['category_id'] ?? '') === (string) $category['id']) ? ' selected' : '' ?>>
                            <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Supplier</span>
                <select class="form-select" name="supplier_id">
                    <option value="">Select supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= (int) $supplier['id'] ?>"<?= (($formValues['supplier_id'] ?? '') === (string) $supplier['id']) ? ' selected' : '' ?>>
                            <?= htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-field">
                <span class="form-label">Unit</span>
                <input class="form-input" type="text" name="unit_of_measure" maxlength="20" value="<?= htmlspecialchars($formValues['unit_of_measure'] ?? 'pcs', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="form-field">
                <span class="form-label">Reorder Level</span>
                <input class="form-input" type="number" step="0.001" min="0" name="reorder_level" value="<?= htmlspecialchars($formValues['reorder_level'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="form-field">
                <span class="form-label">Price</span>
                <input class="form-input" type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars($formValues['price'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="form-field full">
                <span class="form-label">Description</span>
                <textarea class="form-textarea" name="description"><?= htmlspecialchars($formValues['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
        </div>

        <div class="form-actions">
            <a href="<?= htmlspecialchars($buildUrl('/products'), ENT_QUOTES, 'UTF-8') ?>" class="btn alt">Cancel</a>
            <button class="btn primary" type="submit">Save Item</button>
        </div>
    </form>
</section>
