<article class="product-card">
    <a class="product-photo" href="<?= e(url('producto.php?id=' . (int) $product['id'])) ?>">
        <img src="<?= e(product_image_url($product['foto'])) ?>" alt="<?= e($product['nombre']) ?>" loading="lazy">
        <?php if ($product['estado'] === 'vendido'): ?><span class="badge badge-sold">Vendido</span><?php endif; ?>
    </a>
    <div class="product-card-body">
        <p class="eyebrow"><?= e($product['categoria_nombre']) ?></p>
        <h3><a href="<?= e(url('producto.php?id=' . (int) $product['id'])) ?>"><?= e($product['nombre']) ?></a></h3>
        <p class="product-sizes">Talla<?= strpos((string) $product['tallas'], ',') !== false ? 's' : '' ?>: <?= e($product['tallas'] ?: 'Consultar') ?></p>
        <div class="product-card-bottom"><strong class="price"><?= e(format_price($product['precio'])) ?></strong><a class="text-link" href="<?= e(url('producto.php?id=' . (int) $product['id'])) ?>">Ver prenda <span aria-hidden="true">→</span></a></div>
    </div>
</article>
