<?php
$meta['title'] = 'Nous rejoindre';
$meta['description'] = setting('join_page_intro');
$cats = get_member_categories();
$wa = setting('whatsapp');
?>
<?= page_hero('Nous rejoindre', 'Entrer dans le cercle', setting('join_page_intro'), 'vert') ?>

<section class="section">
  <?= section_head('Les catégories de membres', 'Six manières de faire vivre le Cénacle', 'Membres') ?>
  <div class="cat-grid">
    <?php foreach ($cats as $i => $c): ?>
      <article class="cat-card" data-reveal data-tilt>
        <span class="cat-card__n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <h3><?= e($c['title']) ?></h3>
        <p><?= e($c['description']) ?></p>
        <?php if ($c['note']): ?><p class="cat-card__note"><?= e($c['note']) ?></p><?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="section section--tint" id="formulaire">
  <div class="form-layout">
    <div class="form-layout__aside" data-reveal>
      <span class="kicker">Adhésion</span>
      <h2 data-split>Demande d'adhésion</h2>
      <p><?= e(setting('join_form_intro')) ?></p>
      <div class="prose prose--small"><?= markup(setting('join_practical')) ?></div>
      <div class="btn-col">
        <a href="<?= e(wa_link($wa, setting('wa_member_msg'))) ?>" class="btn btn-green" target="_blank" rel="noopener" data-magnetic>Devenir membre via WhatsApp</a>
        <a href="<?= e(wa_link($wa, setting('wa_support_msg'))) ?>" class="btn btn-outline" target="_blank" rel="noopener">Soutenir un projet</a>
      </div>
    </div>
    <div class="form-layout__form" data-reveal>
      <?= contact_form('adhesion', 'rejoindre') ?>
    </div>
  </div>
</section>
