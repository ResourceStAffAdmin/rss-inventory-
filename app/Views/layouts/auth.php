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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --accent: #43b7ff;
            --accent-strong: #1d74e8;
            --background: #071d3b;
            --panel: rgba(12, 45, 84, 0.78);
            --text: #f8fafc;
            --muted: #b6c8e4;
            --border: rgba(139, 190, 255, 0.3);
            --field: rgba(9, 35, 68, 0.78);
            --viewport-scale: clamp(1, calc(100vw / 1920px), 6);
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: var(--background);
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(circle at 100% 70%, rgba(37, 228, 255, 0.22), transparent 32%),
                radial-gradient(circle at 65% -10%, rgba(67, 183, 255, 0.24), transparent 35%),
                linear-gradient(115deg, #071d3b 0%, #0a2850 54%, #0e3a70 130%);
            font-family: "Inter", "Segoe UI", sans-serif;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            left: -155px;
            bottom: -250px;
            width: 540px;
            height: 540px;
            border: 1px solid rgba(139, 207, 255, 0.1);
            border-radius: 50%;
            box-shadow:
                0 0 0 86px rgba(67, 183, 255, 0.035),
                0 0 0 170px rgba(37, 228, 255, 0.025);
            pointer-events: none;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.012), transparent);
            pointer-events: none;
        }

        button,
        input {
            font: inherit;
        }

        .auth-page {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(390px, 0.85fr);
            align-items: center;
            gap: clamp(54px, 8vw, 116px);
            width: min(1160px, calc(100% - 64px));
            min-height: 100vh;
            margin: 0 auto;
            padding: 58px 0;
        }

        /*
         * Browser zoom-out increases the CSS viewport while shrinking fixed-size
         * content. Scale the desktop canvas back up so it remains usable.
         */
        @media (min-width: 1921px) {
            .auth-page {
                min-height: calc(100vh / var(--viewport-scale));
                zoom: var(--viewport-scale);
            }
        }

        .auth-intro {
            min-width: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: clamp(46px, 7vh, 70px);
        }

        .brand-logo {
            width: 62px;
            height: 54px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .brand-title {
            margin: 0;
            font-size: 25px;
            font-weight: 700;
            letter-spacing: -0.04em;
        }

        .brand-title span {
            color: var(--accent);
        }

        .brand-subtitle {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .hero-title {
            max-width: 560px;
            margin: 0;
            font-size: clamp(37px, 4vw, 51px);
            line-height: 1.12;
            letter-spacing: -0.045em;
            font-weight: 800;
        }

        .hero-title span {
            display: block;
            color: var(--accent);
        }

        .hero-copy {
            max-width: 520px;
            margin: 20px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.75;
        }

        .feature-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            margin-top: clamp(42px, 7vh, 64px);
        }

        .feature {
            min-width: 0;
            padding: 0 28px;
            border-left: 1px solid rgba(132, 167, 192, 0.2);
        }

        .feature:first-child {
            padding-left: 0;
            border-left: 0;
        }

        .feature:last-child {
            padding-right: 0;
        }

        .feature-icon {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
            color: var(--accent);
        }

        .feature-icon svg {
            width: 46px;
            height: 46px;
            stroke-width: 1.7;
        }

        .feature h3 {
            margin: 0 0 9px;
            font-size: 15px;
            font-weight: 700;
        }

        .feature p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.65;
        }

        .auth-panel {
            position: relative;
            padding: clamp(30px, 4vw, 44px);
            border: 1px solid var(--border);
            border-radius: 15px;
            background:
                linear-gradient(145deg, rgba(18, 68, 116, 0.74), rgba(8, 38, 74, 0.88)),
                var(--panel);
            box-shadow:
                0 28px 75px rgba(3, 18, 42, 0.32),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            overflow: hidden;
            animation: panel-in 0.55s ease-out;
        }

        .auth-panel::before {
            content: "";
            position: absolute;
            top: -130px;
            right: -110px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(67, 183, 255, 0.12);
            filter: blur(8px);
            pointer-events: none;
        }

        .auth-card {
            position: relative;
            z-index: 1;
        }

        .auth-heading {
            text-align: center;
        }

        .lock-badge {
            display: grid;
            place-items: center;
            width: 66px;
            height: 66px;
            margin: 0 auto 14px;
            color: var(--accent);
            border: 1px solid rgba(139, 207, 255, 0.4);
            border-radius: 50%;
            background: rgba(10, 58, 101, 0.62);
        }

        .lock-badge svg {
            width: 27px;
            height: 27px;
        }

        .auth-title {
            margin: 0;
            font-size: 23px;
            letter-spacing: -0.03em;
        }

        .auth-subtitle {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .auth-alert {
            margin: 20px 0 -4px;
            padding: 11px 13px;
            color: #ffd1d1;
            border: 1px solid rgba(248, 113, 113, 0.38);
            border-radius: 9px;
            background: rgba(127, 29, 29, 0.22);
            font-size: 12px;
            line-height: 1.45;
        }

        .auth-form {
            margin-top: 25px;
        }

        .auth-fields {
            display: grid;
            gap: 20px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field label {
            color: #b9c5d6;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            color: #7890aa;
            pointer-events: none;
        }

        .field input {
            width: 100%;
            height: 43px;
            padding: 0 43px 0 42px;
            color: var(--text);
            border: 1px solid rgba(113, 157, 190, 0.32);
            border-radius: 9px;
            outline: none;
            background: var(--field);
            font-size: 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .field input::placeholder {
            color: #76889e;
        }

        .field input:focus {
            border-color: rgba(67, 183, 255, 0.75);
            background: rgba(10, 44, 85, 0.94);
            box-shadow: 0 0 0 3px rgba(67, 183, 255, 0.12);
        }

        .password-toggle {
            position: absolute;
            right: 7px;
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            padding: 0;
            color: #8aa2bb;
            border: 0;
            border-radius: 7px;
            background: transparent;
            cursor: pointer;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            color: var(--accent);
            outline: none;
            background: rgba(67, 183, 255, 0.1);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
        }

        .form-options {
            display: flex;
            align-items: center;
            margin: 18px 0 22px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 12px;
            cursor: pointer;
        }

        .remember input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .checkmark {
            display: grid;
            place-items: center;
            width: 16px;
            height: 16px;
            color: transparent;
            border: 1.5px solid var(--accent);
            border-radius: 3px;
            transition: color 0.15s ease, background 0.15s ease;
        }

        .checkmark svg {
            width: 11px;
            height: 11px;
        }

        .remember input:checked + .checkmark {
            color: #062246;
            background: var(--accent);
        }

        .remember input:focus-visible + .checkmark {
            box-shadow: 0 0 0 3px rgba(67, 183, 255, 0.18);
        }

        .auth-btn {
            width: 100%;
            height: 45px;
            color: #fff;
            border: 0;
            border-radius: 9px;
            background: linear-gradient(100deg, #1d74e8, #43b7ff);
            box-shadow: 0 12px 30px rgba(67, 183, 255, 0.18);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .auth-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 16px 34px rgba(67, 183, 255, 0.24);
        }

        .auth-btn:active {
            transform: translateY(0);
        }

        .auth-help {
            margin: 27px 0 0;
            color: var(--muted);
            text-align: center;
            font-size: 12px;
        }

        .auth-help span {
            color: var(--accent);
        }

        @keyframes panel-in {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 950px) {
            .auth-page {
                grid-template-columns: 1fr;
                width: min(640px, calc(100% - 48px));
                gap: 54px;
                padding: 52px 0 70px;
            }

            .brand {
                margin-bottom: 42px;
            }

            .auth-panel {
                width: min(100%, 480px);
                justify-self: center;
            }
        }

        @media (max-width: 600px) {
            .auth-page {
                width: min(calc(100% - 32px), 480px);
                padding: 32px 0 48px;
            }

            .brand {
                margin-bottom: 36px;
            }

            .brand-logo {
                width: 52px;
                height: 46px;
            }

            .brand-title {
                font-size: 22px;
            }

            .hero-copy {
                font-size: 14px;
                line-height: 1.65;
            }

            .hero-title {
                font-size: clamp(32px, 7vw, 37px);
            }

            .feature-list {
                grid-template-columns: 1fr;
                gap: 22px;
                margin-top: 38px;
            }

            .feature,
            .feature:first-child {
                display: grid;
                grid-template-columns: 48px 1fr;
                column-gap: 16px;
                padding: 0;
                border: 0;
            }

            .feature-icon {
                grid-row: 1 / span 2;
                margin: 0;
            }

            .feature h3 {
                align-self: end;
            }

            .auth-panel {
                padding: 28px 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <?= $content ?>
</body>
</html>
