<?php
$meta['title'] = 'Contact';
$meta['description'] = 'Contacter le Cénacle : siège à Tokoin-Nukafu, Lomé. Téléphone, e-mail, WhatsApp, blogs et formulaire de contact.';
$links = get_links();
$fb = setting('facebook_url');
?>
<?= page_hero('Contact', 'Écrire au Cénacle', 'Une question, une proposition de partenariat, un manuscrit à présenter ? Nous vous lisons avec attention.', 'jaune') ?>

<section class="section" id="formulaire">
  <div class="form-layout">
    <div class="form-layout__aside">
      <div class="contact-card" data-reveal>
        <h3>Coordonnées</h3>
        <div class="contact-row"><span class="k">Siège</span><span><?= nl2br(e(setting('address'))) ?></span></div>
        <div class="contact-row"><span class="k">Téléphone</span><a href="<?= e(tel_link(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></div>
        <div class="contact-row"><span class="k">Courriel</span><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div>
        <div class="contact-row"><span class="k">WhatsApp</span><a href="<?= e(wa_link(setting('whatsapp'))) ?>" target="_blank" rel="noopener"><?= e(setting('whatsapp')) ?></a></div>
        <div class="contact-row"><span class="k">Président</span><span><?= e(setting('president_name')) ?></span></div>
      </div>
      <div class="contact-card" data-reveal>
        <h3>Nous suivre</h3>
        <?php if ($fb): ?>
          <div class="contact-row"><span class="k">Facebook</span><a href="<?= e($fb) ?>" target="_blank" rel="noopener">Page officielle</a></div>
        <?php endif; ?>
        <?php foreach ($links as $l): ?>
          <div class="contact-row"><span class="k"><?= e(LINK_KINDS[$l['kind']] ?? 'Lien') ?></span><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['label']) ?></a></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="form-layout__form" data-reveal>
      <h2 class="form-title">Formulaire de contact</h2>
      <?= contact_form('contact', 'contact') ?>
    </div>
  </div>
</section>
