<?php
/** Galerie d'un événement. @var array $event @var array $media */
$base = fn($a) => admin_url('events', ['a' => $a, 'id' => $event['id']]);
$actions = '<a class="btn btn-ghost" href="' . e(admin_url('events')) . '">← Événements</a>'
    . '<a class="btn btn-ghost" href="' . e($base('edit')) . '">' . icon('edit', 16) . ' Modifier le texte</a>';
if ($event['published'] && $event['in_events']) {
    $actions .= '<a class="btn btn-ghost" href="' . e(url('evenements/' . $event['slug'])) . '" target="_blank" rel="noopener">' . icon('external', 16) . ' Voir</a>';
}
?>
<?= page_header('Galerie — ' . $event['title'], $event['date_label'] . ($event['location'] ? ' · ' . $event['location'] : ''), $actions) ?>

<div class="media-add">
  <form class="dropzone panel" method="post" action="<?= e($base('media_upload')) ?>" enctype="multipart/form-data" data-dropzone>
    <?= csrf_field() ?>
    <?= icon('upload', 34) ?>
    <h2>Déposez vos photos et vidéos ici</h2>
    <p>ou <label class="link">parcourez vos fichiers<input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" hidden></label></p>
    <small class="muted">Photos JPG/PNG/WebP (optimisées automatiquement) · vidéos MP4/WebM · <?= e(human_size((int) $GLOBALS['config']['max_upload'])) ?> max. par fichier</small>
    <div class="dropzone__progress" hidden><span></span></div>
    <noscript><button class="btn btn-primary" type="submit">Envoyer</button></noscript>
  </form>
  <form class="panel video-add" method="post" action="<?= e($base('media_video')) ?>">
    <?= csrf_field() ?>
    <h2 class="panel__title"><?= icon('video', 18) ?> Ajouter une vidéo YouTube / Vimeo</h2>
    <div class="fld"><label for="video_url">Lien de la vidéo</label><input id="video_url" type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=…" required></div>
    <div class="fld"><label for="vcap">Légende</label><input id="vcap" name="caption" placeholder="Facultatif"></div>
    <button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?> Ajouter la vidéo</button>
  </form>
</div>

<?php if (!$media): ?>
  <div class="empty-state">
    <?= icon('image', 40) ?>
    <h2>Aucun média pour cet événement</h2>
    <p>Sur le site, des emplacements « Photo à venir » sont affichés en attendant.</p>
  </div>
<?php else: ?>
  <form method="post" action="<?= e($base('media_update')) ?>" id="captions-form">
    <?= csrf_field() ?>
    <div class="panel__head"><h2><?= count($media) ?> média<?= count($media) > 1 ? 's' : '' ?></h2><button class="btn btn-primary btn-sm" type="submit"><?= icon('check', 15) ?> Enregistrer les légendes</button></div>
  </form>
  <div class="media-grid" data-sortable="<?= e($base('media_reorder')) ?>">
    <?php foreach ($media as $m):
        $embed = video_embed($m['video_url']); ?>
      <div class="media-card" data-id="<?= (int) $m['id'] ?>">
        <div class="media-card__thumb">
          <span class="drag" title="Glisser pour réordonner"><?= icon('drag', 16) ?></span>
          <?php if ($m['kind'] === 'image'): ?>
            <img src="<?= e(media_url($m['path'])) ?>" alt="" loading="lazy">
            <?php if ($event['cover'] === $m['path']): ?><span class="tag tag--ok media-card__cover">Couverture</span><?php endif; ?>
          <?php elseif ($embed): ?>
            <?php if ($embed['thumb']): ?><img src="<?= e($embed['thumb']) ?>" alt="" loading="lazy"><?php endif; ?>
            <span class="media-card__play"><?= icon('video', 20) ?> <?= e(ucfirst($embed['provider'])) ?></span>
          <?php else: ?>
            <video src="<?= e(media_url($m['path'])) ?>#t=0.5" preload="metadata" muted></video>
            <span class="media-card__play"><?= icon('video', 20) ?> Vidéo</span>
          <?php endif; ?>
        </div>
        <input class="media-card__cap" name="caption[<?= (int) $m['id'] ?>]" form="captions-form" value="<?= e($m['caption']) ?>" placeholder="Légende…" aria-label="Légende">
        <div class="media-card__actions">
          <?php if ($m['kind'] === 'image' && $event['cover'] !== $m['path']): ?>
            <?= post_button($base('media_cover'), icon('star', 15) . ' Couverture', 'btn btn-ghost btn-xs', '', ['media_id' => $m['id']]) ?>
          <?php endif; ?>
          <?= post_button($base('media_delete'), icon('trash', 15), 'adm-iconbtn adm-iconbtn--danger', 'Supprimer ce média ?', ['media_id' => $m['id']]) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
