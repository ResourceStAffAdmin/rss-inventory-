<?php

declare(strict_types=1);

/** @var string $content */
/** @var string|null $pageTitle */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Print', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }
        .print-toolbar button {
            border: 1px solid #d1d5db;
            background: #111827;
            color: #fff;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            cursor: pointer;
        }
        .print-page {
            width: 8.5in;
            min-height: 11in;
            margin: 18px auto;
            background: #fff;
            padding: 0.55in;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        }
        @media print {
            body {
                background: #fff;
            }
            .print-toolbar {
                display: none;
            }
            .print-page {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button type="button" onclick="window.print()">Print Form</button>
    </div>
    <main class="print-page">
        <?= $content ?>
    </main>
</body>
</html>
