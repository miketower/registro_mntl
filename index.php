<?php
declare(strict_types=1);
require __DIR__ . '/app.php';

startSession();
$fields = [
    'nombre' => ['Nombre', 'Tu nombre completo', 'text', 'name', 120],
    'correo' => ['Correo', 'nombre@ejemplo.com', 'email', 'email', 254],
    'celular' => ['Celular', '+52 81 1234 5678', 'tel', 'tel', 32],
    'ocupacion' => ['¿A qué te dedicas?', 'Tu profesión o actividad', 'text', 'organization-title', 160],
];
$values = [];
$errors = [];
$message = '';
$registered = !empty($_SESSION['registered']);
unset($_SESSION['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $key => [$label, $placeholder, $type, $autocomplete, $length]) {
        $value = $values[$key] = post($key);
        if ($value === '') {
            $errors[$key] = 'Completa este campo.';
        } elseif (!preg_match('/\A[^\p{C}]{1,' . $length . '}\z/u', $value)) {
            $errors[$key] = 'Revisa el texto y la longitud de este campo.';
        }
    }
    if (!isset($errors['correo']) && !filter_var($values['correo'], FILTER_VALIDATE_EMAIL)) {
        $errors['correo'] = 'Escribe un correo válido.';
    }
    $digits = preg_replace('/\D/', '', $values['celular']);
    if (!isset($errors['celular']) && (!preg_match('/^\+?[0-9(). -]+$/D', $values['celular']) || strlen($digits) < 10 || strlen($digits) > 15)) {
        $errors['celular'] = 'Escribe un celular de 10 a 15 dígitos.';
    }
    if (!validCsrf()) {
        http_response_code(400);
        $message = 'La sesión del formulario venció. Vuelve a enviar tus datos.';
    } elseif ($errors) {
        http_response_code(422);
    } else {
        try {
            $statement = database(config())->prepare(
                'INSERT INTO registros (nombre, correo, celular, ocupacion) VALUES (:nombre, :correo, :celular, :ocupacion)'
            );
            $statement->execute($values);
            $_SESSION['registered'] = true;
            unset($_SESSION['csrf']);
            header('Location: ./', true, 303);
            exit;
        } catch (PDOException | RuntimeException $exception) {
            error_log('MntlGym: no se pudo guardar un registro (' . $exception->getCode() . ')');
            http_response_code(503);
            $message = 'No pudimos guardar tu registro. Inténtalo de nuevo en unos minutos.';
        }
    }
}

pageStart('Registro ISATUM 2026');
?>
<main class="wrap layout">
    <section class="intro" aria-labelledby="intro-title">
        <p class="eyebrow">ISATUM 2026 <span>·</span> 24 de noviembre</p>
        <h1 id="intro-title">Tu diagnóstico<br> empieza <em>aquí.</em></h1>
        <p class="description">Regístrate para recibir el cuestionario de diagnóstico de salud mental y bienestar organizacional de MntlGym.</p>
        <div class="speaker">
            <span class="speaker-icon" aria-hidden="true">↗</span>
            <div>
                <strong>Jorge Mario Blanco</strong>
                <span>Fundador y CEO de MntlGym · Expositor en ISATUM 2026</span>
            </div>
        </div>
        <div class="next">
            <strong>¿Qué sigue?</strong>
            <p>En unos días te enviaremos por correo una liga al cuestionario. Responde las preguntas para obtener tu diagnóstico.</p>
        </div>
    </section>
    <section class="card" aria-labelledby="registration-title">
        <?php if ($registered): ?>
            <div class="confirmation" role="status">
                <span class="confirmation-icon" aria-hidden="true">✓</span>
                <h2 id="registration-title">¡Registro recibido!</h2>
                <p>En unos días recibirás en tu correo la liga al cuestionario.</p>
                <a class="return" href="./">Volver al formulario</a>
            </div>
        <?php else: ?>
            <h2 id="registration-title">Regístrate</h2>
            <p class="muted form-intro">Completa los cuatro campos obligatorios.</p>
            <?php if ($message): ?><p class="notice" role="alert"><?= e($message) ?></p><?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <?php foreach ($fields as $key => [$label, $placeholder, $type, $autocomplete, $length]): ?>
                    <div class="field">
                        <label for="<?= $key ?>"><?= e($label) ?> <span aria-hidden="true">*</span></label>
                        <input id="<?= $key ?>" name="<?= $key ?>" type="<?= $type ?>" autocomplete="<?= $autocomplete ?>"
                            maxlength="<?= $length ?>" placeholder="<?= e($placeholder) ?>" value="<?= e($values[$key] ?? '') ?>" required
                            <?php if (isset($errors[$key])): ?>aria-invalid="true" aria-describedby="<?= $key ?>-error"<?php endif; ?>>
                        <?php if (isset($errors[$key])): ?><small class="field-error" id="<?= $key ?>-error"><?= e($errors[$key]) ?></small><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button class="primary" type="submit">Enviar registro <span aria-hidden="true">→</span></button>
                <p class="privacy">MntlGym usará tus datos para contactarte sobre el diagnóstico.</p>
            </form>
        <?php endif; ?>
    </section>
</main>
<?php pageEnd(); ?>
