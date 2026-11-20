<?php
/**
 * Traitement des formulaires publics : validation, anti-spam
 * (jeton CSRF + champ piège + délai minimal + limite par IP),
 * enregistrement en base et notification par e-mail.
 */
function handle_public_form(string $kind, string $back): never
{
    start_session();
    $target = url($back) . '#formulaire';
    $in = [
        'name'    => trim((string) ($_POST['name'] ?? '')),
        'email'   => trim((string) ($_POST['email'] ?? '')),
        'phone'   => trim((string) ($_POST['phone'] ?? '')),
        'subject' => trim((string) ($_POST['subject'] ?? '')),
        'body'    => trim((string) ($_POST['body'] ?? '')),
    ];

    // Robots : champ piège rempli ou envoi trop rapide → on fait semblant d'accepter.
    $started = (int) ($_POST['_t'] ?? 0);
    if (!empty($_POST['website']) || ($started && time() - $started < 3)) {
        flash('success', 'Merci, votre message a bien été envoyé.');
        redirect($target);
    }

    $errors = [];
    if (!verify_csrf()) {
        $errors['form'] = 'Votre session a expiré, merci de renvoyer le formulaire.';
    }
    if (mb_strlen($in['name']) < 2 || mb_strlen($in['name']) > 120) {
        $errors['name'] = 'Indiquez votre nom (2 caractères minimum).';
    }
    if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Adresse e-mail invalide.';
    }
    if ($in['phone'] !== '' && !preg_match('/^[\d\s+().-]{6,25}$/', $in['phone'])) {
        $errors['phone'] = 'Numéro de téléphone invalide.';
    }
    if (mb_strlen($in['body']) < 10 || mb_strlen($in['body']) > 5000) {
        $errors['body'] = 'Votre message doit contenir entre 10 et 5000 caractères.';
    }
    $recent = (int) val("SELECT COUNT(*) FROM messages WHERE ip = ? AND created_at > datetime('now', '-1 hour')", [client_ip()]);
    if ($recent >= 5) {
        $errors['form'] = 'Trop de messages envoyés récemment. Merci de réessayer plus tard.';
    }

    if ($errors) {
        $_SESSION['_form'] = ['errors' => $errors, 'old' => $in];
        redirect($target);
    }

    $subject = $in['subject'] !== '' ? $in['subject'] : ($kind === 'adhesion' ? 'Demande d\'adhésion' : 'Message depuis le site');
    db_insert('messages', [
        'kind'    => $kind,
        'name'    => $in['name'],
        'email'   => $in['email'],
        'phone'   => $in['phone'],
        'subject' => mb_substr($subject, 0, 200),
        'body'    => $in['body'],
        'ip'      => client_ip(),
    ]);

    // Notification e-mail (si le serveur sait envoyer des e-mails).
    $to = setting('contact_recipient') ?: setting('email');
    if ($to && function_exists('mail')) {
        $label = $kind === 'adhesion' ? 'Nouvelle demande d\'adhésion' : 'Nouveau message';
        $mailBody = "$label reçu(e) via le site du Cénacle.\n\n"
            . "Nom : {$in['name']}\nE-mail : {$in['email']}\nTéléphone : {$in['phone']}\nObjet : $subject\n\n"
            . $in['body'] . "\n\n— Consultez tous les messages dans l'administration : " . abs_url('admin/?p=messages');
        $headers = [
            'From'         => $GLOBALS['config']['mail_from'],
            'Reply-To'     => $in['email'],
            'Content-Type' => 'text/plain; charset=UTF-8',
        ];
        @mail($to, '=?UTF-8?B?' . base64_encode('[Le Cénacle] ' . $subject) . '?=', $mailBody, $headers);
    }

    flash('success', $kind === 'adhesion'
        ? 'Merci ! Votre demande d\'adhésion a bien été transmise. Le Cénacle reviendra vers vous rapidement.'
        : 'Merci, votre message a bien été envoyé. Nous vous répondrons rapidement.');
    redirect($target);
}
