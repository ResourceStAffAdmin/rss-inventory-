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
<main class="auth-page">
    <section class="auth-intro" aria-labelledby="inventory-heading">
        <header class="brand">
            <img class="brand-logo" src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt="">
            <div>
                <p class="brand-title">RSS <span>Inventory</span></p>
                <p class="brand-subtitle">Internal inventory and stock management</p>
            </div>
        </header>

        <h1 class="hero-title" id="inventory-heading">
            Inventory management
            <span>made simple.</span>
        </h1>
        <p class="hero-copy">
            Track stock in real time, manage items and suppliers, and gain the visibility you need to make better
            inventory decisions.
        </p>

        <div class="feature-list" aria-label="Inventory system benefits">
            <article class="feature">
                <div class="feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m24 3.8 18 10.4v19.6L24 44.2 6 33.8V14.2L24 3.8Z"/>
                        <path d="m6.8 14.6 17.2 10 17.2-10M24 24.6v19"/>
                        <path d="m15.2 9 17.5 10.2"/>
                    </svg>
                </div>
                <h3>Track Stock</h3>
                <p>Real-time visibility across all locations.</p>
            </article>

            <article class="feature">
                <div class="feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="19" cy="15" r="7"/>
                        <circle cx="32.5" cy="15.5" r="6"/>
                        <path d="M5.5 39v-4.5A8.5 8.5 0 0 1 14 26h10a8.5 8.5 0 0 1 8.5 8.5V39h-27Z"/>
                        <path d="M32 27h2.5a8 8 0 0 1 8 8v4H36"/>
                    </svg>
                </div>
                <h3>Manage Items</h3>
                <p>Organize items and manage suppliers.</p>
            </article>

            <article class="feature">
                <div class="feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M24 3.5 40 10v12.2c0 10.1-6.5 18.5-16 22.3-9.5-3.8-16-12.2-16-22.3V10l16-6.5Z"/>
                        <path d="m17 24 4.5 4.5L31.5 18"/>
                    </svg>
                </div>
                <h3>Secure Access</h3>
                <p>Role-based access and data protection.</p>
            </article>
        </div>
    </section>

    <section class="auth-panel" aria-labelledby="sign-in-heading">
        <div class="auth-card">
            <header class="auth-heading">
                <div class="lock-badge" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="7" y="14" width="18" height="14" rx="2.5"/>
                        <path d="M11 14V9a5 5 0 0 1 10 0v5"/>
                        <path d="M16 20v3"/>
                    </svg>
                </div>
                <h2 class="auth-title" id="sign-in-heading">Sign in</h2>
                <p class="auth-subtitle">Internal access only</p>
            </header>

            <?php if ($errorMessage): ?>
                <div class="auth-alert" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="<?= htmlspecialchars($buildUrl('/login'), ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($next ?? '')): ?>
                    <input type="hidden" name="next" value="<?= htmlspecialchars($next ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>

                <div class="auth-fields">
                    <div class="field">
                        <label for="username">Username</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M5 21v-2a7 7 0 0 1 14 0v2H5Z"/>
                            </svg>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                autocomplete="username"
                                placeholder="Enter your username"
                                value="<?= htmlspecialchars($formValues['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="10" width="14" height="11" rx="2"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>
                            </svg>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                required
                            >
                            <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                    <circle cx="12" cy="12" r="2.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1">
                        <span class="checkmark" aria-hidden="true">
                            <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m2 6 2.5 2.5L10 3"/>
                            </svg>
                        </span>
                        Remember me
                    </label>
                </div>

                <button class="auth-btn" type="submit">Sign in</button>
                <p class="auth-help">Need access? <span>Contact IT</span></p>
            </form>
        </div>
    </section>
</main>

<script>
    (() => {
        const toggle = document.querySelector('.password-toggle');
        const password = document.querySelector('#password');

        if (!toggle || !password) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isVisible = password.type === 'text';
            password.type = isVisible ? 'password' : 'text';
            toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
            toggle.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
            password.focus();
        });
    })();
</script>
