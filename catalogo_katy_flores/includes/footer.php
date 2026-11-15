</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div><strong><?= e(setting_value('nombre_tienda', 'Katy Flores')) ?></strong><p><?= e(setting_value('eslogan', 'Prendas únicas, nuevas historias')) ?></p></div>
        <div class="footer-links"><a href="<?= e(url('catalogo.php')) ?>">Ver catálogo</a><a href="<?= e(url('contacto.php')) ?>">Contacto</a><a href="<?= e(url('admin/login.php')) ?>">Administración</a></div>
        <small>© <?= date('Y') ?> Katy Flores</small>
    </div>
</footer>
<script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
</body>
</html>
