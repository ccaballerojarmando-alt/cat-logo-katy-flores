<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/catalog.php';
require_once __DIR__ . '/../includes/admin_helpers.php';
require_admin();
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$product = $id > 0 ? get_product($id) : null;
if ($id > 0 && !$product) { set_flash('error', 'No encontramos esa prenda.'); redirect('admin/productos.php'); }
$categories = db()->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();
$sizes = db()->query('SELECT id, nombre FROM tallas ORDER BY orden, nombre')->fetchAll();
$errors = array();
$form = array(
    'nombre' => $product ? $product['nombre'] : '',
    'categoria_id' => $product ? (int) $product['categoria_id'] : 0,
    'precio' => $product ? $product['precio'] : '',
    'descripcion' => $product ? $product['descripcion'] : '',
    'estado' => $product ? $product['estado'] : 'disponible'
);
$selectedSizes = $product ? array_map('intval', array_column($product['tallas_lista'], 'id')) : array();
$deleteIds = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['nombre'] = trim(isset($_POST['nombre']) ? (string) $_POST['nombre'] : '');
    $form['categoria_id'] = isset($_POST['categoria_id']) ? (int) $_POST['categoria_id'] : 0;
    $form['precio'] = isset($_POST['precio']) ? trim((string) $_POST['precio']) : '';
    $form['descripcion'] = trim(isset($_POST['descripcion']) ? (string) $_POST['descripcion'] : '');
    $form['estado'] = isset($_POST['estado']) ? (string) $_POST['estado'] : 'disponible';
    $selectedSizes = valid_size_ids(isset($_POST['tallas']) ? $_POST['tallas'] : array());
    $deleteIds = isset($_POST['eliminar_foto']) && is_array($_POST['eliminar_foto']) ? array_values(array_unique(array_map('intval', $_POST['eliminar_foto']))) : array();

    if ($form['nombre'] === '' || strlen($form['nombre']) > 150) { $errors[] = 'Escribe el nombre de la prenda (máximo 150 caracteres).'; }
    if (!category_exists($form['categoria_id'])) { $errors[] = 'Selecciona una categoría válida.'; }
    if ($form['precio'] === '' || !is_numeric($form['precio']) || (float) $form['precio'] < 0 || (float) $form['precio'] > 99999999.99) { $errors[] = 'Escribe un precio válido en bolivianos.'; }
    if (!$selectedSizes) { $errors[] = 'Selecciona al menos una talla.'; }
    if (!in_array($form['estado'], array('disponible', 'vendido'), true)) { $form['estado'] = 'disponible'; }
    $uploads = image_uploads_from_request($errors);

    $existingImages = $product ? $product['imagenes'] : array();
    $validDeleteIds = array();
    foreach ($existingImages as $image) {
        if (in_array((int) $image['id'], $deleteIds, true)) { $validDeleteIds[] = (int) $image['id']; }
    }
    if (count($existingImages) - count($validDeleteIds) + count($uploads) > 5) {
        $errors[] = 'Puedes conservar hasta cinco fotografías por prenda. Quita alguna foto o selecciona menos archivos.';
    }

    if (!$errors) {
        $pdo = db();
        $removedPaths = array();
        try {
            $pdo->beginTransaction();
            if ($product) {
                $statement = $pdo->prepare('UPDATE productos SET categoria_id = ?, nombre = ?, precio = ?, descripcion = ?, estado = ? WHERE id = ?');
                $statement->execute(array($form['categoria_id'], $form['nombre'], number_format((float) $form['precio'], 2, '.', ''), $form['descripcion'], $form['estado'], $id));
                $productId = $id;
            } else {
                $slug = slugify($form['nombre']) . '-' . dechex(time()) . '-' . random_int(100, 999);
                $statement = $pdo->prepare('INSERT INTO productos (categoria_id, nombre, slug, precio, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?)');
                $statement->execute(array($form['categoria_id'], $form['nombre'], $slug, number_format((float) $form['precio'], 2, '.', ''), $form['descripcion'], $form['estado']));
                $productId = (int) $pdo->lastInsertId();
            }
            $pdo->prepare('DELETE FROM producto_tallas WHERE producto_id = ?')->execute(array($productId));
            $sizeInsert = $pdo->prepare('INSERT INTO producto_tallas (producto_id, talla_id) VALUES (?, ?)');
            foreach ($selectedSizes as $sizeId) { $sizeInsert->execute(array($productId, $sizeId)); }

            if ($validDeleteIds) {
                $marks = implode(',', array_fill(0, count($validDeleteIds), '?'));
                $params = array_merge(array($productId), $validDeleteIds);
                $imageQuery = $pdo->prepare('SELECT ruta FROM producto_imagenes WHERE producto_id = ? AND id IN (' . $marks . ')');
                $imageQuery->execute($params);
                $removedPaths = $imageQuery->fetchAll(PDO::FETCH_COLUMN);
                $pdo->prepare('DELETE FROM producto_imagenes WHERE producto_id = ? AND id IN (' . $marks . ')')->execute($params);
            }
            $pdo->commit();
            foreach ($removedPaths as $path) { delete_product_image_file($path); }
            try {
                store_product_uploads($productId, $uploads);
                set_flash('success', $product ? 'Los cambios de la prenda se guardaron.' : 'La prenda ya está publicada en el catálogo.');
            } catch (Exception $uploadException) {
                set_flash('error', 'La información se guardó, pero hubo un problema al guardar una foto. Puedes volver a editar la prenda para subirla.');
            }
            redirect('admin/producto_form.php?id=' . $productId);
        } catch (Exception $exception) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $errors[] = 'No se pudieron guardar los cambios. Revisa la conexión con la base de datos e inténtalo de nuevo.';
        }
    }
}

$adminTitle = $product ? 'Editar prenda' : 'Agregar prenda'; $activePage = 'productos';
require __DIR__ . '/header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Mis prendas</p><h1><?= $product ? 'Editar prenda' : 'Agregar una prenda' ?></h1><p>Completa lo principal y tu prenda aparecerá en el catálogo.</p></div><a class="button button-outline" href="<?= e(url('admin/productos.php')) ?>">← Volver a mis prendas</a></div>
<?php if ($errors): ?><div class="alert alert-error"><strong>Revisa estos datos:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="admin-tip">Completa nombre, categoría, talla y precio. Si tienes fotos, puedes añadir hasta cinco; la primera será la imagen principal.</div>
<section class="admin-panel"><form class="admin-form" method="post" enctype="multipart/form-data" action="<?= e(url('admin/producto_form.php' . ($product ? '?id=' . (int) $product['id'] : ''))) ?>">
    <?= csrf_field() ?><?php if ($product): ?><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><?php endif; ?>
    <div class="admin-form-grid">
        <label>Nombre de la prenda<input type="text" name="nombre" maxlength="150" required placeholder="Ej. Chamarra de jean clásica" value="<?= e($form['nombre']) ?>"></label>
        <label>Categoría<select name="categoria_id" required><option value="">Elige una categoría</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $form['categoria_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option><?php endforeach; ?></select></label>
        <label>Precio en bolivianos (Bs)<input type="number" name="precio" min="0" max="99999999.99" step="0.01" required placeholder="Ej. 85" value="<?= e($form['precio']) ?>"></label>
        <label>Disponibilidad<select name="estado"><option value="disponible" <?= $form['estado'] === 'disponible' ? 'selected' : '' ?>>Disponible</option><option value="vendido" <?= $form['estado'] === 'vendido' ? 'selected' : '' ?>>Vendida</option></select></label>
    </div>
    <div><label style="display:block;font-size:12px;color:#685d5b;font-weight:bold;margin-bottom:8px">Tallas disponibles</label><div class="checkbox-list"><?php foreach ($sizes as $size): ?><label class="checkbox-pill"><input type="checkbox" name="tallas[]" value="<?= (int) $size['id'] ?>" <?= in_array((int) $size['id'], $selectedSizes, true) ? 'checked' : '' ?>><?= e($size['nombre']) ?></label><?php endforeach; ?></div><small class="help-text">Puedes marcar más de una talla. Administra la lista en la sección Tallas.</small></div>
    <label>Descripción<textarea name="descripcion" maxlength="10000" placeholder="Cuéntale a tus clientes sobre el color, estilo o detalles de la prenda."><?= e($form['descripcion']) ?></textarea></label>
    <?php if ($product && $product['imagenes']): ?><div><label style="display:block;font-size:12px;color:#685d5b;font-weight:bold;margin-bottom:9px">Fotografías actuales</label><div class="existing-photos"><?php foreach ($product['imagenes'] as $image): ?><div class="existing-photo"><img src="<?= e(product_image_url($image['ruta'])) ?>" alt=""><label><input type="checkbox" name="eliminar_foto[]" value="<?= (int) $image['id'] ?>"> Quitar foto</label></div><?php endforeach; ?></div></div><?php endif; ?>
    <label>Agregar fotografías<input class="upload-input" type="file" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple><small class="help-text">JPG, PNG o WebP. Máximo 5 MB por foto y 5 fotos en total.</small></label>
    <div class="form-actions"><button class="button button-primary" type="submit"><?= $product ? 'Guardar cambios' : 'Publicar prenda' ?></button><a class="button button-outline" href="<?= e(url('admin/productos.php')) ?>">Cancelar</a></div>
</form></section>
<?php require __DIR__ . '/footer.php'; ?>
