<?php
/** @var array $users */
$me = admin_user();
?>
<?= page_header('Utilisateurs', 'Personnes autorisées à accéder à l\'administration.', '<a class="btn btn-primary" href="' . e(admin_url('users', ['a' => 'new'])) . '">' . icon('plus', 16) . ' Nouveau compte</a>') ?>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Nom</th><th>Rôle</th><th>Dernière connexion</th><th class="right">Actions</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td class="cell-main"><span class="cell-title"><span class="msglist__av"><?= e(initials($u['name'])) ?></span><span><strong><?= e($u['name']) ?><?= (int) $u['id'] === (int) $me['id'] ? ' (vous)' : '' ?></strong><small class="muted"><?= e($u['email']) ?></small></span></span></td>
        <td><span class="tag <?= $u['role'] === 'admin' ? 'tag--adhesion' : '' ?>"><?= $u['role'] === 'admin' ? 'Administrateur' : 'Éditeur' ?></span></td>
        <td><small><?= e(time_ago($u['last_login'])) ?></small></td>
        <td class="right actions">
          <a class="adm-iconbtn" href="<?= e(admin_url('users', ['a' => 'edit', 'id' => $u['id']])) ?>" title="Modifier"><?= icon('edit', 17) ?></a>
          <?php if ((int) $u['id'] !== (int) $me['id']): ?>
            <?= post_button(admin_url('users', ['a' => 'delete', 'id' => $u['id']]), icon('trash', 17), 'adm-iconbtn adm-iconbtn--danger', 'Supprimer ce compte ?') ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
