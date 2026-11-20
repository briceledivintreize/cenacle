<?php
/** @var ?string $error @var string $email */
ob_start(); ?>
<h1>Espace d'administration</h1>
<p class="auth__lede">Connectez-vous pour gérer le site du Cénacle.</p>
<?php foreach (take_flashes() as $f): ?>
  <div class="alert alert--<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<?php if ($error): ?><div class="alert alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e(admin_url('login')) ?>" class="auth__form">
  <?= csrf_field() ?>
  <div class="fld"><label for="email">Adresse e-mail</label><input id="email" type="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus></div>
  <div class="fld"><label for="password">Mot de passe</label>
    <div class="pw"><input id="password" type="password" name="password" required autocomplete="current-password"><button type="button" class="pw__toggle" data-pw aria-label="Afficher le mot de passe"><?= icon('eye', 18) ?></button></div>
  </div>
  <button class="btn btn-primary btn-block" type="submit">Se connecter</button>
</form>
<?php
$inner = ob_get_clean();
$title = 'Connexion';
require __DIR__ . '/_auth_shell.php';
