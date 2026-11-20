<?php
/** @var array $meta @var string $body */
$siteName = setting('site_name');
$fullTitle = $meta['title'] ? $meta['title'] . ' — ' . $siteName : $siteName . ' — ' . setting('site_tagline');
$nav = [
    'association'  => ['association', 'L\'association'],
    'histoire'     => ['histoire', 'Histoire'],
    'evenements'   => ['evenements', 'Événements'],
    'publications' => ['publications', 'Nos publications'],
    'figures'      => ['figures', 'Figures'],
    'contact'      => ['contact', 'Contact'],
];
$links = get_links();
$fb = setting('facebook_url');
$sameAs = array_values(array_filter(array_merge(array_column($links, 'url'), [$fb])));
$jsonLd = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Organization',
    'name'          => $siteName,
    'alternateName' => setting('site_tagline'),
    'url'           => abs_url(),
    'logo'          => abs_url(setting('logo')),
    'foundingDate'  => '2008-08-30',
    'email'         => setting('email'),
    'telephone'     => trim(explode('/', setting('phone'))[0]),
    'address'       => ['@type' => 'PostalAddress', 'streetAddress' => setting('address_short'), 'addressLocality' => 'Lomé', 'addressCountry' => 'TG'],
    'founder'       => ['@type' => 'Person', 'name' => setting('president_name')],
    'sameAs'        => $sameAs,
];
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e(excerpt($meta['description'], 165)) ?>">
<link rel="canonical" href="<?= e($meta['canonical']) ?>">
<?php if (!empty($meta['noindex'])): ?><meta name="robots" content="noindex"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e(excerpt($meta['description'], 200)) ?>">
<meta property="og:url" content="<?= e($meta['canonical']) ?>">
<meta property="og:image" content="<?= e(abs_url($meta['image'] ?: setting('og_image'))) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#50A028">
<link rel="icon" href="<?= e(url('assets/img/icon-192.png')) ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= e(url('assets/img/icon-192.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
<script>
  // Active les animations seulement si JS est disponible et que l'utilisateur ne demande pas de réduire les animations.
  (function (d) {
    var h = d.documentElement; h.classList.add('js');
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      h.classList.add('anim');
      try { if (sessionStorage.getItem('cenacle-nav')) { h.classList.add('is-entering'); sessionStorage.removeItem('cenacle-nav'); } } catch (e) {}
      setTimeout(function () { if (!window.__cenacleReady) h.classList.remove('anim'); }, 3000);
    }
  })(document);
</script>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body data-page="<?= e($meta['nav'] ?: 'page') ?>">
<a class="skip-link" href="#contenu">Aller au contenu</a>

<div class="transition" aria-hidden="true">
  <span class="transition__panel transition__panel--vert"></span>
  <span class="transition__panel transition__panel--jaune"></span>
  <span class="transition__panel transition__panel--rouge"></span>
  <span class="transition__panel transition__panel--ink">
    <img src="<?= e(media_url(setting('logo'))) ?>" alt="" width="84" height="75">
  </span>
</div>

<div class="scroll-progress" aria-hidden="true"></div>

<header class="site-header">
  <div class="nav">
    <a class="brand" href="<?= e(url()) ?>" aria-label="<?= e($siteName) ?> — accueil">
      <img src="<?= e(media_url(setting('logo'))) ?>" alt="" width="42" height="38">
      <span class="brand__name"><?= e($siteName) ?></span>
    </a>
    <nav class="nav__links" aria-label="Navigation principale">
      <?php foreach ($nav as $key => [$href, $label]): ?>
        <a href="<?= e(url($href)) ?>"<?= $meta['nav'] === $key ? ' aria-current="page"' : '' ?>><span><?= e($label) ?></span></a>
      <?php endforeach; ?>
    </nav>
    <a href="<?= e(url('rejoindre')) ?>" class="btn btn-dark nav__cta" data-magnetic><?= e(setting('nav_cta_label')) ?></a>
    <button class="burger" type="button" aria-expanded="false" aria-controls="menu-mobile" aria-label="Ouvrir le menu">
      <span></span><span></span>
    </button>
  </div>
</header>

<div class="mobile-menu" id="menu-mobile" hidden>
  <nav aria-label="Navigation mobile">
    <a href="<?= e(url()) ?>"<?= $meta['nav'] === 'accueil' ? ' aria-current="page"' : '' ?>>Accueil</a>
    <?php foreach ($nav as $key => [$href, $label]): ?>
      <a href="<?= e(url($href)) ?>"<?= $meta['nav'] === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
    <a href="<?= e(url('rejoindre')) ?>"<?= $meta['nav'] === 'rejoindre' ? ' aria-current="page"' : '' ?>><?= e(setting('nav_cta_label')) ?></a>
  </nav>
  <div class="mobile-menu__foot">
    <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a>
    <a href="<?= e(tel_link(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>
  </div>
</div>

<main id="contenu">
<?= $body ?>
</main>

<footer class="site-footer">
  <div class="site-footer__top">
    <div class="site-footer__brand">
      <img src="<?= e(media_url(setting('logo'))) ?>" alt="Logo <?= e($siteName) ?>" width="72" height="64" loading="lazy">
      <p class="site-footer__name"><?= e($siteName) ?></p>
      <p><?= e(setting('site_tagline')) ?></p>
    </div>
    <div>
      <h2 class="site-footer__h">Le site</h2>
      <ul>
        <?php foreach ($nav as [$href, $label]): ?>
          <li><a href="<?= e(url($href)) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= e(url('rejoindre')) ?>">Nous rejoindre</a></li>
      </ul>
    </div>
    <div>
      <h2 class="site-footer__h">Contact</h2>
      <ul>
        <li><?= e(setting('address_short')) ?></li>
        <li><a href="<?= e(tel_link(setting('phone'))) ?>"><?= e(setting('phone')) ?></a></li>
        <li><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
      </ul>
    </div>
    <div>
      <h2 class="site-footer__h">Nous suivre</h2>
      <ul>
        <?php if ($fb): ?><li><a href="<?= e($fb) ?>" target="_blank" rel="noopener">Facebook</a></li><?php endif; ?>
        <?php foreach ($links as $l): ?>
          <li><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="site-footer__mid">
    <p><?= e(setting('footer_left')) ?></p>
    <p><?= e(setting('footer_right')) ?></p>
  </div>
  <div class="site-footer__bottom">
    <p>Développé par <a href="<?= e(setting('credit_url')) ?>" target="_blank" rel="noopener"><?= e(setting('credit_label')) ?></a> © <?= date('Y') ?></p>
    <p><a href="<?= e(url('mentions-legales')) ?>">Mentions légales</a></p>
  </div>
</footer>

<div class="lightbox" hidden aria-modal="true" role="dialog" aria-label="Visionneuse">
  <button class="lightbox__close" type="button" aria-label="Fermer">×</button>
  <button class="lightbox__nav lightbox__nav--prev" type="button" aria-label="Précédent">‹</button>
  <div class="lightbox__stage"></div>
  <p class="lightbox__caption"></p>
  <button class="lightbox__nav lightbox__nav--next" type="button" aria-label="Suivant">›</button>
</div>

<script src="<?= e(asset('assets/vendor/gsap.min.js')) ?>" defer></script>
<script src="<?= e(asset('assets/vendor/ScrollTrigger.min.js')) ?>" defer></script>
<script src="<?= e(asset('assets/vendor/SplitText.min.js')) ?>" defer></script>
<script src="<?= e(asset('assets/vendor/lenis.min.js')) ?>" defer></script>
<script type="importmap">{"imports":{"three":"<?= e(asset('assets/vendor/three.module.min.js')) ?>"}}</script>
<script type="module" src="<?= e(asset('assets/js/site.js')) ?>"></script>
</body>
</html>
