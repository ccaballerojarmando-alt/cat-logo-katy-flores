<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$errors = array();
$editId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
$edit = null;
if ($editId) {
    $statement = db()->prepare('SELECT id, nombre FROM categorias WHERE id = ?');
    $statement->execute(array($editId));
    $edit = $statement->fetch();
    if (!$edit) { set_flash('error', 'No encontramos esa categoría.'); redirect('admin/categorias.php'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($action === 'delete' && $id > 0) {
        try {
            $statement = db()->prepare('DELETE FROM categorias WHERE id = ?');
            $statement->execute(array($id));
            set_flash('success', 'La categoría se eliminó.');
        } catch (PDOException $exception) {
            set_flash('error', 'No se puede eliminar esa categoría porque todavía tiene prendas asignadas.');
        }
        redirect('admin/categorias.php');
    }
    if ($action === 'save') {
        $name = trim(isset($_POST['nombre']) ? (string) $_POST['nombre'] : '');
        if ($name === '' || strlen($name) > 80) { $errors[] = 'Escribe un nombre de hasta 80 caracteres.'; }
        if (!$errors) {
            $check = db()->prepare('SELECT COUNT(*) FROM categorias WHERE nombre = ? AND id <> ?');
            $check->execute(array($name, $id));
            if ((int) $check->fetchColumn() > 0) { $errors[] = 'Ya existe una categoría con ese nombre.'; }
        }
        if (!$errors) {
            $slug = slugify($name) . '-' . substr(sha1($name . microtime(true)), 0, 5);
            if ($id > 0) {
                $statement = db()->prepare('UPDATE categorias SET nombre = ?, slug = ? WHERE id = ?');
                $statement->execute(array($name, $slug, $id));
                set_flash('success', 'La categoría se actualizó.');
            } else {
                $statement = db()->prepare('INSERT INTO categorias (nombre, slug) VALUES (?, ?)');
                $statement->execute(array($name, $slug));
                set_flash('success', 'La categoría se agregó.');
            }
            redirect('admin/categorias.php');
        }
        $edit = array('id' => $id, 'nombre' => $name);
    }
}
$categories = db()->query('SELECT c.id, c.nombre, COUNT(p.id) AS prendas FROM categorias c LEFT JOIN productos p ON p.categoria_id = c.id GROUP BY c.id ORDER BY c.nombre')->fetchAll();
$adminTitle = 'Categorías'; $activePage = 'categorias';
require __DIR__ . '/header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Organiza el catálogo</p><h1>Categorías</h1><p>Usa categorías para que tus clientes encuentren prendas fácilmente.</p></div></div>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="admin-form-grid">
    <section class="admin-panel"><div class="admin-panel-heading"><div><h2><?= $edit ? 'Editar categoría' : 'Agregar categoría' ?></h2><p><?= $edit ? 'Cambia el nombre de esta categoría.' : 'Por ejemplo: Vestidos o Accesorios.' ?></p></div></div>
        <form class="admin-form" method="post" action="<?= e(url('admin/categorias.php' . ($edit ? '?editar=' . (int) $edit['id'] : ''))) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $edit ? (int) $edit['id'] : 0 ?>"><label>Nombre<input type="text" name="nombre" maxlength="80" required placeholder="Ej. Vestidos" value="<?= e($edit ? $edit['nombre'] : '') ?>"></label><div class="form-actions"><button class="button button-primary" type="submit"><?= $edit ? 'Guardar cambios' : 'Agregar categoría' ?></button><?php if ($edit): ?><a class="button button-outline" href="<?= e(url('admin/categorias.php')) ?>">Cancelar</a><?php endif; ?></div></form>
    </section>
    <section class="admin-panel"><div class="admin-panel-heading"><div><h2>Categorías actuales</h2><p><?= count($categories) ?> en total</p></div></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Nombre</th><th>Prendas</th><th>Acciones</th></tr></thead><tbody><?php foreach ($categories as $category): ?><tr><td><strong><?= e($category['nombre']) ?></strong></td><td><?= (int) $category['prendas'] ?></td><td><div class="table-actions"><a class="small-action" href="<?= e(url('admin/categorias.php?editar=' . (int) $category['id'])) ?>">Editar</a><form class="inline-form" method="post" action="<?= e(url('admin/categorias.php')) ?>" onsubmit="return confirm('¿Eliminar esta categoría? Solo se puede eliminar si no tiene prendas asignadas.');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><button class="small-action danger" type="submit">Eliminar</button></form></div></td></tr><?php endforeach; ?></tbody></table></div>
    </section>
</div>
<?php require __DIR__ . '/footer.php'; ?>
