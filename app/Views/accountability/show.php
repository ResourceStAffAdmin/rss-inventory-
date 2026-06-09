<?php

declare(strict_types=1);

/** @var array<string, string|int> $assignment */
/** @var array<int, array<string, string|int|float|null>> $items */
/** @var string|null $notice */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
$formatBytes = static function (int $bytes): string {
    if ($bytes <= 0) {
        return '';
    }
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    }
    return number_format($bytes / 1024, 1) . ' KB';
};
$isReturned = (string) $assignment['status'] === 'RETURNED';
$hasAttachment = ((string) ($assignment['attachment_path'] ?? '')) !== '';
?>
<style>
    .detail-shell {
        display: grid;
        gap: 12px;
    }
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }
    .detail-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .meta-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
    .meta-box {
        border: 1px solid rgba(104, 151, 255, 0.22);
        border-radius: 12px;
        padding: 12px;
        background: rgba(11, 30, 56, 0.68);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }
    .meta-box .label {
        color: #9db1d3;
        font-size: 11px;
        margin-bottom: 5px;
        font-weight: 700;
    }
    .meta-box .value {
        color: #eef5ff;
        font-size: 14px;
        font-weight: 700;
    }
    .attachment-panel {
        margin-top: 12px;
        border: 1px solid rgba(104, 151, 255, 0.22);
        border-radius: 12px;
        padding: 12px;
        background: rgba(11, 30, 56, 0.68);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .attachment-panel .label {
        color: #9db1d3;
        font-size: 11px;
        margin-bottom: 5px;
        font-weight: 700;
    }
    .attachment-panel .value {
        color: #eef5ff;
        font-size: 14px;
        font-weight: 700;
    }
    .attachment-panel .meta {
        color: #9db1d3;
        font-size: 12px;
        margin-top: 3px;
    }
    .module-notice {
        width: 100%;
        margin: 8px 0 0;
        font-size: 12px;
        color: #0c4a6e;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 8px 10px;
    }
    .return-form {
        display: inline-flex;
        gap: 8px;
        align-items: center;
    }
    .return-form input {
        border: 1px solid rgba(104, 151, 255, 0.22);
        border-radius: 999px;
        padding: 8px 10px;
        font-size: 12px;
        background: rgba(11, 30, 56, 0.78);
        color: #eef5ff;
        color-scheme: dark;
    }
    @media (max-width: 1000px) {
        .meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 680px) {
        .meta-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="detail-shell">
    <article class="ui-panel detail-header">
        <div>
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">Accountability Details</h2>
            <p class="panel-subtitle"><?= htmlspecialchars((string) $assignment['employee'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="detail-actions">
            <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/accountability'), ENT_QUOTES, 'UTF-8') ?>">Back</a>
            <a class="btn" href="<?= htmlspecialchars($buildUrl('/accountability/' . (int) $assignment['id'] . '/print'), ENT_QUOTES, 'UTF-8') ?>" target="_blank">Print Form</a>
        </div>
        <?php if ($notice !== null && $notice !== ''): ?>
            <p class="module-notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </article>

    <article class="ui-panel">
        <div class="meta-grid">
            <div class="meta-box">
                <div class="label">Department/Client</div>
                <div class="value"><?= htmlspecialchars((string) ($assignment['department'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="meta-box">
                <div class="label">Position</div>
                <div class="value"><?= htmlspecialchars((string) ($assignment['position'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="meta-box">
                <div class="label">Assigned Date</div>
                <div class="value"><?= htmlspecialchars((string) $assignment['assigned_date'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="meta-box">
                <div class="label">Status</div>
                <div class="value"><span class="badge <?= $isReturned ? 'neutral' : 'success' ?>"><?= htmlspecialchars($isReturned ? 'Returned' : 'Active', ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </div>
        <?php if ($hasAttachment): ?>
            <div class="attachment-panel">
                <div>
                    <div class="label">Attachment</div>
                    <div class="value"><?= htmlspecialchars((string) ($assignment['attachment_name'] ?: 'Attached file'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="meta">
                        <?= htmlspecialchars((string) ($assignment['attachment_mime'] ?: 'File'), ENT_QUOTES, 'UTF-8') ?>
                        <?php if ((int) ($assignment['attachment_size'] ?? 0) > 0): ?>
                            - <?= htmlspecialchars($formatBytes((int) $assignment['attachment_size']), ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </div>
                </div>
                <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl((string) $assignment['attachment_path']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open Attachment</a>
            </div>
        <?php endif; ?>
        <?php if (!$isReturned): ?>
            <form class="return-form" method="post" action="<?= htmlspecialchars($buildUrl('/accountability/' . (int) $assignment['id'] . '/return'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top:12px;">
                <input type="date" name="returned_date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required>
                <button class="btn btn-outline" type="submit">Mark Returned</button>
            </form>
        <?php endif; ?>
    </article>

    <article class="ui-panel">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Item No.</th>
                    <th>Equipment/Model/Brand</th>
                    <th>Description</th>
                    <th>Serial Number</th>
                    <th>Reason</th>
                    <th>Qty</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars((string) $item['equipment_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($item['description'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($item['serial_number'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($item['reason'] ?: 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(number_format((float) $item['quantity'], 0), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
