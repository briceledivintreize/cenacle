<?php
$meta['title'] = 'Histoire';
$meta['description'] = 'La chronologie du Cénacle depuis sa fondation à Lomé le 30 août 2008 : Journées mondiales de la poésie, célébrations Senghor, bibliothèques offertes et excursions annuelles.';
$items = get_history();
?>
<?= page_hero('Histoire', setting('history_title'), setting('history_subtitle'), 'jaune') ?>

<section class="section">
  <div class="timeline" data-timeline>
    <div class="timeline__rail" aria-hidden="true"><span data-timeline-fill></span></div>
    <?php $prevYear = null; foreach ($items as $i => $it):
        $year = event_year($it); ?>
      <?php if ($year !== $prevYear): $prevYear = $year; ?>
        <div class="timeline__year" data-reveal><span><?= e($year) ?></span></div>
      <?php endif; ?>
      <article class="tl-item tl-item--<?= $i % 2 ? 'right' : 'left' ?>" data-tl-item>
        <span class="tl-item__dot tl-item__dot--<?= e($it['type']) ?>" aria-hidden="true"></span>
        <div class="tl-item__card">
          <div class="tl-item__meta">
            <span class="tl-year"><?= e($it['date_label']) ?></span>
            <span class="pill pill--<?= e($it['type']) ?>"><?= e(event_type_label($it['type'])) ?></span>
          </div>
          <h3><?= e($it['title']) ?></h3>
          <p><?= e($it['summary']) ?></p>
          <?php if ($it['in_events']): ?>
            <a class="card-link" href="<?= e(url('evenements/' . $it['slug'])) ?>">Voir l'événement <span aria-hidden="true">→</span></a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <p class="note" data-reveal>Chronologie établie à partir des archives et blogs de l'association.</p>
</section>

<section class="section section--join"><?= join_block() ?></section>
