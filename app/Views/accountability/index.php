<?php

declare(strict_types=1);

/** @var string $description */
/** @var array<int, array<string, string|int>> $assignments */
/** @var string|null $notice */
/** @var string|null $errorMessage */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
?>
<style>
    .accountability-shell {
        display: grid;
        gap: 12px;
    }
    .accountability-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }
    .accountability-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
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
        color: #94a3b8;
        padding: 18px 0;
    }
    .table-actions {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .link-btn {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }
    .inline-return {
        display: inline;
    }
    .inline-return button {
        border: 1px solid #fed7aa;
        background: #fff7ed;
        color: #c2410c;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }
    @media (max-width: 900px) {
        .accountability-header {
            flex-direction: column;
            align-items: stretch;
        }
        .accountability-actions {
            width: 100%;
        }
        .accountability-actions .btn {
            width: 100%;
            justify-content: center;
        }
        .table-actions {
            flex-wrap: nowrap;
        }
    }
</style>

<section class="accountability-shell">
    <article class="ui-panel accountability-header">
        <div>
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">Asset Accountability</h2>
            <p class="panel-subtitle"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="accountability-actions">
            <a class="btn" href="<?= htmlspecialchars($buildUrl('/accountability/new'), ENT_QUOTES, 'UTF-8') ?>">New Assignment</a>
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
                    <th>Employee</th>
                    <th>Department/Client</th>
                    <th>Items</th>
                    <th>Assigned Date</th>
                    <th>Returned Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($assignments === []): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No accountability assignments yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <?php $isReturned = (string) $assignment['status'] === 'RETURNED'; ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $assignment['employee'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $assignment['department'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $assignment['item_count'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $assignment['assigned_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($assignment['returned_date'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= $isReturned ? 'neutral' : 'success' ?>"><?= htmlspecialchars($isReturned ? 'Returned' : 'Active', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="link-btn" href="<?= htmlspecialchars($buildUrl('/accountability/' . (int) $assignment['id']), ENT_QUOTES, 'UTF-8') ?>">View</a>
                                    <a class="link-btn" href="<?= htmlspecialchars($buildUrl('/accountability/' . (int) $assignment['id'] . '/print'), ENT_QUOTES, 'UTF-8') ?>" target="_blank">Print</a>
                                    <?php if (!$isReturned): ?>
                                        <form class="inline-return" method="post" action="<?= htmlspecialchars($buildUrl('/accountability/' . (int) $assignment['id'] . '/return'), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="returned_date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit">Return</button>
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
