<?php
/** Boîte de réception. @var array $messages @var string $filter */
$tabs = ['' => 'Tous', 'unread' => 'Non lus', 'adhesion' => 'Adhésions', 'contact' => 'Contacts'];
?>
<?= page_header('Messages', 'Demandes reçues via les formulaires Contact et Nous rejoindre. Une copie est aussi envoyée par e-mail à l\'adresse définie dans les réglages.',
    '<a class="btn btn-ghost" href="' . e(admin_url('messages', ['a' => 'export'])) . '">' . icon('download', 16) . ' Exporter (CSV)</a>') ?>

<nav class="tabs">
  <?php foreach ($tabs as $k => $l): ?>
    <a href="<?= e(admin_url('messages', $k ? ['f' => $k] : [])) ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= e($l) ?></a>
  <?php endforeach; ?>
</nav>

<?php if (!$messages): ?>
  <div class="empty-state"><?= icon('mail', 40) ?><h2>Aucun message</h2><p>Les nouveaux messages apparaîtront ici.</p></div>
<?php else: ?>
<form method="post" action="<?= e(admin_url('messages', ['a' => 'delete'])) ?>" data-confirm="Supprimer les messages sélectionnés ?">
  <?= csrf_field() ?>
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th class="col-check"><input type="checkbox" data-checkall aria-label="Tout sélectionner"></th><th>Expéditeur</th><th>Objet</th><th>Type</th><th>Reçu</th></tr></thead>
    <tbody>
      <?php foreach ($messages as $m): $href = admin_url('messages', ['a' => 'view', 'id' => $m['id']]); ?>
        <tr class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
          <td class="col-check"><input type="checkbox" name="ids[]" value="<?= (int) $m['id'] ?>" aria-label="Sélectionner"></td>
          <td class="cell-main"><a href="<?= e($href) ?>" class="cell-title"><span class="msglist__av"><?= e(initials($m['name'])) ?></span><span><strong><?= e($m['name']) ?></strong><small class="muted"><?= e($m['email']) ?></small></span></a></td>
          <td><a href="<?= e($href) ?>"><?= e($m['subject']) ?><br><small class="muted"><?= e(excerpt($m['body'], 70)) ?></small></a></td>
          <td><span class="tag tag--<?= e($m['kind']) ?>"><?= $m['kind'] === 'adhesion' ? 'Adhésion' : 'Contact' ?></span></td>
          <td><small><?= e(time_ago($m['created_at'])) ?></small></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <button class="btn btn-ghost btn-sm" type="submit"><?= icon('trash', 15) ?> Supprimer la sélection</button>
</form>
<?php endif; ?>
