<?php
declare(strict_types=1);

function config(): array
{
    $file = __DIR__ . '/config.local.php';
    return is_file($file) ? require $file : [];
}

function database(array $config): PDO
{
    if (empty($config['dsn']) || empty($config['db_user'])) {
        throw new RuntimeException('Database configuration is missing');
    }
    return new PDO($config['dsn'], $config['db_user'], $config['db_password'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function startSession(): void
{
    session_start([
        'use_strict_mode' => 1,
        'cookie_httponly' => true,
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'cookie_samesite' => 'Lax',
    ]);
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: default-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function validCsrf(): bool
{
    return is_string($_POST['csrf'] ?? null) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function post(string $key): string
{
    return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
}

function pageStart(string $title): void
{
    ?>
<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · MntlGym</title>
    <link rel="stylesheet" href="assets/site.css">
</head>
<body>
    <header class="topbar">
        <div class="wrap topbar-inner">
            <a class="brand" href="./" aria-label="MntlGym, inicio"><img src="assets/mntlgym.svg" alt="MntlGym" width="108"></a>
            <span class="event"><span></span> ISATUM 2026</span>
        </div>
    </header>
    <?php
}

function pageEnd(): void
{
    ?>
    <footer class="wrap footer">
        <span>© <?= date('Y') ?> MntlGym</span>
        <a href="https://mntlgym.com/">Visita mntlgym.com ↗</a>
    </footer>
</body>
</html>
    <?php
}
