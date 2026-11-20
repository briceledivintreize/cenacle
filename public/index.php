<?php
/**
 * Point d'entrée du site public.
 */
if (!defined('SITE_BASE')) {
    require dirname(__DIR__) . '/app/base.php';
    define('SITE_BASE', detect_site_base(1));
}
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/site/router.php';
