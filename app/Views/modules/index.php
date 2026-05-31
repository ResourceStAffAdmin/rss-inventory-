<?php

declare(strict_types=1);

/** @var string $description */
/** @var array<int, string> $tableHeaders */
/** @var array<int, array<int, string>> $tableRows */
/** @var array<int, array{label:string, variant?:string, href?:string}> $actions */
/** @var array<int, array{label:string, value:string}> $moduleKpis */
/** @var string|null $searchPlaceholder */
/** @var string|null $notice */
?>
<style>
    .module-shell {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .module-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .module-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
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
    .action-btn {
        background: #57c4ff;
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 600;
    }
    .action-btn.alt {
        background: #fff;
        border: 1px solid #dce5ef;
        color: #475569;
    }
    .module-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .kpi-box {
        background: #fff;
        border: 1px solid #e7edf4;
        border-radius: 16px;
        padding: 14px;
    }
    .kpi-box .label {
        color: #64748b;
        font-size: 12px;
        margin-bottom: 6px;
    }
    .kpi-box .value {
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1;
    }
    .module-table .table-wrap {
        overflow-x: auto;
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
    @media (max-width: 1000px) {
        .module-kpis {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="module-shell">
    <article class="ui-panel module-header">
        <div>
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;"><?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="panel-subtitle"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php if ($searchPlaceholder !== null || $actions !== []): ?>
            <div class="module-actions">
                <?php if ($searchPlaceholder !== null): ?>
                    <input
                        class="module-search"
                        type="search"
                        placeholder="<?= htmlspecialchars($searchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php endif; ?>
                <?php foreach ($actions as $action): ?>
                    <?php $variant = $action['variant'] ?? 'primary'; ?>
                    <?php if (isset($action['href'])): ?>
                        <a
                            class="action-btn<?= $variant === 'alt' ? ' alt' : '' ?>"
                            href="<?= htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8') ?>"
                            style="text-decoration:none; display:inline-flex; align-items:center;"
                        >
                            <?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php else: ?>
                        <button class="action-btn<?= $variant === 'alt' ? ' alt' : '' ?>" type="button">
                            <?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($notice) && $notice !== null && $notice !== ''): ?>
            <p class="module-notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </article>

    <?php if ($moduleKpis !== []): ?>
        <section class="module-kpis">
            <?php foreach ($moduleKpis as $kpi): ?>
                <article class="kpi-box">
                    <div class="label"><?= htmlspecialchars($kpi['label'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="value"><?= htmlspecialchars($kpi['value'], ENT_QUOTES, 'UTF-8') ?></div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <article class="ui-panel module-table">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <?php foreach ($tableHeaders as $header): ?>
                        <th><?= htmlspecialchars($header, ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tableRows as $row): ?>
                    <tr>
                        <?php foreach ($row as $column): ?>
                            <td><?= htmlspecialchars($column, ENT_QUOTES, 'UTF-8') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
