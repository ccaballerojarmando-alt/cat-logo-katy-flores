<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/catalog.php';
$products = get_catalog_products(array(), 8);
$qrTarget = public_catalog_url();
$qrImage = $qrTarget ? 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=10&data=' . rawurlencode($qrTarget) : '';
$pageTitle = 'Inicio';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="hero-kicker"><span class="sparkle">✦</span> Moda con historia</span>
            <h1>Encuentra esa prenda que se siente <em>como tú.</em></h1>
            <p><?= e(setting_value('mensaje_inicio', 'Prendas únicas seleccionadas de nuestros fardos. Explora el catálogo y encuentra tu próxima favorita.')) ?></p>
            <div class="hero-actions"><a class="button button-primary" href="<?= e(url('catalogo.php')) ?>">Explorar catálogo <span aria-hidden="true">→</span></a><a class="button button-quiet" href="<?= e(url('contacto.php')) ?>">Conócenos</a></div>
            <div class="hero-note"><span class="note-mark">K</span><span><strong>Una pieza, una oportunidad</strong><small>Ropa seleccionada con cariño en Katy Flores.</small></span></div>
        </div>
        <div class="hero-art" aria-label="Ilustración de una prenda de vestir" role="img">
            <div class="art-sun"></div><div class="art-tag">hallazgos<br>especiales</div>
            <div class="coat"><div class="coat-collar"></div><div class="coat-seam"></div><div class="coat-button"></div><div class="coat-button second"></div></div>
            <div class="art-sticker">HECHO<br>PARA TI <span>♡</span></div>
        </div>
    </div>
</section>
<section class="section section-soft">
    <div class="container">
        <div class="section-heading"><div><p class="eyebrow">Piezas para descubrir</p><h2>Lo nuevo en el perchero</h2><p class="section-intro">Cada prenda es diferente. Mira con calma, encuentra tu favorita y consúltanos.</p></div><a class="text-link desktop-link" href="<?= e(url('catalogo.php')) ?>">Ver todo el catálogo <span aria-hidden="true">→</span></a></div>
        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): require __DIR__ . '/includes/product_card.php'; endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><div class="empty-icon">✧</div><h3>Pronto habrá prendas para descubrir</h3><p>Vuelve a visitarnos o escríbenos para conocer las novedades.</p><?php $contactUrl = whatsapp_url('Hola, quisiera conocer las novedades de Katy Flores.'); if ($contactUrl): ?><a class="button button-primary" href="<?= e($contactUrl) ?>" target="_blank" rel="noopener">Escríbenos por WhatsApp</a><?php endif; ?></div>
        <?php endif; ?>
        <a class="button button-outline mobile-catalog-link" href="<?= e(url('catalogo.php')) ?>">Ver catálogo completo</a>
    </div>
</section>
<section class="story-strip"><div class="container story-inner"><span class="story-flower">✿</span><div><p class="eyebrow">Una forma más linda de encontrar ropa</p><h2>Prendas especiales, sin complicarte.</h2><p>Explora por categoría, talla o precio. Cuando encuentres algo que te guste, pregúntanos por WhatsApp.</p></div><a class="button button-light" href="<?= e(url('catalogo.php')) ?>">Buscar una prenda</a></div></section>
<section class="qr-share"><div class="container qr-share-card"><div><p class="eyebrow">Lleva el catálogo contigo</p><h2>Comparte Katy Flores</h2><p>Escanea el código para abrir el catálogo desde otro teléfono.</p><?php if ($qrTarget): ?><a class="qr-address" href="<?= e($qrTarget) ?>"><?= e($qrTarget) ?></a><?php else: ?><p class="qr-address">Configura el enlace público en Administración → Configuración.</p><?php endif; ?></div><?php if ($qrImage): ?><a class="qr-image-link" href="<?= e($qrImage) ?>" target="_blank" rel="noopener" aria-label="Abrir el código QR del catálogo"><img src="<?= e($qrImage) ?>" alt="Código QR para abrir el catálogo de Katy Flores" loading="lazy"><small>Abre el QR en tamaño completo</small></a><?php else: ?><div class="qr-placeholder">QR<br><span>Enlace por configurar</span></div><?php endif; ?></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
