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
    <title><?= htmlspecialchars($pageTitle ?? 'Login', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');
        :root {
            --accent: #36d399;
            --accent-strong: #1b9f6c;
            --accent-cool: #3e8bff;
            --bg: #06111f;
            --card: rgba(10, 24, 44, 0.94);
            --card-strong: rgba(12, 30, 54, 0.98);
            --text: #f4f7ff;
            --muted: #9eb0cb;
            --border: rgba(112, 162, 255, 0.22);
            --radius: 16px;
        }
        * {
            box-sizing: border-box;
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
        }
        html {
            min-height: 100%;
            background: var(--bg);
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 18% 0%, rgba(54, 211, 153, 0.16) 0%, transparent 38%),
                radial-gradient(circle at 75% -10%, rgba(62, 139, 255, 0.36) 0%, transparent 46%),
                linear-gradient(140deg, #06111f 0%, #0a1a31 55%, #112e4f 100%);
            color: var(--text);
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 12% 16%, rgba(54, 211, 153, 0.18), transparent 38%),
                radial-gradient(circle at 88% 18%, rgba(62, 139, 255, 0.2), transparent 40%),
                radial-gradient(circle at 50% 100%, rgba(12, 30, 54, 0.9), transparent 55%);
            pointer-events: none;
            opacity: 0.7;
        }
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: 140px 140px;
            pointer-events: none;
            opacity: 0.08;
        }
        .auth-shell {
            width: min(460px, 92vw);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.4);
            padding: 30px;
            position: relative;
            overflow: hidden;
            animation: floatIn 0.6s ease;
        }
        .auth-shell::before {
            content: "";
            position: absolute;
            inset: -30% 10% auto auto;
            width: 160px;
            height: 160px;
            background: radial-gradient(circle, rgba(54, 211, 153, 0.4), transparent 70%);
            opacity: 0.5;
            filter: blur(6px);
            pointer-events: none;
        }
        .auth-shell::after {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: calc(var(--radius) - 2px);
            background: linear-gradient(160deg, var(--card-strong), rgba(10, 24, 44, 0.9));
            z-index: 0;
        }
        .auth-shell > * {
            position: relative;
            z-index: 1;
        }
        .auth-card {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .auth-header {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .brand-title {
            font-family: "Space Grotesk", "Segoe UI", sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        .brand-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: var(--muted);
        }
        .auth-title {
            margin: 0 0 6px;
            font-size: 20px;
            font-weight: 700;
            font-family: "Space Grotesk", "Segoe UI", sans-serif;
        }
        .auth-subtitle {
            margin: 0 0 18px;
            font-size: 13px;
            color: var(--muted);
        }
        .auth-alert {
            border: 1px solid rgba(255, 99, 99, 0.45);
            background: rgba(255, 99, 99, 0.12);
            color: #ffd4d4;
            font-size: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 14px;
        }
        .auth-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .auth-fields {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 8px;
        }
        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .field label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            font-weight: 700;
        }
        .field input {
            background: rgba(8, 22, 40, 0.92);
            border: 1px solid rgba(112, 162, 255, 0.24);
            border-radius: 10px;
            color: var(--text);
            padding: 12px 14px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .field input:focus {
            border-color: rgba(54, 211, 153, 0.7);
            box-shadow: 0 0 0 3px rgba(54, 211, 153, 0.18);
        }
        .auth-actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
            margin-top: 4px;
        }
        .auth-btn {
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 700;
            border: 0;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-strong));
            box-shadow: 0 14px 24px rgba(54, 211, 153, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .auth-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(54, 211, 153, 0.34);
        }
        .hint {
            font-size: 11px;
            color: var(--muted);
            text-align: center;
        }
        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 520px) {
            .auth-shell {
                padding: 24px;
            }
            .auth-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .brand-badge {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <?= $content ?>
    </main>
</body>
</html>
