<?php
/**
 * Fragments réutilisables (gabarits) du site public.
 */

function section_head(string $title, string $subtitle = '', string $kicker = '', bool $light = false): string
{
    $html = '<div class="section-head' . ($light ? ' section-head--light' : '') . '">';
    if ($kicker) {
        $html .= '<span class="kicker" data-reveal>' . e($kicker) . '</span>';
    }
    $html .= '<h2 data-split>' . e($title) . '</h2>';
    if ($subtitle) {
        $html .= '<p data-reveal>' . e($subtitle) . '</p>';
    }
    return $html . '</div>';
}

function page_hero(string $kicker, string $title, string $lede = '', string $color = 'vert'): string
{
    $html = '<section class="page-hero page-hero--' . e($color) . '">'
        . '<canvas class="page-hero__gl" data-ink aria-hidden="true"></canvas>'
        . '<div class="page-hero__inner">'
        . '<span class="kicker" data-reveal>' . e($kicker) . '</span>'
        . '<h1 data-split="chars">' . e($title) . '</h1>';
    if ($lede) {
        $html .= '<p class="page-hero__lede" data-reveal>' . e($lede) . '</p>';
    }
    return $html . '</div>'
        . '<span class="dot dot--jaune" data-float></span><span class="dot dot--rouge" data-float></span><span class="dot dot--ring" data-float></span>'
        . '</section>';
}

function media_placeholder(string $label, string $kind = 'Photo'): string
{
    return '<div class="ph-media"><span class="ph-media__icon" aria-hidden="true">'
        . ($kind === 'Vidéo' ? '▶' : '✦') . '</span><span>' . e($kind) . ' à venir</span><small>' . e($label) . '</small></div>';
}

function event_card(array $ev): string
{
    $cover = $ev['cover']
        ? picture($ev['cover'], $ev['title'])
        : media_placeholder($ev['title']);
    $count = (int) ($ev['media_count'] ?? 0);
    return '<article class="event-card" data-reveal data-tilt>'
        . '<a href="' . e(url('evenements/' . $ev['slug'])) . '" class="event-card__link">'
        . '<div class="event-card__media">' . $cover
        . ($count ? '<span class="event-card__count">' . $count . ' média' . ($count > 1 ? 's' : '') . '</span>' : '')
        . '</div>'
        . '<div class="event-card__body">'
        . '<div class="event-card__meta"><span class="pill pill--' . e($ev['type']) . '">' . e(event_type_label($ev['type'])) . '</span>'
        . '<span class="event-card__date">' . e($ev['date_label']) . '</span></div>'
        . '<h3>' . e($ev['title']) . '</h3>'
        . ($ev['location'] ? '<p class="event-card__loc">' . e($ev['location']) . '</p>' : '')
        . '<p>' . e(excerpt($ev['summary'], 140)) . '</p>'
        . '<span class="card-link">Voir l\'événement <span aria-hidden="true">→</span></span>'
        . '</div></a></article>';
}

function figure_card(array $f): string
{
    $avatar = $f['photo']
        ? '<div class="fig-avatar fig-avatar--photo">' . picture($f['photo'], $f['name']) . '</div>'
        : '<div class="fig-avatar" aria-hidden="true">' . e(initials($f['name'])) . '</div>';
    return '<article class="fig-card" data-reveal data-tilt>' . $avatar
        . '<h3>' . e($f['name']) . '</h3>'
        . '<span class="fig-role">' . e($f['role']) . '</span>'
        . '<p>' . e($f['bio']) . '</p></article>';
}

function program_card(array $p): string
{
    $media = $p['image'] ? picture($p['image'], $p['title']) : media_placeholder($p['title']);
    $link = '';
    if ($p['link_url']) {
        $href = preg_match('#^https?://#', $p['link_url']) ? $p['link_url'] : url($p['link_url']);
        $link = '<a href="' . e($href) . '" class="card-link">' . e($p['link_label'] ?: 'En savoir plus') . ' <span aria-hidden="true">→</span></a>';
    }
    return '<article class="card" data-reveal data-tilt>'
        . '<div class="card-media">' . $media . '</div>'
        . '<div class="card-body">'
        . ($p['tag'] ? '<span class="card-tag">' . e($p['tag']) . '</span>' : '')
        . '<h3>' . e($p['title']) . '</h3><p>' . e($p['description']) . '</p>' . $link
        . '</div></article>';
}

function stat_counter(array $s, string $class = 'stat'): string
{
    return '<div class="' . $class . '" data-reveal>'
        . '<span class="' . $class . '__n" data-counter="' . e($s['value']) . '">' . e($s['value']) . '</span>'
        . '<span class="' . $class . '__l">' . e($s['label']) . '</span>'
        . (!empty($s['detail']) && $class === 'legacy-card' ? '<span class="' . $class . '__d">' . e($s['detail']) . '</span>' : '')
        . '</div>';
}

function join_block(): string
{
    $wa = setting('whatsapp');
    return '<div class="join" data-join>'
        . '<canvas class="join__gl" data-ink="dark" aria-hidden="true"></canvas>'
        . '<span class="join__bubble join__bubble--jaune" data-bubble aria-hidden="true"></span>'
        . '<span class="join__bubble join__bubble--rouge" data-bubble aria-hidden="true"></span>'
        . '<div class="join__inner">'
        . '<h2 data-split>' . e(setting('join_title')) . '</h2>'
        . '<p data-reveal>' . e(setting('join_text')) . '</p>'
        . '<div class="btn-row" data-reveal>'
        . '<a href="' . e(wa_link($wa, setting('wa_member_msg'))) . '" class="btn btn-white" target="_blank" rel="noopener" data-magnetic>Devenir membre</a>'
        . '<a href="' . e(wa_link($wa, setting('wa_support_msg'))) . '" class="btn btn-line" target="_blank" rel="noopener" data-magnetic>Soutenir un projet</a>'
        . '<a href="' . e(url('rejoindre')) . '" class="btn btn-ghost">Les catégories de membres <span aria-hidden="true">→</span></a>'
        . '</div></div></div>';
}

function contact_form(string $kind, string $action): string
{
    start_session();
    $state = $_SESSION['_form'] ?? ['errors' => [], 'old' => []];
    unset($_SESSION['_form']);
    $err = $state['errors'];
    $old = $state['old'];
    $flashes = take_flashes();
    $v = fn($k) => e($old[$k] ?? '');
    $fe = fn($k) => isset($err[$k]) ? '<span class="field__error" id="err-' . $k . '">' . e($err[$k]) . '</span>' : '';
    $inv = fn($k) => isset($err[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';

    $html = '<form class="form" method="post" action="' . e(url($action)) . '#formulaire" novalidate data-form>';
    foreach ($flashes as $f) {
        $html .= '<div class="alert alert--' . e($f['type']) . '" role="status">' . e($f['message']) . '</div>';
    }
    if (isset($err['form'])) {
        $html .= '<div class="alert alert--error" role="alert">' . e($err['form']) . '</div>';
    }
    $html .= csrf_field()
        . '<input type="hidden" name="_t" value="' . time() . '">'
        . '<div class="hp" aria-hidden="true"><label>Ne pas remplir <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>'
        . '<div class="form__row">'
        . '<div class="field"><label for="f-name">Nom complet *</label><input id="f-name" name="name" required autocomplete="name" value="' . $v('name') . '"' . $inv('name') . '>' . $fe('name') . '</div>'
        . '<div class="field"><label for="f-email">E-mail *</label><input id="f-email" type="email" name="email" required autocomplete="email" value="' . $v('email') . '"' . $inv('email') . '>' . $fe('email') . '</div>'
        . '</div><div class="form__row">'
        . '<div class="field"><label for="f-phone">Téléphone / WhatsApp</label><input id="f-phone" type="tel" name="phone" autocomplete="tel" value="' . $v('phone') . '"' . $inv('phone') . '>' . $fe('phone') . '</div>'
        . '<div class="field"><label for="f-subject">Objet</label><input id="f-subject" name="subject" value="' . ($v('subject') ?: ($kind === 'adhesion' ? 'Demande d\'adhésion' : '')) . '"></div>'
        . '</div>'
        . '<div class="field"><label for="f-body">' . ($kind === 'adhesion' ? 'Présentez-vous (parcours, écrits, motivation) *' : 'Votre message *') . '</label>'
        . '<textarea id="f-body" name="body" rows="6" required' . $inv('body') . '>' . $v('body') . '</textarea>' . $fe('body') . '</div>'
        . '<div class="form__foot"><p class="form__note">Vos données servent uniquement à vous répondre. <a href="' . e(url('mentions-legales')) . '">En savoir plus</a></p>'
        . '<button type="submit" class="btn btn-green" data-magnetic>' . ($kind === 'adhesion' ? 'Envoyer ma demande' : 'Envoyer le message') . ' <span aria-hidden="true">→</span></button></div>'
        . '</form>';
    return $html;
}

function marquee(array $words, string $variant = 'rouge'): string
{
    $item = '';
    foreach ($words as $w) {
        $item .= '<span>' . e($w) . '</span><i aria-hidden="true">✦</i>';
    }
    return '<div class="marquee marquee--' . e($variant) . '" aria-hidden="true"><div class="marquee__track">'
        . '<div class="marquee__item">' . $item . '</div><div class="marquee__item">' . $item . '</div>'
        . '</div></div>';
}
