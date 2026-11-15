<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$errors = array();
$defaults = array(
    'nombre_tienda' => setting_value('nombre_tienda', 'Katy Flores'),
    'eslogan' => setting_value('eslogan', 'Prendas únicas, nuevas historias'),
    'mensaje_inicio' => setting_value('mensaje_inicio', ''),
    'whatsapp' => setting_value('whatsapp', ''),
    'public_url' => setting_value('public_url', ''),
    'telefono' => setting_value('telefono', ''),
    'direccion' => setting_value('direccion', '')
);
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $settings = array();
    foreach ($defaults as $key => $unused) {
        $settings[$key] = trim(isset($_POST[$key]) ? (string) $_POST[$key] : '');
    }
    $newUsername = trim(isset($_POST['usuario']) ? (string) $_POST['usuario'] : '');
    $currentPassword = isset($_POST['password_actual']) ? (string) $_POST['password_actual'] : '';
    $newPassword = isset($_POST['password_nueva']) ? (string) $_POST['password_nueva'] : '';
    $confirmPassword = isset($_POST['password_confirmar']) ? (string) $_POST['password_confirmar'] : '';
    $settings['whatsapp'] = preg_replace('/\D+/', '', $settings['whatsapp']);
    if ($settings['nombre_tienda'] === '' || strlen($settings['nombre_tienda']) > 100) { $errors[] = 'El nombre de la tienda es obligatorio (máximo 100 caracteres).'; }
    if (strlen($settings['eslogan']) > 150 || strlen($settings['mensaje_inicio']) > 500 || strlen($settings['telefono']) > 40 || strlen($settings['direccion']) > 180 || strlen($settings['public_url']) > 255) { $errors[] = 'Algún dato de contacto o presentación es demasiado largo.'; }
    if ($settings['public_url'] !== '' && (!filter_var($settings['public_url'], FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($settings['public_url'], PHP_URL_SCHEME)), array('http', 'https'), true))) { $errors[] = 'El enlace público debe comenzar con http:// o https://'; }
    if ($settings['whatsapp'] !== '' && (strlen($settings['whatsapp']) < 7 || strlen($settings['whatsapp']) > 15)) { $errors[] = 'El WhatsApp debe incluir el código de país y tener entre 7 y 15 dígitos.'; }
    if (!preg_match('/^[A-Za-z0-9_.-]{4,60}$/', $newUsername)) { $errors[] = 'El usuario debe tener entre 4 y 60 caracteres: letras, números, punto, guion o guion bajo.'; }
    $credentialsChanged = $newUsername !== $username || $newPassword !== '';
    if ($credentialsChanged) {
        if ($currentPassword === '') { $errors[] = 'Escribe tu contraseña actual para cambiar los datos de acceso.'; }
        if ($newPassword !== '' && strlen($newPassword) < 10) { $errors[] = 'La nueva contraseña debe tener al menos 10 caracteres.'; }
        if ($newPassword !== $confirmPassword) { $errors[] = 'Las nuevas contraseñas no coinciden.'; }
        if (!$errors) {
            $current = db()->prepare('SELECT password_hash FROM usuarios WHERE id = ?');
            $current->execute(array((int) $_SESSION['admin_id']));
            $storedHash = $current->fetchColumn();
            if (!$storedHash || !password_verify($currentPassword, $storedHash)) { $errors[] = 'La contraseña actual no es correcta.'; }
        }
    }
    if (!$errors) {
        $check = db()->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = ? AND id <> ?');
        $check->execute(array($newUsername, (int) $_SESSION['admin_id']));
        if ((int) $check->fetchColumn() > 0) { $errors[] = 'Ese nombre de usuario ya está en uso.'; }
    }
    if (!$errors) {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            $saveSetting = $pdo->prepare('INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
            foreach ($settings as $key => $value) { $saveSetting->execute(array($key, $value)); }
            if ($credentialsChanged) {
                if ($newPassword !== '') {
                    $account = $pdo->prepare('UPDATE usuarios SET usuario = ?, password_hash = ? WHERE id = ?');
                    $account->execute(array($newUsername, password_hash($newPassword, PASSWORD_DEFAULT), (int) $_SESSION['admin_id']));
                } else {
                    $account = $pdo->prepare('UPDATE usuarios SET usuario = ? WHERE id = ?');
                    $account->execute(array($newUsername, (int) $_SESSION['admin_id']));
                }
                $_SESSION['admin_username'] = $newUsername;
            }
            $pdo->commit();
            set_flash('success', 'La configuración se guardó.');
            redirect('admin/configuracion.php');
        } catch (Exception $exception) {
            if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
            $errors[] = 'No se pudo guardar la configuración. Inténtalo de nuevo.';
        }
    }
    $defaults = $settings;
    $username = $newUsername;
}

$adminTitle = 'Configuración'; $activePage = 'configuracion';
require __DIR__ . '/header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Datos de tu tienda</p><h1>Configuración</h1><p>Actualiza el WhatsApp, la información pública y tu acceso.</p></div></div>
<?php if ($errors): ?><div class="alert alert-error"><strong>Revisa estos datos:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<section class="admin-panel"><form class="admin-form" method="post" action="<?= e(url('admin/configuracion.php')) ?>"><?= csrf_field() ?>
    <div class="admin-panel-heading"><div><h2>Información de la tienda</h2><p>Estos datos se muestran en el catálogo público.</p></div></div>
    <div class="admin-form-grid"><label>Nombre de la tienda<input type="text" name="nombre_tienda" maxlength="100" required value="<?= e($defaults['nombre_tienda']) ?>"></label><label>Eslogan<input type="text" name="eslogan" maxlength="150" value="<?= e($defaults['eslogan']) ?>"></label></div>
    <label>Mensaje de bienvenida<textarea name="mensaje_inicio" maxlength="500" placeholder="Una presentación breve para la página principal."><?= e($defaults['mensaje_inicio']) ?></textarea></label>
    <div class="admin-form-grid"><label>WhatsApp de la tienda<input type="tel" name="whatsapp" inputmode="numeric" placeholder="59171234567" value="<?= e($defaults['whatsapp']) ?>"><small class="help-text">Código de país y número, solo dígitos. Ejemplo Bolivia: 59171234567.</small></label><label>Teléfono adicional<input type="text" name="telefono" maxlength="40" value="<?= e($defaults['telefono']) ?>"></label></div>
    <label>Enlace público del catálogo<input type="url" name="public_url" maxlength="255" placeholder="https://tudominio.com" value="<?= e($defaults['public_url']) ?>"><small class="help-text">Cuando lo publiques, agrega aquí la dirección del sitio. El QR de la página principal usará este enlace.</small></label>
    <label>Dirección o zona<input type="text" name="direccion" maxlength="180" value="<?= e($defaults['direccion']) ?>"></label>
    <div class="detail-divider"></div><div class="admin-panel-heading"><div><h2>Datos de acceso</h2><p>Deja la nueva contraseña vacía si no deseas cambiarla.</p></div></div>
    <div class="admin-form-grid"><label>Usuario<input type="text" name="usuario" maxlength="60" required value="<?= e($username) ?>" autocomplete="username"></label><label>Contraseña actual<input type="password" name="password_actual" autocomplete="current-password"><small class="help-text">Necesaria solo si cambias el usuario o contraseña.</small></label><label>Nueva contraseña<input type="password" name="password_nueva" minlength="10" autocomplete="new-password"><small class="help-text">Mínimo 10 caracteres.</small></label><label>Repite la nueva contraseña<input type="password" name="password_confirmar" minlength="10" autocomplete="new-password"></label></div>
    <div class="form-actions"><button class="button button-primary" type="submit">Guardar configuración</button></div>
</form></section>
<?php require __DIR__ . '/footer.php'; ?>
