<?php
$meta['title'] = 'Figures';
$meta['description'] = 'Les figures qui ont porté le Cénacle depuis 2008 : fondateur, membres du bureau, parrains et personnalités liées à l\'association.';
$figures = get_figures();
?>
<?= page_hero('Figures', setting('figures_title'), setting('figures_subtitle'), 'rouge') ?>

<section class="section">
  <div class="fig-grid">
    <?php foreach ($figures as $f) echo figure_card($f); ?>
  </div>
</section>

<section class="quote quote--compact" aria-label="Citation du Président">
  <blockquote class="quote__inner">
    <p data-quote>« <?= e(setting('quote_text')) ?> »</p>
    <footer class="quote__attr" data-reveal><?= e(setting('quote_attr')) ?></footer>
  </blockquote>
</section>
