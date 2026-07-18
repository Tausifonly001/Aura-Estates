<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $currentDir = str_replace('\\', '/', __DIR__);
    $projectRoot = $currentDir;
    while ($projectRoot !== $docRoot && !file_exists($projectRoot . '/.env') && dirname($projectRoot) !== $projectRoot) {
        $projectRoot = dirname($projectRoot);
    }
    $basePath = str_replace($docRoot, '', str_replace('\\', '/', $projectRoot));
    $baseHref = rtrim($basePath, '/') . '/';
    ?>
    <base href="<?php echo htmlspecialchars($baseHref); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aura Estates — Bespoke property management defined by clarity, materiality, and trust. Manage your properties with thoughtful, design-led service.">
    <meta property="og:title" content="<?php echo $pageTitle ?? 'Aura Estates'; ?> — Aura Estates">
    <meta property="og:description" content="Bespoke property management defined by clarity, materiality, and trust.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>/favicon.svg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>/favicon.svg">
    <title><?php echo $pageTitle ?? 'Aura Estates'; ?> — Aura Estates</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <script src="resources/js/tailwindcss.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#fcfbfa',
                        'bg-alt': '#f5f3f0',
                        surface: '#ffffff',
                        ink: '#111111',
                        'ink-secondary': '#555555',
                        muted: '#888888',
                        border: '#e8e6e1',
                        'border-light': '#f0eee9',
                        accent: '#8c7b6c',
                        'accent-hover': '#6f5f51',
                        success: '#4c6a46',
                        warning: '#a68352',
                        danger: '#964a4a',
                    },
                    fontFamily: {
                        sans: ['DM Sans', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                    },
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@300;400;500&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="resources/css/saas.css">

    <script src="resources/js/gsap.min.js"></script>
    <script src="resources/js/ScrollTrigger.min.js"></script>
    <script src="resources/js/gsap-animations.js"></script>

    <?php
    $googleMapsKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?: '';
    if (empty($googleMapsKey) || $googleMapsKey === 'YOUR_API_KEY_HERE') {
        $dotenvPath = str_replace('\\', '/', dirname(__DIR__, 3)) . '/.env';
        if (file_exists($dotenvPath)) {
            $envLines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($envLines as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] === '#') continue;
                if (strpos($line, '=') !== false) {
                    list($k, $v) = explode('=', $line, 2);
                    if (trim($k) === 'GOOGLE_MAPS_API_KEY') {
                        $v = trim(trim($v), '"\'');
                        if (!empty($v) && $v !== 'YOUR_API_KEY_HERE') {
                            $googleMapsKey = $v;
                        }
                        break;
                    }
                }
            }
        }
    }
    ?>
    <?php if (!empty($googleMapsKey)): ?>
    <script>
        (function(){var s=document.createElement('script');s.src='https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($googleMapsKey, ENT_QUOTES, 'UTF-8'); ?>&callback=__onGoogleMapsLoaded&libraries=places';s.async=true;s.defer=true;window.__onGoogleMapsLoaded=function(){window.__googleMapsReady=true;document.dispatchEvent(new Event('google-maps-ready'));};document.head.appendChild(s);})();
    </script>
    <?php endif; ?>
    <script src="resources/js/maps.js"></script>

    <style>
        .page-hero { min-height: 40vh; display: flex; align-items: flex-end; padding: 8rem 0 3rem; position: relative; overflow: hidden; }
        .page-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 40%, rgba(252,251,250,1) 100%); pointer-events: none; }
        @media (max-width: 768px) { .page-hero { min-height: 30vh; } }
        .nav-compact { top: 0.5rem !important; padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; box-shadow: 0 4px 24px rgba(28,27,24,0.05) !important; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .animate-fadeIn { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="min-h-screen bg-bg text-ink antialiased">

<nav class="fixed top-4 left-1/2 -translate-x-1/2 z-50 flex items-center justify-between px-5 sm:px-8 py-3 w-[calc(100%-1.5rem)] sm:w-[calc(100%-2rem)] max-w-5xl bg-bg/80 backdrop-blur-2xl border border-border/30 shadow-xl shadow-ink/5 rounded-2xl sm:rounded-full transition-all duration-500" id="mainNav">
    <a href="index.html" class="flex items-center gap-3 font-sans font-medium text-[0.8125rem] tracking-[0.15em] uppercase text-ink no-underline shrink-0 group">
        <span class="inline-flex items-center justify-center w-7 h-7 bg-accent text-bg text-[0.625rem] font-semibold leading-none transition-transform duration-500 group-hover:scale-105">A</span>
        <span class="hidden sm:inline">Aura Estates</span>
    </a>
    <div class="hidden md:flex items-center gap-8 list-none m-0 p-0">
        <a href="about" class="nav-link <?php echo $currentPage === 'about' ? 'text-ink' : ''; ?>">About</a>
        <a href="properties" class="nav-link <?php echo $currentPage === 'properties' ? 'text-ink' : ''; ?>">Properties</a>
        <a href="services" class="nav-link <?php echo $currentPage === 'services' ? 'text-ink' : ''; ?>">Services</a>
        <a href="blog" class="nav-link <?php echo $currentPage === 'blog' ? 'text-ink' : ''; ?>">Journal</a>
        <a href="contact" class="nav-link <?php echo $currentPage === 'contact' ? 'text-ink' : ''; ?>">Contact</a>
    </div>
    <div class="hidden md:flex items-center gap-3 shrink-0">
        <a href="login" class="btn-outline text-[0.625rem] px-4 py-2 no-underline">Sign In</a>
        <a href="register" class="btn-primary text-[0.5625rem] px-5 py-2.5">
            Get Started
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1 11L11 1M11 1H4M11 1V8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
    <button class="md:hidden p-2 text-ink bg-none border-none cursor-pointer" onclick="openMobileNav()" aria-label="Open menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg>
    </button>
</nav>

<div id="mobileNav" class="fixed inset-0 z-[100] flex flex-col p-10 transition-all duration-500 opacity-0 pointer-events-none" style="background:rgba(242,239,233,0.98);backdrop-filter:blur(30px)">
    <div class="flex items-center justify-between mb-16">
        <span class="text-xl font-serif italic text-ink/30">Aura</span>
        <button class="p-2 text-ink bg-none border-none cursor-pointer" onclick="closeMobileNav()" aria-label="Close menu">
            <svg width="20" height="20" viewBox="0 0 14 14" fill="none"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
        </button>
    </div>
    <ul class="flex flex-col gap-8 list-none">
        <li><a href="about" onclick="closeMobileNav()" class="font-sans font-medium text-4xl text-ink/70 hover:text-ink no-underline transition-colors duration-300">About</a></li>
        <li><a href="properties" onclick="closeMobileNav()" class="font-sans font-medium text-4xl text-ink/70 hover:text-ink no-underline transition-colors duration-300">Properties</a></li>
        <li><a href="services" onclick="closeMobileNav()" class="font-sans font-medium text-4xl text-ink/70 hover:text-ink no-underline transition-colors duration-300">Services</a></li>
        <li><a href="blog" onclick="closeMobileNav()" class="font-sans font-medium text-4xl text-ink/70 hover:text-ink no-underline transition-colors duration-300">Journal</a></li>
        <li><a href="contact" onclick="closeMobileNav()" class="font-sans font-medium text-4xl text-ink/70 hover:text-ink no-underline transition-colors duration-300">Contact</a></li>
    </ul>
    <div class="mt-auto pt-10 flex gap-4">
        <a href="login" onclick="closeMobileNav()" class="btn-outline flex-1 text-center">Sign In</a>
        <a href="register" onclick="closeMobileNav()" class="btn-primary flex-1 text-center">Get Started</a>
    </div>
</div>

<script>
function openMobileNav() { document.getElementById('mobileNav').classList.remove('opacity-0','pointer-events-none'); document.body.style.overflow = 'hidden'; }
function closeMobileNav() { document.getElementById('mobileNav').classList.add('opacity-0','pointer-events-none'); document.body.style.overflow = ''; }
</script>

<main>
