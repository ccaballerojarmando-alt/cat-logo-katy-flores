<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/catalog.php';
$options = catalog_options();
$filters = array(
    'q' => isset($_GET['q']) ? trim(substr((string) $_GET['q'], 0, 100)) : '',
    'categoria' => isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0,
    'talla' => isset($_GET['talla']) ? (int) $_GET['talla'] : 0,
    'min' => isset($_GET['min']) && is_numeric($_GET['min']) ? max(0, (float) $_GET['min']) : '',
    'max' => isset($_GET['max']) && is_numeric($_GET['max']) ? max(0, (float) $_GET['max']) : '',
    'estado' => isset($_GET['estado']) ? (string) $_GET['estado'] : ''
);
if (!in_array($filters['estado'], array('', 'disponible', 'vendido'), true)) { $filters['estado'] = ''; }
$products = get_catalog_products($filters);
$pageTitle = 'Catálogo';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-small"><div class="container"><p class="eyebrow">El perchero Katy Flores</p><h1>Catálogo de prendas</h1><p>Busca fácil, filtra a tu gusto y encuentra tu próximo hallazgo.</p></div></section>
<section class="section catalog-section"><div class="container">
    <form class="filter-panel" method="get" action="<?= e(url('catalogo.php')) ?>">
        <div class="filter-heading"><div><p class="eyebrow">Encuentra lo que buscas</p><h2>Filtrar prendas</h2></div><a class="text-link" href="<?= e(url('catalogo.php')) ?>">Limpiar filtros</a></div>
        <div class="filter-grid">
            <label class="field field-search"><span>Buscar por nombre</span><input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Ej. chamarra de jean"></label>
            <label class="field"><span>Categoría</span><select name="categoria"><option value="0">Todas</option><?php foreach ($options['categorias'] as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $filters['categoria'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>Talla</span><select name="talla"><option value="0">Todas</option><?php foreach ($options['tallas'] as $size): ?><option value="<?= (int) $size['id'] ?>" <?= $filters['talla'] === (int) $size['id'] ? 'selected' : '' ?>><?= e($size['nombre']) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>Precio desde (Bs)</span><input type="number" name="min" min="0" step="0.01" value="<?= e($filters['min']) ?>" placeholder="0"></label>
            <label class="field"><span>Precio hasta (Bs)</span><input type="number" name="max" min="0" step="0.01" value="<?= e($filters['max']) ?>" placeholder="Sin límite"></label>
            <label class="field"><span>Disponibilidad</span><select name="estado"><option value="">Todas</option><option value="disponible" <?= $filters['estado'] === 'disponible' ? 'selected' : '' ?>>Disponibles</option><option value="vendido" <?= $filters['estado'] === 'vendido' ? 'selected' : '' ?>>Vendidas</option></select></label>
        </div>
        <button class="button button-primary filter-submit" type="submit">Mostrar resultados <span aria-hidden="true">→</span></button>
    </form>
    <div class="results-heading"><p><strong><?= count($products) ?></strong> <?= count($products) === 1 ? 'prenda encontrada' : 'prendas encontradas' ?></p><span>Los filtros se pueden combinar</span></div>
    <?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product): require __DIR__ . '/includes/product_card.php'; endforeach; ?></div>
    <?php else: ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>No encontramos prendas con esos filtros</h3><p>Prueba quitar algún filtro o buscar con otra palabra.</p><a class="button button-outline" href="<?= e(url('catalogo.php')) ?>">Ver todas las prendas</a></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
