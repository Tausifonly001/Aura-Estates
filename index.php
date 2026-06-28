<?php
try {
    require_once __DIR__ . '/src/helpers.php';
} catch (Throwable $e) {
    // DB down — serve index.html as-is
    readfile(__DIR__ . '/index.html');
    exit;
}

$html = file_get_contents(__DIR__ . '/index.html');
if ($html === false) {
    http_response_code(500);
    echo 'Failed to load page.';
    exit;
}

// Dynamic content replacements
$html = str_replace(
    ['Bespoke property management defined by clarity',
     'Spaces crafted through warmth, materiality, and clarity.',
     'About the Studio',
     'Founded in 2020, Aura Estates is a deliberately focused practice. Every property is managed with the same thinking that shapes the first sketch — all the way through to the last detail on site.',
     'We founded Aura Estates with the belief that property management should feel seamless, transparent, and deeply human. Every relationship is approached with clarity, warmth, and careful attention to detail — creating experiences that feel refined, functional, and made to last.',
     'The Aura Estates Team'],
    [content('home','hero','tagline','Bespoke property management defined by clarity'),
     content('home','hero','body','Spaces crafted through warmth, materiality, and clarity.'),
     content('home','about','heading','About the Studio'),
     content('home','about','body','Founded in 2020, Aura Estates is a deliberately focused practice. Every property is managed with the same thinking that shapes the first sketch — all the way through to the last detail on site.'),
     content('home','about','quote','We founded Aura Estates with the belief that property management should feel seamless, transparent, and deeply human. Every relationship is approached with clarity, warmth, and careful attention to detail — creating experiences that feel refined, functional, and made to last.'),
     content('home','about','attribution','The Aura Estates Team')],
    $html
);

echo $html;
