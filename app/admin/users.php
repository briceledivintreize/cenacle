<?php
/**
 * Gestion des comptes (réservée aux administrateurs).
 */
const USER_ROLES = [
    'admin'  => 'Administrateur (tout le site, réglages et comptes)',
    'editor' => 'Éditeur (contenus, médias et messages)',
];

function users_dispatch(string $a, int $id): array
{
    $me = admin_user();
    if (($a === 'new' || $a === 'edit')) {
        $row = $a === 'edit' ? one('SELECT id, name, email, role FROM users WHERE id = ?', [$id]) : ['name' => '', 'email' => '', 'role' => 'editor'];
        if (!$row) redirect(admin_url('users'));
        $errors = [];
        if (is_post()) {
            $row = array_merge($row, [
                'name'  => trim((string) ($_POST['name'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'role'  => isset(USER_ROLES[$_POST['role'] ?? '']) ? $_POST['role'] : 'editor',
            ]);
            $pw = (string) ($_POST['password'] ?? '');
            if (mb_strlen($row['name']) < 2) $errors[] = 'Indiquez un nom.';
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail invalide.';
            if (val('SELECT id FROM users WHERE lower(email) = lower(?) AND id != ?', [$row['email'], $id])) $errors[] = 'Cette adresse est déjà utilisée.';
            if ($a === 'new' || $pw !== '') {
                if ($e = validate_password($pw)) $errors[] = $e;
            }
            if ($a === 'edit' && $id === (int) $me['id'] && $row['role'] !== 'admin') $errors[] = 'Vous ne pouvez pas retirer votre propre rôle d\'administrateur.';
            if (!$errors) {
                $data = ['name' => $row['name'], 'email' => $row['email'], 'role' => $row['role']];
                if ($pw !== '') $data['password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
                if ($a === 'new') db_insert('users', $data);
                else db_update('users', $data, $id);
                flash('success', $a === 'new' ? 'Compte créé. Communiquez le mot de passe à son titulaire de vive voix.' : 'Compte mis à jour.');
                redirect(admin_url('users'));
            }
        }
        return ['user_form', compact('row', 'errors', 'a')];
    }
    if ($a === 'delete' && is_post()) {
        if ($id === (int) $me['id']) {
            flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        } elseif ((int) val("SELECT COUNT(*) FROM users WHERE role = 'admin' AND id != ?", [$id]) === 0) {
            flash('error', 'Il doit rester au moins un administrateur.');
        } else {
            db_delete('users', $id);
            flash('success', 'Compte supprimé.');
        }
        redirect(admin_url('users'));
    }
    return ['users', ['users' => all('SELECT id, name, email, role, last_login, created_at FROM users ORDER BY name')]];
}
