<?php
/** Premier lancement : création du compte administrateur. @var array $errors @var array $old */
ob_start(); ?>
<h1>Bienvenue !</h1>
<p class="auth__lede">Première mise en service : créez le compte administrateur principal du site.</p>
<?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" action="<?= e(admin_url()) ?>" class="auth__form">
  <?= csrf_field() ?>
  <div class="fld"><label for="name">Votre nom</label><input id="name" name="name" value="<?= e($old['name']) ?>" required autocomplete="name"></div>
  <div class="fld"><label for="email">Adresse e-mail</label><input id="email" type="email" name="email" value="<?= e($old['email']) ?>" required autocomplete="username"></div>
  <div class="fld"><label for="password">Mot de passe</label><input id="password" type="password" name="password" required minlength="10" autocomplete="new-password"><small class="help">10 caractères minimum, lettres et chiffres.</small></div>
  <div class="fld"><label for="password2">Confirmer le mot de passe</label><input id="password2" type="password" name="password2" required autocomplete="new-password"></div>
  <button class="btn btn-primary btn-block" type="submit">Créer le compte</button>
</form>
<?php
$inner = ob_get_clean();
$title = 'Installation';
require __DIR__ . '/_auth_shell.php';
