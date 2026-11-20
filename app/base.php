<?php
/**
 * Détecte le chemin URL de base du site.
 * Gère les deux installations possibles :
 *  - racine web = dossier public/ (SCRIPT_NAME = /index.php)
 *  - racine web = dossier du projet (Hostinger public_html), les requêtes étant
 *    réécrites vers public/ par le .htaccess racine (SCRIPT_NAME = /public/index.php)
 *
 * @param int $levels 1 pour public/index.php, 2 pour public/admin/index.php
 */
function detect_site_base(int $levels = 1): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $base = rtrim(str_replace('\\', '/', dirname($script, $levels)), '/');
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    // URL « propre » réécrite en interne vers /public : on masque ce segment.
    if (str_ends_with($base, '/public') && !str_starts_with($uri, $base . '/') && $uri !== $base) {
        $base = substr($base, 0, -strlen('/public'));
    }
    return $base === '/' ? '' : $base;
}
