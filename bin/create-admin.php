<?php
/**
 * Crée (ou réinitialise) un compte administrateur en ligne de commande.
 * Utile en SSH (Hostinger Business) si l'accès au panel est perdu.
 *
 *   php bin/create-admin.php "Nom" email@domaine.tg
 * Le mot de passe est demandé de façon interactive.
 */
if (PHP_SAPI !== 'cli') {
    exit(1);
}
define('SITE_BASE', '');
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/admin/auth.php';

[$_, $name, $email] = $argv + [null, null, null];
if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage : php bin/create-admin.php \"Nom\" email@domaine\n");
    exit(1);
}
echo 'Mot de passe : ';
$pw = trim((string) fgets(STDIN));
if ($err = validate_password($pw)) {
    fwrite(STDERR, $err . "\n");
    exit(1);
}
$hash = password_hash($pw, PASSWORD_DEFAULT);
if ($id = val('SELECT id FROM users WHERE lower(email) = lower(?)', [$email])) {
    db_update('users', ['name' => $name, 'password_hash' => $hash, 'role' => 'admin'], (int) $id);
    echo "Compte mis à jour.\n";
} else {
    db_insert('users', ['name' => $name, 'email' => $email, 'password_hash' => $hash, 'role' => 'admin']);
    echo "Compte administrateur créé.\n";
}
