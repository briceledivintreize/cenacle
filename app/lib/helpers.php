<?php
/**
 * Fonctions utilitaires partagées.
 */

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/** URL relative au site public. */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return SITE_BASE . '/' . $path;
}

/** URL absolue (sitemap, Open Graph). */
function abs_url(string $path = ''): string
{
    $base = rtrim($GLOBALS['config']['site_url'] ?? '', '/');
    if ($base === '') {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = (is_https() ? 'https' : 'http') . '://' . $host . SITE_BASE;
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return $base . '/' . ltrim($path, '/');
}

/** Fichier statique avec paramètre de version pour le cache. */
function asset(string $path): string
{
    $file = APP_ROOT . '/public/' . ltrim($path, '/');
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return url($path) . $v;
}

/** URL d'un média stocké (assets/... ou uploads/...) ou externe. */
function media_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    if (preg_match('#^(https?:)?//#', $path)) {
        return $path;
    }
    return url($path);
}

/**
 * Rendu <picture> avec source WebP si elle existe à côté du fichier original.
 */
function picture(?string $path, string $alt = '', array $attrs = []): string
{
    if (!$path) {
        return '';
    }
    $attrs += ['loading' => 'lazy', 'decoding' => 'async'];
    $html = '<picture>';
    if (!preg_match('#^(https?:)?//#', $path)) {
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
        if ($webp !== $path && is_file(APP_ROOT . '/public/' . $webp)) {
            $html .= '<source srcset="' . e(url($webp)) . '" type="image/webp">';
        }
        $size = @getimagesize(APP_ROOT . '/public/' . $path);
        if ($size && !isset($attrs['width'])) {
            $attrs['width'] = $size[0];
            $attrs['height'] = $size[1];
        }
    }
    $a = '';
    foreach ($attrs as $k => $v) {
        $a .= ' ' . $k . '="' . e((string) $v) . '"';
    }
    $html .= '<img src="' . e(media_url($path)) . '" alt="' . e($alt) . '"' . $a . '>';
    return $html . '</picture>';
}

function start_session(string $name = 'cenacle_sess', ?string $path = null): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name($name);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $path ?? SITE_BASE . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    start_session();
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($sent) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $sent);
}

function flash(string $type, string $message): void
{
    start_session();
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    start_session();
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function slugify(string $text): string
{
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'element';
}

function unique_slug(string $table, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $candidate = $slug;
    $i = 2;
    while (val("SELECT id FROM $table WHERE slug = ? AND id != ?", [$candidate, $ignoreId ?? 0])) {
        $candidate = $slug . '-' . $i++;
    }
    return $candidate;
}

/** Lien wa.me avec message pré-rempli. */
function wa_link(string $number, string $message = ''): string
{
    $digits = preg_replace('/\D+/', '', $number);
    return 'https://wa.me/' . $digits . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

/** Premier numéro d'une chaîne « +228 90 09 09 23 / 99 59 26 29 » → lien tel:. */
function tel_link(string $phone): string
{
    $first = trim(explode('/', $phone)[0]);
    return 'tel:' . preg_replace('/[^\d+]/', '', $first);
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

function excerpt(?string $text, int $len = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags(markup_strip((string) $text))));
    return mb_strlen($text) > $len ? rtrim(mb_substr($text, 0, $len - 1)) . '…' : $text;
}

function fr_date(?string $date, bool $withDay = true): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return $date;
    }
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $m = $months[(int) date('n', $ts) - 1];
    return $withDay ? date('j', $ts) . ' ' . $m . ' ' . date('Y', $ts) : $m . ' ' . date('Y', $ts);
}

function json_out(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Inclut une vue avec des variables locales et renvoie le HTML. */
function render(string $file, array $vars = []): string
{
    extract($vars, EXTR_SKIP | EXTR_REFS);
    ob_start();
    require $file;
    return (string) ob_get_clean();
}

/** Extrait l'identifiant d'une vidéo YouTube / Vimeo et renvoie l'URL d'intégration. */
function video_embed(?string $url): ?array
{
    if (!$url) {
        return null;
    }
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
        return ['provider' => 'youtube', 'id' => $m[1], 'embed' => 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0', 'thumb' => 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg'];
    }
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
        return ['provider' => 'vimeo', 'id' => $m[1], 'embed' => 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1', 'thumb' => null];
    }
    return null;
}
