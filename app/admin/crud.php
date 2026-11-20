<?php
/**
 * CRUD générique piloté par admin_resources().
 */

function crud_dispatch(string $key, array $res, string $a, int $id): array
{
    $table = $res['table'];

    switch ($a) {
        case 'new':
        case 'edit':
            $row = $a === 'edit' ? one("SELECT * FROM $table WHERE id = ?", [$id]) : null;
            if ($a === 'edit' && !$row) {
                flash('error', 'Élément introuvable.');
                redirect(admin_url($key));
            }
            $errors = [];
            if (is_post()) {
                [$values, $errors] = crud_collect($res, $row);
                if (!$errors) {
                    if (!empty($res['slug'])) {
                        $values['slug'] = unique_slug($table, ($values['event_date'] ?? '') ? substr($values['event_date'], 0, 4) . '-' . $values[$res['slug']] : $values[$res['slug']], $row['id'] ?? null);
                    }
                    if ($row) {
                        $values['updated_at'] = date('Y-m-d H:i:s');
                        db_update($table, $values, (int) $row['id']);
                        $newId = (int) $row['id'];
                    } else {
                        if (!empty($res['sortable'])) {
                            $values['sort_order'] = (int) val("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM $table");
                        }
                        $newId = db_insert($table, $values);
                    }
                    // Nettoyage des images remplacées
                    foreach ($res['fields'] as $f => $def) {
                        if ($def[1] === 'image' && $row && $row[$f] && $row[$f] !== $values[$f] && !upload_in_use($row[$f])) {
                            delete_upload($row[$f]);
                        }
                    }
                    flash('success', $row ? 'Modifications enregistrées.' : 'Élément créé.');
                    $next = ($_POST['_next'] ?? '') === 'stay' ? admin_url($key, ['a' => 'edit', 'id' => $newId]) : admin_url($key);
                    if ($key === 'events' && !$row) {
                        $next = admin_url($key, ['a' => 'media', 'id' => $newId]);
                        flash('success', 'Ajoutez maintenant les photos et vidéos de l\'événement.');
                    }
                    redirect($next);
                }
                $row = array_merge($row ?? [], $values);
            }
            return ['form', compact('key', 'res', 'row', 'errors')];

        case 'delete':
            if (!is_post()) redirect(admin_url($key));
            $row = one("SELECT * FROM $table WHERE id = ?", [$id]);
            if ($row) {
                $files = [];
                foreach ($res['fields'] as $f => $def) {
                    if ($def[1] === 'image' && $row[$f]) $files[] = $row[$f];
                }
                if ($key === 'events') {
                    foreach (all('SELECT path FROM event_media WHERE event_id = ?', [$id]) as $m) $files[] = $m['path'];
                }
                db_delete($table, $id);
                foreach ($files as $path) {
                    if ($path && !upload_in_use($path)) delete_upload($path);
                }
                flash('success', 'Élément supprimé.');
            }
            redirect(admin_url($key));

        case 'toggle':
            if (is_post()) {
                q("UPDATE $table SET published = 1 - published WHERE id = ?", [$id]);
                if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                    json_out(['ok' => true, 'published' => (int) val("SELECT published FROM $table WHERE id = ?", [$id])]);
                }
            }
            redirect(admin_url($key));

        case 'duplicate':
            if (is_post() && ($row = one("SELECT * FROM $table WHERE id = ?", [$id]))) {
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $first = array_key_first($res['fields']);
                $row[$first] = $row[$first] . ' (copie)';
                $row['published'] = 0;
                if (isset($row['slug'])) $row['slug'] = unique_slug($table, $row[$res['slug']]);
                $newId = db_insert($table, $row);
                flash('success', 'Copie créée (non publiée).');
                redirect(admin_url($key, ['a' => 'edit', 'id' => $newId]));
            }
            redirect(admin_url($key));

        case 'reorder':
            if (is_post() && !empty($res['sortable'])) {
                $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
                db()->beginTransaction();
                foreach ($ids as $i => $rid) {
                    q("UPDATE $table SET sort_order = ? WHERE id = ?", [$i + 1, $rid]);
                }
                db()->commit();
                json_out(['ok' => true]);
            }
            json_out(['ok' => false], 400);

        case 'media':
        case 'media_upload':
        case 'media_video':
        case 'media_update':
        case 'media_delete':
        case 'media_reorder':
        case 'media_cover':
            if ($key !== 'events') redirect(admin_url($key));
            return event_media_dispatch($a, $id);

        default:
            $where = [];
            $params = [];
            $filters = [];
            foreach ($res['filters'] ?? [] as $col => $opts) {
                $v = (string) ($_GET[$col] ?? '');
                if ($v !== '' && isset($opts[$v])) {
                    $where[] = "$col = ?";
                    $params[] = $v;
                    $filters[$col] = $v;
                }
            }
            $search = trim((string) ($_GET['q'] ?? ''));
            if ($search !== '' && !empty($res['search'])) {
                $where[] = '(' . implode(' OR ', array_map(fn($c) => "$c LIKE ?", $res['search'])) . ')';
                foreach ($res['search'] as $_) $params[] = '%' . $search . '%';
            }
            $sql = "SELECT * FROM $table" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $res['order'];
            $rows = all($sql, $params);
            if ($key === 'events') {
                $counts = [];
                foreach (all('SELECT event_id, COUNT(*) n FROM event_media GROUP BY event_id') as $c) $counts[$c['event_id']] = $c['n'];
                foreach ($rows as &$r) $r['media'] = $counts[$r['id']] ?? 0;
                unset($r);
            }
            return ['list', compact('key', 'res', 'rows', 'filters', 'search')];
    }
}

/** Lit et valide les champs envoyés. */
function crud_collect(array $res, ?array $row): array
{
    $values = [];
    $errors = [];
    foreach ($res['fields'] as $f => [$label, $type]) {
        $opt = $res['fields'][$f][2] ?? [];
        if ($type === 'image') {
            $values[$f] = $row[$f] ?? null;
            if (!empty($_POST['remove_' . $f])) {
                $values[$f] = null;
            }
            if (!empty($_FILES['f_' . $f]['name'])) {
                try {
                    $values[$f] = handle_upload($_FILES['f_' . $f]);
                } catch (RuntimeException $e) {
                    $errors[$f] = $e->getMessage();
                }
            }
            continue;
        }
        if ($type === 'checkbox') {
            $values[$f] = empty($_POST[$f]) ? 0 : 1;
            continue;
        }
        $v = trim((string) ($_POST[$f] ?? ''));
        if (!empty($opt['required']) && $v === '') {
            $errors[$f] = 'Ce champ est obligatoire.';
        }
        if ($v !== '') {
            if ($type === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) $errors[$f] = 'Date invalide.';
            if ($type === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $errors[$f] = 'Adresse e-mail invalide.';
            if ($type === 'url') {
                if (!preg_match('#^https?://#i', $v)) $v = 'https://' . $v;
                if (!filter_var($v, FILTER_VALIDATE_URL)) $errors[$f] = 'Adresse invalide.';
            }
            if ($type === 'select' && !isset($opt['options'][$v])) $errors[$f] = 'Choix invalide.';
            if ($type === 'number' && !is_numeric($v)) $errors[$f] = 'Nombre attendu.';
        }
        $values[$f] = $v === '' && in_array($type, ['date', 'number'], true) ? null : $v;
    }
    return [$values, $errors];
}

/* ------------------------------------------------------------------ Galerie d'un événement */
function event_media_dispatch(string $a, int $id): array
{
    $event = one('SELECT * FROM events WHERE id = ?', [$id]);
    if (!$event) {
        flash('error', 'Événement introuvable.');
        redirect(admin_url('events'));
    }
    $back = admin_url('events', ['a' => 'media', 'id' => $id]);
    $isAjax = !empty($_SERVER['HTTP_X_CSRF_TOKEN']);

    if ($a === 'media_upload' && is_post()) {
        $files = $_FILES['files'] ?? null;
        $done = 0;
        $errs = [];
        $order = (int) val('SELECT COALESCE(MAX(sort_order), 0) FROM event_media WHERE event_id = ?', [$id]);
        if ($files && is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                if (!$name) continue;
                try {
                    $path = handle_upload(['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]], 'any');
                    $kind = preg_match('/\.(mp4|webm)$/', $path) ? 'video' : 'image';
                    db_insert('event_media', ['event_id' => $id, 'kind' => $kind, 'path' => $path, 'caption' => '', 'sort_order' => ++$order]);
                    $done++;
                } catch (RuntimeException $e) {
                    $errs[] = $name . ' : ' . $e->getMessage();
                }
            }
        }
        if ($isAjax) json_out(['ok' => $done > 0, 'done' => $done, 'errors' => $errs]);
        foreach ($errs as $e) flash('error', $e);
        if ($done) flash('success', $done . ' média' . ($done > 1 ? 's' : '') . ' ajouté' . ($done > 1 ? 's' : '') . '.');
        redirect($back);
    }
    if ($a === 'media_video' && is_post()) {
        $url = trim((string) ($_POST['video_url'] ?? ''));
        if (!video_embed($url)) {
            flash('error', 'Lien non reconnu : collez l\'adresse d\'une vidéo YouTube ou Vimeo.');
        } else {
            $order = (int) val('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM event_media WHERE event_id = ?', [$id]);
            db_insert('event_media', ['event_id' => $id, 'kind' => 'video', 'video_url' => $url, 'caption' => trim((string) ($_POST['caption'] ?? '')), 'sort_order' => $order]);
            flash('success', 'Vidéo ajoutée.');
        }
        redirect($back);
    }
    if ($a === 'media_update' && is_post()) {
        foreach ((array) ($_POST['caption'] ?? []) as $mid => $cap) {
            q('UPDATE event_media SET caption = ? WHERE id = ? AND event_id = ?', [mb_substr(trim((string) $cap), 0, 300), (int) $mid, $id]);
        }
        flash('success', 'Légendes enregistrées.');
        redirect($back);
    }
    if ($a === 'media_delete' && is_post()) {
        $m = one('SELECT * FROM event_media WHERE id = ? AND event_id = ?', [(int) ($_POST['media_id'] ?? 0), $id]);
        if ($m) {
            db_delete('event_media', (int) $m['id']);
            if ($m['path'] && !upload_in_use($m['path'])) delete_upload($m['path']);
            flash('success', 'Média supprimé.');
        }
        redirect($back);
    }
    if ($a === 'media_cover' && is_post()) {
        $m = one("SELECT * FROM event_media WHERE id = ? AND event_id = ? AND kind = 'image'", [(int) ($_POST['media_id'] ?? 0), $id]);
        if ($m) {
            q('UPDATE events SET cover = ? WHERE id = ?', [$m['path'], $id]);
            flash('success', 'Image de couverture mise à jour.');
        }
        redirect($back);
    }
    if ($a === 'media_reorder' && is_post()) {
        foreach (array_map('intval', (array) ($_POST['ids'] ?? [])) as $i => $mid) {
            q('UPDATE event_media SET sort_order = ? WHERE id = ? AND event_id = ?', [$i + 1, $mid, $id]);
        }
        json_out(['ok' => true]);
    }

    return ['event_media', ['event' => $event, 'media' => get_event_media($id)]];
}
