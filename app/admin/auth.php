<?php
/**
 * Authentification du panel.
 */

function admin_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $id = $_SESSION['admin_uid'] ?? null;
        $user = $id ? one('SELECT id, name, email, role, last_login FROM users WHERE id = ?', [$id]) : null;
    }
    return $user;
}

function is_admin(): bool
{
    return (admin_user()['role'] ?? '') === 'admin';
}

function require_admin_role(): void
{
    if (!is_admin()) {
        flash('error', 'Cette section est réservée aux administrateurs.');
        redirect(admin_url());
    }
}

function login_blocked(): bool
{
    q('DELETE FROM login_attempts WHERE attempted_at < ?', [time() - 900]);
    return (int) val('SELECT COUNT(*) FROM login_attempts WHERE ip = ?', [client_ip()]) >= 6;
}

function attempt_login(string $email, string $password): bool
{
    $u = one('SELECT * FROM users WHERE lower(email) = lower(?)', [trim($email)]);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        db_insert('login_attempts', ['ip' => client_ip(), 'attempted_at' => time()]);
        return false;
    }
    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
    }
    session_regenerate_id(true);
    $_SESSION['admin_uid'] = (int) $u['id'];
    $_SESSION['admin_seen'] = time();
    q('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?', [$u['id']]);
    q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
    return true;
}

function logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function validate_password(string $pw): ?string
{
    if (mb_strlen($pw) < 10) {
        return 'Le mot de passe doit contenir au moins 10 caractères.';
    }
    if (!preg_match('/\d/', $pw) || !preg_match('/\p{L}/u', $pw)) {
        return 'Le mot de passe doit contenir des lettres et des chiffres.';
    }
    return null;
}
