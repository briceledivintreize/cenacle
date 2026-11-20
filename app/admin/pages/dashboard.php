<?php
$user = admin_user();
$counts = [
    ['events', 'Événements', 'calendar', (int) val('SELECT COUNT(*) FROM events WHERE published = 1')],
    ['publications', 'Publications', 'book', (int) val('SELECT COUNT(*) FROM publications WHERE published = 1')],
    ['figures', 'Figures', 'users', (int) val('SELECT COUNT(*) FROM figures WHERE published = 1')],
    ['media', 'Médias', 'image', (int) val('SELECT COUNT(*) FROM media')],
];
$unread = (int) val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$recent = all('SELECT * FROM messages ORDER BY created_at DESC LIMIT 5');
$latestEvents = all('SELECT e.*, (SELECT COUNT(*) FROM event_media m WHERE m.event_id = e.id) n FROM events e WHERE in_events = 1 ORDER BY event_date IS NULL, event_date DESC LIMIT 5');
$noMedia = (int) val('SELECT COUNT(*) FROM events e WHERE in_events = 1 AND NOT EXISTS (SELECT 1 FROM event_media m WHERE m.event_id = e.id)');
$noProgramImg = (int) val("SELECT COUNT(*) FROM programs WHERE image IS NULL OR image = ''");

$todo = [];
if (setting('facebook_url') === '') $todo[] = ['Ajouter le lien de la page Facebook', admin_url('settings', ['tab' => 'contact'])];
if ((int) val('SELECT COUNT(*) FROM publications') === 0) $todo[] = ['Ajouter les premiers ouvrages (la page affiche « Bientôt disponible »)', admin_url('publications', ['a' => 'new'])];
if ($noMedia) $todo[] = [$noMedia . ' événement' . ($noMedia > 1 ? 's' : '') . ' sans photo ni vidéo', admin_url('events')];
if ($noProgramImg) $todo[] = [$noProgramImg . ' programme' . ($noProgramImg > 1 ? 's' : '') . ' sans photo', admin_url('programs')];
$hour = (int) date('G');
?>
<?= page_header(($hour < 18 ? 'Bonjour' : 'Bonsoir') . ', ' . explode(' ', $user['name'])[0], 'Voici l\'état du site du Cénacle aujourd\'hui, ' . fr_date(date('Y-m-d')) . '.') ?>

<div class="kpis" data-stagger>
  <a class="kpi kpi--mail" href="<?= e(admin_url('messages', ['f' => 'unread'])) ?>">
    <span class="kpi__ico"><?= icon('mail', 22) ?></span>
    <span class="kpi__n" data-count="<?= $unread ?>"><?= $unread ?></span>
    <span class="kpi__l">Message<?= $unread > 1 ? 's' : '' ?> non lu<?= $unread > 1 ? 's' : '' ?></span>
  </a>
  <?php foreach ($counts as [$p, $label, $ico, $n]): ?>
    <a class="kpi" href="<?= e(admin_url($p)) ?>">
      <span class="kpi__ico"><?= icon($ico, 22) ?></span>
      <span class="kpi__n" data-count="<?= $n ?>"><?= $n ?></span>
      <span class="kpi__l"><?= e($label) ?></span>
    </a>
  <?php endforeach; ?>
</div>

<div class="dash">
  <section class="panel">
    <div class="panel__head"><h2>Actions rapides</h2></div>
    <div class="quick">
      <a href="<?= e(admin_url('events', ['a' => 'new'])) ?>" class="quick__item"><?= icon('calendar', 22) ?><span>Nouvel événement</span></a>
      <a href="<?= e(admin_url('publications', ['a' => 'new'])) ?>" class="quick__item"><?= icon('book', 22) ?><span>Ajouter un ouvrage</span></a>
      <a href="<?= e(admin_url('figures', ['a' => 'new'])) ?>" class="quick__item"><?= icon('users', 22) ?><span>Ajouter une figure</span></a>
      <a href="<?= e(admin_url('media')) ?>" class="quick__item"><?= icon('upload', 22) ?><span>Envoyer des médias</span></a>
      <?php if (is_admin()): ?>
      <a href="<?= e(admin_url('settings', ['tab' => 'hero'])) ?>" class="quick__item"><?= icon('edit', 22) ?><span>Modifier l'accueil</span></a>
      <a href="<?= e(admin_url('settings', ['tab' => 'contact'])) ?>" class="quick__item"><?= icon('settings', 22) ?><span>Coordonnées</span></a>
      <?php endif; ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel__head"><h2>À compléter</h2></div>
    <?php if (!$todo): ?>
      <p class="muted">Tout est à jour. 🎉</p>
    <?php else: ?>
      <ul class="todo">
        <?php foreach ($todo as [$label, $href]): ?>
          <li><a href="<?= e($href) ?>"><span class="todo__dot"></span><?= e($label) ?><span class="todo__go">→</span></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <section class="panel panel--wide">
    <div class="panel__head"><h2>Derniers messages</h2><a href="<?= e(admin_url('messages')) ?>" class="link">Tout voir →</a></div>
    <?php if (!$recent): ?>
      <p class="muted">Aucun message reçu pour l'instant. Les formulaires Contact et Nous rejoindre arrivent ici.</p>
    <?php else: ?>
      <ul class="msglist">
        <?php foreach ($recent as $m): ?>
          <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
            <a href="<?= e(admin_url('messages', ['a' => 'view', 'id' => $m['id']])) ?>">
              <span class="msglist__av"><?= e(initials($m['name'])) ?></span>
              <span class="msglist__main"><strong><?= e($m['name']) ?></strong><span><?= e($m['subject']) ?> — <?= e(excerpt($m['body'], 80)) ?></span></span>
              <span class="tag tag--<?= e($m['kind']) ?>"><?= $m['kind'] === 'adhesion' ? 'Adhésion' : 'Contact' ?></span>
              <time><?= e(time_ago($m['created_at'])) ?></time>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <section class="panel panel--wide">
    <div class="panel__head"><h2>Événements récents</h2><a href="<?= e(admin_url('events')) ?>" class="link">Gérer →</a></div>
    <table class="table">
      <thead><tr><th>Événement</th><th>Date</th><th>Médias</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($latestEvents as $ev): ?>
          <tr>
            <td><strong><?= e($ev['title']) ?></strong></td>
            <td><?= e($ev['date_label']) ?></td>
            <td><?= $ev['n'] ? '<span class="tag tag--ok">' . $ev['n'] . '</span>' : '<span class="tag">0</span>' ?></td>
            <td class="right"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('events', ['a' => 'media', 'id' => $ev['id']])) ?>"><?= icon('image', 15) ?> Galerie</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>
