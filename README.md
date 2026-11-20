# Le Cénacle — site officiel et panel d'administration

Site de l'association **Le Cénacle — Association de la Nouvelle Génération d'Écrivains Africains** (Lomé, Togo),
construit d'après le cahier des charges v1.5 et la maquette `cenacle-site-FINAL`.

- **Site public** animé : GSAP (ScrollTrigger, SplitText), défilement doux (Lenis), scènes WebGL Three.js
  (poussière d'encre du hero, logo recomposé en particules), transitions de pages, chronologie horizontale épinglée.
  Respecte « réduire les animations » : tout le contenu reste lisible sans JavaScript.
- **Panel d'administration** séparé (`/admin`) : événements + galeries photo/vidéo, programmes, chiffres clés,
  publications, figures, catégories de membres, liens, réglages de tous les textes, messages reçus, médiathèque, comptes.

## Technologies

PHP 8.1+ (aucune dépendance Composer) · SQLite · HTML/CSS/JS sans framework.
Bibliothèques JS hébergées localement dans `public/assets/vendor/` : GSAP 3.13, Three.js r170, Lenis 1.1, SortableJS 1.15.

## Structure

```
app/            code PHP (inaccessible depuis le web)
  admin/        panel : routeur, CRUD générique, vues
  site/         site public : routeur, pages, gabarits
  install/      schéma de la base + contenu initial
  lib/          base de données, médias (WebP), mise en forme, réglages
bin/            outils en ligne de commande
public/         racine web : index.php, admin/, assets/, uploads/
storage/        base SQLite (créée automatiquement, non versionnée)
```

## Lancer en local

```bash
php -S localhost:8000 -t public public/router.php
```

Puis ouvrir http://localhost:8000 (site) et http://localhost:8000/admin (création du premier compte administrateur au premier accès).
La base `storage/cenacle.sqlite` est créée et pré-remplie automatiquement à la première visite.

## Mise en forme des textes longs (admin)

`**gras**` · `*italique*` · `- élément de liste` · `## intertitre` · `[texte](https://lien)` — une barre d'outils les insère.

## Déploiement

Voir [DEPLOIEMENT.md](DEPLOIEMENT.md) (Hostinger Business, déploiement Git).

---
Développé par [Darollo Technologies Corporation](https://darollotech.com).
