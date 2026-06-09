<?php

declare(strict_types=1);

/** @var array<int, array{id:int,name:string,description:string}> $products */
/** @var array{product_id:int,equipment_name:string,description:string}|null $prefillItem */
/** @var array{id:int,name:string,meta:string}|null $selectedEmployee */
/** @var string|null $errorMessage */
/** @var array<string,string> $formValues */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};

$selectedEmployeeName = $selectedEmployee !== null ? $selectedEmployee['name'] : '';
$selectedEmployeeMeta = $selectedEmployee !== null ? $selectedEmployee['meta'] : '';
$productById = static function (string $productId) use ($products): ?array {
    foreach ($products as $product) {
        if ((string) $product['id'] === $productId) {
            return $product;
        }
    }

    return null;
};
?>
<style>
    .assignment-form {
        display: grid;
        gap: 12px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }
    .field.full {
        grid-column: 1 / -1;
    }
    .field span {
        color: #9db1d3;
        font-size: 12px;
        font-weight: 700;
    }
    .field input,
    .field input[type="file"],
    .field select,
    .field textarea,
    .item-row input,
    .item-row select {
        border: 1px solid rgba(104, 151, 255, 0.2);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        color: #e6eeff;
        background: rgba(11, 30, 56, 0.78);
        min-width: 0;
        color-scheme: dark;
    }
    .field input::placeholder,
    .field textarea::placeholder,
    .item-row input::placeholder {
        color: #7890ba;
    }
    .field textarea {
        min-height: 84px;
        resize: vertical;
    }
    .field-hint {
        color: #7890ba;
        font-size: 11px;
        line-height: 1.35;
    }
    .file-input::file-selector-button {
        border: 1px solid rgba(104, 151, 255, 0.35);
        border-radius: 8px;
        background: rgba(47, 120, 255, 0.18);
        color: #dbe8ff;
        padding: 7px 10px;
        margin-right: 10px;
        cursor: pointer;
    }
    .module-error {
        font-size: 12px;
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 8px 10px;
        margin: 0;
    }
    .employee-picker {
        position: relative;
    }
    .employee-meta {
        min-height: 16px;
        color: #9db1d3;
        font-size: 11px;
    }
    .employee-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        display: none;
        background: #102a4a;
        border: 1px solid rgba(104, 151, 255, 0.24);
        border-radius: 14px;
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
        max-height: 240px;
        overflow-y: auto;
        z-index: 20;
    }
    .employee-results.open {
        display: block;
    }
    .employee-option {
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        padding: 10px 12px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .employee-option:hover,
    .employee-option:focus {
        background: rgba(47, 120, 255, 0.12);
        outline: none;
    }
    .employee-option strong {
        color: #eef5ff;
        font-size: 13px;
    }
    .employee-option span {
        color: #9db1d3;
        font-size: 11px;
        font-weight: 500;
    }
    .employee-empty {
        padding: 10px 12px;
        color: #9db1d3;
        font-size: 12px;
    }
    .product-picker {
        position: relative;
        min-width: 0;
    }
    .product-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        display: none;
        background: #102a4a;
        border: 1px solid rgba(104, 151, 255, 0.24);
        border-radius: 14px;
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.22);
        max-height: 240px;
        overflow-y: auto;
        z-index: 20;
    }
    .product-results.open {
        display: block;
    }
    .product-option {
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        padding: 10px 12px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .product-option:hover,
    .product-option:focus {
        background: rgba(47, 120, 255, 0.12);
        outline: none;
    }
    .product-option strong {
        color: #eef5ff;
        font-size: 13px;
    }
    .product-option span,
    .product-empty {
        color: #9db1d3;
        font-size: 11px;
    }
    .product-empty {
        padding: 10px 12px;
    }
    .items-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .items-table {
        display: grid;
        gap: 8px;
    }
    .item-row {
        display: grid;
        grid-template-columns: 1.2fr 1.2fr 1fr 1fr 0.9fr 80px 36px;
        gap: 8px;
        align-items: center;
    }
    .remove-item {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid rgba(255, 76, 97, 0.35);
        background: rgba(11, 30, 56, 0.78);
        color: #ff6a79;
        cursor: pointer;
        font-weight: 800;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    @media (max-width: 1100px) {
        .item-row {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 780px) {
        .form-grid,
        .item-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<form class="assignment-form" method="post" action="<?= htmlspecialchars($buildUrl('/accountability'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" autocomplete="off">
    <article class="ui-panel">
        <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start;">
            <div>
                <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">New Asset Assignment</h2>
                <p class="panel-subtitle">Assign equipment to an active employee and prepare the printable form.</p>
            </div>
            <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/accountability'), ENT_QUOTES, 'UTF-8') ?>">Back</a>
        </div>
        <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
            <p class="module-error" style="margin-top:10px;"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </article>

    <article class="ui-panel">
        <div class="form-grid">
            <label class="field employee-picker">
                <span>Employee *</span>
                <input
                    id="employeeSearch"
                    type="search"
                    placeholder="Search active employee by name, email, position, or company"
                    value="<?= htmlspecialchars($selectedEmployeeName, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
                <input id="employeeId" type="hidden" name="employee_id" value="<?= htmlspecialchars($formValues['employee_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div id="employeeMeta" class="employee-meta"><?= htmlspecialchars($selectedEmployeeMeta, ENT_QUOTES, 'UTF-8') ?></div>
                <div id="employeeResults" class="employee-results" role="listbox" aria-label="Employee search results"></div>
            </label>
            <label class="field">
                <span>Date Borrowed/Received *</span>
                <input type="date" name="assigned_date" required value="<?= htmlspecialchars($formValues['assigned_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="field">
                <span>Date Returned</span>
                <input type="date" name="returned_date" value="<?= htmlspecialchars($formValues['returned_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label class="field full">
                <span>Notes</span>
                <textarea name="notes"><?= htmlspecialchars($formValues['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <label class="field full">
                <span>Attachment File</span>
                <input class="file-input" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                <small class="field-hint">Optional proof or signed document. PDF, JPG, PNG, or WebP up to 10 MB.</small>
            </label>
        </div>
    </article>

    <article class="ui-panel">
        <div class="items-header">
            <div>
                <h3 class="panel-title">Equipment Items</h3>
                <p class="panel-subtitle">Choose a product when available, then enter the exact model/brand and serial number from the issued equipment.</p>
            </div>
            <button class="btn btn-outline" type="button" id="addItem">Add Item</button>
        </div>
        <div class="items-table" id="itemsTable" style="margin-top:12px;">
            <?php for ($index = 0; $index < 4; $index++): ?>
                <?php
                $isPrefilled = $index === 0 && $prefillItem !== null;
                $selectedProductId = $isPrefilled ? (string) $prefillItem['product_id'] : '';
                $selectedProduct = $productById($selectedProductId);
                ?>
                <div class="item-row">
                    <div class="product-picker">
                        <input class="product-search" type="search" placeholder="Search product name or Product ID" autocomplete="off" value="<?= htmlspecialchars($selectedProduct !== null ? $selectedProduct['name'] . ' - Product ID: ' . $selectedProduct['id'] : '', ENT_QUOTES, 'UTF-8') ?>">
                        <input class="product-id" type="hidden" name="item_product_id[]" value="<?= htmlspecialchars($selectedProductId, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="product-results" role="listbox" aria-label="Product search results"></div>
                    </div>
                    <input type="text" name="item_equipment_name[]" placeholder="Equipment / Model / Brand<?= $index === 0 ? ' *' : '' ?>" value="<?= htmlspecialchars($isPrefilled ? $prefillItem['equipment_name'] : '', ENT_QUOTES, 'UTF-8') ?>"<?= $index === 0 ? ' required' : '' ?>>
                    <input type="text" name="item_description[]" placeholder="Description" value="<?= htmlspecialchars($isPrefilled ? $prefillItem['description'] : '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="text" name="item_serial_number[]" placeholder="Serial Number">
                    <input type="text" name="item_reason[]" placeholder="Reason">
                    <input type="number" name="item_quantity[]" min="1" step="1" value="1" title="Quantity">
                    <button class="remove-item" type="button" title="Remove item">x</button>
                </div>
            <?php endfor; ?>
        </div>
    </article>

    <div class="form-actions">
        <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/accountability'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
        <button class="btn" type="submit">Save Assignment</button>
    </div>
</form>

<template id="itemTemplate">
    <div class="item-row">
        <div class="product-picker">
            <input class="product-search" type="search" placeholder="Search product name or Product ID" autocomplete="off">
            <input class="product-id" type="hidden" name="item_product_id[]">
            <div class="product-results" role="listbox" aria-label="Product search results"></div>
        </div>
        <input type="text" name="item_equipment_name[]" placeholder="Equipment / Model / Brand">
        <input type="text" name="item_description[]" placeholder="Description">
        <input type="text" name="item_serial_number[]" placeholder="Serial Number">
        <input type="text" name="item_reason[]" placeholder="Reason">
        <input type="number" name="item_quantity[]" min="1" step="1" value="1" title="Quantity">
        <button class="remove-item" type="button" title="Remove item">x</button>
    </div>
</template>

<script>
(() => {
    const products = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const table = document.getElementById('itemsTable');
    const template = document.getElementById('itemTemplate');
    const addItem = document.getElementById('addItem');
    const employeeSearch = document.getElementById('employeeSearch');
    const employeeId = document.getElementById('employeeId');
    const employeeMeta = document.getElementById('employeeMeta');
    const employeeResults = document.getElementById('employeeResults');

    const closeProductResults = (picker) => {
        const results = picker.querySelector('.product-results');
        if (results) {
            results.classList.remove('open');
            results.innerHTML = '';
        }
    };

    const bindProductPicker = (picker) => {
        const search = picker.querySelector('.product-search');
        const productId = picker.querySelector('.product-id');
        const results = picker.querySelector('.product-results');
        if (!search || !productId || !results) {
            return;
        }

        const selectProduct = (product) => {
            search.value = `${product.name} - Product ID: ${product.id}`;
            productId.value = String(product.id);
            closeProductResults(picker);
        };

        const renderResults = () => {
            const query = productId.value !== '' ? '' : search.value.trim().toLowerCase();
            const matches = products.filter((product) => (
                query === ''
                || product.name.toLowerCase().includes(query)
                || String(product.id).includes(query)
                || product.description.toLowerCase().includes(query)
            )).slice(0, 30);

            results.innerHTML = '';
            if (matches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'product-empty';
                empty.textContent = 'No matching products.';
                results.appendChild(empty);
            } else {
                matches.forEach((product) => {
                    const option = document.createElement('button');
                    const name = document.createElement('strong');
                    const meta = document.createElement('span');
                    option.type = 'button';
                    option.className = 'product-option';
                    name.textContent = product.name;
                    meta.textContent = `Product ID: ${product.id} - ${product.description}`;
                    option.append(name, meta);
                    option.addEventListener('click', () => selectProduct(product));
                    results.appendChild(option);
                });
            }
            results.classList.add('open');
        };

        search.addEventListener('input', () => {
            productId.value = '';
            renderResults();
        });
        search.addEventListener('focus', renderResults);
    };

    if (table && template && addItem) {
        table.querySelectorAll('.product-picker').forEach(bindProductPicker);

        addItem.addEventListener('click', () => {
            const row = template.content.firstElementChild.cloneNode(true);
            bindProductPicker(row.querySelector('.product-picker'));
            table.appendChild(row);
        });

        table.addEventListener('click', (event) => {
            const button = event.target.closest('.remove-item');
            if (!button) {
                return;
            }
            const rows = table.querySelectorAll('.item-row');
            if (rows.length <= 1) {
                return;
            }
            button.closest('.item-row').remove();
        });

        document.addEventListener('click', (event) => {
            table.querySelectorAll('.product-picker').forEach((picker) => {
                if (!picker.contains(event.target)) {
                    closeProductResults(picker);
                }
            });
        });
    }

    if (!employeeSearch || !employeeId || !employeeMeta || !employeeResults) {
        return;
    }

    let searchToken = 0;

    const closeResults = () => {
        employeeResults.classList.remove('open');
        employeeResults.innerHTML = '';
    };

    const selectEmployee = (employee) => {
        employeeSearch.value = employee.name;
        employeeId.value = String(employee.id);
        employeeMeta.textContent = employee.meta || '';
        closeResults();
    };

    const renderResults = (employees) => {
        employeeResults.innerHTML = '';
        if (!employees.length) {
            employeeResults.innerHTML = '<div class="employee-empty">No matching active employees.</div>';
            employeeResults.classList.add('open');
            return;
        }

        employees.forEach((employee) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'employee-option';
            button.innerHTML = `<strong>${employee.name}</strong><span>${employee.meta || ''}</span>`;
            button.addEventListener('click', () => selectEmployee(employee));
            employeeResults.appendChild(button);
        });
        employeeResults.classList.add('open');
    };

    employeeSearch.addEventListener('input', async () => {
        const query = employeeSearch.value.trim();
        employeeId.value = '';
        employeeMeta.textContent = '';

        if (query.length < 2) {
            closeResults();
            return;
        }

        const token = ++searchToken;
        try {
            const response = await fetch(`<?= htmlspecialchars($buildUrl('/employees/search'), ENT_QUOTES, 'UTF-8') ?>?active_only=1&q=${encodeURIComponent(query)}`);
            if (!response.ok) {
                closeResults();
                return;
            }
            const employees = await response.json();
            if (token !== searchToken) {
                return;
            }
            renderResults(Array.isArray(employees) ? employees : []);
        } catch (error) {
            closeResults();
        }
    });

    employeeSearch.addEventListener('focus', () => {
        if (employeeSearch.value.trim().length >= 2 && employeeResults.innerHTML !== '') {
            employeeResults.classList.add('open');
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.employee-picker')) {
            closeResults();
        }
    });
})();
</script>
