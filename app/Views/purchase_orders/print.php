<?php

declare(strict_types=1);

/** @var array<string, mixed> $purchaseOrder */

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$nl2brEscape = static fn (string $value): string => nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
?>
<style>
    .po-document {
        color: #111827;
        font-size: 12px;
        line-height: 1.35;
    }
    .po-top {
        display: grid;
        grid-template-columns: 1fr 240px;
        gap: 24px;
        align-items: start;
        margin-bottom: 28px;
    }
    .po-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 58px;
    }
    .po-logo {
        width: 74px;
        height: 58px;
        object-fit: contain;
    }
    .po-brand-name {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 0.02em;
    }
    .po-meta {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
    }
    .po-meta th,
    .po-meta td {
        border: 1px solid #111827;
        padding: 7px 9px;
        text-align: left;
        vertical-align: top;
    }
    .po-meta th {
        background: #e5e7eb;
        font-weight: 800;
    }
    .po-parties {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 26px;
        margin-bottom: 28px;
    }
    .party-title {
        font-weight: 800;
        margin-bottom: 8px;
    }
    .party-name {
        font-weight: 800;
        margin-bottom: 4px;
    }
    .party-lines {
        min-height: 74px;
        white-space: pre-line;
    }
    .po-items {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .po-items th,
    .po-items td {
        border: 1px solid #111827;
        padding: 8px;
        vertical-align: top;
    }
    .po-items th {
        text-align: left;
        background: #e5e7eb;
        font-weight: 800;
    }
    .po-items .description {
        width: 48%;
        white-space: pre-line;
    }
    .po-items .uom {
        width: 10%;
        text-align: center;
    }
    .po-items .qty {
        width: 10%;
        text-align: center;
    }
    .po-items .money {
        width: 16%;
        text-align: right;
        white-space: nowrap;
    }
    .po-total-label {
        text-align: right;
        font-weight: 800;
    }
    .po-total-value {
        text-align: right;
        font-weight: 800;
    }
    .po-notes {
        margin-top: 18px;
        min-height: 42px;
    }
    .po-notes-title {
        font-weight: 800;
        margin-bottom: 5px;
    }
    .po-signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 88px;
        margin-top: 58px;
    }
    .signature-label {
        margin-bottom: 36px;
    }
    .signature-name {
        border-top: 1px solid #111827;
        padding-top: 7px;
        font-weight: 800;
    }
    .signature-title {
        margin-top: 3px;
    }
    @media print {
        .po-document {
            font-size: 11px;
        }
        .po-top {
            margin-bottom: 24px;
        }
        .po-parties {
            margin-bottom: 24px;
        }
    }
</style>

<section class="po-document">
    <header class="po-top">
        <div class="po-brand">
            <img class="po-logo" src="<?= $escape(app_url('/images/rss_logo.png')) ?>" alt="RSS">
            <div>
                <div class="po-brand-name">Resource Staffing Solution</div>
                <div>Purchase Order</div>
            </div>
        </div>
        <table class="po-meta">
            <thead>
            <tr>
                <th>PO Number</th>
                <th>PO Date</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?= $escape((string) $purchaseOrder['po_number']) ?></td>
                <td><?= $escape((string) $purchaseOrder['order_date']) ?></td>
            </tr>
            </tbody>
        </table>
    </header>

    <section class="po-parties">
        <div>
            <div class="party-title">Vendor</div>
            <div class="party-name"><?= $escape((string) $purchaseOrder['vendor']['name']) ?></div>
            <div class="party-lines"><?= $escape(implode("\n", $purchaseOrder['vendor']['lines'])) ?></div>
        </div>
        <div>
            <div class="party-title">Ship to</div>
            <div class="party-name"><?= $escape((string) $purchaseOrder['ship_to']['name']) ?></div>
            <div class="party-lines"><?= $escape(implode("\n", $purchaseOrder['ship_to']['lines'])) ?></div>
        </div>
    </section>

    <table class="po-items">
        <thead>
        <tr>
            <th class="description">Item Description</th>
            <th class="uom">UOM</th>
            <th class="qty">Qty</th>
            <th class="money">Unit price</th>
            <th class="money">Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($purchaseOrder['items'] as $item): ?>
            <tr>
                <td class="description"><?= $nl2brEscape((string) $item['description']) ?></td>
                <td class="uom"><?= $escape((string) $item['uom']) ?></td>
                <td class="qty"><?= $escape((string) $item['quantity']) ?></td>
                <td class="money"><?= $escape((string) $item['unit_price']) ?></td>
                <td class="money"><?= $escape((string) $item['amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="4" class="po-total-label">Total</td>
            <td class="po-total-value"><?= $escape((string) $purchaseOrder['total']) ?></td>
        </tr>
        </tbody>
    </table>

    <?php if ((string) $purchaseOrder['notes'] !== ''): ?>
        <section class="po-notes">
            <div class="po-notes-title">Notes</div>
            <div><?= $nl2brEscape((string) $purchaseOrder['notes']) ?></div>
        </section>
    <?php endif; ?>

    <section class="po-signatures">
        <div>
            <div class="signature-label">Prepared by:</div>
            <div class="signature-name"><?= $escape((string) $purchaseOrder['prepared_by']) ?></div>
            <div class="signature-title"><?= $escape((string) $purchaseOrder['prepared_title']) ?></div>
        </div>
        <div>
            <div class="signature-label">Approved by:</div>
            <div class="signature-name"><?= $escape((string) $purchaseOrder['approved_by']) ?></div>
            <div class="signature-title"><?= $escape((string) $purchaseOrder['approved_title']) ?></div>
        </div>
    </section>
</section>
