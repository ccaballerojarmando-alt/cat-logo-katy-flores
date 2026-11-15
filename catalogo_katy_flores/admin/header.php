<?php
$adminTitle = isset($adminTitle) ? $adminTitle : 'Administración';
$activePage = isset($activePage) ? $activePage : '';
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administración';
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#3f2830"><title><?= e($adminTitle) ?> | Katy Flores</title><link rel="icon" href="<?= e(url('assets/img/logo.svg')) ?>" type="image/svg+xml"><link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>"></head><body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="<?= e(url('admin/dashboard.php')) ?>"><img src="<?= e(url('assets/img/logo.svg')) ?>" alt=""><span><strong>Katy Flores</strong><small>Panel sencillo</small></span></a>
        <nav class="admin-nav" aria-label="Administración">
            <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= e(url('admin/dashboard.php')) ?>"><span class="nav-icon">⌂</span>Resumen</a>
            <a class="<?= $activePage === 'productos' ? 'active' : '' ?>" href="<?= e(url('admin/productos.php')) ?>"><span class="nav-icon">◇</span>Mis prendas</a>
            <a class="<?= $activePage === 'categorias' ? 'active' : '' ?>" href="<?= e(url('admin/categorias.php')) ?>"><span class="nav-icon">▦</span>Categorías</a>
            <a class="<?= $activePage === 'tallas' ? 'active' : '' ?>" href="<?= e(url('admin/tallas.php')) ?>"><span class="nav-icon">↔</span>Tallas</a>
            <a class="<?= $activePage === 'configuracion' ? 'active' : '' ?>" href="<?= e(url('admin/configuracion.php')) ?>"><span class="nav-icon">⚙</span>Configuración</a>
        </nav>
        <div class="admin-sidebar-bottom"><span>Sesión: <?= e($adminName) ?></span><a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">Ver catálogo público ↗</a><a href="<?= e(url('admin/cerrar_sesion.php')) ?>">Cerrar sesión</a></div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar"><button class="mobile-sidebar-toggle" type="button" aria-label="Abrir menú">☰</button><span>Hola, <strong><?= e($adminName) ?></strong>. Administra tu catálogo aquí.</span></header>
        <main class="admin-content">
            <?php $flash = get_flash(); if ($flash): ?><div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>"><?= e($flash['message']) ?></div><?php endif; ?>
