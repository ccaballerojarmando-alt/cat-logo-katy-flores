<?php
function image_uploads_from_request(&$errors)
{
    $uploads = array();
    if (!isset($_FILES['fotos']) || !is_array($_FILES['fotos']['name'])) {
        return $uploads;
    }
    $fileCount = count($_FILES['fotos']['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        $error = (int) $_FILES['fotos']['error'][$i];
        if ($error === UPLOAD_ERR_NO_FILE) { continue; }
        if ($error !== UPLOAD_ERR_OK) { $errors[] = 'Una de las fotografías no se pudo cargar. Prueba con una imagen más pequeña.'; continue; }
        $tmp = $_FILES['fotos']['tmp_name'][$i];
        $size = (int) $_FILES['fotos']['size'][$i];
        if ($size <= 0 || $size > 5 * 1024 * 1024) { $errors[] = 'Cada fotografía debe pesar como máximo 5 MB.'; continue; }
        $info = @getimagesize($tmp);
        if (!$info || empty($info['mime'])) { $errors[] = 'Solo se permiten fotografías JPG, PNG o WebP.'; continue; }
        $extensions = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
        if (!isset($extensions[$info['mime']])) { $errors[] = 'Solo se permiten fotografías JPG, PNG o WebP.'; continue; }
        $uploads[] = array('tmp' => $tmp, 'extension' => $extensions[$info['mime']], 'mime' => $info['mime']);
    }
    return $uploads;
}

function store_product_uploads($productId, $uploads)
{
    if (!$uploads) { return; }
    $directory = dirname(__DIR__) . '/uploads/productos';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException('No se pudo preparar la carpeta de fotografías.');
    }
    $statement = db()->prepare('INSERT INTO producto_imagenes (producto_id, ruta, texto_alternativo, orden) VALUES (?, ?, ?, ?)');
    $current = db()->prepare('SELECT COALESCE(MAX(orden), -1) FROM producto_imagenes WHERE producto_id = ?');
    $current->execute(array((int) $productId));
    $order = (int) $current->fetchColumn() + 1;
    foreach ($uploads as $upload) {
        $filename = bin2hex(random_bytes(16)) . '.' . $upload['extension'];
        $target = $directory . '/' . $filename;
        if (!move_uploaded_file($upload['tmp'], $target)) {
            throw new RuntimeException('No se pudo guardar una de las fotografías. Revisa los permisos de uploads/productos.');
        }
        $relative = 'uploads/productos/' . $filename;
        try {
            $statement->execute(array((int) $productId, $relative, '', $order++));
        } catch (Exception $exception) {
            @unlink($target);
            throw $exception;
        }
    }
}

function delete_product_image_file($relativePath)
{
    $prefix = 'uploads/productos/';
    if (strpos((string) $relativePath, $prefix) !== 0) { return; }
    $filename = basename($relativePath);
    if ($filename === '' || $filename === '.' || $filename === '..') { return; }
    $path = dirname(__DIR__) . '/uploads/productos/' . $filename;
    if (is_file($path)) { @unlink($path); }
}

function category_exists($id)
{
    $statement = db()->prepare('SELECT COUNT(*) FROM categorias WHERE id = ?');
    $statement->execute(array((int) $id));
    return (int) $statement->fetchColumn() > 0;
}

function valid_size_ids($ids)
{
    if (!is_array($ids)) { return array(); }
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) { return $id > 0; })));
    if (!$ids) { return array(); }
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $statement = db()->prepare('SELECT id FROM tallas WHERE id IN (' . $marks . ')');
    $statement->execute($ids);
    return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
}
