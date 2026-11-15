<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/catalog.php';
require_admin();
$counts = array(
    'productos' => (int) db()->query('SELECT COUNT(*) FROM productos')->fetchColumn(),
    'categorias' => (int) db()->query('SELECT COUNT(*) FROM categorias')->fetchColumn(),
    'disponibles' => (int) db()->query("SELECT COUNT(*) FROM productos WHERE estado = 'disponible'")->fetchColumn(),
    'vendidos' => (int) db()->query("SELECT COUNT(*) FROM productos WHERE estado = 'vendido'")->fetchColumn()
);
$recent = get_catalog_products(array(), 5);
$adminTitle = 'Resumen'; $activePage = 'dashboard';
require __DIR__ . '/header.php';
?>
<div class="admin-page-heading"><div><p class="eyebrow">Tu espacio de trabajo</p><h1>Resumen</h1><p>Aquí puedes ver cómo va tu catálogo.</p></div><a class="button button-primary" href="<?= e(url('admin/producto_form.php')) ?>">+ Agregar una prenda</a></div>
<div class="admin-cards">
    <div class="stat-card"><span class="stat-icon">◇</span><div><span>Prendas publicadas</span><strong><?= $counts['productos'] ?></strong></div></div>
    <div class="stat-card"><span class="stat-icon">▦</span><div><span>Categorías</span><strong><?= $counts['categorias'] ?></strong></div></div>
    <div class="stat-card"><span class="stat-icon">✓</span><div><span>Disponibles</span><strong><?= $counts['disponibles'] ?></strong></div></div>
    <div class="stat-card"><span class="stat-icon">♡</span><div><span>Vendidas</span><strong><?= $counts['vendidos'] ?></strong></div></div>
</div>
<section class="admin-panel"><div class="admin-panel-heading"><div><h2>Prendas recientes</h2><p>Tus últimas publicaciones y su estado.</p></div><a class="text-link" href="<?= e(url('admin/productos.php')) ?>">Ver todas →</a></div>
    <?php if ($recent): ?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Prenda</th><th>Precio</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach ($recent as $product): ?><tr><td><div class="table-product"><img src="<?= e(product_image_url($product['foto'])) ?>" alt=""><span><strong><?= e($product['nombre']) ?></strong><small><?= e($product['categoria_nombre']) ?></small></span></div></td><td><?= e(format_price($product['precio'])) ?></td><td><span class="status-pill <?= $product['estado'] === 'vendido' ? 'sold' : '' ?>"><?= $product['estado'] === 'disponible' ? 'Disponible' : 'Vendida' ?></span></td><td><a class="small-action" href="<?= e(url('admin/producto_form.php?id=' . (int) $product['id'])) ?>">Editar</a></td></tr><?php endforeach; ?></tbody></table></div>
    <?php else: ?><div class="empty-table">Aún no publicaste prendas. Agrega la primera y aparecerá en tu catálogo.</div><?php endif; ?>
</section>
<div class="admin-tip">Consejo: marca una prenda como <strong>Vendida</strong> cuando ya no esté disponible. Así seguirá apareciendo en el catálogo con su estado actualizado.</div>
<?php require __DIR__ . '/footer.php'; ?>
