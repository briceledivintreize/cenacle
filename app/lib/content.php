<?php
/**
 * Définition du contenu éditable : réglages du site, types d'événements,
 * et requêtes utilisées par le site public.
 */

const EVENT_TYPES = [
    'jalon'          => 'Jalon',
    'journee-poesie' => 'Journée de la poésie',
    'senghor'        => 'Célébration Senghor',
    'bibliotheque'   => 'Bibliothèque',
    'excursion'      => 'Excursion',
    'anniversaire'   => 'Anniversaire',
    'atelier'        => 'Atelier',
    'autre'          => 'Autre',
];

const STAT_GROUPS = [
    'hero'    => 'Accueil — chiffres du hero',
    'welcome' => 'Accueil — bandeau « Bienvenue »',
    'legacy'  => 'Bande « Ce que le Cénacle laisse »',
];

const LINK_KINDS = [
    'blog'   => 'Blog',
    'social' => 'Réseau social',
    'other'  => 'Autre',
];

/**
 * Réglages éditables depuis l'admin, groupés par onglet.
 * Chaque champ : [label, type, défaut, aide?]. Types : text, textarea, rich, image, email, url, tel.
 */
function settings_schema(): array
{
    return [
        'general' => [
            'title'  => 'Général',
            'fields' => [
                'site_name'       => ['Nom du site', 'text', 'Le Cénacle'],
                'site_tagline'    => ['Nom complet de l\'association', 'text', 'Association de la Nouvelle Génération d\'Écrivains Africains'],
                'seo_description' => ['Description (référencement)', 'textarea', 'Le Cénacle, association littéraire fondée à Lomé en 2008 : poètes, romanciers et conteurs togolais et africains. Histoire, événements, publications et adhésion.', 'Affichée par Google et lors des partages (160 caractères max. conseillés).'],
                'logo'            => ['Logo', 'image', 'assets/img/logo-cenacle.jpg'],
                'og_image'        => ['Image de partage (WhatsApp, Facebook)', 'image', 'assets/img/og-image.jpg', 'Format conseillé : 1200 × 630 px.'],
                'nav_cta_label'   => ['Bouton du menu', 'text', 'Nous rejoindre'],
            ],
        ],
        'hero' => [
            'title'  => 'Accueil — Hero',
            'fields' => [
                'hero_title'        => ['Titre (début)', 'text', 'L\'écriture commence là où'],
                'hero_title_accent' => ['Titre (partie en couleur)', 'text', 'le silence finit'],
                'hero_lede'         => ['Phrase d\'introduction', 'textarea', 'Depuis 2008, le Cénacle réunit à Lomé des poètes, romanciers et conteurs togolais et africains. Un cercle littéraire devenu mouvement panafricain, toujours vivant sous la présidence de son fondateur, Kodzo Adzewoda Vondoly.'],
                'hero_image'        => ['Portrait du Président', 'image', 'assets/img/vondoly.jpg'],
                'president_name'    => ['Nom du Président', 'text', 'Kodzo Adzewoda Vondoly'],
                'president_role'    => ['Fonction du Président', 'text', 'Fondateur & Président'],
                'hero_cta1'         => ['Bouton principal', 'text', 'Rejoindre le Cénacle'],
                'hero_cta2'         => ['Bouton secondaire', 'text', 'Découvrir l\'histoire'],
            ],
        ],
        'home' => [
            'title'  => 'Accueil — Sections',
            'fields' => [
                'programs_title'    => ['Programmes : titre', 'text', 'Nos programmes'],
                'programs_subtitle' => ['Programmes : sous-titre', 'text', 'Trois piliers qui structurent l\'action du Cénacle depuis sa création'],
                'welcome_title'     => ['Bienvenue : titre', 'text', 'Bienvenue dans le réseau panafricain du Cénacle'],
                'welcome_text'      => ['Bienvenue : texte', 'textarea', 'Né cercle de poètes togolais en 2008, le Cénacle est devenu en 2014 l\'Association de la Nouvelle Génération d\'Écrivains Africains — élargissant son cercle aux romanciers et conteurs venus de tout le continent.'],
                'history_title'     => ['Histoire : titre', 'text', 'Une chronologie en mouvement'],
                'history_subtitle'  => ['Histoire : sous-titre', 'text', 'De cercle de poètes togolais à mouvement panafricain'],
                'events_title'      => ['Événements : titre', 'text', 'Événements & excursions'],
                'events_subtitle'   => ['Événements : sous-titre', 'text', 'La vie du Cénacle : anniversaires, hommages, ateliers et excursions annuelles'],
                'figures_title'     => ['Figures : titre', 'text', 'Celles et ceux qui ont fait le Cénacle'],
                'figures_subtitle'  => ['Figures : sous-titre', 'text', 'Les figures qui ont porté l\'association depuis 2008'],
                'legacy_title'      => ['Héritage : titre', 'text', 'Ce que le Cénacle laisse derrière lui'],
                'legacy_subtitle'   => ['Héritage : sous-titre', 'text', 'Un héritage mesurable au service de la littérature togolaise et africaine'],
                'quote_text'        => ['Citation', 'textarea', 'La littérature togolaise, à mon humble avis, se porte à merveille, et j\'ai des raisons valables de le dire. Avant, l\'on soutenait qu\'il n\'y a pas une littérature togolaise […]. Mais aujourd\'hui, la littérature a un nom, une étiquette, une image qui va au-delà des frontières togolaises et africaines.'],
                'quote_attr'        => ['Citation : source', 'text', 'Kodzo Adzewoda Vondoly, Président du Cénacle — entretien, golfenews.info'],
                'join_title'        => ['Bloc vert : titre', 'text', 'Rejoindre ou soutenir le Cénacle'],
                'join_text'         => ['Bloc vert : texte', 'textarea', 'Auteur, enseignant, institution ou simple amoureux des lettres : le Cénacle accueille toute bonne volonté désireuse de faire vivre la littérature togolaise et africaine.'],
            ],
        ],
        'about' => [
            'title'  => 'L\'association',
            'fields' => [
                'about_intro'      => ['Présentation', 'rich', "Le Cénacle est une association littéraire togolaise créée le **30 août 2008** à Lomé sous le nom d'*Association de la Nouvelle Génération de Poètes Togolais*. En septembre 2014, elle devient l'**Association de la Nouvelle Génération d'Écrivains Africains** et ouvre son cercle aux romanciers, nouvellistes et conteurs de tout le continent.\n\nElle est présidée depuis sa fondation par Kodzo Adzewoda Vondoly, journaliste, écrivain-poète et directeur des Éditions Continents."],
                'about_missions'   => ['Missions', 'rich', "- Révéler et accompagner les jeunes plumes togolaises et africaines.\n- Faire vivre la poésie et la littérature dans les écoles, les centres culturels et l'espace public.\n- Entretenir la mémoire des grandes figures littéraires du continent.\n- Favoriser l'accès au livre, notamment par des dons de bibliothèques."],
                'about_values'     => ['Valeurs', 'rich', "- **Transmission** entre générations d'auteurs.\n- **Exigence** dans l'écriture et l'accompagnement.\n- **Ouverture** panafricaine.\n- **Fraternité** des lettres."],
                'about_organisation' => ['Organisation', 'rich', "L'association est dirigée par un bureau exécutif présidé par son fondateur. Elle réunit des membres fondateurs, actifs, sympathisants, d'honneur, partenaires et admirateurs, et s'appuie sur un réseau de personnalités du monde des lettres qui la soutiennent depuis ses débuts."],
            ],
        ],
        'join' => [
            'title'  => 'Nous rejoindre',
            'fields' => [
                'join_page_intro' => ['Introduction', 'textarea', 'Le Cénacle rassemble six catégories de membres. Que vous soyez auteur, enseignant, institution ou simple amoureux des lettres, il y a une place pour vous dans le cercle.'],
                'join_form_intro' => ['Texte au-dessus du formulaire', 'textarea', 'Présentez-vous en quelques lignes : nous reviendrons vers vous rapidement.'],
                'join_practical'  => ['Informations pratiques', 'rich', "Les rencontres se tiennent principalement à Lomé. L'adhésion est ouverte à toute personne partageant les valeurs de l'association. Pour toute question, écrivez-nous ou contactez-nous directement sur WhatsApp."],
            ],
        ],
        'publications' => [
            'title'  => 'Publications',
            'fields' => [
                'publications_intro' => ['Introduction', 'textarea', 'Cette rubrique présente les ouvrages liés au Cénacle et à son réseau d\'écrivains — à commencer par les titres des Éditions Continents, et bientôt d\'autres parutions portées par l\'association, comme des œuvres collectives réunissant plusieurs membres.'],
                'publications_empty' => ['Message si aucun ouvrage', 'text', 'Bientôt disponible'],
                'editions_whatsapp'  => ['WhatsApp des Éditions Continents', 'tel', '+228 99 59 26 29', 'Numéro qui reçoit les demandes « Je veux ce livre ».'],
                'book_message'       => ['Message pré-rempli « Je veux ce livre »', 'text', 'Bonjour, je souhaite obtenir le livre « {titre} » vu sur le site du Cénacle.', '{titre} est remplacé par le titre de l\'ouvrage.'],
            ],
        ],
        'contact' => [
            'title'  => 'Contact',
            'fields' => [
                'address'           => ['Siège', 'textarea', 'Tokoin-Nukafu, 168 Rue Mandila, non loin de la Grande Mosquée, 03 B.P. 30188, Lomé, Togo'],
                'address_short'     => ['Siège (version courte)', 'text', 'Tokoin-Nukafu, 168 Rue Mandila, Lomé, Togo'],
                'phone'             => ['Téléphone(s)', 'text', '+228 90 09 09 23 / 99 59 26 29'],
                'email'             => ['Courriel public', 'email', 'cenacleasso@yahoo.fr'],
                'contact_recipient' => ['E-mail qui reçoit les formulaires', 'email', 'cenacleasso@yahoo.fr', 'Les messages sont aussi consultables dans l\'admin (Messages).'],
                'whatsapp'          => ['Numéro WhatsApp de l\'association', 'tel', '+228 99 59 26 29'],
                'wa_member_msg'     => ['Message WhatsApp « Devenir membre »', 'text', 'Bonjour, je souhaite devenir membre du Cénacle.'],
                'wa_support_msg'    => ['Message WhatsApp « Soutenir un projet »', 'text', 'Bonjour, je souhaite soutenir un projet du Cénacle.'],
                'facebook_url'      => ['Page Facebook', 'url', '', 'Laisser vide tant que le lien n\'est pas communiqué.'],
            ],
        ],
        'footer' => [
            'title'  => 'Pied de page & légal',
            'fields' => [
                'footer_left'   => ['Pied de page : gauche', 'text', 'Le Cénacle — Association de la Nouvelle Génération d\'Écrivains Africains, Lomé, Togo.'],
                'footer_right'  => ['Pied de page : droite', 'text', 'Depuis 2008, la plume togolaise au service du continent.'],
                'credit_label'  => ['Crédit développeur', 'text', 'Darollo Technologies Corporation'],
                'credit_url'    => ['Lien du crédit', 'url', 'https://darollotech.com'],
                'legal_content' => ['Mentions légales', 'rich', "## Éditeur du site\nLe Cénacle — Association de la Nouvelle Génération d'Écrivains Africains\nSiège : Tokoin-Nukafu, 168 Rue Mandila, 03 B.P. 30188, Lomé, Togo\nDirecteur de la publication : Kodzo Adzewoda Vondoly, Président\nCourriel : cenacleasso@yahoo.fr\n\n## Conception et hébergement\nSite conçu, hébergé et administré par Darollo Technologies Corporation (DTC) SARL, BE Kpota, Rue BD de l'OTI, Lomé, Togo — [darollotech.com](https://darollotech.com).\n\n## Données personnelles\nLes informations transmises via les formulaires (nom, e-mail, téléphone, message) sont utilisées uniquement pour répondre à votre demande ou traiter votre adhésion. Elles ne sont ni vendues ni cédées à des tiers. Vous pouvez demander leur consultation, rectification ou suppression en écrivant à cenacleasso@yahoo.fr.\n\n## Cookies\nLe site n'utilise aucun cookie publicitaire ni outil de suivi. Seul un cookie technique de session peut être déposé lors de l'envoi d'un formulaire.\n\n## Propriété intellectuelle\nLes textes, le logo et le portrait du Président sont la propriété du Cénacle. Toute reproduction sans autorisation est interdite."],
            ],
        ],
    ];
}

function settings_defaults(): array
{
    static $defaults = null;
    if ($defaults === null) {
        $defaults = [];
        foreach (settings_schema() as $group) {
            foreach ($group['fields'] as $key => $f) {
                $defaults[$key] = $f[2];
            }
        }
    }
    return $defaults;
}

function setting(string $key, string $fallback = ''): string
{
    static $cache = null;
    if ($key === '__reset') {
        $cache = null;
        return '';
    }
    if ($cache === null) {
        $cache = [];
        foreach (all('SELECT key, value FROM settings') as $row) {
            $cache[$row['key']] = (string) $row['value'];
        }
    }
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    return settings_defaults()[$key] ?? $fallback;
}

function save_setting(string $key, string $value): void
{
    q('INSERT INTO settings(key, value) VALUES(?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value', [$key, $value]);
}

/* ---------------------------------------------------------------- Requêtes site */

function get_programs(): array
{
    return all('SELECT * FROM programs WHERE published = 1 ORDER BY sort_order, id');
}

function get_stats(string $group): array
{
    return all('SELECT * FROM stats WHERE published = 1 AND grp = ? ORDER BY sort_order, id', [$group]);
}

function get_history(bool $featuredOnly = false): array
{
    $sql = 'SELECT * FROM events WHERE published = 1 AND in_history = 1'
        . ($featuredOnly ? ' AND featured = 1' : '')
        . ' ORDER BY event_date IS NULL, event_date, sort_order, id';
    return all($sql);
}

function get_events(?int $limit = null): array
{
    $sql = 'SELECT e.*, (SELECT COUNT(*) FROM event_media m WHERE m.event_id = e.id) AS media_count
            FROM events e WHERE published = 1 AND in_events = 1
            ORDER BY event_date IS NULL, event_date DESC, sort_order DESC, id DESC';
    if ($limit) {
        $sql .= ' LIMIT ' . (int) $limit;
    }
    return all($sql);
}

function get_event(string $slug): ?array
{
    return one('SELECT * FROM events WHERE slug = ? AND published = 1', [$slug]);
}

function get_event_media(int $eventId): array
{
    return all('SELECT * FROM event_media WHERE event_id = ? ORDER BY sort_order, id', [$eventId]);
}

function get_publications(): array
{
    return all('SELECT * FROM publications WHERE published = 1 ORDER BY sort_order, release_date DESC, id DESC');
}

function get_figures(): array
{
    return all('SELECT * FROM figures WHERE published = 1 ORDER BY sort_order, id');
}

function get_member_categories(): array
{
    return all('SELECT * FROM member_categories WHERE published = 1 ORDER BY sort_order, id');
}

function get_links(): array
{
    return all('SELECT * FROM links WHERE published = 1 ORDER BY sort_order, id');
}

function event_year(array $e): string
{
    return $e['event_date'] ? substr($e['event_date'], 0, 4) : 'Aujourd\'hui';
}

function event_type_label(?string $type): string
{
    return EVENT_TYPES[$type ?? 'autre'] ?? 'Autre';
}
