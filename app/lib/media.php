<?php
/**
 * Gestion des envois de fichiers : validation, redimensionnement,
 * conversion WebP (avec repli JPEG/PNG) et enregistrement en médiathèque.
 */

const IMAGE_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
const VIDEO_MIMES = ['video/mp4' => 'mp4', 'video/webm' => 'webm'];

/**
 * @param array  $file   entrée de $_FILES
 * @param string $accept 'image' | 'video' | 'any'
 * @return string chemin relatif au dossier public (ex. uploads/2026/11/abc.jpg)
 * @throws RuntimeException message lisible pour l'utilisateur
 */
function handle_upload(array $file, string $accept = 'image'): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $map = [
            UPLOAD_ERR_INI_SIZE  => 'Le fichier dépasse la taille autorisée par le serveur.',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux.',
            UPLOAD_ERR_PARTIAL   => 'Le fichier n\'a été que partiellement envoyé.',
            UPLOAD_ERR_NO_FILE   => 'Aucun fichier envoyé.',
        ];
        throw new RuntimeException($map[$file['error']] ?? 'Erreur lors de l\'envoi du fichier.');
    }
    if ($file['size'] > $GLOBALS['config']['max_upload']) {
        throw new RuntimeException('Le fichier est trop volumineux.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $isImage = isset(IMAGE_MIMES[$mime]);
    $isVideo = isset(VIDEO_MIMES[$mime]);
    if (($accept === 'image' && !$isImage) || ($accept === 'video' && !$isVideo) || (!$isImage && !$isVideo)) {
        throw new RuntimeException('Format non pris en charge (' . $mime . ').');
    }

    $sub = 'uploads/' . date('Y/m');
    $dir = APP_ROOT . '/public/' . $sub;
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $base = slugify(pathinfo($file['name'], PATHINFO_FILENAME));
    $base = substr($base, 0, 40) . '-' . bin2hex(random_bytes(4));

    if ($isVideo) {
        $rel = $sub . '/' . $base . '.' . VIDEO_MIMES[$mime];
        if (!move_uploaded_file($file['tmp_name'], APP_ROOT . '/public/' . $rel) && !rename($file['tmp_name'], APP_ROOT . '/public/' . $rel)) {
            throw new RuntimeException('Impossible d\'enregistrer le fichier.');
        }
        register_media($rel, $file['name'], $mime);
        return $rel;
    }

    $rel = optimize_image($file['tmp_name'], $mime, $sub, $base);
    register_media($rel, $file['name'], $mime);
    return $rel;
}

/** Redimensionne et produit original optimisé + variante .webp. */
function optimize_image(string $src, string $mime, string $sub, string $base): string
{
    $dirAbs = APP_ROOT . '/public/' . $sub;
    if ($mime === 'image/gif' || !function_exists('imagecreatetruecolor')) {
        $rel = $sub . '/' . $base . '.' . IMAGE_MIMES[$mime];
        copy($src, APP_ROOT . '/public/' . $rel);
        return $rel;
    }
    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($src),
        'image/png'  => @imagecreatefrompng($src),
        'image/webp' => @imagecreatefromwebp($src),
        default      => false,
    };
    if (!$img) {
        throw new RuntimeException('Image illisible ou corrompue.');
    }
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($src);
        $o = $exif['Orientation'] ?? 1;
        if ($o === 3) $img = imagerotate($img, 180, 0);
        if ($o === 6) $img = imagerotate($img, -90, 0);
        if ($o === 8) $img = imagerotate($img, 90, 0);
    }
    $w = imagesx($img);
    $h = imagesy($img);
    $max = $GLOBALS['config']['image_max_w'];
    $hasAlpha = $mime !== 'image/jpeg' && image_has_alpha($img);
    if ($w > $max) {
        $nh = (int) round($h * $max / $w);
        $dst = imagecreatetruecolor($max, $nh);
        if ($hasAlpha) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $max, $nh, $w, $h);
        $img = $dst;
    }
    if ($hasAlpha) {
        imagesavealpha($img, true);
        $rel = $sub . '/' . $base . '.png';
        imagepng($img, APP_ROOT . '/public/' . $rel, 8);
    } else {
        $rel = $sub . '/' . $base . '.jpg';
        // Fond blanc pour les PNG/WebP sans transparence utile.
        $flat = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        $img = $flat;
        imageinterlace($img, true);
        imagejpeg($img, APP_ROOT . '/public/' . $rel, 82);
    }
    if (function_exists('imagewebp')) {
        imagewebp($img, $dirAbs . '/' . $base . '.webp', 80);
    }
    return $rel;
}

function image_has_alpha(GdImage $img): bool
{
    $w = imagesx($img);
    $h = imagesy($img);
    $step = max(1, (int) floor(min($w, $h) / 40));
    for ($x = 0; $x < $w; $x += $step) {
        for ($y = 0; $y < $h; $y += $step) {
            if (((imagecolorat($img, $x, $y) >> 24) & 0x7F) > 0) {
                return true;
            }
        }
    }
    return false;
}

function register_media(string $rel, string $original, string $mime): void
{
    $abs = APP_ROOT . '/public/' . $rel;
    $size = @getimagesize($abs);
    db_insert('media', [
        'path'          => $rel,
        'original_name' => mb_substr($original, 0, 200),
        'mime'          => $mime,
        'size'          => filesize($abs),
        'width'         => $size[0] ?? null,
        'height'        => $size[1] ?? null,
    ]);
}

/** Supprime un fichier envoyé (et sa variante WebP) s'il n'est plus référencé. */
function delete_upload(?string $rel): void
{
    if (!$rel || !str_starts_with($rel, 'uploads/') || str_contains($rel, '..')) {
        return;
    }
    foreach ([$rel, preg_replace('/\.(jpe?g|png)$/i', '.webp', $rel)] as $p) {
        $abs = APP_ROOT . '/public/' . $p;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
    q('DELETE FROM media WHERE path = ?', [$rel]);
}

/** Vérifie si un chemin est encore utilisé quelque part. */
function upload_in_use(string $rel): bool
{
    $checks = [
        'SELECT 1 FROM programs WHERE image = ?',
        'SELECT 1 FROM events WHERE cover = ?',
        'SELECT 1 FROM event_media WHERE path = ?',
        'SELECT 1 FROM publications WHERE cover = ?',
        'SELECT 1 FROM figures WHERE photo = ?',
        'SELECT 1 FROM settings WHERE value = ?',
    ];
    foreach ($checks as $sql) {
        if (val($sql, [$rel])) {
            return true;
        }
    }
    return false;
}

function human_size(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' o';
    if ($bytes < 1048576) return round($bytes / 1024) . ' Ko';
    return round($bytes / 1048576, 1) . ' Mo';
}
