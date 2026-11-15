<?php
function catalog_options()
{
    return array(
        'categorias' => db()->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll(),
        'tallas' => db()->query('SELECT id, nombre FROM tallas ORDER BY orden, nombre')->fetchAll()
    );
}

function get_catalog_products($filters = array(), $limit = null)
{
    $sql = "SELECT p.*, c.nombre AS categoria_nombre,
                   GROUP_CONCAT(DISTINCT s.nombre ORDER BY s.orden, s.nombre SEPARATOR ', ') AS tallas,
                   (SELECT pi.ruta FROM producto_imagenes pi WHERE pi.producto_id = p.id ORDER BY pi.orden, pi.id LIMIT 1) AS foto
            FROM productos p
            INNER JOIN categorias c ON c.id = p.categoria_id
            LEFT JOIN producto_tallas ps ON ps.producto_id = p.id
            LEFT JOIN tallas s ON s.id = ps.talla_id
            WHERE 1=1";
    $params = array();

    if (!empty($filters['q'])) {
        $sql .= ' AND p.nombre LIKE ?';
        $params[] = '%' . trim($filters['q']) . '%';
    }
    if (!empty($filters['categoria'])) {
        $sql .= ' AND p.categoria_id = ?';
        $params[] = (int) $filters['categoria'];
    }
    if (!empty($filters['talla'])) {
        $sql .= ' AND EXISTS (SELECT 1 FROM producto_tallas px WHERE px.producto_id = p.id AND px.talla_id = ?)';
        $params[] = (int) $filters['talla'];
    }
    if (isset($filters['min']) && $filters['min'] !== '') {
        $sql .= ' AND p.precio >= ?';
        $params[] = (float) $filters['min'];
    }
    if (isset($filters['max']) && $filters['max'] !== '') {
        $sql .= ' AND p.precio <= ?';
        $params[] = (float) $filters['max'];
    }
    if (!empty($filters['estado']) && in_array($filters['estado'], array('disponible', 'vendido'), true)) {
        $sql .= ' AND p.estado = ?';
        $params[] = $filters['estado'];
    }

    $sql .= " GROUP BY p.id, c.nombre ORDER BY (p.estado = 'disponible') DESC, p.creado_en DESC";
    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, (int) $limit);
    }
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function get_product($id)
{
    $statement = db()->prepare('SELECT p.*, c.nombre AS categoria_nombre FROM productos p INNER JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? LIMIT 1');
    $statement->execute(array((int) $id));
    $product = $statement->fetch();
    if (!$product) {
        return null;
    }
    $sizes = db()->prepare('SELECT s.id, s.nombre FROM producto_tallas ps INNER JOIN tallas s ON s.id = ps.talla_id WHERE ps.producto_id = ? ORDER BY s.orden, s.nombre');
    $sizes->execute(array((int) $id));
    $product['tallas_lista'] = $sizes->fetchAll();
    $product['tallas'] = implode(', ', array_column($product['tallas_lista'], 'nombre'));
    $images = db()->prepare('SELECT id, ruta, texto_alternativo FROM producto_imagenes WHERE producto_id = ? ORDER BY orden, id');
    $images->execute(array((int) $id));
    $product['imagenes'] = $images->fetchAll();
    return $product;
}
