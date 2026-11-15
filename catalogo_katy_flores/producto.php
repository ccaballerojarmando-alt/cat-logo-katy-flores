<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/catalog.php';
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $productId > 0 ? get_product($productId) : null;
if (!$product) { http_response_code(404); $pageTitle = 'Prenda no encontrada'; require __DIR__ . '/includes/header.php'; ?><section class="section"><div class="container empty-state"><h1>No encontramos esta prenda</h1><p>Puede que ya no esté en el catálogo.</p><a class="button button-primary" href="<?= e(url('catalogo.php')) ?>">Volver al catálogo</a></div></section><?php require __DIR__ . '/includes/footer.php'; exit; }
$pageTitle = $product['nombre'];
require __DIR__ . '/includes/header.php';
$mainImage = !empty($product['imagenes']) ? $product['imagenes'][0]['ruta'] : '';
$wa = whatsapp_url(product_whatsapp_message($product));
?>
<section class="section product-detail-section"><div class="container">
    <p class="breadcrumb"><a href="<?= e(url('index.php')) ?>">Inicio</a><span>/</span><a href="<?= e(url('catalogo.php')) ?>">Catálogo</a><span>/</span><?= e($product['nombre']) ?></p>
    <div class="product-detail">
        <div class="product-gallery"><div class="main-product-photo"><img id="main-product-image" src="<?= e(product_image_url($mainImage)) ?>" alt="<?= e($product['nombre']) ?>"><?php if ($product['estado'] === 'vendido'): ?><span class="badge badge-sold">Vendido</span><?php endif; ?></div>
            <?php if (count($product['imagenes']) > 1): ?><div class="thumbnail-row"><?php foreach ($product['imagenes'] as $index => $image): ?><button class="thumbnail <?= $index === 0 ? 'is-active' : '' ?>" type="button" data-image="<?= e(product_image_url($image['ruta'])) ?>" aria-label="Ver fotografía <?= $index + 1 ?>"><img src="<?= e(product_image_url($image['ruta'])) ?>" alt="" loading="lazy"></button><?php endforeach; ?></div><?php endif; ?>
        </div>
        <div class="product-info"><p class="eyebrow"><?= e($product['categoria_nombre']) ?></p><h1><?= e($product['nombre']) ?></h1><p class="detail-price"><?= e(format_price($product['precio'])) ?></p><div class="detail-divider"></div>
            <div class="detail-fact"><span>Talla<?= count($product['tallas_lista']) === 1 ? '' : 's' ?></span><strong><?= e($product['tallas'] ?: 'Consultar') ?></strong></div>
            <div class="detail-fact"><span>Disponibilidad</span><strong class="availability <?= $product['estado'] === 'disponible' ? 'is-available' : 'is-sold' ?>"><?= $product['estado'] === 'disponible' ? 'Disponible' : 'Vendida' ?></strong></div>
            <?php if (trim($product['descripcion']) !== ''): ?><div class="detail-description"><h2>Sobre esta prenda</h2><p><?= nl2br(e($product['descripcion'])) ?></p></div><?php endif; ?>
            <?php if ($product['estado'] === 'disponible' && $wa): ?><a class="button button-whatsapp button-wide" href="<?= e($wa) ?>" target="_blank" rel="noopener"><span class="whatsapp-mark">◔</span> Consultar por WhatsApp</a>
            <?php elseif ($product['estado'] === 'disponible'): ?><a class="button button-outline button-wide" href="<?= e(url('contacto.php')) ?>">Consultar disponibilidad</a>
            <?php else: ?><p class="sold-note">Esta prenda ya fue vendida. Mira el catálogo para encontrar otra favorita.</p><a class="button button-outline button-wide" href="<?= e(url('catalogo.php')) ?>">Seguir explorando</a><?php endif; ?>
            <p class="detail-note">¿Te interesa? Escríbenos y con gusto te ayudamos.</p>
        </div>
    </div>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
