<?php
$meta['title'] = 'Nos publications';
$meta['description'] = setting('publications_intro');
$books = get_publications();
$wa = setting('editions_whatsapp');
$tpl = setting('book_message');
?>
<?= page_hero('Nos publications', 'Les livres du réseau Cénacle', setting('publications_intro'), 'vert') ?>

<section class="section">
  <?php if (!$books): ?>
    <div class="soon" data-reveal>
      <div class="soon__book" aria-hidden="true" data-book>
        <div class="book3d">
          <div class="book3d__cover"><img src="<?= e(media_url(setting('logo'))) ?>" alt="" width="120" height="107"><span><?= e(setting('site_name')) ?></span></div>
          <div class="book3d__pages"></div>
          <div class="book3d__back"></div>
        </div>
      </div>
      <h2 data-split><?= e(setting('publications_empty')) ?></h2>
      <p>Le catalogue des ouvrages des Éditions Continents et des œuvres portées par le Cénacle sera publié ici très prochainement.</p>
      <a class="btn btn-green" href="<?= e(wa_link($wa, 'Bonjour, je souhaite être informé(e) des publications du Cénacle.')) ?>" target="_blank" rel="noopener" data-magnetic>Être prévenu sur WhatsApp</a>
    </div>
  <?php else: ?>
    <div class="books">
      <?php foreach ($books as $b): ?>
        <article class="book" data-reveal>
          <div class="book__cover" data-book-tilt>
            <?php if ($b['cover']): ?>
              <?= picture($b['cover'], 'Couverture : ' . $b['title']) ?>
            <?php else: ?>
              <div class="book__blank"><span><?= e($b['title']) ?></span><small><?= e($b['authors']) ?></small></div>
            <?php endif; ?>
            <span class="book__spine" aria-hidden="true"></span>
          </div>
          <div class="book__body">
            <?php if ($b['collection']): ?><span class="card-tag"><?= e($b['collection']) ?></span><?php endif; ?>
            <h3><?= e($b['title']) ?></h3>
            <p class="book__authors"><?= e($b['authors']) ?></p>
            <p class="book__meta">
              <?= e($b['publisher'] ?: '') ?><?= $b['publisher'] && $b['release_date'] ? ' · ' : '' ?><?= e($b['release_date'] ? fr_date($b['release_date'], false) : '') ?>
            </p>
            <?php if ($b['summary']): ?><p class="book__summary"><?= e($b['summary']) ?></p><?php endif; ?>
            <a class="btn btn-green btn-sm" href="<?= e(wa_link($wa, str_replace('{titre}', $b['title'], $tpl))) ?>" target="_blank" rel="noopener" data-magnetic>
              Je veux ce livre
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
