<?php
/** Médiathèque. @var array $items */
?>
<?= page_header('Médiathèque', 'Tous les fichiers envoyés sur le site. Un fichier encore utilisé ne peut pas être supprimé.') ?>

<form class="dropzone panel" method="post" action="<?= e(admin_url('media', ['a' => 'upload'])) ?>" enctype="multipart/form-data" data-dropzone>
  <?= csrf_field() ?>
  <?= icon('upload', 34) ?>
  <h2>Déposez des fichiers ici</h2>
  <p>ou <label class="link">parcourez vos fichiers<input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm" hidden></label></p>
  <small class="muted">Pour illustrer un événement, utilisez plutôt sa « Galerie » (menu Événements).</small>
  <div class="dropzone__progress" hidden><span></span></div>
</form>

<?php if (!$items): ?>
  <div class="empty-state"><?= icon('image', 40) ?><h2>La médiathèque est vide</h2></div>
<?php else: ?>
  <div class="media-grid media-grid--lib">
    <?php foreach ($items as $m):
        $used = upload_in_use($m['path']);
        $isVideo = str_starts_with((string) $m['mime'], 'video/'); ?>
      <figure class="media-card">
        <div class="media-card__thumb">
          <?php if ($isVideo): ?><video src="<?= e(media_url($m['path'])) ?>#t=0.5" preload="metadata" muted></video><span class="media-card__play"><?= icon('video', 18) ?></span>
          <?php else: ?><img src="<?= e(media_url($m['path'])) ?>" alt="" loading="lazy"><?php endif; ?>
          <?php if ($used): ?><span class="tag tag--ok media-card__cover">Utilisé</span><?php endif; ?>
        </div>
        <figcaption>
          <strong title="<?= e($m['original_name']) ?>"><?= e(excerpt($m['original_name'], 28)) ?></strong>
          <small class="muted"><?= e(human_size((int) $m['size'])) ?><?= $m['width'] ? ' · ' . (int) $m['width'] . '×' . (int) $m['height'] : '' ?></small>
        </figcaption>
        <div class="media-card__actions">
          <button type="button" class="btn btn-ghost btn-xs" data-copy="<?= e(abs_url($m['path'])) ?>"><?= icon('copy', 14) ?> Lien</button>
          <?php if (!$used): ?><?= post_button(admin_url('media', ['a' => 'delete', 'id' => $m['id']]), icon('trash', 15), 'adm-iconbtn adm-iconbtn--danger', 'Supprimer ce fichier ?') ?><?php endif; ?>
        </div>
      </figure>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
