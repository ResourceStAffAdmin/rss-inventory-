<?php

declare(strict_types=1);

/** @var array<int,array{label:string,value:string,subtext:string,icon:string}> $kpis */
/** @var array<int,array{date:string,action:string,item:string,reference:string,qty:string,user:string,tone:string}> $recentActivities */
/** @var array<int,array{item:string,sku:string,qty:string,reorder:string}> $lowStockItems */
/** @var array<int,array{item:string,sku:string,qty:string,reorder:string}> $outOfStockItems */

$kpiTone = static function (string $label): string {
    return match ($label) {
        'Total Stock Value' => 'cyan',
        'Low Stock Items' => 'warning',
        'Out of Stock Items' => 'danger',
        'Pending POs' => 'blue',
        'Pending Requests' => 'violet',
        default => 'blue',
    };
};
$dashboardIcons = [
    'Total Items' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.8 8 4.4v9.6l-8 4.4-8-4.4V7.2l8-4.4Z"/><path d="m4.4 7.4 7.6 4.2 7.6-4.2"/><path d="M12 21.2v-9.6"/></svg>',
    'Total Stock Value' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M6 9h.01M18 15h.01"/><circle cx="12" cy="12" r="3"/></svg>',
    'Low Stock Items' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
    'Out of Stock Items' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.8 8 4.4v9.6l-8 4.4-8-4.4V7.2l8-4.4Z"/><path d="m4.4 7.4 7.6 4.2 7.6-4.2"/><path d="M12 11.6v9.6"/><path d="m9 14 6 6m0-6-6 6"/></svg>',
    'Pending POs' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 3.5h6V6H9z"/><path d="M8.5 10h7M8.5 14h7M8.5 18h4"/></svg>',
    'Pending Requests' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.3 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H7"/><path d="m14 5 2-2 2 2M16 3v7"/></svg>',
];
$quickActionIcons = [
    'stock-in' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M4 14v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/></svg>',
    'stock-out' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3"/><path d="m7 8 5-5 5 5"/><path d="M4 14v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/></svg>',
    'new-item' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.8 8 4.4v9.6l-8 4.4-8-4.4V7.2l8-4.4Z"/><path d="m4.4 7.4 7.6 4.2 7.6-4.2"/><path d="M12 11.6v9.6"/><path d="M16.5 3.8v5M14 6.3h5"/></svg>',
    'purchase-order' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.3 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 8H7"/><path d="M14 4h5M16.5 1.5v5"/></svg>',
];
$emptyStockIcon = '<svg viewBox="0 0 96 80" fill="none" aria-hidden="true"><path d="m18 24 30-15 30 15-30 15-30-15Z" fill="#36c9a0"/><path d="M18 24v31l30 16V39L18 24Z" fill="#168a72"/><path d="M78 24v31L48 71V39l30-15Z" fill="#28ad8b"/><circle cx="74" cy="57" r="14" fill="#42e58d"/><path d="m68 57 4 4 8-9" stroke="#063b32" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<style>
    .dashboard-layout {
        display: grid;
        grid-template-columns: minmax(0, 2.1fr) minmax(320px, 0.95fr);
        gap: 12px;
    }
    .left-stack,
    .right-stack {
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-width: 0;
    }
    .dashboard-hero {
        min-height: 138px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        position: relative;
        overflow: hidden;
        padding: 24px;
        background:
            linear-gradient(90deg, rgba(18, 67, 116, 0.96), rgba(11, 47, 91, 0.9)),
            radial-gradient(circle at 88% 8%, rgba(67, 183, 255, 0.3), transparent 42%),
            radial-gradient(circle at 58% 110%, rgba(37, 228, 255, 0.13), transparent 44%);
        border-color: rgba(139, 190, 255, 0.34);
        box-shadow: 0 18px 38px rgba(3, 18, 42, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    .dashboard-hero::before,
    .dashboard-hero::after {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 470px;
        height: 280px;
        border: 1px solid rgba(139, 207, 255, 0.34);
        border-radius: 50%;
        transform: rotate(-16deg);
        pointer-events: none;
    }
    .dashboard-hero::after {
        right: -20px;
        top: 36px;
        width: 390px;
        height: 170px;
        border-color: rgba(37, 228, 255, 0.2);
    }
    .hero-copy {
        position: relative;
        z-index: 1;
    }
    .dashboard-title {
        font-size: 26px;
        font-weight: 800;
        margin: 0;
        letter-spacing: 0;
        color: #f8fbff;
    }
    .dashboard-subtitle {
        margin: 7px 0 0;
        color: #d4e5fb;
        font-size: 13px;
    }
    .last-updated {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: #d3e4f9;
        background: rgba(15, 61, 110, 0.66);
        border: 1px solid rgba(139, 207, 255, 0.3);
        border-radius: 999px;
        padding: 8px 12px;
        margin-top: 20px;
    }
    .last-updated::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #39e47b;
        box-shadow: 0 0 16px rgba(57, 228, 123, 0.55);
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .kpi-card {
        background:
            radial-gradient(circle at 12% 0%, rgba(67, 183, 255, 0.18), transparent 42%),
            linear-gradient(180deg, rgba(18, 67, 116, 0.96), rgba(11, 43, 83, 0.98));
        border: 1px solid rgba(139, 190, 255, 0.3);
        border-radius: 10px;
        padding: 18px;
        min-height: 104px;
        display: grid;
        grid-template-columns: 42px 1fr;
        gap: 14px;
        align-items: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 16px 34px rgba(3, 18, 42, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    .kpi-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), transparent 44%);
        pointer-events: none;
    }
    .kpi-card > * {
        position: relative;
        z-index: 1;
    }
    .kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #9bdcff;
        background: rgba(67, 183, 255, 0.18);
        border: 1px solid rgba(139, 207, 255, 0.32);
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }
    .kpi-icon svg {
        width: 24px;
        height: 24px;
    }
    .kpi-card.cyan .kpi-icon {
        color: #7cf5ff;
        background: rgba(37, 228, 255, 0.16);
        border-color: rgba(37, 228, 255, 0.3);
    }
    .kpi-card.warning .kpi-icon {
        color: #ffd680;
        background: rgba(255, 179, 62, 0.16);
        border-color: rgba(255, 179, 62, 0.32);
    }
    .kpi-card.danger .kpi-icon {
        color: #ff9aa5;
        background: rgba(255, 76, 97, 0.16);
        border-color: rgba(255, 76, 97, 0.32);
    }
    .kpi-card.violet .kpi-icon {
        color: #c9b7ff;
        background: rgba(122, 92, 255, 0.18);
        border-color: rgba(163, 139, 255, 0.32);
    }
    .kpi-label {
        font-size: 12px;
        color: #c7d9f1;
        margin-bottom: 4px;
    }
    .kpi-value {
        font-size: 25px;
        font-weight: 800;
        line-height: 1;
        color: #f7fbff;
    }
    .kpi-subtext {
        font-size: 11px;
        color: #aecaeb;
        margin-top: 7px;
    }
    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 4px;
    }
    .panel-link {
        font-size: 11px;
        color: #6ea2ff;
        text-decoration: none;
        font-weight: 800;
    }
    .table-muted {
        color: #7f93ba;
    }
    .empty-state {
        text-align: center;
        font-size: 12px;
        color: #adc0dc;
        padding: 20px 0;
    }
    .empty-visual {
        min-height: 132px;
        display: grid;
        place-items: center;
        text-align: center;
        color: #adc0dc;
    }
    .empty-box {
        width: 96px;
        height: 80px;
        margin: 4px auto 12px;
        filter: drop-shadow(0 16px 24px rgba(0, 0, 0, 0.26));
    }
    .empty-box svg {
        display: block;
        width: 100%;
        height: 100%;
    }
    .empty-title {
        color: #f7fbff;
        font-weight: 800;
        margin-bottom: 3px;
    }
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .quick-action {
        min-height: 70px;
        border: 1px solid rgba(104, 151, 255, 0.18);
        border-radius: 10px;
        padding: 13px;
        text-decoration: none;
        color: #f7fbff;
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: 12px;
        align-items: center;
        background: rgba(5, 19, 37, 0.9);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .quick-action:hover {
        transform: translateY(-1px);
        border-color: rgba(104, 151, 255, 0.38);
    }
    .quick-action.success {
        background: linear-gradient(135deg, rgba(13, 107, 75, 0.72), rgba(7, 56, 49, 0.78));
        border-color: rgba(67, 230, 146, 0.34);
    }
    .quick-action.danger {
        background: linear-gradient(135deg, rgba(132, 30, 54, 0.72), rgba(62, 15, 34, 0.82));
        border-color: rgba(255, 83, 104, 0.34);
    }
    .quick-action-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.24);
        background: rgba(255, 255, 255, 0.1);
    }
    .quick-action-icon svg {
        width: 24px;
        height: 24px;
    }
    .quick-action.blue {
        background: linear-gradient(135deg, rgba(22, 72, 165, 0.7), rgba(7, 33, 81, 0.86));
        border-color: rgba(54, 119, 255, 0.34);
    }
    .quick-action.violet {
        background: linear-gradient(135deg, rgba(91, 45, 151, 0.72), rgba(43, 22, 78, 0.86));
        border-color: rgba(163, 93, 255, 0.34);
    }
    .quick-action-title {
        display: block;
        font-size: 13px;
        font-weight: 800;
    }
    .quick-action-subtitle {
        display: block;
        margin-top: 3px;
        color: rgba(229, 238, 255, 0.72);
        font-size: 10px;
        line-height: 1.35;
    }
    .qty-positive {
        color: #55ef91 !important;
    }
    .qty-negative {
        color: #ff6a79 !important;
    }
    @media (max-width: 1200px) {
        .dashboard-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 920px) {
        .kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 620px) {
        .dashboard-hero {
            min-height: 160px;
            padding: 18px;
        }
        .dashboard-title {
            font-size: 22px;
        }
        .kpi-grid,
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
        .kpi-card {
            min-height: 98px;
        }
    }
</style>

<section class="dashboard-layout">
    <div class="left-stack">
        <section class="ui-panel dashboard-hero">
            <div class="hero-copy">
                <h2 class="dashboard-title">Welcome back, Admin!</h2>
                <p class="dashboard-subtitle">Here's what's happening with your inventory today.</p>
                <span class="last-updated">Last updated <?= htmlspecialchars(date('g:i A'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </section>

        <section class="kpi-grid">
            <?php foreach ($kpis as $kpi): ?>
                <?php $tone = $kpiTone($kpi['label']); ?>
                <article class="kpi-card <?= htmlspecialchars($tone, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="kpi-icon" aria-hidden="true"><?= $dashboardIcons[$kpi['label']] ?? $dashboardIcons['Total Items'] ?></span>
                    <div>
                        <div class="kpi-label"><?= htmlspecialchars($kpi['label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="kpi-value"><?= htmlspecialchars($kpi['value'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="kpi-subtext"><?= htmlspecialchars($kpi['subtext'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="ui-panel">
            <div class="panel-heading">
                <h3 class="panel-title">Recent Activity</h3>
                <a class="panel-link" href="<?= htmlspecialchars(app_url('/history'), ENT_QUOTES, 'UTF-8') ?>">View all</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Item</th>
                        <th>Reference</th>
                        <th>Qty</th>
                        <th>User</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($recentActivities === []): ?>
                        <tr>
                            <td colspan="6" class="empty-state">No activity yet. Stock movements will appear here.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <?php $qtyClass = str_starts_with($activity['qty'], '-') ? 'qty-negative' : (str_starts_with($activity['qty'], '+') ? 'qty-positive' : ''); ?>
                            <tr>
                                <td><?= htmlspecialchars($activity['date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge <?= htmlspecialchars($activity['tone'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($activity['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars($activity['item'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="table-muted"><?= htmlspecialchars($activity['reference'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="<?= htmlspecialchars($qtyClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($activity['qty'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($activity['user'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="right-stack">
        <section class="ui-panel">
            <div class="panel-heading">
                <h3 class="panel-title">Low Stock Items</h3>
                <a class="panel-link" href="<?= htmlspecialchars(app_url('/low-stock'), ENT_QUOTES, 'UTF-8') ?>">View all</a>
            </div>
            <?php if ($lowStockItems === []): ?>
                <div class="empty-visual">
                    <div>
                        <div class="empty-box"><?= $emptyStockIcon ?></div>
                        <div class="empty-title">You're all caught up.</div>
                        <div>No low stock items.</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Product ID</th>
                            <th>Qty</th>
                            <th>Reorder</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['item'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="table-muted"><?= htmlspecialchars($item['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['qty'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['reorder'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="ui-panel">
            <div class="panel-heading">
                <h3 class="panel-title">Out of Stock</h3>
                <a class="panel-link" href="<?= htmlspecialchars(app_url('/low-stock'), ENT_QUOTES, 'UTF-8') ?>">View all</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Product ID</th>
                        <th>Qty</th>
                        <th>Reorder</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($outOfStockItems === []): ?>
                        <tr>
                            <td colspan="4" class="empty-state">No items are fully out of stock.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($outOfStockItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['item'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="table-muted"><?= htmlspecialchars($item['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['qty'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['reorder'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-panel quick-actions">
            <div class="panel-heading">
                <h3 class="panel-title">Quick Actions</h3>
            </div>
            <div class="quick-actions-grid">
                <a class="quick-action success" href="<?= htmlspecialchars(app_url('/stock?open=stock-in'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="quick-action-icon" aria-hidden="true"><?= $quickActionIcons['stock-in'] ?></span>
                    <span>
                        <span class="quick-action-title">Stock In</span>
                        <span class="quick-action-subtitle">Record incoming stock</span>
                    </span>
                </a>
                <a class="quick-action danger" href="<?= htmlspecialchars(app_url('/stock?open=stock-out'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="quick-action-icon" aria-hidden="true"><?= $quickActionIcons['stock-out'] ?></span>
                    <span>
                        <span class="quick-action-title">Stock Out</span>
                        <span class="quick-action-subtitle">Record outgoing stock</span>
                    </span>
                </a>
                <a class="quick-action blue" href="<?= htmlspecialchars(app_url('/products?openNewItem=1'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="quick-action-icon" aria-hidden="true"><?= $quickActionIcons['new-item'] ?></span>
                    <span>
                        <span class="quick-action-title">New Item</span>
                        <span class="quick-action-subtitle">Add a new item</span>
                    </span>
                </a>
                <a class="quick-action violet" href="<?= htmlspecialchars(app_url('/purchase-orders?openNewPo=1'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="quick-action-icon" aria-hidden="true"><?= $quickActionIcons['purchase-order'] ?></span>
                    <span>
                        <span class="quick-action-title">Create PO</span>
                        <span class="quick-action-subtitle">Create purchase order</span>
                    </span>
                </a>
            </div>
        </section>
    </aside>
</section>
