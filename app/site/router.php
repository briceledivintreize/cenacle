<?php
/**
 * Routage du site public.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (SITE_BASE !== '' && str_starts_with($path, SITE_BASE)) {
    $path = substr($path, strlen(SITE_BASE));
}
$path = trim(rawurldecode($path), '/');
if ($path === 'index.php') {
    $path = '';
}

$views = APP_DIR . '/site';
require $views . '/partials.php';

// Fichiers techniques
if ($path === 'robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /admin/\nSitemap: " . abs_url('sitemap.xml') . "\n";
    exit;
}
if ($path === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    echo render($views . '/sitemap.php');
    exit;
}

// Envoi des formulaires (contact / adhésion)
if (is_post() && in_array($path, ['contact', 'rejoindre'], true)) {
    require $views . '/form-handler.php';
    handle_public_form($path === 'rejoindre' ? 'adhesion' : 'contact', $path);
}

$routes = [
    ''                 => ['home', 'accueil'],
    'association'      => ['association', 'association'],
    'histoire'         => ['histoire', 'histoire'],
    'evenements'       => ['evenements', 'evenements'],
    'publications'     => ['publications', 'publications'],
    'figures'          => ['figures', 'figures'],
    'rejoindre'        => ['rejoindre', 'rejoindre'],
    'contact'          => ['contact', 'contact'],
    'mentions-legales' => ['legal', ''],
];

$page = null;
$params = [];
if (isset($routes[$path])) {
    [$page, $nav] = $routes[$path];
} elseif (preg_match('#^evenements/([a-z0-9-]+)$#', $path, $m)) {
    $page = 'evenement';
    $nav = 'evenements';
    $params['slug'] = $m[1];
}

if ($page !== null) {
    $meta = ['title' => '', 'description' => setting('seo_description'), 'image' => setting('og_image'), 'nav' => $nav, 'canonical' => abs_url($path)];
    $body = render($views . '/pages/' . $page . '.php', ['meta' => &$meta, 'params' => $params]);
    if ($body === '__404__') {
        $page = null;
    }
}

if ($page === null) {
    http_response_code(404);
    $meta = ['title' => 'Page introuvable', 'description' => setting('seo_description'), 'image' => setting('og_image'), 'nav' => '', 'canonical' => abs_url($path), 'noindex' => true];
    $body = render($views . '/pages/404.php', ['meta' => &$meta]);
}

echo render($views . '/layout.php', ['meta' => $meta, 'body' => $body]);
