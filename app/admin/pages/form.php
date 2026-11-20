<?php
/** Formulaire générique. @var string $key @var array $res @var ?array $row @var array $errors */
$isEdit = !empty($row['id']);
$title = $isEdit ? 'Modifier ' . $res['singular'] : 'Ajouter ' . $res['singular'];
$actions = '<a class="btn btn-ghost" href="' . e(admin_url($key)) . '">← ' . e($res['label']) . '</a>';
if ($isEdit && $key === 'events') {
    $actions .= '<a class="btn btn-ghost" href="' . e(admin_url($key, ['a' => 'media', 'id' => $row['id']])) . '">' . icon('image', 16) . ' Galerie</a>';
}
if ($isEdit && !empty($res['public']) && !empty($row['published']) && !empty($row['slug'])) {
    $actions .= '<a class="btn btn-ghost" href="' . e(url($res['public']($row))) . '" target="_blank" rel="noopener">' . icon('external', 16) . ' Voir</a>';
}
?>
<?= page_header($title, $isEdit ? '' : ($res['intro'] ?? ''), $actions) ?>

<?php if ($errors): ?><div class="alert alert--error">Merci de corriger les champs signalés.</div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="editor" data-dirty-check>
  <?= csrf_field() ?>
  <div class="editor__main panel">
    <div class="fields">
      <?php foreach ($res['fields'] as $f => $def):
          if (in_array($def[1], ['checkbox', 'image'], true)) continue; ?>
        <?= form_field($f, $def, $row[$f] ?? null, $errors[$f] ?? null) ?>
      <?php endforeach; ?>
    </div>
  </div>
  <aside class="editor__side">
    <div class="panel">
      <h2 class="panel__title">Publication</h2>
      <?php foreach ($res['fields'] as $f => $def):
          if ($def[1] !== 'checkbox') continue; ?>
        <?= form_field($f, $def, $isEdit || is_post() ? ($row[$f] ?? 0) : null, $errors[$f] ?? null) ?>
      <?php endforeach; ?>
      <div class="editor__btns">
        <button class="btn btn-primary btn-block" type="submit" name="_next" value="list"><?= icon('check', 16) ?> Enregistrer</button>
        <button class="btn btn-ghost btn-block" type="submit" name="_next" value="stay">Enregistrer et continuer</button>
      </div>
      <?php if ($isEdit && !empty($row['updated_at'])): ?><p class="muted small">Dernière modification : <?= e(time_ago($row['updated_at'])) ?></p><?php endif; ?>
    </div>
    <?php foreach ($res['fields'] as $f => $def):
        if ($def[1] !== 'image') continue; ?>
      <div class="panel"><?= form_field($f, $def, $row[$f] ?? null, $errors[$f] ?? null) ?></div>
    <?php endforeach; ?>
  </aside>
</form>
