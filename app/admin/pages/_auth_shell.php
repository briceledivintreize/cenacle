<?php
/** Gabarit commun des écrans de connexion / installation. @var string $inner @var string $title */
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · <?= e(setting('site_name')) ?></title>
<link rel="icon" href="<?= e(url('assets/img/icon-192.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="auth">
  <div class="auth__art" aria-hidden="true">
    <canvas id="auth-gl"></canvas>
    <div class="auth__quote">
      <p>« L'écriture commence là où le silence finit. »</p>
      <span><?= e(setting('site_name')) ?> · depuis 2008</span>
    </div>
  </div>
  <div class="auth__panel">
    <div class="auth__box">
      <img class="auth__logo" src="<?= e(media_url(setting('logo'))) ?>" alt="<?= e(setting('site_name')) ?>" width="96" height="86">
      <?= $inner ?>
      <a class="auth__back" href="<?= e(url()) ?>">← Retour au site</a>
    </div>
  </div>
<script src="<?= e(asset('assets/vendor/gsap.min.js')) ?>"></script>
<script src="<?= e(asset('assets/js/admin.js')) ?>"></script>
</body>
</html>
