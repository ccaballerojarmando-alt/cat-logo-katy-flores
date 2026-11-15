<?php
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url($path = '')
{
    $base = rtrim(APP_BASE_PATH, '/');
    $path = ltrim((string) $path, '/');
    return $base . '/' . $path;
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $sent = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if ($sent === '' || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(419);
        exit('La sesión expiró. Regresa a la página anterior e inténtalo otra vez.');
    }
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function is_admin()
{
    return !empty($_SESSION['admin_id']);
}

function require_admin()
{
    if (!is_admin()) {
        set_flash('error', 'Inicia sesión para continuar.');
        redirect('admin/login.php');
    }
}

function setting_value($key, $fallback = '')
{
    static $settings = null;
    if ($settings === null) {
        $settings = array();
        try {
            foreach (db()->query('SELECT clave, valor FROM configuracion') as $row) {
                $settings[$row['clave']] = $row['valor'];
            }
        } catch (Exception $exception) {
            return $fallback;
        }
    }
    return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $fallback;
}

function whatsapp_url($message)
{
    $number = preg_replace('/\D+/', '', setting_value('whatsapp', ''));
    if ($number === '') {
        return '';
    }
    return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
}

function product_whatsapp_message($product, $size = '')
{
    $message = "Hola, estoy interesado/a en esta prenda de Katy Flores.\n\n";
    $message .= 'Producto: ' . $product['nombre'] . "\n";
    $message .= 'Categoría: ' . $product['categoria_nombre'] . "\n";
    $message .= 'Talla: ' . ($size !== '' ? $size : (isset($product['tallas']) ? $product['tallas'] : 'Consultar')) . "\n";
    $message .= 'Precio: Bs ' . number_format((float) $product['precio'], 2, ',', '.') . "\n\n";
    $message .= '¿Todavía está disponible?';
    return $message;
}

function format_price($price)
{
    return 'Bs ' . number_format((float) $price, 2, ',', '.');
}

function slugify($text)
{
    $text = trim((string) $text);
    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'producto';
}

function product_image_url($path)
{
    if (!$path) {
        return url('assets/img/placeholder.svg');
    }
    return url($path);
}

function public_catalog_url()
{
    $configured = trim(setting_value('public_url', ''));
    if ($configured !== '') {
        $validated = filter_var($configured, FILTER_VALIDATE_URL);
        $scheme = $validated ? strtolower((string) parse_url($validated, PHP_URL_SCHEME)) : '';
        if ($validated && in_array($scheme, array('http', 'https'), true)) {
            $configured = rtrim($validated, '/');
            return substr($configured, -9) === 'index.php' ? $configured : $configured . '/index.php';
        }
    }
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ($host === '' || !preg_match('/^[A-Za-z0-9.\-:\[\]]+$/', $host)) {
        return '';
    }
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    return ($isSecure ? 'https://' : 'http://') . $host . url('index.php');
}
