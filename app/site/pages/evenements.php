<?php
$meta['title'] = 'Événements';
$meta['description'] = 'Tous les événements du Cénacle : excursions commémoratives annuelles, célébrations Senghor, Journées de la poésie, bibliothèques offertes et ateliers.';
$events = get_events();
$byYear = [];
foreach ($events as $ev) {
    $byYear[event_year($ev)][] = $ev;
}
?>
<?= page_hero('Événements', setting('events_title'), setting('events_subtitle'), 'rouge') ?>

<section class="section">
  <?php if (!$events): ?>
    <div class="empty" data-reveal><p>Aucun événement publié pour le moment.</p></div>
  <?php endif; ?>
  <nav class="year-nav" aria-label="Aller à l'année" data-reveal>
    <?php foreach (array_keys($byYear) as $y): ?>
      <a href="#annee-<?= e((string) $y) ?>"><?= e((string) $y) ?></a>
    <?php endforeach; ?>
  </nav>
  <?php foreach ($byYear as $year => $list): ?>
    <div class="year-block" id="annee-<?= e((string) $year) ?>">
      <div class="year-block__head">
        <h2 class="year-block__year" data-year><?= e((string) $year) ?></h2>
        <span class="year-block__count"><?= count($list) ?> événement<?= count($list) > 1 ? 's' : '' ?></span>
      </div>
      <div class="cards-grid">
        <?php foreach ($list as $ev) echo event_card($ev); ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>
