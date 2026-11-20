<?php
/**
 * Configuration par défaut.
 * Pour la production, copier `config.local.example.php` en `config.local.php`
 * (même dossier) et y surcharger les valeurs : ce fichier n'est jamais écrasé.
 */
$config = [
    // Base SQLite (hors du dossier public).
    'db_path'      => APP_ROOT . '/storage/cenacle.sqlite',
    // Dossier des médias envoyés depuis l'admin (doit être dans public/).
    'uploads_dir'  => APP_ROOT . '/public/uploads',
    // URL absolue du site (sans / final) : utilisée pour le sitemap, Open Graph, e-mails.
    // Laisser vide pour la détecter automatiquement.
    'site_url'     => '',
    'timezone'     => 'Africa/Lome',
    'debug'        => false,
    // Adresse d'expédition des notifications (formulaire de contact).
    // Vide = no-reply@<domaine du site> (ex. no-reply@lecenacle.youngwaverecords.com).
    'mail_from'    => '',
    // Taille maximale d'un envoi (octets).
    'max_upload'   => 40 * 1024 * 1024,
    // Largeur maximale des images optimisées.
    'image_max_w'  => 1800,
];

if (is_file(__DIR__ . '/config.local.php')) {
    $config = array_replace($config, require __DIR__ . '/config.local.php');
}
return $config;
