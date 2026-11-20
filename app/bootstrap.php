<?php
declare(strict_types=1);

/*
 * Amorçage commun au site public et au panel d'administration.
 * Chaque point d'entrée définit SITE_BASE (chemin URL du site, ex. "" ou "/cenacle")
 * avant d'inclure ce fichier.
 */

define('APP_ROOT', dirname(__DIR__));
define('APP_DIR', __DIR__);

$GLOBALS['config'] = require APP_DIR . '/config.php';
date_default_timezone_set($GLOBALS['config']['timezone']);
mb_internal_encoding('UTF-8');

if (!empty($GLOBALS['config']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

if (!defined('SITE_BASE')) {
    define('SITE_BASE', '');
}

require APP_DIR . '/lib/db.php';
require APP_DIR . '/lib/helpers.php';
require APP_DIR . '/lib/markup.php';
require APP_DIR . '/lib/media.php';
require APP_DIR . '/lib/content.php';

// En-têtes de sécurité (complétés par .htaccess en production).
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}
