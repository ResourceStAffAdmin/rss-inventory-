<?php

declare(strict_types=1);

/** @var array<string, string|int> $assignment */
/** @var array<int, array<string, string|int|float|null>> $items */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
?>
<style>
    .form-head {
        display: grid;
        justify-items: center;
        gap: 7px;
        margin-bottom: 18px;
    }
    .form-logo {
        width: 118px;
        height: 77px;
        display: block;
        object-fit: contain;
    }
    .form-title {
        text-align: center;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.15;
        margin: 0;
    }
    .accountability-form {
        font-family: Candara, Arial, Helvetica, sans-serif;
        color: #000;
        font-size: 13px;
    }
    .meta-table,
    .items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .meta-table td,
    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 7px 9px;
        vertical-align: top;
    }
    .meta-table {
        margin-bottom: 14px;
    }
    .meta-label {
        font-weight: 800;
    }
    .items-table th {
        text-align: center;
        font-weight: 800;
    }
    .items-table td:first-child {
        text-align: center;
        width: 12%;
    }
    .items-table td {
        min-height: 32px;
    }
    .ack-title {
        margin: 20px 0 8px;
        font-weight: 800;
        text-align: center;
        letter-spacing: 0.5px;
    }
    .ack-copy {
        margin: 0;
        text-align: justify;
        line-height: 1.45;
    }
    .signature {
        margin-top: 52px;
        width: 280px;
        text-align: center;
    }
    .signature-line {
        border-top: 1px solid #000;
        padding-top: 6px;
        font-size: 12px;
    }
</style>

<section class="accountability-form">
    <header class="form-head">
        <img
            class="form-logo"
            src="<?= htmlspecialchars($buildUrl('/images/rss_logo.png'), ENT_QUOTES, 'UTF-8') ?>"
            alt="Resource Staffing Solution logo"
        >
        <h1 class="form-title">Equipment Accountability Form</h1>
    </header>

    <table class="meta-table">
        <tr>
            <td><span class="meta-label">Name:</span> <?= htmlspecialchars((string) $assignment['employee'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="meta-label">Department:</span> <?= htmlspecialchars((string) ($assignment['department'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td><span class="meta-label">Position:</span> <?= htmlspecialchars((string) ($assignment['position'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <span class="meta-label">Date Borrowed/Received:</span>
                <?= htmlspecialchars((string) $assignment['assigned_date'], ENT_QUOTES, 'UTF-8') ?>
                <br>
                <span class="meta-label">Date Returned:</span>
                <?= htmlspecialchars((string) ($assignment['returned_date'] ?: ''), ENT_QUOTES, 'UTF-8') ?>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
        <tr>
            <th>Item No.</th>
            <th>Equipment/Model/Brand</th>
            <th>Description</th>
            <th>Serial Number</th>
            <th>Reason</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $index => $item): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars((string) $item['equipment_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['description'] ?: ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['serial_number'] ?: ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($item['reason'] ?: 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="ack-title">ACKNOWLEDGEMENT</div>
    <p class="ack-copy">
        I hereby acknowledge my accountability for the items referenced above. It is understood that I bear responsibility for any loss or damage resulting from my actions or negligence, and I am committed to reimbursing the associated expenses or arranging for replacements accordingly. Should I choose to resign, separate, or transfer, I pledge to return these items prior to the issuance of my clearance. For any additional software protected with license installed that do not appear on the list above, or do not have any supporting document(s) coming from the Resource Staffing Solution, it is my responsibility to face any charges or liability coming from any software authority or organization. Also, I will follow the rules and regulations imposed by RSS and I will be liable for any consequences that may arise for not complying with the set of rules and regulations.
    </p>

    <div class="signature">
        <div style="text-align:left; margin-bottom:42px;">Conforme:</div>
        <div class="signature-line">Signature over printed name</div>
    </div>
</section>
