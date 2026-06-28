<?php
// Placeholder image generator — SVG output, no GD required
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');

$seed = preg_replace('/[^a-z0-9-]/', '', $_GET['seed'] ?? 'default');
$w = (int)($_GET['w'] ?? 800);
$h = (int)($_GET['h'] ?? 600);
$text = htmlspecialchars($_GET['text'] ?? '', ENT_QUOTES);
$textLen = strlen($text) ?: 1;

$hash = abs(crc32($seed));

$palettes = [
    ['#2d2823','#4a3f36','#e8e5db'],
    ['#e8e5db','#d9d0c1','#3a322c'],
    ['#f0ece3','#e3dcd0','#2d2823'],
    ['#3a322c','#1e1b18','#faf8f4'],
    ['#564b40','#6b5b4b','#f0ece3'],
];
$p = $palettes[$hash % count($palettes)];
$bg1 = $p[0];
$bg2 = $p[1];
$fg = $p[2];
$highlight = $p[1];

$bg = $hash % 2 === 0 ? $bg1 : $bg2;
$accent = sprintf('#%s', substr(md5($seed), 0, 6));

// Grid pattern overlay
$gridSize = max(20, min($w, $h) / 15);

?><svg xmlns="http://www.w3.org/2000/svg" width="<?=$w?>" height="<?=$h?>" viewBox="0 0 <?=$w?> <?=$h?>">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="<?=$bg1?>"/>
      <stop offset="100%" stop-color="<?=$bg2?>"/>
    </linearGradient>
    <pattern id="grid" width="<?=$gridSize?>" height="<?=$gridSize?>" patternUnits="userSpaceOnUse">
      <path d="M <?=$gridSize?> 0 L 0 0 0 <?=$gridSize?>" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="0.5"/>
    </pattern>
  </defs>

  <!-- Background -->
  <rect width="<?=$w?>" height="<?=$h?>" fill="url(#bg)"/>

  <!-- Diagonal stripe accent -->
  <g opacity="0.06">
    <line x1="0" y1="<?=$h?>" x2="<?=$w?>" y2="0" stroke="<?=$fg?>" stroke-width="<?=max(2, min($w,$h)/100)?>"/>
    <line x1="0" y1="<?=$h*0.8?>" x2="<?=$w*0.8?>" y2="0" stroke="<?=$fg?>" stroke-width="<?=max(1, min($w,$h)/200)?>"/>
    <line x1="<?=$w*0.2?>" y1="<?=$h?>" x2="<?=$w?>" y2="<?=$h*0.2?>" stroke="<?=$fg?>" stroke-width="<?=max(1, min($w,$h)/200)?>"/>
  </g>

  <!-- Grid overlay -->
  <rect width="<?=$w?>" height="<?=$h?>" fill="url(#grid)"/>

  <!-- Accent bar top -->
  <rect x="0" y="0" width="<?=$w?>" height="<?=max(2, min($w,$h)/300)?>" fill="<?=$highlight?>" opacity="0.3"/>

  <!-- Large geometric shape -- architectural motif -->
  <g opacity="0.07">
    <circle cx="<?=$w*0.5?>" cy="<?=$h*0.4?>" r="<?=min($w,$h)*0.3?>" fill="none" stroke="<?=$fg?>" stroke-width="1"/>
    <rect x="<?=$w*0.5-min($w,$h)*0.2?>" y="<?=$h*0.4-min($w,$h)*0.2?>" width="<?=min($w,$h)*0.4?>" height="<?=min($w,$h)*0.4?>" fill="none" stroke="<?=$fg?>" stroke-width="1" transform="rotate(45 <?=$w*0.5?> <?=$h*0.4?>)"/>
  </g>

  <!-- Text -->
  <g font-family="system-ui, -apple-system, sans-serif" text-anchor="middle">
    <?php if ($text): ?>
    <text x="<?=$w/2?>" y="<?=$h/2+$h*0.08?>" font-size="<?=max(14, min($w/$textLen*1.2, $w/15))?>" font-weight="300" letter-spacing="0.05em" fill="rgba(255,255,255,0.85)"><?=$text?></text>
    <?php endif; ?>
    <text x="<?=$w/2?>" y="<?=$h*0.92?>" font-size="<?=max(9, min($w,$h)/40)?>" font-weight="400" letter-spacing="0.15em" fill="rgba(255,255,255,0.25)">AURA ESTATES</text>
  </g>

  <!-- Bottom accent -->
  <rect x="0" y="<?=$h-max(2, min($w,$h)/300)?>" width="<?=$w?>" height="<?=max(2, min($w,$h)/300)?>" fill="<?=$highlight?>" opacity="0.15"/>
</svg>
