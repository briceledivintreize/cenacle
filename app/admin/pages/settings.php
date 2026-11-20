<?php
/** Réglages du site par onglets. @var array $schema @var string $tab */
$group = $schema[$tab];
$previews = [
    'general' => '', 'hero' => '', 'home' => '#programmes', 'about' => 'association', 'join' => 'rejoindre',
    'publications' => 'publications', 'contact' => 'contact', 'footer' => 'mentions-legales',
];
?>
<?= page_header('Réglages du site', 'Textes, coordonnées et images du site public. Les modifications sont visibles immédiatement.',
    '<a class="btn btn-ghost" href="' . e(url($previews[$tab] ?? '')) . '" target="_blank" rel="noopener">' . icon('external', 16) . ' Prévisualiser</a>') ?>

<nav class="tabs" aria-label="Sections des réglages">
  <?php foreach ($schema as $k => $g): ?>
    <a href="<?= e(admin_url('settings', ['tab' => $k])) ?>"<?= $k === $tab ? ' aria-current="page"' : '' ?>><?= e($g['title']) ?></a>
  <?php endforeach; ?>
</nav>

<form method="post" enctype="multipart/form-data" class="panel settings-form" data-dirty-check>
  <?= csrf_field() ?>
  <div class="fields">
    <?php foreach ($group['fields'] as $key => $def):
        $value = setting($key);
        $fdef = [$def[0], $def[1], ['help' => $def[3] ?? null]];
        if ($def[1] === 'image') $fdef[2]['help'] = ($def[3] ?? '') ?: null; ?>
      <?= form_field($key, $fdef, $value) ?>
    <?php endforeach; ?>
  </div>
  <div class="settings-form__foot">
    <button class="btn btn-primary" type="submit"><?= icon('check', 16) ?> Enregistrer « <?= e($group['title']) ?> »</button>
  </div>
</form>
