<?php
/**
 * Routeur pour le serveur de développement PHP intégré :
 *   php -S localhost:8000 -t public public/router.php
 * (En production, Apache utilise les fichiers .htaccess.)
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false; // fichier statique servi tel quel
}
define('SITE_BASE', '');
if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    require __DIR__ . '/admin/index.php';
    return true;
}
require __DIR__ . '/index.php';
