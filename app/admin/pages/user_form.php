<?php
/** @var array $row @var array $errors @var string $a */
?>
<?= page_header($a === 'new' ? 'Nouveau compte' : 'Modifier le compte', '', '<a class="btn btn-ghost" href="' . e(admin_url('users')) . '">← Utilisateurs</a>') ?>
<?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="panel narrow">
  <?= csrf_field() ?>
  <div class="fields">
    <div class="fld fld--half"><label for="name">Nom</label><input id="name" name="name" value="<?= e($row['name']) ?>" required></div>
    <div class="fld fld--half"><label for="email">E-mail</label><input id="email" type="email" name="email" value="<?= e($row['email']) ?>" required autocomplete="off"></div>
    <div class="fld"><label for="role">Rôle</label><select id="role" name="role">
      <?php foreach (USER_ROLES as $k => $l): ?><option value="<?= e($k) ?>"<?= $row['role'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select></div>
    <div class="fld"><label for="password"><?= $a === 'new' ? 'Mot de passe' : 'Nouveau mot de passe' ?></label>
      <div class="pw"><input id="password" type="password" name="password" autocomplete="new-password"<?= $a === 'new' ? ' required' : '' ?>><button type="button" class="pw__toggle" data-pw aria-label="Afficher"><?= icon('eye', 18) ?></button></div>
      <small class="help"><?= $a === 'new' ? '10 caractères minimum, lettres et chiffres.' : 'Laisser vide pour ne pas le changer.' ?></small></div>
  </div>
  <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Enregistrer</button>
</form>
