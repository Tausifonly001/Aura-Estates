<?php
require_once __DIR__ . '/src/config/auth.php';
require_once __DIR__ . '/src/core/CsrfProtection.php';
require_once __DIR__ . '/src/core/Response.php';
require_once __DIR__ . '/src/core/Validator.php';
require_once __DIR__ . '/src/core/AuditLogger.php';

Auth::startSession();
CsrfProtection::generate();

// Calculate base path prefix dynamically
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$projectRoot = str_replace('\\', '/', __DIR__);
$basePath = str_replace($docRoot, '', $projectRoot);
$basePrefix = rtrim($basePath, '/');

if (isset($_SESSION['user_id'])) {
    header('Location: ' . Auth::getDashboardUrl());
    exit;
}

$message = '';
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        $message = 'Invalid security token. Please refresh and try again.';
    } else {
        try {
            $user = Auth::login($_POST['email'], $_POST['password']);
            if ($user) {
                AuditLogger::log('login', 'user', $user['id'], "User logged in: {$user['email']}");
                header('Location: ' . Auth::getDashboardUrl($user['role']));
                exit;
            }
            $message = 'Invalid email or password.';
        } catch (Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $message = 'System temporarily unavailable. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Aura Estates</title>
    <script src="resources/js/tailwindcss.js"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['DM Sans', 'sans-serif'], display: ['Cormorant Garamond', 'serif'] } } } }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <script src="resources/js/gsap.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; overflow: hidden; height: 100vh; background: #fcfbfa; }
        .hero-bg { position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1600&q=80') center/cover no-repeat; filter: saturate(0.85); z-index: -10; }
        .hero-bg::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(252,251,250,0.95) 30%, rgba(252,251,250,0.65) 70%, rgba(252,251,250,0.9) 100%); }
        .hero-overlay { position: absolute; inset: 0; background: radial-gradient(ellipse at 20% 50%, rgba(140,123,108,0.08) 0%, transparent 60%), radial-gradient(ellipse at 80% 20%, rgba(140,123,108,0.04) 0%, transparent 40%); z-index: -9; }
        .glass-panel { background: rgba(255,255,255,0.7); backdrop-filter: blur(40px); border: 1px solid rgba(28,27,24,0.06); box-shadow: 0 32px 120px -24px rgba(28,27,24,0.05); }
        .glass-panel-light { background: rgba(255,255,255,0.4); backdrop-filter: blur(20px); border: 1px solid rgba(28,27,24,0.03); }
        .inp { background: transparent; border: none; border-bottom: 1px solid rgba(28,27,24,0.1); padding: 0.875rem 0; font-size: 0.875rem; color: #111111; outline: none; width: 100%; transition: border-color 0.4s, padding 0.4s; }
        .inp:focus { border-bottom-color: #8c7b6c; padding-left: 0.5rem; }
        .inp::placeholder { color: rgba(28,27,24,0.2); font-weight: 300; }
        .inp ~ label { position: absolute; left: 0; top: 0.875rem; font-size: 0.875rem; color: #555555; pointer-events: none; transition: 0.4s; }
        .inp:focus ~ label, .inp:not(:placeholder-shown) ~ label { top: -1.25rem; font-size: 0.5625rem; color: #8c7b6c; letter-spacing: 0.15em; text-transform: uppercase; }
        .btn-primary { position: relative; overflow: hidden; background: #8c7b6c; color: #ffffff; border: none; padding: 0.8rem 2rem; font-size: 0.6875rem; letter-spacing: 0.15em; text-transform: uppercase; cursor: pointer; transition: transform 0.4s, box-shadow 0.4s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 40px -8px rgba(140,123,108,0.15); }
        .btn-primary .ripple { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); transform: scale(0); pointer-events: none; }
        .line-reveal { width: 0; height: 1px; background: rgba(28,27,24,0.08); transition: width 1.2s cubic-bezier(0.16,1,0.3,1); }
        .line-reveal.show { width: 100%; }
        .err-msg { font-size: 0.6875rem; color: #964a4a; padding: 0.75rem 1rem; background: rgba(150,74,74,0.03); border-left: 2px solid rgba(150,74,74,0.3); }
        .morph-circle { position: absolute; border-radius: 50%; pointer-events: none; border: 1px solid rgba(28,27,24,0.04); z-index: -8; }
        .slide-link { position: relative; text-decoration: none; color: #8c7b6c; font-size: 0.6875rem; transition: color 0.4s; }
        .slide-link:hover { color: #6f5f51; }
        .slide-link::after { content: ''; position: absolute; left: 0; bottom: -2px; width: 0; height: 1px; background: #8c7b6c; transition: width 0.4s; }
        .slide-link:hover::after { width: 100%; }
        .glow { box-shadow: 0 0 80px -20px rgba(140,123,108,0.03); }

        /* Layout Fallbacks for CDN Failures */
        .relative { position: relative !important; }
        .z-10 { z-index: 10 !important; }
        .z-20 { z-index: 20 !important; }
        .fixed { position: fixed !important; }
        .top-8 { top: 2rem !important; }
        .left-8 { left: 2rem !important; }
        .flex { display: flex !important; }
        .h-screen { height: 100vh !important; }
        .w-screen { width: 100vw !important; }

        @media (max-width: 768px) { .hero-panel { display: none; } .glass-panel { background: rgba(255,255,255,0.9); } }
    </style>
</head>
<body>
<div class="hero-bg"></div>
<div class="hero-overlay"></div>

<div class="morph-circle" style="width:55vmin;height:55vmin;top:-10%;right:-5%;border-color:rgba(28,27,24,0.03);animation:float1 30s ease-in-out infinite;"></div>
<div class="morph-circle" style="width:35vmin;height:35vmin;bottom:5%;left:-8%;border-color:rgba(28,27,24,0.02);animation:float2 25s ease-in-out infinite reverse;"></div>

<div class="fixed top-8 left-8 z-20">
    <a href="index.html" class="flex items-center gap-3 no-underline group">
        <span class="inline-flex items-center justify-center w-8 h-8 bg-[#8c7b6c]/5 text-[#8c7b6c] text-[0.625rem] font-semibold border border-[#8c7b6c]/10 group-hover:bg-[#8c7b6c]/10 transition-all duration-500">A</span>
        <span class="font-display text-lg text-[#111111]/40 italic group-hover:text-[#111111]/60 transition-all duration-500">Aura</span>
    </a>
</div>

<div class="relative z-10 flex h-screen w-screen items-center justify-center p-6 sm:p-12 lg:p-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 w-full max-w-6xl h-full items-center">
        
        <!-- Left Panel: Editorial Context Column (7 Columns) -->
        <div class="lg:col-span-7 hidden lg:flex flex-col justify-between h-[75%] pr-12 relative after:absolute after:right-0 after:top-1/4 after:h-1/2 after:w-[1px] after:bg-[#111111]/5">
            <div></div>
            <div id="heroMain">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-6 h-[1px] bg-[#8c7b6c]/40"></span>
                    <span class="font-mono text-[0.5625rem] tracking-[0.25em] text-[#8c7b6c] uppercase">Est. 2020 // Resident Portal</span>
                </div>
                <div class="font-display text-[5.5rem] leading-[0.9] text-[#111111] font-light tracking-tight mt-4">Welcome<br><span class="italic text-[#8c7b6c]">Back</span></div>
                <p class="text-xs text-[#555555]/80 mt-6 max-w-sm font-light leading-relaxed">Access the secure portal for refined property management, amenity bookings, and real-time maintenance dispatch.</p>
                <div class="font-mono text-[0.5rem] text-[#888888] tracking-widest mt-8">LAT. 52.5200° N // LONG. 13.4050° E</div>
            </div>
            
            <!-- Sleek stats row -->
            <div class="flex gap-12 border-t border-[rgba(28,27,24,0.06)] pt-8" id="stats">
                <div>
                    <div class="font-serif italic font-light text-2xl text-[#8c7b6c] counter" data-target="150">0</div>
                    <div class="text-[0.5rem] tracking-[0.2em] uppercase text-[#888888] mt-1">Properties</div>
                </div>
                <div>
                    <div class="font-serif italic font-light text-2xl text-[#8c7b6c] counter" data-target="2400">0</div>
                    <div class="text-[0.5rem] tracking-[0.2em] uppercase text-[#888888] mt-1">Residents</div>
                </div>
                <div>
                    <div class="font-serif italic font-light text-2xl text-[#8c7b6c] counter" data-target="12">0</div>
                    <div class="text-[0.5rem] tracking-[0.2em] uppercase text-[#888888] mt-1">Years</div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Frosted Login Panel (5 Columns) -->
        <div class="w-full lg:col-span-5 flex items-center justify-center p-4">
            <div id="formWrap" class="glass-panel w-full max-w-sm p-10 rounded-[2rem] glow">
                <div id="formHead">
                    <div class="font-mono text-[0.5625rem] tracking-[0.2em] text-[#8c7b6c] uppercase mb-2">Access Portal</div>
                    <h1 class="font-sans text-2xl font-light text-[#111111]">Sign in to <span class="font-medium">dashboard</span></h1>
                </div>

                <?php if ($message): ?>
                    <div class="err-msg mt-4" id="errMsg"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="post" class="mt-8 space-y-7" id="loginForm">
                    <input type="hidden" name="action" value="login">
                    <?php echo CsrfProtection::field(); ?>
                    <div class="relative">
                        <input type="email" name="email" id="email" class="inp" placeholder=" " required>
                        <label for="email">Email</label>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="inp" placeholder=" " required>
                        <label for="password">Password</label>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" class="w-3.5 h-3.5 accent-[#8c7b6c]">
                            <span class="text-[0.625rem] text-[#555555] group-hover:text-[#111111] transition-colors">Remember me</span>
                        </label>
                        <a href="<?php echo $basePrefix; ?>/forgot-password" class="slide-link">Forgot?</a>
                    </div>
                    <button type="submit" class="btn-primary w-full rounded-xl py-3.5" id="submitBtn">
                        <span class="relative z-10">Sign In</span>
                    </button>
                </form>

                <div class="relative my-7">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[rgba(28,27,24,0.06)]"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white/90 px-4 text-[0.5rem] uppercase tracking-[0.2em] text-[#888888]">or</span></div>
                </div>

                <button disabled class="w-full bg-transparent border border-[rgba(28,27,24,0.06)] py-3 text-[0.75rem] text-[#555555]/30 cursor-not-allowed flex items-center justify-center gap-3 rounded-xl">
                    <svg width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" opacity="0.8"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" opacity="0.8"/><path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" opacity="0.8"/><path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" opacity="0.8"/></svg>
                    Sign in with Google
                </button>

                <p class="text-center mt-7 text-[0.625rem] text-[#555555]">
                    No account? <a href="<?php echo $basePrefix; ?>/register" class="slide-link font-medium text-[#8c7b6c]">Create one</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
gsap.timeline({defaults:{ease:'power3.out'}})
  .from('#heroMain', {opacity:0, y:60, duration:1.4, delay:0.2})
  .from('.line-reveal', {width:'0%', duration:1.2, ease:'power4.out'}, '-=0.6')
  .from('#stats', {opacity:0, y:30, duration:1}, '-=0.4')
  .from('#formWrap', {opacity:0, y:40, duration:1.2}, '-=0.8')
  .from('#formHead', {opacity:0, y:20, duration:0.8}, '-=0.6');

document.querySelectorAll('.counter').forEach(el => {
    const target = parseInt(el.dataset.target);
    gsap.to(el, {textContent: target, duration: 3, ease: 'power2.out', snap: {textContent: 1}, onUpdate: function() {
        const val = Math.round(parseFloat(el.textContent) || 0);
        el.textContent = val < target ? val + Math.max(1, Math.floor(target/80)) : target;
    }});
});

document.getElementById('submitBtn')?.addEventListener('click', function(e) {
    const rect = this.getBoundingClientRect();
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (e.clientX - rect.left - size/2) + 'px';
    ripple.style.top = (e.clientY - rect.top - size/2) + 'px';
    this.appendChild(ripple);
    gsap.fromTo(ripple, {scale:0, opacity:1}, {scale:4, opacity:0, duration:0.7, ease:'power2.out', onComplete:()=>ripple.remove()});
});
</script>
</body>
</html>
