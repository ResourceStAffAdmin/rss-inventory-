<?php

declare(strict_types=1);

/** @var array<int, array<string, string|int|bool>> $orders */
/** @var array<int, array{label:string,value:string}> $kpis */
/** @var array<string,string> $filters */
/** @var array<int, array{id:int,name:string}> $suppliers */
/** @var array<int, array{id:int,name:string,cost_price:string}> $products */
/** @var string|null $notice */
/** @var string|null $errorMessage */
/** @var bool $isModalOpen */
/** @var array<string,string> $formValues */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
$statusTone = static function (string $status): string {
    return match ($status) {
        'DRAFT' => 'neutral',
        'SENT' => 'info',
        'PARTIALLY_RECEIVED' => 'warning',
        'RECEIVED' => 'success',
        'CANCELLED' => 'danger',
        default => 'neutral',
    };
};
?>
<style>
    .po-shell {
        display: grid;
        gap: 12px;
    }
    .po-header {
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
    .po-actions,
    .table-actions {
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
    .module-select {
        border: 1px solid #e7edf4;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        color: #4b5563;
        outline: none;
        background: #fafcff;
        min-width: 180px;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .summary-card {
        background: #fff;
        border: 1px solid #e7edf4;
        border-radius: 16px;
        padding: 14px;
    }
    .summary-card .label {
        color: #64748b;
        font-size: 12px;
        margin-bottom: 6px;
    }
    .summary-card .value {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }
    .module-notice,
    .module-error {
        width: 100%;
        margin: 8px 0 0;
        font-size: 12px;
        border-radius: 10px;
        padding: 8px 10px;
    }
    .module-notice {
        color: #0c4a6e;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
    }
    .module-error {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
    }
    .empty-state {
        text-align: center;
        font-size: 12px;
        color: #94a3b8;
        padding: 18px 0;
    }
    .inline-form {
        display: inline-flex;
    }
    .link-btn,
    .inline-form button {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }
    .inline-form .receive {
        color: #15803d;
        border-color: #bbf7d0;
        background: #dcfce7;
    }
    .inline-form .cancel {
        color: #b91c1c;
        border-color: #fecaca;
        background: #fee2e2;
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
        width: min(980px, 100%);
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
        font-weight: 700;
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
        min-height: 82px;
        resize: vertical;
    }
    .product-picker {
        position: relative;
    }
    .product-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        display: none;
        max-height: 230px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dce5ef;
        border-radius: 10px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.16);
        z-index: 30;
    }
    .product-results.open {
        display: block;
    }
    .product-option {
        width: 100%;
        border: 0;
        background: transparent;
        padding: 9px 11px;
        text-align: left;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .product-option:hover,
    .product-option:focus {
        background: #eff6ff;
        outline: none;
    }
    .product-option strong {
        color: #0f172a;
        font-size: 13px;
    }
    .product-option span,
    .product-empty {
        color: #64748b;
        font-size: 11px;
    }
    .product-empty {
        padding: 10px 12px;
    }
    .po-lines {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }
    .po-line {
        display: grid;
        grid-template-columns: minmax(220px, 2fr) minmax(90px, 0.6fr) minmax(110px, 0.7fr) minmax(150px, 1fr) 34px;
        gap: 8px;
        align-items: end;
    }
    .remove-line {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid rgba(255, 76, 97, 0.3);
        background: rgba(255, 76, 97, 0.12);
        color: #ff7b88;
        font-weight: 800;
        cursor: pointer;
    }
    .modal-actions {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-top: 12px;
        flex-wrap: wrap;
    }
    .modal-actions-right {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    @media (max-width: 1000px) {
        .summary-grid,
        .modal-grid,
        .po-line {
            grid-template-columns: 1fr;
        }
        .remove-line {
            width: 100%;
        }
    }
    @media (max-width: 900px) {
        .po-header {
            flex-direction: column;
            align-items: stretch;
        }
        .po-actions,
        .module-filters,
        .module-search,
        .module-select,
        .po-actions .btn {
            width: 100%;
        }
        .table-actions {
            flex-wrap: nowrap;
        }
    }
</style>

<section class="po-shell">
    <article class="ui-panel po-header">
        <div class="title-block">
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">Purchase Orders</h2>
            <p class="panel-subtitle">Create supplier orders, track open quantities, and receive items into stock.</p>
        </div>
        <div class="po-actions">
            <form class="module-filters" method="get" action="<?= htmlspecialchars($buildUrl('/purchase-orders'), ENT_QUOTES, 'UTF-8') ?>">
                <input
                    class="module-search"
                    type="search"
                    name="q"
                    placeholder="Search PO or supplier..."
                    value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <select class="module-select" name="filter_status">
                    <option value="">All statuses</option>
                    <?php foreach (['DRAFT', 'SENT', 'PARTIALLY_RECEIVED', 'RECEIVED', 'CANCELLED'] as $status): ?>
                        <option value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"<?= (($filters['status'] ?? '') === $status) ? ' selected' : '' ?>>
                            <?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', $status))), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline" type="submit">Filter</button>
                <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/purchase-orders'), ENT_QUOTES, 'UTF-8') ?>">Reset</a>
            </form>
            <button class="btn" id="openPoModal" type="button">New PO</button>
        </div>
        <?php if ($notice !== null && $notice !== ''): ?>
            <p class="module-notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
            <p class="module-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </article>

    <section class="summary-grid">
        <?php foreach ($kpis as $kpi): ?>
            <article class="summary-card">
                <div class="label"><?= htmlspecialchars($kpi['label'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="value"><?= htmlspecialchars($kpi['value'], ENT_QUOTES, 'UTF-8') ?></div>
            </article>
        <?php endforeach; ?>
    </section>

    <article class="ui-panel">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Expected</th>
                    <th>Lines</th>
                    <th>Ordered</th>
                    <th>Received</th>
                    <th>Remaining</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($orders === []): ?>
                    <tr>
                        <td colspan="11" class="empty-state">No purchase orders yet. Create one to start receiving supplier stock.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $order['po_number'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $order['supplier'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($statusTone((string) $order['status']), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $order['status_label'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars((string) $order['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(((string) $order['expected_date']) !== '' ? (string) $order['expected_date'] : '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $order['line_count'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $order['ordered_qty'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $order['received_qty'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $order['remaining_qty'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $order['total_cost'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="link-btn" href="<?= htmlspecialchars($buildUrl('/purchase-orders/' . (int) $order['id'] . '/print'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">View/Print</a>
                                    <?php if ($order['can_send']): ?>
                                        <form class="inline-form" method="post" action="<?= htmlspecialchars($buildUrl('/purchase-orders/' . (int) $order['id'] . '/send'), ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit">Send</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($order['can_receive']): ?>
                                        <form class="inline-form" method="post" action="<?= htmlspecialchars($buildUrl('/purchase-orders/' . (int) $order['id'] . '/receive'), ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="receive" type="submit">Receive</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($order['can_cancel']): ?>
                                        <form class="inline-form" method="post" action="<?= htmlspecialchars($buildUrl('/purchase-orders/' . (int) $order['id'] . '/cancel'), ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="cancel" type="submit">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<div id="poModal" class="modal-backdrop<?= $isModalOpen ? ' open' : '' ?>" aria-hidden="true">
    <article class="modal-card" role="dialog" aria-modal="true" aria-labelledby="poTitle">
        <h3 id="poTitle" class="panel-title" style="font-size:20px; margin:0 0 6px;">New Purchase Order</h3>
        <p class="panel-subtitle" style="margin-top:0;">Create a supplier order with one or more line items.</p>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/purchase-orders'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
            <div class="modal-grid">
                <label class="modal-field">
                    <span class="modal-label">Supplier *</span>
                    <select class="modal-select" name="supplier_id" required>
                        <option value="">Select supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"<?= (($formValues['supplier_id'] ?? '') === (string) $supplier['id']) ? ' selected' : '' ?>>
                                <?= htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="modal-field">
                    <span class="modal-label">Order Date *</span>
                    <input class="modal-input" type="date" name="order_date" required value="<?= htmlspecialchars($formValues['order_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field">
                    <span class="modal-label">Expected Date</span>
                    <input class="modal-input" type="date" name="expected_date" value="<?= htmlspecialchars($formValues['expected_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label class="modal-field full">
                    <span class="modal-label">Notes</span>
                    <textarea class="modal-textarea" name="notes"><?= htmlspecialchars($formValues['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
            </div>

            <div class="po-lines" id="poLines">
                <div class="po-line" data-line>
                    <label class="modal-field product-picker">
                        <span class="modal-label">Item *</span>
                        <input class="modal-input product-search" type="search" placeholder="Search item name or Product ID" autocomplete="off" required>
                        <input class="po-product" type="hidden" name="item_product_id[]">
                        <div class="product-results" role="listbox" aria-label="Item search results"></div>
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Qty *</span>
                        <input class="modal-input" type="number" name="item_quantity[]" min="1" step="1" required>
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Unit Cost *</span>
                        <input class="modal-input po-cost" type="number" name="item_unit_cost[]" min="0" step="0.01" required>
                    </label>
                    <label class="modal-field">
                        <span class="modal-label">Line Notes</span>
                        <input class="modal-input" type="text" name="item_notes[]" maxlength="255">
                    </label>
                    <button class="remove-line" type="button" title="Remove line" aria-label="Remove line">x</button>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn btn-outline" id="addPoLine" type="button">Add Line</button>
                <div class="modal-actions-right">
                    <button class="btn btn-outline" id="closePoModal" type="button">Cancel</button>
                    <button class="btn" type="submit">Save PO</button>
                </div>
            </div>
        </form>
    </article>
</div>

<script>
(() => {
    const products = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const modal = document.getElementById('poModal');
    const openBtn = document.getElementById('openPoModal');
    const closeBtn = document.getElementById('closePoModal');
    const addBtn = document.getElementById('addPoLine');
    const lines = document.getElementById('poLines');

    if (!modal || !openBtn || !closeBtn || !addBtn || !lines) {
        return;
    }

    const openModal = () => modal.classList.add('open');
    const closeModal = () => modal.classList.remove('open');

    const closeProductResults = (picker) => {
        const results = picker.querySelector('.product-results');
        if (results) {
            results.classList.remove('open');
            results.innerHTML = '';
        }
    };

    const bindProductPicker = (picker, onSelect) => {
        const search = picker.querySelector('.product-search');
        const productId = picker.querySelector('.po-product');
        const results = picker.querySelector('.product-results');
        if (!search || !productId || !results) {
            return;
        }

        const selectProduct = (product) => {
            search.value = `${product.name} - Product ID: ${product.id}`;
            search.setCustomValidity('');
            productId.value = String(product.id);
            closeProductResults(picker);
            onSelect(product);
        };

        const renderResults = () => {
            const query = productId.value !== '' ? '' : search.value.trim().toLowerCase();
            const matches = products.filter((product) => (
                query === ''
                || product.name.toLowerCase().includes(query)
                || String(product.id).includes(query)
            )).slice(0, 30);

            results.innerHTML = '';
            if (matches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'product-empty';
                empty.textContent = 'No matching items.';
                results.appendChild(empty);
            } else {
                matches.forEach((product) => {
                    const option = document.createElement('button');
                    const name = document.createElement('strong');
                    const meta = document.createElement('span');
                    option.type = 'button';
                    option.className = 'product-option';
                    name.textContent = product.name;
                    meta.textContent = `Product ID: ${product.id}`;
                    option.append(name, meta);
                    option.addEventListener('click', () => selectProduct(product));
                    results.appendChild(option);
                });
            }
            results.classList.add('open');
        };

        search.addEventListener('input', () => {
            productId.value = '';
            search.setCustomValidity('');
            renderResults();
        });
        search.addEventListener('focus', renderResults);
    };

    const bindLine = (line) => {
        const cost = line.querySelector('.po-cost');
        const remove = line.querySelector('.remove-line');
        const picker = line.querySelector('.product-picker');

        if (picker && cost) {
            bindProductPicker(picker, (product) => {
                if (product.cost_price && cost.value === '') {
                    cost.value = product.cost_price;
                }
            });
        }

        if (remove) {
            remove.addEventListener('click', () => {
                if (lines.querySelectorAll('[data-line]').length > 1) {
                    line.remove();
                }
            });
        }
    };

    lines.querySelectorAll('[data-line]').forEach(bindLine);

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    addBtn.addEventListener('click', () => {
        const firstLine = lines.querySelector('[data-line]');
        if (!firstLine) {
            return;
        }
        const clone = firstLine.cloneNode(true);
        clone.querySelectorAll('input').forEach((input) => {
            input.value = '';
        });
        bindLine(clone);
        lines.appendChild(clone);
    });

    modal.querySelector('form')?.addEventListener('submit', (event) => {
        const invalidSearch = Array.from(lines.querySelectorAll('[data-line]')).find((line) => {
            const productId = line.querySelector('.po-product');
            return !productId || productId.value === '';
        })?.querySelector('.product-search');

        if (invalidSearch) {
            event.preventDefault();
            invalidSearch.setCustomValidity('Select an item from the search results.');
            invalidSearch.reportValidity();
        }
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('click', (event) => {
        lines.querySelectorAll('.product-picker').forEach((picker) => {
            if (!picker.contains(event.target)) {
                closeProductResults(picker);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });
})();
</script>
