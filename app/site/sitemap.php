<?php
$pages = ['', 'association', 'histoire', 'evenements', 'publications', 'figures', 'rejoindre', 'contact', 'mentions-legales'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $p): ?>
  <url><loc><?= e(abs_url($p)) ?></loc><changefreq>monthly</changefreq><priority><?= $p === '' ? '1.0' : '0.7' ?></priority></url>
<?php endforeach; ?>
<?php foreach (get_events() as $ev): ?>
  <url><loc><?= e(abs_url('evenements/' . $ev['slug'])) ?></loc><lastmod><?= e(substr($ev['updated_at'] ?? '', 0, 10)) ?></lastmod></url>
<?php endforeach; ?>
</urlset>
