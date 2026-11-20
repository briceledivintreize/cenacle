<?php
/** @var array $msg */
$reply = 'mailto:' . $msg['email'] . '?subject=' . rawurlencode('Re: ' . $msg['subject']);
?>
<?= page_header($msg['subject'] ?: 'Message', '', '<a class="btn btn-ghost" href="' . e(admin_url('messages')) . '">← Messages</a>') ?>
<div class="editor">
  <article class="panel editor__main message">
    <header class="message__head">
      <span class="msglist__av msglist__av--lg"><?= e(initials($msg['name'])) ?></span>
      <div>
        <strong><?= e($msg['name']) ?></strong>
        <span class="muted"><?= e($msg['email']) ?><?= $msg['phone'] ? ' · ' . e($msg['phone']) : '' ?></span>
      </div>
      <span class="tag tag--<?= e($msg['kind']) ?>"><?= $msg['kind'] === 'adhesion' ? 'Demande d\'adhésion' : 'Contact' ?></span>
    </header>
    <div class="message__body"><?= nl2br(e($msg['body'])) ?></div>
    <p class="muted small">Reçu le <?= e(fr_date($msg['created_at'])) ?> à <?= e(substr($msg['created_at'], 11, 5)) ?> (GMT)</p>
  </article>
  <aside class="editor__side">
    <div class="panel">
      <h2 class="panel__title">Répondre</h2>
      <div class="btn-stack">
        <a class="btn btn-primary btn-block" href="<?= e($reply) ?>"><?= icon('mail', 16) ?> Par e-mail</a>
        <?php if ($msg['phone']): ?>
          <a class="btn btn-ghost btn-block" href="<?= e(wa_link($msg['phone'], 'Bonjour ' . $msg['name'] . ', ')) ?>" target="_blank" rel="noopener">WhatsApp</a>
        <?php endif; ?>
        <?= post_button(admin_url('messages', ['a' => 'toggle', 'id' => $msg['id']]), 'Marquer comme non lu', 'btn btn-ghost btn-block') ?>
        <?= post_button(admin_url('messages', ['a' => 'delete', 'id' => $msg['id']]), icon('trash', 15) . ' Supprimer', 'btn btn-danger btn-block', 'Supprimer ce message ?') ?>
      </div>
    </div>
  </aside>
</div>
