<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$_SESSION = array();
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], '', $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: ' . url('admin/login.php'));
exit;
