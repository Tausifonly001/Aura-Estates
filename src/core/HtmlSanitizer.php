<?php
class HtmlSanitizer {
    private static $allowedTags = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'width', 'height'],
        'blockquote' => [],
        'code' => [],
        'pre' => [],
        'hr' => [],
    ];

    private static $allowedSchemes = ['http', 'https', 'mailto'];

    public static function sanitize($html) {
        if (empty($html)) return '';
        $html = strip_tags($html, '<' . implode('><', array_keys(self::$allowedTags)) . '>');
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*\S+/i', '', $html);
        $html = preg_replace('/javascript\s*:/i', '', $html);
        $html = preg_replace('/vbscript\s*:/i', '', $html);
        $html = preg_replace('/data\s*:/i', '', $html);
        foreach (self::$allowedTags as $tag => $attrs) {
            if (!empty($attrs)) {
                $html = preg_replace_callback(
                    '/<' . preg_quote($tag) . '\s[^>]*>/i',
                    function ($matches) use ($tag, $attrs) {
                        return self::sanitizeTagAttributes($matches[0], $tag, $attrs);
                    },
                    $html
                );
            }
        }
        return $html;
    }

    private static function sanitizeTagAttributes($tag, $tagName, $allowedAttrs) {
        preg_match_all('/(\w+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(\S+)))?/', $tag, $matches, PREG_SET_ORDER);
        $result = '<' . $tagName;
        foreach ($matches as $match) {
            $attrName = strtolower($match[1]);
            $attrValue = $match[2] ?? $match[3] ?? $match[4] ?? '';
            if (in_array($attrName, $allowedAttrs)) {
                if ($attrName === 'href' || $attrName === 'src') {
                    $scheme = parse_url($attrValue, PHP_URL_SCHEME);
                    if ($scheme && !in_array(strtolower($scheme), self::$allowedSchemes)) {
                        continue;
                    }
                    $attrValue = htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8');
                } else {
                    $attrValue = htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8');
                }
                $result .= ' ' . $attrName . '="' . $attrValue . '"';
            }
        }
        $result .= '>';
        return $result;
    }
}
