<?php
$meta['title'] = 'Histoire';
$meta['description'] = 'Le chemin parcouru par le Cénacle depuis sa fondation à Lomé le 30 août 2008 : Journées mondiales de la poésie, célébrations Senghor, bibliothèques offertes et excursions annuelles.';
$items = get_history();

// Chiffres de la vue d'ensemble
$years = array_filter(array_map(fn($i) => $i['event_date'] ? (int) substr($i['event_date'], 0, 4) : null, $items));
$firstYear = $years ? min($years) : 2008;
$span = (int) date('Y') - $firstYear;
$places = count(array_unique(array_filter(array_map(fn($i) => trim(explode(',', (string) $i['location'])[0]), $items))));
$first = $items[0] ?? null;
$last = $items ? $items[count($items) - 1] : null;
?>
<?= page_hero('Histoire', 'Le chemin parcouru', 'De Lomé en 2008 aux routes de Ouïdah : suivez pas à pas l\'itinéraire du Cénacle, étape après étape.', 'jaune') ?>

<section class="journey-section" aria-labelledby="h-journey">
  <h2 id="h-journey" class="sr-only">Chronologie détaillée</h2>
  <div class="journey" data-journey>
    <svg class="journey__svg" aria-hidden="true" focusable="false">
      <defs>
        <linearGradient id="journey-grad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stop-color="#50A028"/>
          <stop offset=".55" stop-color="#E6D800"/>
          <stop offset="1" stop-color="#C81414"/>
        </linearGradient>
      </defs>
      <path class="journey__ghost" d=""/>
      <path class="journey__trail" d="" stroke="url(#journey-grad)"/>
    </svg>
    <div class="journey__traveler" aria-hidden="true">
      <span class="journey__halo"></span>
      <img src="<?= e(media_url(setting('logo'))) ?>" alt="" width="40" height="36">
    </div>

    <div class="journey__start" data-reveal>
      <span class="journey__flag">Départ</span>
      <strong><?= e($first['location'] ?? 'Lomé') ?></strong>
      <span><?= e($first['date_label'] ?? '') ?></span>
    </div>

    <?php $prevYear = null; foreach ($items as $i => $it):
        $year = event_year($it);
        $newYear = $year !== $prevYear;
        $prevYear = $year;
        $side = $i % 2 ? 'right' : 'left'; ?>
      <article class="stop stop--<?= $side ?> stop--<?= e($it['type']) ?>" data-stop data-index="<?= $i ?>">
        <span class="stop__pin" data-pin aria-hidden="true"><b><?= $i + 1 ?></b></span>
        <?php if ($newYear): ?><span class="stop__year" aria-hidden="true"><?= e($year) ?></span><?php endif; ?>
        <div class="stop__card">
          <div class="stop__meta">
            <span class="stop__step">Étape <?= $i + 1 ?></span>
            <span class="pill pill--<?= e($it['type']) ?>"><?= e(event_type_label($it['type'])) ?></span>
          </div>
          <p class="stop__date"><?= e($it['date_label']) ?><?php if ($it['location']): ?> · <span><?= e($it['location']) ?></span><?php endif; ?></p>
          <h3><?= e($it['title']) ?></h3>
          <p class="stop__text"><?= e($it['summary']) ?></p>
          <?php if ($it['in_events']): ?>
            <a class="card-link" href="<?= e(url('evenements/' . $it['slug'])) ?>">Voir l'événement <span aria-hidden="true">→</span></a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>

    <div class="journey__end">
      <span class="journey__flag journey__flag--end">Le chemin continue</span>
      <strong><?= e($last['title'] ?? '') ?></strong>
    </div>
  </div>
</section>

<section class="overview" id="vue-ensemble" data-overview aria-labelledby="h-overview">
  <div class="overview__head">
    <span class="kicker" data-reveal>Vue d'ensemble</span>
    <h2 id="h-overview" data-split>Tout le parcours, d'un seul regard</h2>
    <p data-reveal>Survolez ou touchez une étape pour la revoir.</p>
  </div>

  <div class="overview__stats">
    <div class="ov-stat" data-reveal><span class="ov-stat__n" data-counter="<?= $span ?>"><?= $span ?></span><span class="ov-stat__l">années de route</span></div>
    <div class="ov-stat" data-reveal><span class="ov-stat__n" data-counter="<?= count($items) ?>"><?= count($items) ?></span><span class="ov-stat__l">étapes franchies</span></div>
    <div class="ov-stat" data-reveal><span class="ov-stat__n" data-counter="<?= $places ?>"><?= $places ?></span><span class="ov-stat__l">lieux traversés</span></div>
  </div>

  <div class="overview__stage">
    <div class="overview__map" data-map>
      <svg class="overview__svg" aria-hidden="true" focusable="false" preserveAspectRatio="none">
        <defs>
          <linearGradient id="overview-grad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#50A028"/>
            <stop offset=".55" stop-color="#E6D800"/>
            <stop offset="1" stop-color="#FF4A3D"/>
          </linearGradient>
        </defs>
        <path class="overview__ghost" d=""/>
        <path class="overview__trail" d="" stroke="url(#overview-grad)"/>
      </svg>
      <div class="overview__traveler" aria-hidden="true"><img src="<?= e(media_url(setting('logo'))) ?>" alt="" width="30" height="27"></div>
    </div>
    <div class="overview__detail" aria-live="polite">
      <span class="overview__detail-step"></span>
      <p class="overview__detail-date"></p>
      <h3 class="overview__detail-title"></h3>
      <p class="overview__detail-text"></p>
      <a class="card-link overview__detail-link" href="#" hidden>Voir l'événement <span aria-hidden="true">→</span></a>
    </div>
  </div>

  <ol class="overview__list" data-overview-list>
    <?php foreach ($items as $i => $it): ?>
      <li data-type="<?= e($it['type']) ?>" data-year="<?= e(event_year($it)) ?>" data-date="<?= e($it['date_label']) ?>" data-place="<?= e($it['location']) ?>" data-title="<?= e($it['title']) ?>" data-text="<?= e(excerpt($it['summary'], 190)) ?>" data-url="<?= $it['in_events'] ? e(url('evenements/' . $it['slug'])) : '' ?>">
        <span class="overview__list-year"><?= e($it['date_label']) ?></span> <?= e($it['title']) ?>
      </li>
    <?php endforeach; ?>
  </ol>

  <div class="overview__actions">
    <button type="button" class="btn btn-dark" data-replay data-magnetic>Rejouer le parcours</button>
    <a href="#contenu" class="btn btn-outline" data-back-top>Revenir au départ</a>
  </div>
</section>

<section class="section section--join"><?= join_block() ?></section>
