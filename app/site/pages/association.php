<?php
$meta['title'] = 'L\'association';
$meta['description'] = excerpt(setting('about_intro'), 160);
$programs = get_programs();
$cats = get_member_categories();
?>
<?= page_hero('L\'association', setting('site_tagline'), excerpt(setting('about_intro'), 200)) ?>

<section class="section">
  <div class="about">
    <aside class="about__aside" data-reveal>
      <div class="about__logo" data-tilt><?= picture(setting('logo'), 'Logo officiel du Cénacle') ?></div>
      <dl class="about__facts">
        <div><dt>Création</dt><dd>30 août 2008, Lomé</dd></div>
        <div><dt>Nom actuel</dt><dd>Depuis septembre 2014</dd></div>
        <div><dt>Président</dt><dd><?= e(setting('president_name')) ?></dd></div>
        <div><dt>Siège</dt><dd><?= e(setting('address_short')) ?></dd></div>
      </dl>
    </aside>
    <div class="about__body">
      <article class="prose" data-reveal>
        <h2 data-split>Présentation</h2>
        <?= markup(setting('about_intro')) ?>
      </article>
      <div class="about__cols">
        <article class="prose prose--card" data-reveal>
          <span class="prose__num">01</span>
          <h2>Missions</h2>
          <?= markup(setting('about_missions')) ?>
        </article>
        <article class="prose prose--card" data-reveal>
          <span class="prose__num">02</span>
          <h2>Valeurs</h2>
          <?= markup(setting('about_values')) ?>
        </article>
      </div>
      <article class="prose" data-reveal>
        <h2 data-split>Organisation</h2>
        <?= markup(setting('about_organisation')) ?>
      </article>
    </div>
  </div>
</section>

<section class="section section--tint">
  <?= section_head(setting('programs_title'), setting('programs_subtitle'), 'Programmes') ?>
  <div class="cards-grid">
    <?php foreach ($programs as $p) echo program_card($p); ?>
  </div>
</section>

<?php if ($cats): ?>
<section class="section">
  <?= section_head('Six façons d\'appartenir au Cénacle', 'Les catégories de membres de l\'association', 'Membres') ?>
  <div class="cat-grid cat-grid--compact">
    <?php foreach ($cats as $i => $c): ?>
      <div class="cat-card" data-reveal><span class="cat-card__n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e($c['title']) ?></h3></div>
    <?php endforeach; ?>
  </div>
  <div class="center-cta" data-reveal><a href="<?= e(url('rejoindre')) ?>" class="btn btn-green" data-magnetic>Nous rejoindre</a></div>
</section>
<?php endif; ?>
