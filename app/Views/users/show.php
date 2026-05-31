<?php

declare(strict_types=1);

/** @var array<string, string|int> $employee */
/** @var array{active_assignments:string,total_items:string,returned_items:string} $summary */
/** @var array<int, array<string, string>> $transactions */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};

$statusValue = strtolower((string) ($employee['status'] ?? ''));
$statusClass = $statusValue === 'active' ? 'success' : 'neutral';
?>
<style>
    .employee-shell {
        display: grid;
        gap: 12px;
    }
    .employee-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }
    .employee-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .summary-card {
        border: 1px solid #e7edf4;
        border-radius: 16px;
        padding: 14px;
        background: #fff;
    }
    .summary-card .label {
        color: #64748b;
        font-size: 12px;
        margin-bottom: 6px;
    }
    .summary-card .value {
        color: #0f172a;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }
    .employee-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
    .meta-box {
        border: 1px solid #e7edf4;
        border-radius: 12px;
        padding: 12px;
        background: #f8fafc;
    }
    .meta-box .label {
        color: #64748b;
        font-size: 11px;
        margin-bottom: 5px;
        font-weight: 700;
    }
    .meta-box .value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }
    .users-empty {
        text-align: center;
        font-size: 13px;
        color: #64748b;
        padding: 24px 12px;
    }
    @media (max-width: 1000px) {
        .employee-summary,
        .employee-meta {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 700px) {
        .employee-summary,
        .employee-meta {
            grid-template-columns: 1fr;
        }
        .employee-header {
            flex-direction: column;
            align-items: stretch;
        }
        .employee-header .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<section class="employee-shell">
    <article class="ui-panel employee-header">
        <div>
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;"><?= htmlspecialchars((string) $employee['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="panel-subtitle">Inventory activity and accountability history for this employee.</p>
        </div>
        <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/users'), ENT_QUOTES, 'UTF-8') ?>">Back to Users</a>
    </article>

    <section class="employee-summary">
        <article class="summary-card">
            <div class="label">Active Assignments</div>
            <div class="value"><?= htmlspecialchars($summary['active_assignments'], ENT_QUOTES, 'UTF-8') ?></div>
        </article>
        <article class="summary-card">
            <div class="label">Total Issued Items</div>
            <div class="value"><?= htmlspecialchars($summary['total_items'], ENT_QUOTES, 'UTF-8') ?></div>
        </article>
        <article class="summary-card">
            <div class="label">Returned Items</div>
            <div class="value"><?= htmlspecialchars($summary['returned_items'], ENT_QUOTES, 'UTF-8') ?></div>
        </article>
    </section>

    <article class="ui-panel">
        <div class="employee-meta">
            <div class="meta-box">
                <div class="label">Email</div>
                <div class="value"><?= htmlspecialchars((string) ($employee['email'] !== '' ? $employee['email'] : '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="meta-box">
                <div class="label">Contact</div>
                <div class="value"><?= htmlspecialchars((string) ($employee['contact'] !== '' ? $employee['contact'] : '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="meta-box">
                <div class="label">Position</div>
                <div class="value"><?= htmlspecialchars((string) ($employee['position'] !== '' ? $employee['position'] : '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="meta-box">
                <div class="label">Status</div>
                <div class="value"><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst((string) $employee['status']), ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </div>
    </article>

    <article class="ui-panel">
        <div class="panel-heading" style="margin-bottom:12px;">
            <h3 class="panel-title">Transaction History</h3>
        </div>
        <?php if ($transactions === []): ?>
            <div class="users-empty">No inventory accountability transactions found for this employee yet.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Item</th>
                        <th>Details</th>
                        <th>Serial</th>
                        <th>Qty</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge <?= $transaction['type'] === 'RETURNED' ? 'warning' : 'info' ?>">
                                    <?= htmlspecialchars($transaction['type'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($transaction['reference'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($transaction['item'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($transaction['details'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($transaction['serial'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($transaction['qty'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($transaction['reason'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($transaction['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>
