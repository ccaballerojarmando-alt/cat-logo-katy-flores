<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Contacto';
$message = 'Hola, quisiera consultar sobre las prendas de Katy Flores.';
$wa = whatsapp_url($message);
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-small"><div class="container"><p class="eyebrow">Estamos para ayudarte</p><h1>Hablemos</h1><p>¿Viste algo que te gustó? Escríbenos para consultar por tu prenda favorita.</p></div></section>
<section class="section contact-section"><div class="container contact-card"><div class="contact-flower">✿</div><div><p class="eyebrow">Contacto directo</p><h2>Una pregunta, un mensaje.</h2><p>Te atenderemos con gusto para confirmar disponibilidad, tallas y cualquier detalle que necesites.</p><?php if (setting_value('telefono', '') !== ''): ?><p><strong>Teléfono:</strong> <?= e(setting_value('telefono')) ?></p><?php endif; ?><?php if (setting_value('direccion', '') !== ''): ?><p><strong>Estamos en:</strong> <?= e(setting_value('direccion')) ?></p><?php endif; ?><?php if ($wa): ?><a class="button button-whatsapp" href="<?= e($wa) ?>" target="_blank" rel="noopener">Escribir por WhatsApp <span aria-hidden="true">↗</span></a><?php else: ?><p class="contact-unavailable">El número de WhatsApp aún no está configurado. Vuelve a visitarnos pronto.</p><?php endif; ?></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
