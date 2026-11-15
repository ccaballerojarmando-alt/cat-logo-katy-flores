<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin_helpers.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $images = db()->prepare('SELECT ruta FROM producto_imagenes WHERE producto_id = ?');
        $images->execute(array($id));
        $paths = $images->fetchAll(PDO::FETCH_COLUMN);
        $delete = db()->prepare('DELETE FROM productos WHERE id = ?');
        $delete->execute(array($id));
        if ($delete->rowCount()) {
            foreach ($paths as $path) { delete_product_image_file($path); }
            set_flash('success', 'La prenda se eliminó del catálogo.');
        }
    }
    redirect('admin/productos.php');
}
$products = db()->query("SELECT p.id, p.nombre, p.precio, p.estado, c.nombre AS categoria_nombre, GROUP_CONCAT(DISTINCT s.nombre ORDER BY s.orden, s.nombre SEPARATOR ', ') AS tallas, (SELECT pi.ruta FROM producto_imagenes pi WHERE pi.producto_id = p.id ORDER BY pi.orden, pi.id LIMIT 1) AS foto FROM productos p INNER JOIN categorias c ON c.id = p.categoria_id LEFT JOIN producto_tallas ps ON ps.producto_id = p.id LEFT JOIN tallas s ON s.id = ps.talla_id GROUP BY p.id, c.nombre ORDER BY p.creado_en DESC")->fetchAll();
$adminTitle = 'Mis prendas'; $activePage = 'productos';
require __DIR__ . '/header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Catálogo público</p><h1>Mis prendas</h1><p>Agrega una prenda, cambia su información o márcala como vendida.</p></div><a class="button button-primary" href="<?= e(url('admin/producto_form.php')) ?>">+ Agregar una prenda</a></div>
<section class="admin-panel"><div class="admin-panel-heading"><div><h2><?= count($products) ?> <?= count($products) === 1 ? 'prenda' : 'prendas' ?></h2><p>Lo que ves aquí se refleja en el catálogo público.</p></div></div>
    <?php if ($products): ?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Prenda</th><th>Talla</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr></thead><tbody><?php foreach ($products as $product): ?><tr><td><div class="table-product"><img src="<?= e(product_image_url($product['foto'])) ?>" alt=""><span><strong><?= e($product['nombre']) ?></strong><small><?= e($product['categoria_nombre']) ?></small></span></div></td><td><?= e($product['tallas'] ?: '—') ?></td><td><?= e(format_price($product['precio'])) ?></td><td><span class="status-pill <?= $product['estado'] === 'vendido' ? 'sold' : '' ?>"><?= $product['estado'] === 'disponible' ? 'Disponible' : 'Vendida' ?></span></td><td><div class="table-actions"><a class="small-action" href="<?= e(url('admin/producto_form.php?id=' . (int) $product['id'])) ?>">Editar</a><form class="inline-form" method="post" action="<?= e(url('admin/productos.php')) ?>" onsubmit="return confirm('¿Eliminar esta prenda del catálogo?');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><button class="small-action danger" type="submit">Eliminar</button></form></div></td></tr><?php endforeach; ?></tbody></table></div>
    <?php else: ?><div class="empty-state"><div class="empty-icon">◇</div><h3>Tu catálogo empieza aquí</h3><p>Agrega la primera prenda y aparecerá automáticamente en la página principal.</p><a class="button button-primary" href="<?= e(url('admin/producto_form.php')) ?>">Agregar mi primera prenda</a></div><?php endif; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
