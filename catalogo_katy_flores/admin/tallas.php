<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$errors = array();
$editId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
$edit = null;
if ($editId) {
    $statement = db()->prepare('SELECT id, nombre, orden FROM tallas WHERE id = ?');
    $statement->execute(array($editId));
    $edit = $statement->fetch();
    if (!$edit) { set_flash('error', 'No encontramos esa talla.'); redirect('admin/tallas.php'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($action === 'delete' && $id > 0) {
        try {
            $statement = db()->prepare('DELETE FROM tallas WHERE id = ?');
            $statement->execute(array($id));
            set_flash('success', 'La talla se eliminó.');
        } catch (PDOException $exception) {
            set_flash('error', 'No se puede eliminar esa talla porque todavía está asignada a prendas.');
        }
        redirect('admin/tallas.php');
    }
    if ($action === 'save') {
        $name = trim(isset($_POST['nombre']) ? (string) $_POST['nombre'] : '');
        $order = isset($_POST['orden']) && is_numeric($_POST['orden']) ? (int) $_POST['orden'] : 0;
        if ($name === '' || strlen($name) > 30) { $errors[] = 'Escribe un nombre de hasta 30 caracteres.'; }
        if (!$errors) {
            $check = db()->prepare('SELECT COUNT(*) FROM tallas WHERE nombre = ? AND id <> ?');
            $check->execute(array($name, $id));
            if ((int) $check->fetchColumn() > 0) { $errors[] = 'Ya existe una talla con ese nombre.'; }
        }
        if (!$errors) {
            if ($id > 0) {
                $statement = db()->prepare('UPDATE tallas SET nombre = ?, orden = ? WHERE id = ?');
                $statement->execute(array($name, $order, $id));
                set_flash('success', 'La talla se actualizó.');
            } else {
                $statement = db()->prepare('INSERT INTO tallas (nombre, orden) VALUES (?, ?)');
                $statement->execute(array($name, $order));
                set_flash('success', 'La talla se agregó.');
            }
            redirect('admin/tallas.php');
        }
        $edit = array('id' => $id, 'nombre' => $name, 'orden' => $order);
    }
}
$sizes = db()->query('SELECT s.id, s.nombre, s.orden, COUNT(ps.producto_id) AS prendas FROM tallas s LEFT JOIN producto_tallas ps ON ps.talla_id = s.id GROUP BY s.id ORDER BY s.orden, s.nombre')->fetchAll();
$adminTitle = 'Tallas'; $activePage = 'tallas';
require __DIR__ . '/header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Tallas disponibles</p><h1>Tallas</h1><p>Administra las opciones que podrás asignar a cada prenda.</p></div></div>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="admin-form-grid">
    <section class="admin-panel"><div class="admin-panel-heading"><div><h2><?= $edit ? 'Editar talla' : 'Agregar talla' ?></h2><p>Ejemplos: M, XL o Talla única.</p></div></div>
        <form class="admin-form" method="post" action="<?= e(url('admin/tallas.php' . ($edit ? '?editar=' . (int) $edit['id'] : ''))) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>"><label>Nombre<input type="text" name="nombre" maxlength="30" required placeholder="Ej. M" value="<?= e($edit ? $edit['nombre'] : '') ?>"></label><label>Orden para mostrar<input type="number" name="orden" min="0" max="999" value="<?= e($edit ? $edit['orden'] : (count($sizes) + 1)) ?>"><small class="help-text">Las tallas con número menor aparecen primero.</small></label><div class="form-actions"><button class="button button-primary" type="submit"><?= $edit ? 'Guardar cambios' : 'Agregar talla' ?></button><?php if ($edit): ?><a class="button button-outline" href="<?= e(url('admin/tallas.php')) ?>">Cancelar</a><?php endif; ?></div></form>
    </section>
    <section class="admin-panel"><div class="admin-panel-heading"><div><h2>Tallas actuales</h2><p><?= count($sizes) ?> en total</p></div></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Talla</th><th>Prendas</th><th>Acciones</th></tr></thead><tbody><?php foreach ($sizes as $size): ?><tr><td><strong><?= e($size['nombre']) ?></strong></td><td><?= (int) $size['prendas'] ?></td><td><div class="table-actions"><a class="small-action" href="<?= e(url('admin/tallas.php?editar=' . (int) $size['id'])) ?>">Editar</a><form class="inline-form" method="post" action="<?= e(url('admin/tallas.php')) ?>" onsubmit="return confirm('¿Eliminar esta talla? Solo se puede eliminar si no está asignada a prendas.');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $size['id'] ?>"><button class="small-action danger" type="submit">Eliminar</button></form></div></td></tr><?php endforeach; ?></tbody></table></div>
    </section>
</div>
<?php require __DIR__ . '/footer.php'; ?>
