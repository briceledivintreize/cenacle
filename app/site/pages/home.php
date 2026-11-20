<?php
/** Page d'accueil */
$heroStats = get_stats('hero');
$programs = get_programs();
$welcomeStats = get_stats('welcome');
$milestones = get_history(true);
$latest = get_events(3);
$figures = get_figures();
$legacy = get_stats('legacy');
$links = get_links();
$president = setting('president_name');
?>
<section class="hero" data-hero>
  <canvas class="hero__gl" id="hero-gl" aria-hidden="true"></canvas>
  <span class="dot dot--jaune" style="top:110px;left:5%" data-float></span>
  <span class="dot dot--rouge" style="top:230px;left:40%" data-float></span>
  <span class="dot dot--ring" style="top:70px;right:7%" data-float></span>
  <span class="dot dot--vert" style="bottom:90px;left:46%" data-float></span>

  <div class="hero__inner">
    <div class="hero__text">
      <span class="kicker" data-hero-in><?= e(setting('site_tagline')) ?></span>
      <h1 class="hero__title" data-hero-title>
        <?= e(setting('hero_title')) ?> <span class="accent"><?= e(setting('hero_title_accent')) ?></span>.
      </h1>
      <p class="hero__lede" data-hero-in><?= e(setting('hero_lede')) ?></p>
      <div class="hero__cta" data-hero-in>
        <a href="<?= e(url('rejoindre')) ?>" class="btn btn-green" data-magnetic><?= e(setting('hero_cta1')) ?></a>
        <a href="<?= e(url('histoire')) ?>" class="btn btn-outline" data-magnetic><?= e(setting('hero_cta2')) ?></a>
      </div>
      <?php if (count($heroStats) > 1): ?>
      <div class="hero__stats" data-hero-in>
        <?php foreach (array_slice($heroStats, 1) as $s): ?>
          <div class="hero__stat">
            <span class="hero__stat-n" data-counter="<?= e($s['value']) ?>"><?= e($s['value']) ?></span>
            <span class="hero__stat-l"><?= e($s['label']) ?><?php if ($s['detail']): ?><small><?= e($s['detail']) ?></small><?php endif; ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="hero__media" data-hero-media>
      <div class="hero__blob-bg" aria-hidden="true"></div>
      <div class="hero__frame" data-blob>
        <?= picture(setting('hero_image'), $president . ', ' . setting('president_role'), ['loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'sync']) ?>
      </div>
      <?php if ($heroStats): ?>
      <div class="hero__badge hero__badge--year" data-badge>
        <span class="num"><?= e($heroStats[0]['value']) ?></span>
        <span class="lbl"><?= e($heroStats[0]['label']) ?></span>
      </div>
      <?php endif; ?>
      <div class="hero__badge hero__badge--name" data-badge>
        <span class="lbl"><strong><?= e($president) ?></strong><br><?= e(setting('president_role')) ?></span>
      </div>
      <svg class="hero__feather" viewBox="0 0 120 220" aria-hidden="true" data-feather>
        <path d="M60 210 C 58 160, 40 120, 30 70 C 24 40, 44 12, 70 8 C 92 30, 96 70, 84 112 C 76 140, 66 170, 60 210 Z" fill="#000" opacity=".9"/>
        <path d="M60 210 C 62 150, 66 90, 70 10" stroke="#fff" stroke-width="2" fill="none" opacity=".6"/>
      </svg>
    </div>
  </div>

  <a href="#programmes" class="hero__scroll" aria-label="Défiler vers le contenu"><span></span></a>
</section>

<?= marquee(['Poésie', 'Roman', 'Conte', 'Mémoire', 'Transmission', 'Lomé', 'Afrique', 'Depuis 2008']) ?>

<section id="programmes" class="section">
  <?= section_head(setting('programs_title'), setting('programs_subtitle'), 'L\'association') ?>
  <div class="cards-grid" data-stagger>
    <?php foreach ($programs as $p) echo program_card($p); ?>
  </div>
  <div class="center-cta" data-reveal><a href="<?= e(url('association')) ?>" class="btn btn-outline" data-magnetic>Découvrir l'association</a></div>
</section>

<section class="welcome" data-welcome>
  <div class="welcome__inner">
    <div class="welcome__text">
      <span class="kicker" data-reveal>Le réseau</span>
      <h2 data-split><?= e(setting('welcome_title')) ?></h2>
      <p data-reveal><?= e(setting('welcome_text')) ?></p>
      <div class="stat-row">
        <?php foreach ($welcomeStats as $s) echo stat_counter($s); ?>
      </div>
    </div>
    <div class="welcome__visual">
      <canvas id="africa-gl" aria-hidden="true"></canvas>
      <img class="welcome__fallback" src="<?= e(media_url(setting('logo'))) ?>" alt="" width="360" height="322" loading="lazy">
      <span class="welcome__hint" aria-hidden="true">Survolez le continent</span>
    </div>
  </div>
</section>

<?php if ($milestones): ?>
<section class="hscroll" data-hscroll aria-labelledby="h-histoire">
  <div class="hscroll__head">
    <span class="kicker" data-reveal>Histoire</span>
    <h2 id="h-histoire" data-split><?= e(setting('history_title')) ?></h2>
    <p data-reveal><?= e(setting('history_subtitle')) ?></p>
  </div>
  <div class="hscroll__viewport">
    <ol class="hscroll__track" data-hscroll-track>
      <?php foreach ($milestones as $i => $m): ?>
        <li class="milestone milestone--<?= $i % 3 ?>">
          <span class="milestone__year"><?= e(event_year($m)) ?></span>
          <span class="milestone__date"><?= e($m['date_label']) ?></span>
          <h3><?= e($m['title']) ?></h3>
          <p><?= e(excerpt($m['summary'], 170)) ?></p>
        </li>
      <?php endforeach; ?>
      <li class="milestone milestone--end">
        <a href="<?= e(url('histoire')) ?>" class="milestone__more">Toute la chronologie <span aria-hidden="true">→</span></a>
      </li>
    </ol>
    <div class="hscroll__line" aria-hidden="true"><span data-hscroll-progress></span></div>
  </div>
</section>
<?php endif; ?>

<section class="section section--tint" id="evenements">
  <?= section_head(setting('events_title'), setting('events_subtitle'), 'À la une') ?>
  <div class="cards-grid">
    <?php foreach ($latest as $ev) echo event_card($ev); ?>
  </div>
  <div class="center-cta" data-reveal><a href="<?= e(url('evenements')) ?>" class="btn btn-dark" data-magnetic>Tous les événements</a></div>
</section>

<?php if ($figures): ?>
<section class="section" id="figures">
  <?= section_head(setting('figures_title'), setting('figures_subtitle'), 'Figures') ?>
  <div class="fig-grid">
    <?php foreach (array_slice($figures, 0, 6) as $f) echo figure_card($f); ?>
  </div>
  <?php if (count($figures) > 6): ?>
    <div class="center-cta" data-reveal><a href="<?= e(url('figures')) ?>" class="btn btn-outline">Toutes les figures</a></div>
  <?php endif; ?>
</section>
<?php endif; ?>

<section class="legacy" data-legacy>
  <canvas class="legacy__gl" data-ink="dark" aria-hidden="true"></canvas>
  <?= section_head(setting('legacy_title'), setting('legacy_subtitle'), 'Héritage', true) ?>
  <div class="legacy-grid">
    <?php foreach ($legacy as $s) echo stat_counter($s, 'legacy-card'); ?>
  </div>
</section>

<section class="quote" aria-label="Citation du Président">
  <span class="quote__mark" aria-hidden="true">«</span>
  <blockquote class="quote__inner">
    <p data-quote>« <?= e(setting('quote_text')) ?> »</p>
    <footer class="quote__attr" data-reveal><?= e(setting('quote_attr')) ?></footer>
  </blockquote>
</section>

<section class="section section--join" id="contact">
  <?= join_block() ?>
  <div class="contact-grid">
    <div class="contact-card" data-reveal>
      <h3>Contact</h3>
      <div class="contact-row"><span class="k">Association</span><span><?= e(setting('site_name')) ?></span></div>
      <div class="contact-row"><span class="k">Siège</span><span><?= e(setting('address_short')) ?></span></div>
      <div class="contact-row"><span class="k">Téléphone</span><a href="<?= e(tel_link(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></div>
      <div class="contact-row"><span class="k">Courriel</span><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div>
      <div class="contact-row"><span class="k">Président</span><span><?= e($president) ?></span></div>
    </div>
    <div class="contact-card" data-reveal>
      <h3>Nous suivre</h3>
      <?php if (setting('facebook_url')): ?>
        <div class="contact-row"><span class="k">Facebook</span><a href="<?= e(setting('facebook_url')) ?>" target="_blank" rel="noopener">Page officielle</a></div>
      <?php endif; ?>
      <?php foreach ($links as $l): ?>
        <div class="contact-row"><span class="k"><?= e(LINK_KINDS[$l['kind']] ?? 'Lien') ?></span><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['label']) ?></a></div>
      <?php endforeach; ?>
      <a href="<?= e(url('contact')) ?>" class="btn btn-outline btn-sm" style="margin-top:20px">Écrire au Cénacle</a>
    </div>
  </div>
</section>
