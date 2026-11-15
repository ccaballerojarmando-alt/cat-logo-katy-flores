<?php
require_once __DIR__ . '/includes/bootstrap.php';
$errors = array();
$complete = false;
try {
    $count = (int) db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    if ($count > 0) { $complete = true; }
} catch (Exception $exception) {
    $errors[] = 'No se pudo conectar con la base de datos. Revisa los datos de config/database.php y confirma que importaste el archivo SQL.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$complete && !$errors) {
    verify_csrf();
    $name = trim(isset($_POST['nombre']) ? (string) $_POST['nombre'] : '');
    $username = trim(isset($_POST['usuario']) ? (string) $_POST['usuario'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $confirm = isset($_POST['confirmar']) ? (string) $_POST['confirmar'] : '';
    if ($name === '' || strlen($name) > 100) { $errors[] = 'Escribe el nombre de la persona administradora.'; }
    if (!preg_match('/^[A-Za-z0-9_.-]{4,60}$/', $username)) { $errors[] = 'El usuario debe tener entre 4 y 60 caracteres: letras, números, punto, guion o guion bajo.'; }
    if (strlen($password) < 10) { $errors[] = 'La contraseña debe tener al menos 10 caracteres.'; }
    if ($password !== $confirm) { $errors[] = 'Las contraseñas no coinciden.'; }
    if (!$errors) {
        try {
            $statement = db()->prepare('INSERT INTO usuarios (nombre, usuario, password_hash) VALUES (?, ?, ?)');
            $statement->execute(array($name, $username, password_hash($password, PASSWORD_DEFAULT)));
            $complete = true;
        } catch (PDOException $exception) {
            $errors[] = 'No se pudo crear la cuenta. Comprueba que ese usuario no exista e inténtalo de nuevo.';
        }
    }
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Configuración inicial | Katy Flores</title><link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>"></head>
<body class="login-page"><section class="login-card">
    <div class="login-brand"><img src="<?= e(url('assets/img/logo.svg')) ?>" alt=""><span><strong>Katy Flores</strong><small>Configuración inicial</small></span></div>
    <?php if ($complete): ?><h1>¡Listo para empezar!</h1><p>La cuenta de administración está configurada. Ya puedes entrar al panel y publicar tus prendas.</p><a class="button button-primary" href="<?= e(url('admin/login.php')) ?>">Ir a administración <span aria-hidden="true">→</span></a><p class="login-back">Por seguridad, elimina setup.php del hosting una vez que hayas iniciado sesión.</p>
    <?php else: ?><h1>Crea tu acceso</h1><p>Este paso se realiza una sola vez. Luego usarás estos datos para administrar el catálogo.</p>
        <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" action="<?= e(url('setup.php')) ?>">
            <?= csrf_field() ?>
            <label>Tu nombre<input type="text" name="nombre" maxlength="100" required value="<?= e(isset($_POST['nombre']) ? $_POST['nombre'] : '') ?>" autocomplete="name"></label>
            <label>Usuario<input type="text" name="usuario" minlength="4" maxlength="60" required value="<?= e(isset($_POST['usuario']) ? $_POST['usuario'] : '') ?>" autocomplete="username"></label>
            <label>Contraseña<input type="password" name="password" minlength="10" required autocomplete="new-password"><small class="help-text">Usa 10 caracteres o más.</small></label>
            <label>Repite la contraseña<input type="password" name="confirmar" minlength="10" required autocomplete="new-password"></label>
            <button class="button button-primary" type="submit">Crear cuenta administradora</button>
        </form>
    <?php endif; ?>
    <a class="login-back" href="<?= e(url('index.php')) ?>">Volver al catálogo</a>
</section></body></html>
