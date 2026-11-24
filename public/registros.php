<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app.php';

startSession();
header('X-Robots-Tag: noindex, nofollow');
$settings = config();
$enabled = !empty($settings['admin_user']) && !empty($settings['admin_password_hash']);
$authenticated = $enabled && ($_SESSION['admin_user'] ?? null) === $settings['admin_user'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrf()) {
        http_response_code(400);
        $message = 'La sesión venció. Inténtalo de nuevo.';
    } elseif ($authenticated && post('action') === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: registros.php', true, 303);
        exit;
    } elseif (!$authenticated && $enabled) {
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $validPassword = strlen($password) <= 1024 && password_verify($password, $settings['admin_password_hash']);
        if ($validPassword && hash_equals($settings['admin_user'], post('username'))) {
            session_regenerate_id(true);
            $_SESSION['admin_user'] = $settings['admin_user'];
            unset($_SESSION['csrf']);
            header('Location: registros.php', true, 303);
            exit;
        }
        http_response_code(403);
        $message = 'El usuario o la contraseña son incorrectos.';
    }
}

$rows = [];
$page = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]) ?: 1;
$pageSize = 25;
$hasMore = false;
if (!$enabled) {
    http_response_code(503);
    $message = 'El acceso al listado aún no está configurado.';
} elseif ($authenticated) {
    try {
        $statement = database($settings)->prepare(
            'SELECT nombre, correo, celular, ocupacion FROM registros ORDER BY id DESC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $pageSize + 1, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * $pageSize, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll();
        $hasMore = count($rows) > $pageSize;
        if ($hasMore) {
            array_pop($rows);
        }
    } catch (PDOException | RuntimeException $exception) {
        error_log('MntlGym: no se pudo consultar el listado (' . $exception->getCode() . ')');
        http_response_code(503);
        $message = 'No pudimos cargar los registros. Inténtalo de nuevo en unos minutos.';
    }
}

pageStart('Registros');
?>
<main class="wrap admin">
    <section class="card <?= $authenticated ? 'listing' : 'login' ?>" aria-labelledby="admin-title">
        <div class="admin-title">
            <h1 id="admin-title"><?= $authenticated ? 'Registros' : 'Acceso al listado' ?></h1>
            <?php if ($authenticated): ?>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <button class="secondary" name="action" value="logout">Cerrar sesión</button>
                </form>
            <?php endif; ?>
        </div>
        <?php if ($message): ?><p class="notice" role="alert"><?= e($message) ?></p><?php endif; ?>
        <?php if ($authenticated): ?>
            <?php if (!$message && !$rows): ?>
                <p class="muted"><?= $page === 1 ? 'Aún no hay registros.' : 'No hay registros en esta página.' ?></p>
            <?php elseif ($rows): ?>
                <div class="table-wrap" tabindex="0" aria-label="Tabla de registros">
                    <table>
                        <thead><tr><th scope="col">Nombre</th><th scope="col">Correo</th><th scope="col">Celular</th><th scope="col">Ocupación</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr><td><?= e($row['nombre']) ?></td><td><?= e($row['correo']) ?></td><td><?= e($row['celular']) ?></td><td><?= e($row['ocupacion']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <?php if (!$message && ($page > 1 || $hasMore)): ?>
                <nav class="pagination" aria-label="Páginas del listado">
                    <?php if ($page > 1): ?><a href="?pagina=<?= $page - 1 ?>">← Anterior</a><?php endif; ?>
                    <span>Página <?= $page ?></span>
                    <?php if ($hasMore): ?><a href="?pagina=<?= $page + 1 ?>">Siguiente →</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php elseif ($enabled): ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <div class="field">
                    <label for="username">Usuario</label>
                    <input id="username" name="username" autocomplete="username" required>
                </div>
                <div class="field">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <button class="primary" type="submit">Entrar <span aria-hidden="true">→</span></button>
            </form>
        <?php endif; ?>
    </section>
</main>
<?php pageEnd(); ?>
