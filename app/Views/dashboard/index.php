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
            linear-gradient(90deg, rgba(17, 44, 78, 0.98), rgba(18, 53, 94, 0.86)),
            radial-gradient(circle at 90% 5%, rgba(47, 120, 255, 0.36), transparent 42%);
    }
    .dashboard-hero::before,
    .dashboard-hero::after {
        content: "";
        position: absolute;
        right: -80px;
        top: -110px;
        width: 470px;
        height: 280px;
        border: 1px solid rgba(61, 112, 255, 0.38);
        border-radius: 50%;
        transform: rotate(-16deg);
        pointer-events: none;
    }
    .dashboard-hero::after {
        right: -20px;
        top: 36px;
        width: 390px;
        height: 170px;
        border-color: rgba(17, 215, 207, 0.14);
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
        color: #c0d0e8;
        font-size: 13px;
    }
    .last-updated {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: #b9c8e5;
        background: rgba(11, 30, 56, 0.66);
        border: 1px solid rgba(132, 174, 255, 0.24);
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
        background: linear-gradient(180deg, rgba(18, 47, 84, 0.94), rgba(12, 35, 65, 0.94));
        border: 1px solid rgba(132, 174, 255, 0.24);
        border-radius: 10px;
        padding: 18px;
        min-height: 104px;
        display: grid;
        grid-template-columns: 42px 1fr;
        gap: 14px;
        align-items: center;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow), inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }
    .kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #83adff;
        background: rgba(47, 120, 255, 0.16);
        border: 1px solid rgba(104, 151, 255, 0.22);
        font-size: 16px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .kpi-card.cyan .kpi-icon {
        color: #63eee9;
        background: rgba(17, 215, 207, 0.12);
        border-color: rgba(17, 215, 207, 0.22);
    }
    .kpi-card.warning .kpi-icon {
        color: #ffc164;
        background: rgba(255, 179, 62, 0.13);
        border-color: rgba(255, 179, 62, 0.24);
    }
    .kpi-card.danger .kpi-icon {
        color: #ff7b88;
        background: rgba(255, 76, 97, 0.13);
        border-color: rgba(255, 76, 97, 0.24);
    }
    .kpi-card.violet .kpi-icon {
        color: #b9a1ff;
        background: rgba(122, 92, 255, 0.15);
        border-color: rgba(122, 92, 255, 0.24);
    }
    .kpi-label {
        font-size: 12px;
        color: #adc0dc;
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
        color: #91a8c8;
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
        width: 74px;
        height: 68px;
        margin: 4px auto 12px;
        position: relative;
        border-radius: 12px;
        background: linear-gradient(145deg, rgba(47, 120, 255, 0.62), rgba(47, 120, 255, 0.16));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 18px 32px rgba(0, 0, 0, 0.2);
    }
    .empty-box::before {
        content: "";
        position: absolute;
        left: 12px;
        right: 12px;
        top: -11px;
        height: 22px;
        border-radius: 8px 8px 2px 2px;
        background: rgba(126, 168, 255, 0.38);
        transform: skewX(-18deg);
    }
    .empty-box::after {
        content: "";
        position: absolute;
        right: -8px;
        top: 4px;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: #39e47b;
        box-shadow: 0 0 18px rgba(57, 228, 123, 0.55);
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
        background: rgba(11, 30, 56, 0.54);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .quick-action:hover {
        transform: translateY(-1px);
        border-color: rgba(104, 151, 255, 0.38);
    }
    .quick-action.success {
        background: linear-gradient(135deg, rgba(67, 230, 146, 0.65), rgba(11, 159, 104, 0.35));
        border-color: rgba(67, 230, 146, 0.34);
    }
    .quick-action.danger {
        background: linear-gradient(135deg, rgba(255, 83, 104, 0.65), rgba(165, 15, 42, 0.38));
        border-color: rgba(255, 83, 104, 0.34);
    }
    .quick-action-icon {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.24);
        background: rgba(255, 255, 255, 0.1);
        font-size: 20px;
        font-weight: 800;
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
                    <span class="kpi-icon"><?= htmlspecialchars($kpi['icon'], ENT_QUOTES, 'UTF-8') ?></span>
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
                        <div class="empty-box" aria-hidden="true"></div>
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
                    <span class="quick-action-icon">+</span>
                    <span>
                        <span class="quick-action-title">Stock In</span>
                        <span class="quick-action-subtitle">Record incoming stock</span>
                    </span>
                </a>
                <a class="quick-action danger" href="<?= htmlspecialchars(app_url('/stock?open=stock-out'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="quick-action-icon">-</span>
                    <span>
                        <span class="quick-action-title">Stock Out</span>
                        <span class="quick-action-subtitle">Record outgoing stock</span>
                    </span>
                </a>
                <a class="quick-action" href="<?= htmlspecialchars(app_url('/products?openNewItem=1'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="quick-action-icon">+</span>
                    <span>
                        <span class="quick-action-title">New Item</span>
                        <span class="quick-action-subtitle">Add a new item</span>
                    </span>
                </a>
                <a class="quick-action" href="<?= htmlspecialchars(app_url('/purchase-orders?openNewPo=1'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="quick-action-icon">#</span>
                    <span>
                        <span class="quick-action-title">Create PO</span>
                        <span class="quick-action-subtitle">Create purchase order</span>
                    </span>
                </a>
            </div>
        </section>
    </aside>
</section>
