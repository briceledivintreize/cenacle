<?php
/**
 * Composants d'interface du panel.
 */

function icon(string $name, int $size = 18): string
{
    $paths = [
        'home'     => '<path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="17" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>',
        'layers'   => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
        'hash'     => '<path d="M5 9h14M5 15h14M10 3 8 21M16 3l-2 18"/>',
        'book'     => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 21.5A2.5 2.5 0 0 1 6.5 19H20v3H6.5"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2a5 5 0 0 1 5.5 5.8"/>',
        'grid'     => '<rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/>',
        'link'     => '<path d="M10 14a4.5 4.5 0 0 0 6.4 0l3.2-3.2a4.5 4.5 0 0 0-6.4-6.4L12 5.6"/><path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3.2 3.2a4.5 4.5 0 0 0 6.4 6.4l1.2-1.2"/>',
        'mail'     => '<rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="m3 6 9 7 9-7"/>',
        'image'    => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.8"/><path d="m21 15-5-5L5 21"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'trash'    => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
        'eye'      => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'  => '<path d="M17.9 17.9A10.5 10.5 0 0 1 12 20C5 20 1 12 1 12a18.5 18.5 0 0 1 5.1-5.9M9.9 4.2A9.6 9.6 0 0 1 12 4c7 0 11 8 11 8a18.6 18.6 0 0 1-2.2 3.2M1 1l22 22"/>',
        'drag'     => '<circle cx="9" cy="6" r="1.4"/><circle cx="15" cy="6" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="9" cy="18" r="1.4"/><circle cx="15" cy="18" r="1.4"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'copy'     => '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
        'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
        'video'    => '<rect x="2" y="5" width="14" height="14" rx="2"/><path d="m22 8-6 4 6 4z"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'menu'     => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        'star'     => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'globe'    => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
    ];
    return '<svg class="ico" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

function admin_nav(): array
{
    $unread = (int) val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
    $nav = [
        ['', [['dashboard', 'Tableau de bord', 'home', 0]]],
        ['Contenus', [
            ['events', 'Événements', 'calendar', 0],
            ['programs', 'Programmes', 'layers', 0],
            ['stats', 'Chiffres clés', 'hash', 0],
            ['publications', 'Publications', 'book', 0],
            ['figures', 'Figures', 'users', 0],
            ['categories', 'Catégories de membres', 'grid', 0],
            ['links', 'Blogs & réseaux', 'link', 0],
        ]],
        ['Suivi', [
            ['messages', 'Messages', 'mail', $unread],
            ['media', 'Médiathèque', 'image', 0],
        ]],
    ];
    if (is_admin()) {
        $nav[] = ['Administration', [
            ['settings', 'Réglages du site', 'settings', 0],
            ['users', 'Utilisateurs', 'shield', 0],
        ]];
    }
    return $nav;
}

function admin_layout(string $view, array $data): string
{
    $current = $_GET['p'] ?? 'dashboard';
    $user = admin_user();
    $content = render(APP_DIR . '/admin/pages/' . $view . '.php', $data);
    $title = $GLOBALS['admin_title'] ?? 'Administration';
    ob_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($title) ?> · Admin <?= e(setting('site_name')) ?></title>
<link rel="icon" href="<?= e(url('assets/img/icon-192.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="adm">
<aside class="adm-side" id="adm-side">
  <a class="adm-brand" href="<?= e(admin_url()) ?>">
    <img src="<?= e(media_url(setting('logo'))) ?>" alt="" width="40" height="36">
    <span><strong><?= e(setting('site_name')) ?></strong><small>Administration</small></span>
  </a>
  <nav class="adm-nav" aria-label="Menu d'administration">
    <?php foreach (admin_nav() as [$group, $items]): ?>
      <?php if ($group): ?><p class="adm-nav__group"><?= e($group) ?></p><?php endif; ?>
      <?php foreach ($items as [$key, $label, $ico, $badge]): ?>
        <a href="<?= e(admin_url($key === 'dashboard' ? '' : $key)) ?>" class="adm-nav__link"<?= $current === $key ? ' aria-current="page"' : '' ?>>
          <?= icon($ico) ?><span><?= e($label) ?></span>
          <?php if ($badge): ?><b class="badge"><?= $badge ?></b><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <a class="adm-side__site" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('globe') ?> Voir le site public</a>
</aside>
<div class="adm-main">
  <header class="adm-top">
    <button class="adm-burger" type="button" aria-controls="adm-side" aria-expanded="false" aria-label="Menu"><?= icon('menu', 22) ?></button>
    <div class="adm-top__spacer"></div>
    <a class="adm-top__link" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external', 16) ?> <span>Site public</span></a>
    <a class="adm-user" href="<?= e(admin_url('account')) ?>">
      <span class="adm-user__av"><?= e(initials($user['name'])) ?></span>
      <span class="adm-user__txt"><strong><?= e($user['name']) ?></strong><small><?= $user['role'] === 'admin' ? 'Administrateur' : 'Éditeur' ?></small></span>
    </a>
    <form method="post" action="<?= e(admin_url('logout')) ?>"><?= csrf_field() ?><button class="adm-iconbtn" title="Se déconnecter" aria-label="Se déconnecter"><?= icon('logout') ?></button></form>
  </header>
  <div class="adm-toasts" aria-live="polite">
    <?php foreach (take_flashes() as $f): ?>
      <div class="toast toast--<?= e($f['type']) ?>" role="status"><?= icon($f['type'] === 'success' ? 'check' : 'shield', 16) ?><span><?= e($f['message']) ?></span><button type="button" aria-label="Fermer">×</button></div>
    <?php endforeach; ?>
  </div>
  <main class="adm-content" id="adm-content">
    <?= $content ?>
  </main>
</div>
<div class="adm-backdrop" hidden></div>
<script src="<?= e(asset('assets/vendor/gsap.min.js')) ?>"></script>
<script src="<?= e(asset('assets/vendor/Sortable.min.js')) ?>"></script>
<script src="<?= e(asset('assets/js/admin.js')) ?>"></script>
</body>
</html>
<?php
    return (string) ob_get_clean();
}

function page_header(string $title, string $intro = '', string $actions = ''): string
{
    $GLOBALS['admin_title'] = $title;
    return '<div class="adm-head"><div><h1>' . e($title) . '</h1>' . ($intro ? '<p>' . e($intro) . '</p>' : '') . '</div>'
        . ($actions ? '<div class="adm-head__actions">' . $actions . '</div>' : '') . '</div>';
}

function post_button(string $action, string $label, string $class = 'btn btn-ghost', string $confirm = '', array $hidden = []): string
{
    $h = '';
    foreach ($hidden as $k => $v) {
        $h .= '<input type="hidden" name="' . e($k) . '" value="' . e((string) $v) . '">';
    }
    return '<form method="post" action="' . e($action) . '" class="inline"' . ($confirm ? ' data-confirm="' . e($confirm) . '"' : '') . '>'
        . csrf_field() . $h . '<button type="submit" class="' . e($class) . '">' . $label . '</button></form>';
}

/** Rendu d'un champ de formulaire selon sa définition. */
function form_field(string $name, array $def, $value, ?string $error = null): string
{
    [$label, $type] = $def;
    $opt = $def[2] ?? [];
    if ($value === null && isset($opt['default'])) {
        $value = $opt['default'];
    }
    $id = 'fld-' . $name;
    $req = !empty($opt['required']);
    $help = !empty($opt['help']) ? '<small class="help">' . e($opt['help']) . '</small>' : '';
    $err = $error ? '<small class="err">' . e($error) . '</small>' : '';
    $cls = 'fld' . (!empty($opt['col']) ? ' fld--' . $opt['col'] : '') . ($error ? ' fld--error' : '') . ' fld--' . $type;
    $lab = '<label for="' . $id . '">' . e($label) . ($req ? ' <span class="req">*</span>' : '') . '</label>';
    $ph = !empty($opt['placeholder']) ? ' placeholder="' . e($opt['placeholder']) . '"' : '';
    $v = e((string) $value);

    switch ($type) {
        case 'checkbox':
            return '<div class="' . $cls . '"><label class="switch"><input type="checkbox" id="' . $id . '" name="' . e($name) . '" value="1"' . ($value ? ' checked' : '') . '><span class="switch__ui"></span><span>' . e($label) . '</span></label>' . $help . '</div>';
        case 'textarea':
            return '<div class="' . $cls . '">' . $lab . '<textarea id="' . $id . '" name="' . e($name) . '" rows="4"' . ($req ? ' required' : '') . $ph . '>' . $v . '</textarea>' . $help . $err . '</div>';
        case 'rich':
            return '<div class="' . $cls . '">' . $lab
                . '<div class="rich" data-rich><div class="rich__bar" role="toolbar" aria-label="Mise en forme">'
                . '<button type="button" data-md="h" title="Intertitre">H</button><button type="button" data-md="b" title="Gras"><b>B</b></button><button type="button" data-md="i" title="Italique"><i>I</i></button><button type="button" data-md="ul" title="Liste">•</button><button type="button" data-md="a" title="Lien">' . icon('link', 14) . '</button>'
                . '<span class="rich__hint">**gras** · *italique* · - liste · ## titre · [texte](https://…)</span></div>'
                . '<textarea id="' . $id . '" name="' . e($name) . '" rows="9">' . $v . '</textarea></div>' . $help . $err . '</div>';
        case 'select':
            $o = '';
            foreach ($opt['options'] as $k => $l) {
                $o .= '<option value="' . e((string) $k) . '"' . ((string) $value === (string) $k ? ' selected' : '') . '>' . e($l) . '</option>';
            }
            return '<div class="' . $cls . '">' . $lab . '<select id="' . $id . '" name="' . e($name) . '">' . $o . '</select>' . $help . $err . '</div>';
        case 'image':
            $preview = $value
                ? '<div class="img-field__preview"><img src="' . e(media_url((string) $value)) . '" alt=""></div>'
                : '<div class="img-field__preview img-field__preview--empty">' . icon('image', 28) . '<span>Aucune image</span></div>';
            $remove = $value ? '<label class="img-field__remove"><input type="checkbox" name="remove_' . e($name) . '" value="1"> Retirer l\'image</label>' : '';
            return '<div class="' . $cls . '">' . $lab . '<div class="img-field" data-img-field>' . $preview
                . '<div class="img-field__ctrl"><label class="btn btn-ghost btn-sm">' . icon('upload', 16) . ' Choisir une image<input type="file" name="f_' . e($name) . '" accept="image/jpeg,image/png,image/webp,image/gif" hidden></label>'
                . $remove . '<small class="help">JPG, PNG ou WebP. Optimisée et convertie automatiquement.</small></div></div>' . $help . $err . '</div>';
        default:
            $t = in_array($type, ['date', 'url', 'email', 'tel', 'number'], true) ? $type : 'text';
            return '<div class="' . $cls . '">' . $lab . '<input type="' . $t . '" id="' . $id . '" name="' . e($name) . '" value="' . $v . '"' . ($req ? ' required' : '') . $ph . '>' . $help . $err . '</div>';
    }
}

function time_ago(?string $date): string
{
    if (!$date) return '—';
    $diff = time() - strtotime($date . (str_contains($date, 'T') ? '' : ' UTC'));
    if ($diff < 60) return 'à l\'instant';
    if ($diff < 3600) return 'il y a ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'il y a ' . floor($diff / 3600) . ' h';
    if ($diff < 86400 * 30) return 'il y a ' . floor($diff / 86400) . ' j';
    return fr_date($date);
}
