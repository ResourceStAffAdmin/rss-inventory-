<?php

declare(strict_types=1);

/** @var string $content */
/** @var string|null $pageTitle */
/** @var string|null $currentRoute */

$activeRoute = $currentRoute ?? '/';
$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
$knownRoutes = [
    '/',
    '/products',
    '/categories',
    '/stock',
    '/suppliers',
    '/reports',
    '/accountability',
    '/history',
    '/users',
    '/low-stock',
];

$menuGroups = [
    [
        'items' => [
            ['path' => '/', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['path' => '/products', 'label' => 'Products', 'icon' => 'package'],
            ['path' => '/categories', 'label' => 'Categories', 'icon' => 'tags'],
            ['path' => '/stock', 'label' => 'Stock', 'icon' => 'arrows'],
        ],
    ],
    [
        'items' => [
            ['path' => '/accountability', 'label' => 'Accountability', 'icon' => 'clipboard'],
            ['path' => '/suppliers', 'label' => 'Suppliers', 'icon' => 'truck'],
            ['path' => '/users', 'label' => 'Users', 'icon' => 'user'],
        ],
    ],
    [
        'items' => [
            ['path' => '/reports', 'label' => 'Reports', 'icon' => 'chart'],
        ],
    ],
];
$iconSvgs = [
    'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7"/><path d="M5 10v10a1 1 0 0 0 1 1h4v-7h4v7h4a1 1 0 0 0 1-1V10"/></svg>',
    'package' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96 12 12.01l8.73-5.05"/><path d="M12 22V12"/></svg>',
    'tags' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 12 4.83V2H2v10l8.59 8.59a2 2 0 0 0 2.82 0l7.18-7.18a2 2 0 0 0 0-2.82Z"/><path d="M7 7h.01"/></svg>',
    'arrows' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h11"/><path d="m13 3 5 4-5 4"/><path d="M17 17H6"/><path d="m11 21-5-4 5-4"/></svg>',
    'clipboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2" width="6" height="4" rx="1"/><path d="M9 4H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><path d="M8 10h8"/><path d="M8 14h8"/><path d="M8 18h5"/></svg>',
    'truck' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3"/><path d="M14 8h4l4 4v5h-3"/><circle cx="5" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M14 17H7"/></svg>',
    'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 20V14"/><path d="M12 20V10"/><path d="M18 20V4"/></svg>',
    'user' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c2-4 6-6 8-6s6 2 8 6"/></svg>',
    'search' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>',
    'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>',
    'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06A2 2 0 1 1 7.03 3.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.18.38.5.68.9.85.2.08.42.13.7.15H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15Z"/></svg>',
];

$authName = (string) ($_SESSION['auth_employee_name'] ?? '');
$authInitials = 'AD';
if ($authName !== '') {
    $parts = preg_split('/\s+/', trim($authName)) ?: [];
    $letters = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        $letters .= strtoupper(substr($part, 0, 1));
        if (strlen($letters) >= 2) {
            break;
        }
    }
    if ($letters !== '') {
        $authInitials = $letters;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(($pageTitle ?? 'Inventory') . ' | RSS Inventory', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');
        :root {
            --accent: #2f78ff;
            --accent-strong: #1157d9;
            --accent-soft: rgba(47, 120, 255, 0.18);
            --cyan: #11d7cf;
            --bg: #071426;
            --card: #102844;
            --card-strong: #14325a;
            --text: #f6f9ff;
            --muted: #adc0dc;
            --muted-soft: #8298b9;
            --border: rgba(132, 174, 255, 0.24);
            --success: #39e47b;
            --danger: #ff4c61;
            --warning: #ffb33e;
            --shadow: 0 18px 42px rgba(0, 0, 0, 0.22);
            --radius: 12px;
        }
        * {
            box-sizing: border-box;
            font-family: "Manrope", "Noto Sans", sans-serif;
        }
        html {
            min-height: 100%;
            background: var(--bg);
        }
        body {
            margin: 0;
            background:
                radial-gradient(circle at 12% 0%, rgba(17, 215, 207, 0.16) 0%, transparent 32%),
                radial-gradient(circle at 70% -10%, rgba(47, 120, 255, 0.32) 0%, transparent 40%),
                linear-gradient(135deg, #071426 0%, #0b1e37 56%, #102a4a 100%);
            color: var(--text);
            padding: 0;
        }
        .frame {
            width: 100%;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 86px 1fr;
            gap: 12px;
            padding: 8px;
        }
        .sidebar {
            background: linear-gradient(180deg, rgba(16, 40, 72, 0.96), rgba(10, 26, 49, 0.98));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 10px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: center;
            position: sticky;
            top: 8px;
            height: calc(100vh - 16px);
            box-shadow: var(--shadow), inset 1px 0 0 rgba(255, 255, 255, 0.03);
        }
        .brand {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px 0 10px;
        }
        .brand-logo {
            width: 62px;
            height: 52px;
            display: block;
            object-fit: contain;
        }
        .icon-nav {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 2px;
            align-items: center;
        }
        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
            align-items: center;
        }
        .nav-divider {
            height: 1px;
            width: 52px;
            background: rgba(139, 164, 213, 0.16);
            margin: 6px auto;
        }
        .icon-link {
            width: 44px;
            height: 42px;
            border-radius: 8px;
            border: 1px solid transparent;
            color: #b7c6e4;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 0;
            font-size: 10px;
            font-weight: 600;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            position: relative;
        }
        .icon-link .icon {
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 18px;
        }
        .icon-link .icon svg {
            width: 17px;
            height: 17px;
        }
        .icon-link .label {
            color: inherit;
            display: none;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .icon-link:hover,
        .icon-link.active {
            background: linear-gradient(135deg, rgba(47, 120, 255, 0.95), rgba(15, 77, 190, 0.86));
            border-color: rgba(91, 147, 255, 0.55);
            color: #fff;
            box-shadow: 0 12px 24px rgba(47, 120, 255, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.12);
            transform: translateY(-1px);
        }
        .icon-link[data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%) translateX(-4px);
            background: #152f52;
            color: #fff;
            font-size: 11px;
            padding: 6px 8px;
            border-radius: 8px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
            z-index: 20;
        }
        .icon-link:hover::after {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }
        .workspace {
            display: flex;
            flex-direction: column;
            min-width: 0;
            gap: 12px;
        }
        .topbar {
            background: linear-gradient(180deg, rgba(17, 42, 76, 0.96), rgba(11, 30, 56, 0.96));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: var(--shadow), inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }
        .top-tabs {
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 0;
            flex-wrap: wrap;
        }
        .top-tab {
            text-decoration: none;
            color: #c6d5ed;
            font-size: 11px;
            padding: 9px 17px;
            border-radius: 22px;
            background: rgba(13, 34, 62, 0.72);
            border: 1px solid rgba(132, 174, 255, 0.16);
            transition: all 0.2s ease;
        }
        .top-tab.active {
            background: linear-gradient(135deg, #2f78ff, #145bd8);
            color: #fff;
            font-weight: 700;
            border-color: rgba(111, 160, 255, 0.55);
            box-shadow: 0 12px 24px rgba(47, 120, 255, 0.28);
        }
        .top-tab:hover {
            border-color: rgba(104, 151, 255, 0.36);
            color: #fff;
        }
        .top-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        .search-wrap {
            position: relative;
            margin: 0;
        }
        .search-wrap svg {
            position: absolute;
            left: 13px;
            top: 50%;
            width: 16px;
            height: 16px;
            transform: translateY(-50%);
            color: #8ba4d5;
            pointer-events: none;
        }
        .search {
            width: 310px;
            max-width: 38vw;
            border: 1px solid rgba(104, 151, 255, 0.18);
            border-radius: 8px;
            padding: 10px 14px 10px 38px;
            font-size: 12px;
            color: #eef5ff;
            outline: none;
            background: rgba(11, 30, 56, 0.76);
        }
        .search::placeholder {
            color: #8ba4d5;
        }
        .search:focus {
            border-color: rgba(47, 120, 255, 0.8);
            box-shadow: 0 0 0 3px rgba(47, 120, 255, 0.18);
        }
        .status-pill {
            border: 1px solid rgba(104, 151, 255, 0.18);
            border-radius: 8px;
            font-size: 11px;
            color: #e2ebfb;
            padding: 9px 11px;
            background: rgba(11, 30, 56, 0.66);
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .status-pill svg {
            width: 15px;
            height: 15px;
            color: #8ba4d5;
        }
        .status-pill.settings {
            width: 36px;
            height: 36px;
            justify-content: center;
            padding: 0;
            background: rgba(11, 30, 56, 0.78);
            border-color: rgba(104, 151, 255, 0.2);
            color: #b7c6e4;
            cursor: pointer;
        }
        .settings-menu {
            position: relative;
        }
        .settings-menu summary {
            list-style: none;
        }
        .settings-menu summary::-webkit-details-marker {
            display: none;
        }
        .settings-menu summary:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(47, 120, 255, 0.24);
        }
        .settings-menu[open] .status-pill.settings {
            border-color: rgba(91, 147, 255, 0.7);
            box-shadow: 0 12px 26px rgba(47, 120, 255, 0.3);
        }
        .settings-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            min-width: 220px;
            background: linear-gradient(160deg, rgba(18, 47, 84, 0.98), rgba(12, 35, 65, 0.98));
            border: 1px solid rgba(104, 151, 255, 0.28);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            z-index: 30;
        }
        .settings-title {
            font-size: 12px;
            font-weight: 700;
            color: #f4f7ff;
        }
        .settings-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            font-size: 11px;
            color: #9db1d3;
        }
        .settings-row strong {
            color: #e8f0ff;
            font-weight: 700;
        }
        .settings-divider {
            height: 1px;
            background: rgba(104, 151, 255, 0.2);
            margin: 8px 0;
        }
        .settings-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid rgba(104, 151, 255, 0.3);
            background: rgba(11, 30, 56, 0.7);
            color: #d5e0f2;
            text-decoration: none;
        }
        .settings-link:hover {
            border-color: rgba(104, 151, 255, 0.5);
            color: #fff;
        }
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: radial-gradient(circle at 73% 78%, #52e66c 0 10%, transparent 11%),
                linear-gradient(145deg, #2e78ff, #113a9f);
            border: 1px solid rgba(104, 151, 255, 0.28);
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.25);
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .avatar::before {
            content: attr(data-initials);
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
        }
        .user-menu {
            position: relative;
        }
        .user-menu summary {
            list-style: none;
        }
        .user-menu summary::-webkit-details-marker {
            display: none;
        }
        .user-menu summary:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(47, 120, 255, 0.24);
        }
        .user-menu[open] .avatar {
            border-color: rgba(91, 147, 255, 0.7);
            box-shadow: 0 12px 26px rgba(47, 120, 255, 0.3);
        }
        .user-menu-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            min-width: 200px;
            background: linear-gradient(160deg, rgba(18, 47, 84, 0.98), rgba(12, 35, 65, 0.98));
            border: 1px solid rgba(104, 151, 255, 0.28);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            z-index: 30;
        }
        .user-menu-name {
            font-size: 12px;
            font-weight: 700;
            color: #f4f7ff;
        }
        .user-menu-meta {
            font-size: 11px;
            color: #9db1d3;
            margin-top: 4px;
        }
        .user-menu-divider {
            height: 1px;
            background: rgba(104, 151, 255, 0.2);
            margin: 10px 0;
        }
        .user-menu-action {
            width: 100%;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid rgba(104, 151, 255, 0.3);
            background: rgba(11, 30, 56, 0.7);
            color: #d5e0f2;
            cursor: pointer;
        }
        .user-menu-action:hover {
            border-color: rgba(104, 151, 255, 0.5);
            color: #fff;
        }
        .content {
            min-width: 0;
            animation: fadeIn 0.35s ease;
        }
        .ui-panel {
            background: linear-gradient(180deg, rgba(18, 47, 84, 0.94), rgba(12, 35, 65, 0.94));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px;
            box-shadow: var(--shadow), inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }
        .panel-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: var(--text);
        }
        .panel-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: var(--muted);
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            display: inline-block;
            margin-right: 6px;
        }
        .status-success { background: var(--success); }
        .status-warning { background: var(--warning); }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .table-wrap {
            overflow-x: auto;
        }
        .table th {
            text-align: left;
            color: #adc0dc;
            font-weight: 700;
            border-bottom: 1px solid rgba(104, 151, 255, 0.13);
            padding: 10px 8px;
        }
        .table td {
            border-bottom: 1px solid rgba(104, 151, 255, 0.09);
            padding: 10px 8px;
            color: #e2ebfb;
        }
        .table tbody tr:hover {
            background: rgba(47, 120, 255, 0.08);
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(47, 120, 255, 0.14);
            color: #8db5ff;
            border: 1px solid rgba(104, 151, 255, 0.24);
        }
        .badge.success {
            background: rgba(57, 228, 123, 0.14);
            color: #55ef91;
            border-color: rgba(57, 228, 123, 0.26);
        }
        .badge.warning {
            background: rgba(255, 179, 62, 0.14);
            color: #ffc164;
            border-color: rgba(255, 179, 62, 0.26);
        }
        .badge.danger {
            background: rgba(255, 76, 97, 0.14);
            color: #ff6a79;
            border-color: rgba(255, 76, 97, 0.26);
        }
        .badge.info {
            background: rgba(17, 215, 207, 0.12);
            color: #61ede8;
            border-color: rgba(17, 215, 207, 0.22);
        }
        .badge.neutral {
            background: rgba(145, 165, 199, 0.12);
            color: #c3d0e8;
            border-color: rgba(145, 165, 199, 0.2);
        }
        .btn {
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #fff;
            background: linear-gradient(135deg, #2f78ff, #145bd8);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(47, 120, 255, 0.24);
        }
        .btn-outline {
            background: rgba(11, 30, 56, 0.58);
            color: #c7d5f0;
            border-color: rgba(104, 151, 255, 0.2);
            box-shadow: none;
        }
        .btn-success {
            background: linear-gradient(135deg, #43e692, #0b9f68);
            box-shadow: 0 8px 16px rgba(34, 197, 94, 0.22);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff5368, #a50f2a);
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.24);
        }
        .btn-info {
            background: linear-gradient(135deg, #2f78ff, #1554c7);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.24);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 1200px) {
            .frame {
                grid-template-columns: 82px 1fr;
            }
            .search {
                width: 240px;
            }
            .top-tab {
                padding-inline: 13px;
            }
        }
        @media (max-width: 900px) {
            .frame {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 10px 10px 92px;
            }
            .sidebar {
                position: fixed;
                left: 10px;
                right: 10px;
                bottom: 10px;
                top: auto;
                height: auto;
                z-index: 40;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 12px;
                padding: 10px 12px;
                border-radius: 14px;
                overflow-x: auto;
            }
            .brand,
            .nav-divider {
                display: none;
            }
            .icon-nav {
                width: 100%;
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;
                gap: 8px;
                margin-top: 0;
                overflow-x: auto;
                padding-bottom: 2px;
            }
            .nav-group {
                flex-direction: row;
                gap: 8px;
                width: auto;
                flex-shrink: 0;
            }
            .icon-link {
                width: auto;
                min-width: 66px;
                height: 56px;
                padding: 10px 12px;
                border-radius: 10px;
                flex-direction: column;
                justify-content: center;
                gap: 5px;
            }
            .icon-link .label {
                display: block;
                font-size: 10px;
                line-height: 1;
                white-space: nowrap;
                max-width: 78px;
            }
            .icon-link[data-tooltip]::after {
                display: none;
            }
            .topbar {
                flex-direction: column;
                align-items: stretch;
                padding: 12px;
            }
            .top-tabs {
                display: none;
            }
            .top-controls {
                width: 100%;
                margin-left: 0;
                flex-wrap: wrap;
            }
            .search-wrap {
                width: 100%;
            }
            .search {
                max-width: none;
                width: 100%;
            }
            .status-pill {
                display: none;
            }
            .avatar {
                display: none;
            }
            .ui-panel {
                padding: 12px;
                border-radius: 12px;
            }
            .table {
                min-width: 720px;
            }
        }
        @media (max-width: 560px) {
            body {
                background:
                    radial-gradient(circle at 20% 15%, rgba(17, 215, 207, 0.12) 0%, transparent 42%),
                    radial-gradient(circle at 80% 0%, rgba(47, 120, 255, 0.24) 0%, transparent 35%),
                    var(--bg);
            }
            .frame {
                padding: 8px 8px 92px;
            }
            .topbar {
                padding: 10px;
            }
            .search {
                padding: 10px 12px 10px 38px;
            }
            .icon-link {
                min-width: 62px;
            }
            .icon-link .icon svg {
                width: 18px;
                height: 18px;
            }
            .content {
                padding-bottom: 2px;
            }
        }
    </style>
</head>
<body>
<div class="frame">
    <aside class="sidebar">
        <div class="brand">
            <?php $logo = $buildUrl('/images/rss_logo.png'); ?>
            <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt="RSS Inventory" class="brand-logo" />
        </div>
        <nav class="icon-nav">
            <?php foreach ($menuGroups as $groupIndex => $group): ?>
                <div class="nav-group">
                    <?php foreach ($group['items'] as $item): ?>
                        <?php
                        $path = $item['path'];
                        if (!in_array($path, $knownRoutes, true)) {
                            continue;
                        }
                        $isActive = $activeRoute === $path;
                        $href = $buildUrl($path);
                        ?>
                        <?php $iconSvg = $iconSvgs[$item['icon']] ?? ''; ?>
                        <a
                            href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
                            class="icon-link<?= $isActive ? ' active' : '' ?>"
                            aria-label="<?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <span class="icon" aria-hidden="true"><?= $iconSvg ?></span>
                            <span class="label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php if ($groupIndex < count($menuGroups) - 1): ?>
                    <div class="nav-divider" role="separator" aria-hidden="true"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main class="workspace">
        <div class="topbar">
            <div class="top-tabs">
                <?php
                $topTabs = [
                    ['path' => '/', 'label' => 'Dashboard'],
                    ['path' => '/products', 'label' => 'Products'],
                    ['path' => '/stock', 'label' => 'Stock'],
                    ['path' => '/accountability', 'label' => 'Accountability'],
                    ['path' => '/reports', 'label' => 'Reports'],
                ];
                ?>
                <?php foreach ($topTabs as $tab): ?>
                    <?php $tabActive = $activeRoute === $tab['path']; ?>
                    <a
                        class="top-tab<?= $tabActive ? ' active' : '' ?>"
                        href="<?= htmlspecialchars($buildUrl($tab['path']), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="top-controls">
                <?php
                $globalSearchValue = '';
                if ($activeRoute === '/products' && isset($filters) && is_array($filters)) {
                    $globalSearchValue = (string) ($filters['q'] ?? '');
                }
                ?>
                <form class="search-wrap" method="get" action="<?= htmlspecialchars($buildUrl('/products'), ENT_QUOTES, 'UTF-8') ?>" role="search">
                    <label class="sr-only" for="globalSearch">Search products</label>
                    <?= $iconSvgs['search'] ?>
                    <input
                        id="globalSearch"
                        class="search"
                        type="search"
                        name="q"
                        placeholder="Search products, Product ID, category..."
                        value="<?= htmlspecialchars($globalSearchValue, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </form>
                <span class="status-pill"><?= $iconSvgs['calendar'] ?><?= htmlspecialchars(date('D, d M'), ENT_QUOTES, 'UTF-8') ?></span>
                <details class="settings-menu">
                    <summary class="status-pill settings" aria-label="Settings">
                        <?= $iconSvgs['settings'] ?>
                    </summary>
                    <div class="settings-panel">
                        <div class="settings-title">Quick settings</div>
                        <div class="settings-row">
                            <span>Timezone</span>
                            <strong><?= htmlspecialchars(date('T'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <div class="settings-row">
                            <span>Status</span>
                            <strong>Online</strong>
                        </div>
                        <div class="settings-divider"></div>
                        <a class="settings-link" href="<?= htmlspecialchars($buildUrl('/reports'), ENT_QUOTES, 'UTF-8') ?>">Open reports</a>
                    </div>
                </details>
                <?php if ($authName !== ''): ?>
                    <details class="user-menu">
                        <summary class="avatar" aria-label="User menu" data-initials="<?= htmlspecialchars($authInitials, ENT_QUOTES, 'UTF-8') ?>"></summary>
                        <div class="user-menu-panel">
                            <div class="user-menu-name"><?= htmlspecialchars($authName, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="user-menu-meta">Internal employee</div>
                            <div class="user-menu-divider"></div>
                            <form method="post" action="<?= htmlspecialchars($buildUrl('/logout'), ENT_QUOTES, 'UTF-8') ?>">
                                <button class="user-menu-action" type="submit">Sign out</button>
                            </form>
                        </div>
                    </details>
                <?php else: ?>
                    <span class="avatar" aria-hidden="true" data-initials="AD"></span>
                <?php endif; ?>
            </div>
        </div>
        <section class="content">
            <?= $content ?>
        </section>
    </main>
</div>
<style>
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .module-filters,
    .users-search-form {
        background: rgba(11, 30, 56, 0.58) !important;
        border-color: rgba(104, 151, 255, 0.16) !important;
        border-radius: 10px !important;
    }
    .module-search,
    .module-select,
    .module-input,
    .users-search,
    .modal-input,
    .modal-select,
    .modal-textarea {
        background: rgba(11, 30, 56, 0.78) !important;
        border-color: rgba(104, 151, 255, 0.2) !important;
        color: #e6eeff !important;
        border-radius: 8px !important;
    }
    .module-search::placeholder,
    .module-input::placeholder,
    .users-search::placeholder,
    .modal-input::placeholder,
    .modal-textarea::placeholder {
        color: #7890ba !important;
    }
    .module-select option,
    .modal-select option {
        background: #102a4a;
        color: #e6eeff;
    }
    .summary-card,
    .kpi-box {
        background: linear-gradient(180deg, rgba(18, 47, 84, 0.94), rgba(12, 35, 65, 0.94)) !important;
        border-color: rgba(104, 151, 255, 0.18) !important;
        border-radius: 12px !important;
        box-shadow: var(--shadow), inset 0 1px 0 rgba(255, 255, 255, 0.04) !important;
    }
    .summary-icon,
    .category-art {
        background: rgba(47, 120, 255, 0.16) !important;
        border-color: rgba(104, 151, 255, 0.22) !important;
        color: #8db5ff !important;
    }
    .summary-card .label,
    .kpi-box .label,
    .modal-label,
    .pagination,
    .users-empty,
    .empty-state,
    .table-muted {
        color: #adc0dc !important;
    }
    .summary-card .value,
    .kpi-box .value,
    .category-name,
    .employee-link {
        color: #f6f9ff !important;
    }
    .employee-link:hover,
    .panel-link {
        color: #6ea2ff !important;
    }
    .modal-backdrop {
        background: rgba(0, 4, 12, 0.72) !important;
        backdrop-filter: blur(12px);
    }
    .modal-card {
        background: linear-gradient(180deg, #163a65, #102a4a) !important;
        border-color: rgba(104, 151, 255, 0.24) !important;
        border-radius: 12px !important;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.48) !important;
    }
    .icon-btn,
    .page-btn,
    .link-btn,
    .inline-form button,
    .action-btn.alt {
        background: rgba(11, 30, 56, 0.68) !important;
        border-color: rgba(104, 151, 255, 0.2) !important;
        color: #c7d5f0 !important;
        border-radius: 8px !important;
    }
    .inline-form .receive {
        background: rgba(57, 228, 123, 0.14) !important;
        border-color: rgba(57, 228, 123, 0.26) !important;
        color: #55ef91 !important;
    }
    .inline-form .cancel {
        background: rgba(255, 76, 97, 0.14) !important;
        border-color: rgba(255, 76, 97, 0.26) !important;
        color: #ff6a79 !important;
    }
    .page-btn.active {
        background: rgba(47, 120, 255, 0.2) !important;
        border-color: rgba(47, 120, 255, 0.58) !important;
        color: #8db5ff !important;
    }
    .action-btn {
        background: linear-gradient(135deg, #2f78ff, #145bd8) !important;
        color: #fff !important;
        border-radius: 8px !important;
    }
    .module-notice {
        color: #7cf0ec !important;
        background: rgba(17, 215, 207, 0.1) !important;
        border-color: rgba(17, 215, 207, 0.24) !important;
    }
    .module-error {
        color: #ff93a0 !important;
        background: rgba(255, 76, 97, 0.12) !important;
        border-color: rgba(255, 76, 97, 0.26) !important;
    }
    .inline-return button {
        background: rgba(255, 179, 62, 0.12) !important;
        border-color: rgba(255, 179, 62, 0.26) !important;
        color: #ffc164 !important;
    }
</style>
</body>
</html>
