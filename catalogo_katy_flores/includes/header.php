<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Katy Flores';
$shopName = setting_value('nombre_tienda', 'Katy Flores');
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4d5b52">
    <title><?= e($pageTitle) ?> | <?= e($shopName) ?></title>
    <link rel="icon" href="<?= e(url('assets/img/logo.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/qr.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= e(url('index.php')) ?>">
            <img src="<?= e(url('assets/img/logo.svg')) ?>" width="48" height="48" alt="">
            <span><strong><?= e($shopName) ?></strong><small>Ropa de fardo</small></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-nav">Menú</button>
        <nav class="main-nav" id="main-nav" aria-label="Navegación principal">
            <a href="<?= e(url('index.php')) ?>">Inicio</a>
            <a href="<?= e(url('catalogo.php')) ?>">Catálogo</a>
            <a href="<?= e(url('contacto.php')) ?>">Contacto</a>
        </nav>
    </div>
</header>
<main>
