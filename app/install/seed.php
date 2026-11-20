<?php
/**
 * Contenu initial, repris du prototype et du cahier des charges (v1.5).
 * Exécuté une seule fois, à la création de la base.
 */
function seed_database(PDO $pdo): void
{
    $ins = function (string $table, array $rows) use ($pdo) {
        foreach ($rows as $i => $row) {
            $row['sort_order'] = $row['sort_order'] ?? $i + 1;
            $cols = array_keys($row);
            $st = $pdo->prepare(sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $table,
                implode(',', $cols),
                implode(',', array_fill(0, count($cols), '?'))
            ));
            $st->execute(array_values($row));
        }
    };

    $ins('programs', [
        ['tag' => 'Formation', 'title' => 'Ateliers d\'écriture', 'description' => 'Initiation à l\'écriture poétique, perfectionnement et renforcement des capacités, organisés chaque année autour de la Journée mondiale de la poésie.', 'link_label' => 'En savoir plus', 'link_url' => 'association'],
        ['tag' => 'Accompagnement', 'title' => 'Accompagnement éditorial', 'description' => 'Conseils et mise en réseau avec des maisons d\'édition pour aider les auteurs membres à faire aboutir leurs manuscrits jusqu\'à la publication.', 'link_label' => 'En savoir plus', 'link_url' => 'association'],
        ['tag' => 'Mémoire', 'title' => 'Mémoire littéraire', 'description' => 'Hommages aux figures disparues, dons de bibliothèques aux établissements scolaires et transmission entre générations d\'auteurs.', 'link_label' => 'En savoir plus', 'link_url' => 'histoire'],
    ]);

    $ins('stats', [
        ['grp' => 'hero', 'value' => '2008', 'label' => 'Année de création à Lomé', 'detail' => 'Assemblée générale constitutive, 30 août 2008'],
        ['grp' => 'hero', 'value' => '10+', 'label' => 'Auteurs togolais publiés', 'detail' => 'grâce au réseau de l\'association'],
        ['grp' => 'welcome', 'value' => '10+', 'label' => 'Auteurs togolais édités'],
        ['grp' => 'welcome', 'value' => '2', 'label' => 'Bibliothèques scolaires offertes'],
        ['grp' => 'welcome', 'value' => '15+', 'label' => 'Années d\'existence'],
        ['grp' => 'legacy', 'value' => '2', 'label' => 'Bibliothèques scolaires offertes', 'detail' => 'Plus de 1 500 ouvrages remis aux lycées d\'Assahoun et VIC-Intelligentsia, sous le nom d\'Edem Kodjo.'],
        ['grp' => 'legacy', 'value' => '9+', 'label' => 'Éditions de la Journée mondiale de la poésie', 'detail' => 'Célébrée chaque 21 mars à Lomé depuis 2009.'],
        ['grp' => 'legacy', 'value' => '10+', 'label' => 'Auteurs togolais édités', 'detail' => 'Accompagnés par le réseau jusqu\'à la publication de leur premier ouvrage.'],
        ['grp' => 'legacy', 'value' => '1', 'label' => 'Maison d\'édition fondée par son président', 'detail' => 'Les Éditions Continents.'],
    ]);

    // [date_label, event_date (tri), type, titre, lieu, résumé, featured, in_events]
    $events = [
        ['30 août 2008', '2008-08-30', 'jalon', 'Naissance du Cénacle', 'Siège de l\'ANPE, Lomé', 'Assemblée générale constitutive à Lomé. De jeunes auteurs togolais fondent l\'Association de la Nouvelle Génération de Poètes Togolais sous la présidence de Kodzo Adzewoda Vondoly.', 1, 1],
        ['21 mars 2009', '2009-03-21', 'journee-poesie', 'Première Journée mondiale de la poésie au Togo', 'CIREL-Village du Bénin, Lomé', 'Réunis au CIREL-Village du Bénin, les poètes togolais célèbrent pour la première fois la Journée mondiale de la poésie instituée par l\'UNESCO.', 1, 1],
        ['2010 — 2012', '2010-01-01', 'atelier', 'Dédicaces, hommages et ateliers', 'Lomé', 'Accompagnement de plusieurs publications de membres, hommages à des figures disparues, et multiplication des ateliers d\'écriture à Lomé.', 0, 1],
        ['2011', '2011-12-20', 'senghor', 'Célébration Senghor — Centre culturel Dényigba', 'Centre culturel Dényigba, Lomé', 'Célébration de l\'anniversaire de la disparition de Léopold Sédar Senghor au Centre culturel Dényigba, non loin du Collège Saint-Joseph, Lomé.', 0, 1],
        ['2012', '2012-12-20', 'senghor', 'Célébration Senghor — CIREL-Village du Bénin', 'CIREL-Village du Bénin, Lomé', 'Célébration de l\'anniversaire de la disparition de Senghor au CIREL-Village du Bénin.', 0, 1],
        ['2013', '2013-12-20', 'senghor', 'Célébration Senghor — Lycée d\'Assahoun', 'Lycée d\'Assahoun, Avé', 'Célébration de l\'anniversaire de la disparition de Senghor au lycée d\'Assahoun, préfecture de l\'Avé.', 0, 1],
        ['3 septembre 2014', '2014-09-03', 'jalon', 'De poètes togolais à écrivains africains', 'Lomé', 'À l\'occasion de son sixième anniversaire, l\'association devient le Cénacle, Association de la Nouvelle Génération d\'Écrivains Africains, sur le plan panafricain.', 1, 1],
        ['2014', '2014-12-01', 'senghor', 'Célébration Senghor — Lycée de Kpodzi', 'Lycée de Kpodzi, Kpalimé', 'Célébration de l\'anniversaire de la disparition de Senghor au lycée de Kpodzi, Kpalimé.', 0, 1],
        ['18 décembre 2014', '2014-12-18', 'bibliotheque', 'Bibliothèque Edem Kodjo, lycée d\'Assahoun', 'Lycée d\'Assahoun, Avé', 'Pour le 13e anniversaire de la disparition de Senghor, une première bibliothèque de plus de 500 ouvrages est inaugurée au lycée d\'Assahoun.', 1, 1],
        ['2015', '2015-12-20', 'senghor', 'Célébration Senghor — Goethe-Institut de Lomé', 'Goethe-Institut, Lomé', 'Célébration de l\'anniversaire de la disparition de Senghor au Goethe-Institut de Lomé.', 0, 1],
        ['2016', '2016-12-20', 'senghor', 'Célébration Senghor — CEAPIC, Lassa-Nandadè', 'CEAPIC, Lassa-Nandadè (Kara)', 'Célébration de l\'anniversaire de la disparition de Senghor au CEAPIC, à Lassa-Nandadè (Kara).', 0, 1],
        ['2017', '2017-12-01', 'senghor', 'Célébration Senghor — VIC-Intelligentsia', 'Complexe scolaire VIC-Intelligentsia, Avépozo', 'Célébration de l\'anniversaire de la disparition de Senghor au Complexe scolaire VIC-Intelligentsia, Avépozo.', 0, 1],
        ['20 décembre 2017', '2017-12-20', 'bibliotheque', 'Bibliothèque Edem Kodjo, VIC-Intelligentsia', 'Complexe scolaire VIC-Intelligentsia, Avépozo', 'Pour le 16e anniversaire de la disparition de Senghor, une seconde bibliothèque de 1000 ouvrages est offerte au complexe scolaire VIC-Intelligentsia.', 1, 1],
        ['19 août 2023', '2023-08-19', 'excursion', 'Excursion des 15 ans — Ouïdah, Bénin', 'Ouïdah, Bénin', 'Excursion commémorative annuelle du Cénacle à Ouïdah (Bénin), pour les 15 ans de l\'association.', 0, 1],
        ['24 août 2024', '2024-08-24', 'excursion', 'Excursion des 16 ans — Ouïdah, Bénin', 'Ouïdah, Bénin', 'Excursion commémorative annuelle du Cénacle à Ouïdah (Bénin), pour les 16 ans de l\'association.', 0, 1],
        ['23 août 2025', '2025-08-23', 'excursion', 'Excursion des 17 ans — Ouïdah, Bénin', 'Ouïdah, Bénin', 'Excursion commémorative annuelle du Cénacle à Ouïdah (Bénin), pour les 17 ans de l\'association.', 0, 1],
        ['29 août 2026', '2026-08-29', 'excursion', 'Excursion des 18 ans — Ouïdah, Bénin', 'Ouïdah, Bénin', 'Excursion commémorative annuelle du Cénacle à Ouïdah (Bénin), de 5h30 à 18h00, pour les 18 ans de l\'association.', 1, 1],
        ['Aujourd\'hui', null, 'jalon', 'Un héritage vivant', 'Lomé', 'Sous la présidence de Kodzo Adzewoda Vondoly, le Cénacle poursuit sa mission de transmission littéraire et prépare de nouveaux projets pour la scène panafricaine.', 1, 0],
    ];
    $rows = [];
    $used = [];
    foreach ($events as $i => [$label, $date, $type, $title, $loc, $summary, $featured, $inEvents]) {
        $slug = slugify(($date ? substr($date, 0, 4) . '-' : '') . $title);
        while (isset($used[$slug])) {
            $slug .= '-2';
        }
        $used[$slug] = true;
        $rows[] = [
            'slug' => $slug, 'title' => $title, 'date_label' => $label, 'event_date' => $date,
            'type' => $type, 'location' => $loc, 'summary' => $summary, 'content' => '',
            'featured' => $featured, 'in_events' => $inEvents, 'in_history' => 1,
        ];
    }
    $ins('events', $rows);

    $ins('figures', [
        ['name' => 'Kodzo Adzewoda Vondoly', 'role' => 'Fondateur et Président', 'bio' => 'Journaliste, écrivain-poète, il fonde le Cénacle en 2008 et le préside depuis. Aujourd\'hui directeur des Éditions Continents.', 'photo' => 'assets/img/vondoly.jpg'],
        ['name' => 'Samuel Nicada Seou', 'role' => 'Secrétaire général', 'bio' => 'Sociologue et écrivain poète, auteur du recueil « Chants de retour » consacré au pays Kabyè.'],
        ['name' => 'Joseph Kokou Koffigoh', 'role' => 'Parrain et soutien historique', 'bio' => 'Avocat, ancien Premier ministre du Togo et écrivain-poète, invité d\'honneur et préfacier de plusieurs ouvrages de membres.'],
    ]);

    $ins('member_categories', [
        ['title' => 'Fondateurs', 'description' => 'Les personnes ayant participé à l\'Assemblée générale constitutive du Cénacle, le samedi 30 août 2008, dans les locaux du siège de l\'ANPE.'],
        ['title' => 'Actifs', 'description' => 'Membres présents à toutes les activités du Cénacle.'],
        ['title' => 'Sympathisants', 'description' => 'Personnes s\'étant jointes à l\'association en chemin, sans adhésion officielle.'],
        ['title' => 'D\'honneur', 'description' => 'Personnalités qui, par leur volonté propre, soutiennent l\'association sur le plan financier.', 'note' => 'Cette catégorie ne comprend pas Me Joseph Kokou Koffigoh, le Professeur Robert Dussey, Gilbert Fossoun Houngbo, Me Yawovi Agboyibo (paix à son âme) et Edem Kodjo (paix à son âme).'],
        ['title' => 'Partenaires', 'description' => 'Représentants d\'institutions ou d\'organisations privées et publiques.'],
        ['title' => 'Admirateurs', 'description' => 'Amis du livre évoluant dans d\'autres domaines, mais fans des activités du Cénacle.'],
    ]);

    $ins('links', [
        ['label' => 'cenacletogo.blog.fr', 'url' => 'http://cenacletogo.blog.fr', 'kind' => 'blog'],
        ['label' => 'cenacle.over-blog.com', 'url' => 'http://cenacle.over-blog.com', 'kind' => 'blog'],
        ['label' => 'cenacle.centerblog.net', 'url' => 'http://cenacle.centerblog.net', 'kind' => 'blog'],
    ]);
}
