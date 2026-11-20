# Mise en ligne sur Hostinger (offre Business)

Le dépôt est prévu pour être déployé **tel quel dans `public_html`** grâce à l'outil Git de hPanel.
Le `.htaccess` racine redirige toutes les requêtes vers `public/` et bloque l'accès à `app/` et `storage/`.

## 1. Préparer l'hébergement (hPanel)

1. **Site web** : ajoutez le domaine du Cénacle (ou utilisez le domaine temporaire fourni).
2. **Avancé › Configuration PHP** : choisissez **PHP 8.2 ou plus récent**.
   Extensions requises (actives par défaut chez Hostinger) : `pdo_sqlite`, `gd`, `fileinfo`, `intl`, `mbstring`.
   Dans *Options PHP*, conseillé : `upload_max_filesize = 64M`, `post_max_size = 64M` (galeries vidéo).
3. **Sécurité › SSL** : installez le certificat gratuit.
4. **Gestionnaire de fichiers** : videz `public_html` (supprimez le `default.php` de Hostinger), l'outil Git exige un dossier vide.

## 2. Connecter le dépôt GitHub

hPanel › **Avancé › Git** :

| Champ | Valeur |
|---|---|
| Dépôt | `https://github.com/briceledivintreize/cenacle.git` |
| Branche | `main` |
| Répertoire | *(laisser vide = public_html)* |

- Dépôt **privé** : copiez la *clé SSH* affichée par hPanel dans GitHub › *Settings › Deploy keys* du dépôt,
  et utilisez l'adresse SSH `git@github.com:briceledivintreize/cenacle.git`.
- Cliquez **Créer** puis **Déployer**.
- *Déploiement automatique* (facultatif) : copiez l'URL du webhook fournie par hPanel dans
  GitHub › *Settings › Webhooks* (Content type `application/json`). Chaque `git push` sur `main` mettra le site à jour.

Les données de production (`storage/cenacle.sqlite`, `public/uploads/`) ne sont pas versionnées : un nouveau
déploiement ne les écrase jamais.

## 3. Configuration du serveur

Dans le gestionnaire de fichiers, créez `app/config.local.php` (copie de `app/config.local.example.php`) :

```php
<?php
return [
    'site_url'  => 'https://lecenacle.youngwaverecords.com',   // sans / final
    'mail_from' => 'no-reply@youngwaverecords.com',      // adresse du domaine (créée dans hPanel › E-mails)
];
```

`mail_from` doit être une adresse **du domaine** pour que les notifications du formulaire ne tombent pas en spam.

Une fois le SSL actif, décommentez les 3 lignes « Forcer HTTPS » du `.htaccess` racine.

## 4. Premier accès

1. Ouvrez `https://votre-domaine/` : la base est créée et remplie automatiquement avec le contenu du cahier des charges.
2. Ouvrez `https://votre-domaine/admin` : créez le **compte administrateur principal** (écran d'installation, une seule fois).
3. Dans *Réglages du site*, vérifiez coordonnées, e-mail de réception des formulaires et lien Facebook.

## 5. Sauvegardes

- Hostinger Business inclut des sauvegardes quotidiennes automatiques.
- Sauvegarde manuelle : téléchargez `storage/cenacle.sqlite` et `public/uploads/`.

## Dépannage

| Symptôme | Solution |
|---|---|
| Erreur 500 | Vérifier la version PHP (≥ 8.1) et que `storage/` est accessible en écriture (755). |
| Pages intérieures en 404 | Vérifier que les deux `.htaccess` (racine et `public/`) ont bien été déployés. |
| Mot de passe admin perdu | En SSH : `cd public_html && php bin/create-admin.php "Nom" email@domaine` |
| Envoi de vidéos impossible | Augmenter `upload_max_filesize` / `post_max_size` dans hPanel. |
