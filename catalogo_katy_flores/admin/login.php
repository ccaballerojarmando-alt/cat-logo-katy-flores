<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (is_admin()) { redirect('admin/dashboard.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim(isset($_POST['usuario']) ? (string) $_POST['usuario'] : '');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    try {
        $statement = db()->prepare('SELECT id, nombre, usuario, password_hash FROM usuarios WHERE usuario = ? LIMIT 1');
        $statement->execute(array($username));
        $user = $statement->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $user['id'];
            $_SESSION['admin_name'] = $user['nombre'];
            $_SESSION['admin_username'] = $user['usuario'];
            redirect('admin/dashboard.php');
        }
        $error = 'El usuario o la contraseña no son correctos.';
    } catch (Exception $exception) {
        $error = 'No se pudo conectar con el sistema. Revisa la instalación de la base de datos.';
    }
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Ingresar | Katy Flores</title><link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>"></head><body class="login-page"><section class="login-card">
    <div class="login-brand"><img src="<?= e(url('assets/img/logo.svg')) ?>" alt=""><span><strong>Katy Flores</strong><small>Administración del catálogo</small></span></div><h1>Qué gusto verte</h1><p>Ingresa para actualizar tus prendas.</p>
    <?php $flash = get_flash(); if ($flash): ?><div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('admin/login.php')) ?>"><?= csrf_field() ?><label>Usuario<input type="text" name="usuario" required autofocus autocomplete="username" value="<?= e(isset($_POST['usuario']) ? $_POST['usuario'] : '') ?>"></label><label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label><button class="button button-primary" type="submit">Ingresar al panel <span aria-hidden="true">→</span></button></form>
    <a class="login-back" href="<?= e(url('index.php')) ?>">Volver al catálogo</a>
</section></body></html>
