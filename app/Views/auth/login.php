<?php

declare(strict_types=1);

/** @var string|null $errorMessage */
/** @var string|null $next */
/** @var array{username:string} $formValues */
$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};
$logo = $buildUrl('/images/rss_logo.png');
?>
<section class="auth-card">
    <header class="auth-header">
        <div class="brand">
            <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt="RSS Inventory">
            <div>
                <h1 class="brand-title">RSS Inventory</h1>
                <p class="brand-subtitle">Internal access only</p>
            </div>
        </div>
    </header>

    <div class="auth-body">
        <div>
            <h2 class="auth-title">Sign in</h2>
            <p class="auth-subtitle">Use your internal employee username.</p>
        </div>

        <?php if ($errorMessage): ?>
            <div class="auth-alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($buildUrl('/login'), ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($next ?? '')): ?>
                <input type="hidden" name="next" value="<?= htmlspecialchars($next ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <div class="auth-fields">
                <div class="field">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        autocomplete="username"
                        value="<?= htmlspecialchars($formValues['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                </div>
            </div>
            <div class="auth-actions">
                <button class="auth-btn" type="submit">Sign in</button>
                <span class="hint">Contact IT if you need access.</span>
            </div>
        </form>
    </div>
</section>
