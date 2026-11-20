<?php
/**
 * Contrôleur du panel d'administration.
 * URLs : /admin/?p=<page>&a=<action>&id=<id>
 */

require APP_DIR . '/admin/auth.php';
require APP_DIR . '/admin/resources.php';
require APP_DIR . '/admin/ui.php';

start_session('cenacle_admin', ADMIN_BASE . '/');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

function admin_url(string $page = '', array $params = []): string
{
    $q = $page !== '' ? ['p' => $page] + $params : $params;
    return ADMIN_BASE . '/' . ($q ? '?' . http_build_query($q) : '');
}

$p = (string) ($_GET['p'] ?? 'dashboard');
$a = (string) ($_GET['a'] ?? 'index');
$id = (int) ($_GET['id'] ?? 0);

// Toute requête POST doit porter un jeton CSRF valide.
if (is_post() && !verify_csrf()) {
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        json_out(['ok' => false, 'error' => 'Session expirée'], 419);
    }
    flash('error', 'Votre session a expiré. Merci de recommencer.');
    redirect(admin_url($p === 'login' ? 'login' : $p, $id ? ['a' => $a, 'id' => $id] : []));
}

/* ------------------------------------------------ Premier lancement : création du compte */
$hasUsers = (int) val('SELECT COUNT(*) FROM users') > 0;
if (!$hasUsers) {
    $errors = [];
    $old = ['name' => '', 'email' => ''];
    if (is_post()) {
        $old = ['name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? '')];
        $pw = (string) ($_POST['password'] ?? '');
        if (mb_strlen($old['name']) < 2) $errors[] = 'Indiquez votre nom.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail invalide.';
        if ($err = validate_password($pw)) $errors[] = $err;
        if ($pw !== ($_POST['password2'] ?? '')) $errors[] = 'Les deux mots de passe ne correspondent pas.';
        if (!$errors) {
            db_insert('users', ['name' => $old['name'], 'email' => $old['email'], 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'role' => 'admin']);
            attempt_login($old['email'], $pw);
            flash('success', 'Bienvenue ! Votre compte administrateur est créé.');
            redirect(admin_url());
        }
    }
    echo render(APP_DIR . '/admin/pages/setup.php', compact('errors', 'old'));
    exit;
}

/* ------------------------------------------------ Connexion */
if ($p === 'login') {
    if (admin_user()) redirect(admin_url());
    $error = null;
    $email = '';
    if (is_post()) {
        $email = trim((string) ($_POST['email'] ?? ''));
        if (login_blocked()) {
            $error = 'Trop de tentatives. Réessayez dans 15 minutes.';
        } elseif (attempt_login($email, (string) ($_POST['password'] ?? ''))) {
            $next = (string) ($_SESSION['after_login'] ?? '');
            unset($_SESSION['after_login']);
            redirect(str_starts_with($next, ADMIN_BASE . '/') ? $next : admin_url());
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
    echo render(APP_DIR . '/admin/pages/login.php', compact('error', 'email'));
    exit;
}

if (!admin_user()) {
    if (!is_post()) $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    redirect(admin_url('login'));
}
// Déconnexion automatique après 4 h d'inactivité
if (time() - ($_SESSION['admin_seen'] ?? 0) > 4 * 3600) {
    logout();
    flash('error', 'Session expirée, merci de vous reconnecter.');
    redirect(admin_url('login'));
}
$_SESSION['admin_seen'] = time();

if ($p === 'logout') {
    if (is_post()) logout();
    redirect(admin_url('login'));
}

/* ------------------------------------------------ Pages */
$resources = admin_resources();
$view = null;
$data = [];

switch (true) {
    case $p === 'dashboard':
        $view = 'dashboard';
        break;

    case isset($resources[$p]):
        require APP_DIR . '/admin/crud.php';
        [$view, $data] = crud_dispatch($p, $resources[$p], $a, $id);
        break;

    case $p === 'settings':
        require_admin_role();
        $schema = settings_schema();
        $tab = isset($schema[$_GET['tab'] ?? '']) ? $_GET['tab'] : array_key_first($schema);
        if (is_post()) {
            foreach ($schema[$tab]['fields'] as $key => [$label, $type]) {
                if ($type === 'image') {
                    if (!empty($_POST['remove_' . $key])) {
                        save_setting($key, settings_defaults()[$key]);
                    }
                    if (!empty($_FILES['f_' . $key]['name'])) {
                        try {
                            save_setting($key, handle_upload($_FILES['f_' . $key]));
                        } catch (RuntimeException $e) {
                            flash('error', $label . ' : ' . $e->getMessage());
                        }
                    }
                    continue;
                }
                $value = trim((string) ($_POST[$key] ?? ''));
                if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    flash('error', $label . ' : adresse e-mail invalide, valeur non enregistrée.');
                    continue;
                }
                if ($type === 'url' && $value !== '' && !preg_match('#^https?://#', $value)) {
                    $value = 'https://' . $value;
                }
                save_setting($key, $value);
            }
            flash('success', 'Réglages « ' . $schema[$tab]['title'] . ' » enregistrés.');
            redirect(admin_url('settings', ['tab' => $tab]));
        }
        $view = 'settings';
        $data = compact('schema', 'tab');
        break;

    case $p === 'messages':
        if ($a === 'view' && $id) {
            $msg = one('SELECT * FROM messages WHERE id = ?', [$id]);
            if (!$msg) redirect(admin_url('messages'));
            q('UPDATE messages SET is_read = 1 WHERE id = ?', [$id]);
            $view = 'message';
            $data = ['msg' => $msg];
        } elseif ($a === 'delete' && is_post()) {
            $ids = array_map('intval', (array) ($_POST['ids'] ?? [$id]));
            foreach ($ids as $mid) db_delete('messages', $mid);
            flash('success', count($ids) > 1 ? count($ids) . ' messages supprimés.' : 'Message supprimé.');
            redirect(admin_url('messages'));
        } elseif ($a === 'toggle' && is_post()) {
            q('UPDATE messages SET is_read = 1 - is_read WHERE id = ?', [$id]);
            redirect(admin_url('messages'));
        } elseif ($a === 'export') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="messages-cenacle-' . date('Y-m-d') . '.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Type', 'Nom', 'E-mail', 'Téléphone', 'Objet', 'Message'], ';');
            foreach (all('SELECT * FROM messages ORDER BY created_at DESC') as $m) {
                fputcsv($out, [$m['created_at'], $m['kind'], $m['name'], $m['email'], $m['phone'], $m['subject'], $m['body']], ';');
            }
            exit;
        } else {
            $filter = in_array($_GET['f'] ?? '', ['contact', 'adhesion', 'unread'], true) ? $_GET['f'] : '';
            $where = match ($filter) {
                'unread'   => 'WHERE is_read = 0',
                'contact', 'adhesion' => 'WHERE kind = ' . db()->quote($filter),
                default    => '',
            };
            $view = 'messages';
            $data = ['messages' => all("SELECT * FROM messages $where ORDER BY created_at DESC LIMIT 500"), 'filter' => $filter];
        }
        break;

    case $p === 'media':
        if ($a === 'upload' && is_post()) {
            $done = 0;
            $files = $_FILES['files'] ?? null;
            if ($files && is_array($files['name'])) {
                foreach ($files['name'] as $i => $name) {
                    if (!$name) continue;
                    try {
                        handle_upload(['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]], 'any');
                        $done++;
                    } catch (RuntimeException $e) {
                        flash('error', $name . ' : ' . $e->getMessage());
                    }
                }
            }
            if ($done) flash('success', $done . ' fichier' . ($done > 1 ? 's' : '') . ' ajouté' . ($done > 1 ? 's' : '') . '.');
            redirect(admin_url('media'));
        }
        if ($a === 'delete' && is_post()) {
            $m = one('SELECT * FROM media WHERE id = ?', [$id]);
            if ($m && upload_in_use($m['path'])) {
                flash('error', 'Ce fichier est encore utilisé sur le site : retirez-le d\'abord du contenu concerné.');
            } elseif ($m) {
                delete_upload($m['path']);
                flash('success', 'Fichier supprimé.');
            }
            redirect(admin_url('media'));
        }
        $view = 'media';
        $data = ['items' => all('SELECT * FROM media ORDER BY id DESC')];
        break;

    case $p === 'users':
        require_admin_role();
        require APP_DIR . '/admin/users.php';
        [$view, $data] = users_dispatch($a, $id);
        break;

    case $p === 'account':
        $me = admin_user();
        if (is_post()) {
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $cur = (string) ($_POST['current'] ?? '');
            $new = (string) ($_POST['password'] ?? '');
            $row = one('SELECT * FROM users WHERE id = ?', [$me['id']]);
            $err = null;
            if (mb_strlen($name) < 2) $err = 'Indiquez votre nom.';
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Adresse e-mail invalide.';
            elseif (val('SELECT id FROM users WHERE lower(email) = lower(?) AND id != ?', [$email, $me['id']])) $err = 'Cette adresse est déjà utilisée.';
            elseif ($new !== '' && !password_verify($cur, $row['password_hash'])) $err = 'Mot de passe actuel incorrect.';
            elseif ($new !== '' && ($e = validate_password($new))) $err = $e;
            elseif ($new !== '' && $new !== ($_POST['password2'] ?? '')) $err = 'Les deux nouveaux mots de passe ne correspondent pas.';
            if ($err) {
                flash('error', $err);
            } else {
                $upd = ['name' => $name, 'email' => $email];
                if ($new !== '') $upd['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
                db_update('users', $upd, (int) $me['id']);
                flash('success', $new !== '' ? 'Profil et mot de passe mis à jour.' : 'Profil mis à jour.');
            }
            redirect(admin_url('account'));
        }
        $view = 'account';
        $data = ['me' => $me];
        break;
}

if ($view === null) {
    http_response_code(404);
    $view = 'notfound';
}

echo admin_layout($view, $data);
