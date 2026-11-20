<?php
$ev = get_event($params['slug']);
if (!$ev) {
    echo '__404__';
    return;
}
$meta['title'] = $ev['title'];
$meta['description'] = excerpt($ev['summary'] ?: $ev['content'], 160);
if ($ev['cover']) {
    $meta['image'] = $ev['cover'];
}
$media = get_event_media((int) $ev['id']);
$images = array_values(array_filter($media, fn($m) => $m['kind'] === 'image'));
$videos = array_values(array_filter($media, fn($m) => $m['kind'] === 'video'));

// Événements voisins dans la chronologie
$all = get_events();
$idx = array_search($ev['id'], array_column($all, 'id'));
$newer = $idx !== false && $idx > 0 ? $all[$idx - 1] : null;
$older = $idx !== false && $idx < count($all) - 1 ? $all[$idx + 1] : null;
?>
<article class="event">
  <header class="event__hero">
    <canvas class="page-hero__gl" data-ink aria-hidden="true"></canvas>
    <div class="event__hero-inner">
      <a href="<?= e(url('evenements')) ?>" class="back-link" data-reveal><span aria-hidden="true">←</span> Tous les événements</a>
      <div class="event__meta" data-reveal>
        <span class="pill pill--<?= e($ev['type']) ?>"><?= e(event_type_label($ev['type'])) ?></span>
        <span><?= e($ev['date_label']) ?></span>
        <?php if ($ev['location']): ?><span>· <?= e($ev['location']) ?></span><?php endif; ?>
      </div>
      <h1 data-split="chars"><?= e($ev['title']) ?></h1>
      <p class="page-hero__lede" data-reveal><?= e($ev['summary']) ?></p>
    </div>
    <?php if ($ev['cover']): ?>
      <div class="event__cover" data-parallax><?= picture($ev['cover'], $ev['title'], ['loading' => 'eager']) ?></div>
    <?php endif; ?>
  </header>

  <section class="section">
    <?php if (trim((string) $ev['content']) !== ''): ?>
      <div class="prose prose--narrow" data-reveal><?= markup($ev['content']) ?></div>
    <?php endif; ?>

    <div class="gallery-head" data-reveal>
      <h2>Galerie</h2>
      <span><?= count($images) ?> photo<?= count($images) > 1 ? 's' : '' ?> · <?= count($videos) ?> vidéo<?= count($videos) > 1 ? 's' : '' ?></span>
    </div>

    <?php if (!$media): ?>
      <div class="cards-grid">
        <?= media_placeholder($ev['title']) ?>
        <?= media_placeholder($ev['title']) ?>
        <?= media_placeholder($ev['title'], 'Vidéo') ?>
      </div>
      <p class="note">Les photos et vidéos de cet événement seront publiées prochainement.</p>
    <?php else: ?>
      <div class="gallery" data-gallery>
        <?php foreach ($media as $m):
            if ($m['kind'] === 'image' && $m['path']): ?>
            <button type="button" class="gallery__item" data-lightbox="image" data-src="<?= e(media_url($m['path'])) ?>" data-caption="<?= e($m['caption']) ?>" data-reveal>
              <?= picture($m['path'], $m['caption'] ?: $ev['title']) ?>
              <?php if ($m['caption']): ?><span class="gallery__cap"><?= e($m['caption']) ?></span><?php endif; ?>
            </button>
          <?php elseif ($m['kind'] === 'video'):
              $embed = video_embed($m['video_url']);
              $src = $embed ? $embed['embed'] : media_url($m['path']);
              if (!$src) continue; ?>
            <button type="button" class="gallery__item gallery__item--video" data-lightbox="<?= $embed ? 'embed' : 'video' ?>" data-src="<?= e($src) ?>" data-caption="<?= e($m['caption']) ?>" data-reveal>
              <?php if ($embed && $embed['thumb']): ?>
                <img src="<?= e($embed['thumb']) ?>" alt="<?= e($m['caption'] ?: 'Vidéo') ?>" loading="lazy">
              <?php elseif (!$embed): ?>
                <video src="<?= e($src) ?>#t=0.5" preload="metadata" muted playsinline></video>
              <?php else: ?>
                <span class="ph-media"><span class="ph-media__icon">▶</span><span>Vidéo</span></span>
              <?php endif; ?>
              <span class="gallery__play" aria-hidden="true">▶</span>
              <?php if ($m['caption']): ?><span class="gallery__cap"><?= e($m['caption']) ?></span><?php endif; ?>
            </button>
          <?php endif; endforeach; ?>
      </div>
    <?php endif; ?>

    <nav class="event-nav" aria-label="Événements voisins">
      <?php if ($older): ?>
        <a href="<?= e(url('evenements/' . $older['slug'])) ?>" class="event-nav__link"><small>← Précédent · <?= e($older['date_label']) ?></small><span><?= e($older['title']) ?></span></a>
      <?php else: ?><span></span><?php endif; ?>
      <?php if ($newer): ?>
        <a href="<?= e(url('evenements/' . $newer['slug'])) ?>" class="event-nav__link event-nav__link--next"><small><?= e($newer['date_label']) ?> · Suivant →</small><span><?= e($newer['title']) ?></span></a>
      <?php endif; ?>
    </nav>
  </section>
</article>
