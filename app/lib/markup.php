<?php
/**
 * Mini-syntaxe de mise en forme pour les textes longs saisis dans l'admin.
 * Sûre : tout est échappé avant transformation.
 *
 *   ## Titre          → <h3>
 *   **gras**  *italique*
 *   [texte](https://lien)
 *   - élément de liste
 *   ligne vide         → nouveau paragraphe
 */

function markup(?string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", trim((string) $text));
    if ($text === '') {
        return '';
    }
    $blocks = preg_split('/\n{2,}/', $text);
    $html = '';
    foreach ($blocks as $block) {
        $lines = explode("\n", $block);
        $buffer = [];
        $list = [];
        $flushP = function () use (&$buffer, &$html) {
            if ($buffer) {
                $html .= '<p>' . implode('<br>', $buffer) . '</p>';
                $buffer = [];
            }
        };
        $flushL = function () use (&$list, &$html) {
            if ($list) {
                $html .= '<ul>' . implode('', array_map(fn($li) => "<li>$li</li>", $list)) . '</ul>';
                $list = [];
            }
        };
        foreach ($lines as $line) {
            $line = rtrim($line);
            if (preg_match('/^#{2,3}\s+(.+)$/', $line, $m)) {
                $flushP();
                $flushL();
                $html .= '<h3>' . markup_inline($m[1]) . '</h3>';
            } elseif (preg_match('/^[-*•]\s+(.+)$/', $line, $m)) {
                $flushP();
                $list[] = markup_inline($m[1]);
            } else {
                $flushL();
                $buffer[] = markup_inline($line);
            }
        }
        $flushP();
        $flushL();
    }
    return $html;
}

function markup_inline(string $s): string
{
    $s = e($s);
    $s = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);
    $s = preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/u', '<em>$1</em>', $s);
    $s = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+|mailto:[^\s)]+|\/[^\s)]*)\)/u', function ($m) {
        $external = str_starts_with($m[2], 'http');
        return '<a href="' . $m[2] . '"' . ($external ? ' target="_blank" rel="noopener"' : '') . '>' . $m[1] . '</a>';
    }, $s);
    return $s;
}

/** Retire la syntaxe (pour extraits et meta descriptions). */
function markup_strip(string $s): string
{
    $s = preg_replace('/^#{2,3}\s+/m', '', $s);
    $s = preg_replace('/^[-*•]\s+/m', '', $s);
    $s = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $s);
    return str_replace(['**', '*'], '', $s);
}
