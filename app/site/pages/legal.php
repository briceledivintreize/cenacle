<?php
$meta['title'] = 'Mentions légales';
$meta['description'] = 'Mentions légales du site du Cénacle : éditeur, hébergeur et données personnelles.';
?>
<?= page_hero('Informations', 'Mentions légales', '', 'vert') ?>
<section class="section">
  <div class="prose prose--narrow prose--legal" data-reveal><?= markup(setting('legal_content')) ?></div>
</section>
