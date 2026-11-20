<?php
/** @var array $me */
?>
<?= page_header('Mon compte', 'Votre profil et votre mot de passe.') ?>
<form method="post" class="panel narrow">
  <?= csrf_field() ?>
  <div class="fields">
    <div class="fld fld--half"><label for="name">Nom</label><input id="name" name="name" value="<?= e($me['name']) ?>" required></div>
    <div class="fld fld--half"><label for="email">E-mail de connexion</label><input id="email" type="email" name="email" value="<?= e($me['email']) ?>" required></div>
  </div>
  <h2 class="panel__title" style="margin-top:24px">Changer de mot de passe</h2>
  <div class="fields">
    <div class="fld"><label for="current">Mot de passe actuel</label><input id="current" type="password" name="current" autocomplete="current-password"></div>
    <div class="fld fld--half"><label for="password">Nouveau mot de passe</label><div class="pw"><input id="password" type="password" name="password" autocomplete="new-password"><button type="button" class="pw__toggle" data-pw aria-label="Afficher"><?= icon('eye', 18) ?></button></div></div>
    <div class="fld fld--half"><label for="password2">Confirmer</label><input id="password2" type="password" name="password2" autocomplete="new-password"></div>
  </div>
  <p class="muted small">Laissez les champs mot de passe vides pour ne modifier que le profil.</p>
  <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Enregistrer</button>
</form>
