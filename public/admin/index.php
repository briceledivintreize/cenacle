<?php
/**
 * Point d'entrée du panel d'administration (détaché du site public).
 */
if (!defined('SITE_BASE')) {
    require dirname(__DIR__, 2) . '/app/base.php';
    define('SITE_BASE', detect_site_base(2));
}
define('ADMIN_BASE', SITE_BASE . '/admin');
require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_DIR . '/admin/router.php';
