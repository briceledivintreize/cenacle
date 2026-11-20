<?php
/** Liste générique. @var string $key @var array $res @var array $rows @var array $filters @var string $search */
$sortable = !empty($res['sortable']) && !$filters && $search === '';
$newBtn = '<a class="btn btn-primary" href="' . e(admin_url($key, ['a' => 'new'])) . '">' . icon('plus', 16) . ' Ajouter ' . e($res['singular']) . '</a>';
?>
<?= page_header($res['label'], $res['intro'] ?? '', $newBtn) ?>

<?php if (!empty($res['filters']) || !empty($res['search'])): ?>
<form class="toolbar" method="get" action="<?= e(ADMIN_BASE . '/') ?>">
  <input type="hidden" name="p" value="<?= e($key) ?>">
  <?php if (!empty($res['search'])): ?>
    <div class="toolbar__search"><?= icon('search', 16) ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Rechercher…" aria-label="Rechercher"></div>
  <?php endif; ?>
  <?php foreach ($res['filters'] ?? [] as $col => $opts): ?>
    <select name="<?= e($col) ?>" aria-label="Filtrer" data-autosubmit>
      <option value="">Tous les types</option>
      <?php foreach ($opts as $k => $l): ?><option value="<?= e($k) ?>"<?= ($filters[$col] ?? '') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select>
  <?php endforeach; ?>
  <button class="btn btn-ghost btn-sm" type="submit">Filtrer</button>
  <?php if ($filters || $search !== ''): ?><a class="link" href="<?= e(admin_url($key)) ?>">Réinitialiser</a><?php endif; ?>
  <span class="toolbar__count"><?= count($rows) ?> élément<?= count($rows) > 1 ? 's' : '' ?></span>
</form>
<?php endif; ?>

<?php if (!$rows): ?>
  <div class="empty-state">
    <?= icon($res['icon'], 40) ?>
    <h2>Aucun élément pour le moment</h2>
    <p>Commencez par ajouter <?= e($res['singular']) ?>.</p>
    <?= $newBtn ?>
  </div>
<?php else: ?>
<div class="table-wrap">
<table class="table table--list">
  <thead>
    <tr>
      <?php if ($sortable): ?><th class="col-drag" aria-label="Ordre"></th><?php endif; ?>
      <?php foreach ($res['columns'] as $c => $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
      <th>Statut</th>
      <th class="right">Actions</th>
    </tr>
  </thead>
  <tbody<?= $sortable ? ' data-sortable="' . e(admin_url($key, ['a' => 'reorder'])) . '"' : '' ?>>
    <?php foreach ($rows as $r):
        $editUrl = admin_url($key, ['a' => 'edit', 'id' => $r['id']]); ?>
      <tr data-id="<?= (int) $r['id'] ?>">
        <?php if ($sortable): ?><td class="col-drag"><span class="drag" title="Glisser pour réordonner"><?= icon('drag', 16) ?></span></td><?php endif; ?>
        <?php $first = true; foreach ($res['columns'] as $c => $label):
            $val = $r[$c] ?? '';
            if ($c === 'type') $val = EVENT_TYPES[$val] ?? $val;
            if ($c === 'grp') $val = STAT_GROUPS[$val] ?? $val;
            if ($c === 'kind') $val = LINK_KINDS[$val] ?? $val; ?>
          <td<?= $first ? ' class="cell-main"' : '' ?>>
            <?php if ($first):
                $thumbField = null;
                foreach ($res['fields'] as $f => $d) if ($d[1] === 'image') { $thumbField = $f; break; } ?>
              <a href="<?= e($editUrl) ?>" class="cell-title">
                <?php if ($thumbField): ?>
                  <span class="thumb"><?php if (!empty($r[$thumbField])): ?><img src="<?= e(media_url($r[$thumbField])) ?>" alt="" loading="lazy"><?php else: ?><?= icon($res['icon'], 16) ?><?php endif; ?></span>
                <?php endif; ?>
                <span><strong><?= e(excerpt((string) $val, 90)) ?></strong><?php if (!empty($r['featured'])): ?> <span class="tag tag--star" title="Jalon clé"><?= icon('star', 12) ?></span><?php endif; ?></span>
              </a>
            <?php elseif ($c === 'media'): ?>
              <a href="<?= e(admin_url($key, ['a' => 'media', 'id' => $r['id']])) ?>" class="tag <?= $val ? 'tag--ok' : '' ?>"><?= (int) $val ?> média<?= $val > 1 ? 's' : '' ?></a>
            <?php else: ?>
              <?= e(excerpt((string) $val, 70)) ?>
            <?php endif; ?>
          </td>
        <?php $first = false; endforeach; ?>
        <td>
          <button type="button" class="status <?= $r['published'] ? 'is-on' : '' ?>" data-toggle="<?= e(admin_url($key, ['a' => 'toggle', 'id' => $r['id']])) ?>" title="Cliquer pour changer">
            <?= $r['published'] ? 'Publié' : 'Masqué' ?>
          </button>
        </td>
        <td class="right actions">
          <?php if ($key === 'events'): ?>
            <a class="adm-iconbtn" href="<?= e(admin_url($key, ['a' => 'media', 'id' => $r['id']])) ?>" title="Galerie photos/vidéos"><?= icon('image', 17) ?></a>
          <?php endif; ?>
          <?php if (!empty($res['public']) && $r['published']): ?>
            <a class="adm-iconbtn" href="<?= e(url($res['public']($r))) ?>" target="_blank" rel="noopener" title="Voir sur le site"><?= icon('external', 17) ?></a>
          <?php endif; ?>
          <a class="adm-iconbtn" href="<?= e($editUrl) ?>" title="Modifier"><?= icon('edit', 17) ?></a>
          <?= post_button(admin_url($key, ['a' => 'duplicate', 'id' => $r['id']]), icon('copy', 17), 'adm-iconbtn', '', []) ?>
          <?= post_button(admin_url($key, ['a' => 'delete', 'id' => $r['id']]), icon('trash', 17), 'adm-iconbtn adm-iconbtn--danger', 'Supprimer définitivement cet élément ?') ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php if ($sortable): ?><p class="muted small"><?= icon('drag', 14) ?> Glissez les lignes pour modifier l'ordre d'affichage sur le site.</p><?php endif; ?>
<?php endif; ?>
