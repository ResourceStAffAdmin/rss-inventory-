<?php

declare(strict_types=1);

/** @var string $description */
/** @var array<string,string> $filters */
/** @var array<int, array<string, string|int>> $tableRows */

$buildUrl = static function (string $path): string {
    return function_exists('app_url') ? app_url($path) : $path;
};

$hasQuery = trim((string) ($filters['q'] ?? '')) !== '';
?>
<style>
    .users-shell {
        display: grid;
        gap: 12px;
    }
    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }
    .users-search-form {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        width: 100%;
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
    }
    .users-search {
        flex: 1 1 320px;
        border: 1px solid #e7edf4;
        border-radius: 999px;
        padding: 10px 14px;
        font-size: 13px;
        color: #4b5563;
        outline: none;
        background: #fff;
        min-width: 0;
    }
    .users-empty {
        text-align: center;
        font-size: 13px;
        color: #64748b;
        padding: 24px 12px;
    }
    .employee-link {
        color: #0f172a;
        text-decoration: none;
        font-weight: 700;
    }
    .employee-link:hover {
        color: #2563eb;
    }
    @media (max-width: 900px) {
        .users-header {
            flex-direction: column;
            align-items: stretch;
        }
        .users-search,
        .users-search-form .btn {
            width: 100%;
            flex: 1 1 100%;
        }
    }
</style>

<section class="users-shell">
    <article class="ui-panel users-header">
        <div>
            <h2 class="panel-title" style="font-size:20px; margin-bottom:6px;">Users & Roles</h2>
            <p class="panel-subtitle"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <form class="users-search-form" method="get" action="<?= htmlspecialchars($buildUrl('/users'), ENT_QUOTES, 'UTF-8') ?>">
            <input
                class="users-search"
                type="search"
                name="q"
                placeholder="Search employee by name, email, position, or company"
                value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
            <button class="btn btn-outline" type="submit">Search</button>
            <?php if ($hasQuery): ?>
                <a class="btn btn-outline" href="<?= htmlspecialchars($buildUrl('/users'), ENT_QUOTES, 'UTF-8') ?>">Clear</a>
            <?php endif; ?>
        </form>
    </article>

    <article class="ui-panel">
        <?php if (!$hasQuery): ?>
            <div class="users-empty">Start typing a name, email, position, or company to search employees.</div>
        <?php elseif ($tableRows === []): ?>
            <div class="users-empty">No employees matched your search.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Company</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tableRows as $row): ?>
                        <tr>
                            <td>
                                <a class="employee-link" href="<?= htmlspecialchars($buildUrl('/users/' . (int) $row['id']), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string) $row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['position'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['company'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>
