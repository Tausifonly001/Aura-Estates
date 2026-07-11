<?php
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/config/auth.php';
require_once __DIR__ . '/src/core/CsrfProtection.php';

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
if ($_POST) {
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        $message = 'Invalid security token. Please refresh and try again.';
    } else {
    try {
    $db = (new Database())->getConnection();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $message = 'All fields are required.';
    } elseif (!Auth::validatePassword($password)) {
        $message = Auth::getPasswordRequirements();
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $message = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = 'tenant'");
            $roleStmt->execute();
            $tenantRoleId = $roleStmt->fetchColumn();
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, role_id) VALUES (?, ?, ?, 'tenant', ?)");
            if ($stmt->execute([$name, $email, $hash, $tenantRoleId])) {
                $uid = (int)$db->lastInsertId();
                Auth::establishSession([
                    'id' => $uid,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'tenant',
                    'role_id' => $tenantRoleId
                ]);
                header('Location: ' . Auth::getDashboardUrl('tenant'));
                exit;
            }
            $message = 'Registration failed.';
        }
    }
    } catch (Throwable $e) {
        error_log('Registration error: ' . $e->getMessage());
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
    <title>Create Account — Aura Estates</title>
    <script src="resources/js/tailwindcss.js"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['DM Sans', 'sans-serif'], display: ['Cormorant Garamond', 'serif'], mono: ['JetBrains Mono', 'monospace'] } } } }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <script src="resources/js/gsap.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; overflow: hidden; height: 100vh; background: #fcfbfa; }
        .geo-bg { position: absolute; inset: 0; overflow: hidden; }
        .geo-bg::before { content: ''; position: absolute; inset: 0; background: repeating-linear-gradient(0deg, transparent, transparent 80px, rgba(140,123,108,0.015) 80px, rgba(140,123,108,0.015) 81px), repeating-linear-gradient(90deg, transparent, transparent 80px, rgba(140,123,108,0.015) 80px, rgba(140,123,108,0.015) 81px); }
        .geo-shape { position: absolute; pointer-events: none; }
        .card-main { background: #ffffff; box-shadow: 0 32px 120px -32px rgba(140,123,108,0.08); border: 1px solid #e8e6e1; }
        .inp { background: transparent; border: none; border-bottom: 1.5px solid #e8e6e1; padding: 0.875rem 0; font-size: 0.875rem; color: #111111; outline: none; width: 100%; transition: border-color 0.3s, padding 0.3s; }
        .inp:focus { border-bottom-color: #8c7b6c; padding-left: 0.5rem; }
        .inp::placeholder { color: #888888; font-weight: 300; }
        .inp ~ label { position: absolute; left: 0; top: 0.875rem; font-size: 0.875rem; color: #888888; pointer-events: none; transition: 0.3s; }
        .inp:focus ~ label, .inp:not(:placeholder-shown) ~ label { top: -1.25rem; font-size: 0.5625rem; color: #8c7b6c; letter-spacing: 0.08em; text-transform: uppercase; }
        .btn-accent { position: relative; overflow: hidden; background: #8c7b6c; color: #ffffff; border: none; padding: 0.8rem 2rem; font-size: 0.6875rem; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s; }
        .btn-accent:hover { transform: translateY(-2px); box-shadow: 0 12px 40px -8px rgba(140,123,108,0.2); }
        .btn-accent .ripple { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); transform: scale(0); pointer-events: none; }
        .pw-bar { height: 2px; border-radius: 2px; transition: width 0.3s, background 0.3s; }
        .pw-track { height: 2px; background: #e8e6e1; border-radius: 2px; overflow: hidden; flex: 1; }
        .step-dot { width: 6px; height: 6px; border-radius: 50%; background: #e8e6e1; transition: background 0.3s; }
        .step-dot.active { background: #8c7b6c; }
        .step-line { height: 1px; background: #e8e6e1; flex: 1; align-self: center; }
        .slide-link { position: relative; text-decoration: none; color: #8c7b6c; font-size: 0.75rem; transition: color 0.3s; }
        .slide-link::after { content: ''; position: absolute; left: 0; bottom: -2px; width: 0; height: 1px; background: #8c7b6c; transition: width 0.3s; }
        .slide-link:hover { color: #6f5f51; }
        .slide-link:hover::after { width: 100%; }
        @media (max-width: 768px) { .right-panel { display: none; } }
    </style>
</head>
<body>
<div class="geo-bg"></div>
 
<div class="geo-shape" style="top:-20%;right:-10%;width:50vmin;height:50vmin;background:radial-gradient(circle,rgba(140,123,108,0.03) 0%,transparent 70%);animation:drift 20s ease-in-out infinite;"></div>
<div class="geo-shape" style="bottom:-15%;left:-5%;width:40vmin;height:40vmin;background:radial-gradient(circle,rgba(140,123,108,0.03) 0%,transparent 70%);animation:drift2 25s ease-in-out infinite reverse;"></div>
 
<div class="fixed top-8 left-8 z-20">
    <a href="index.html" class="flex items-center gap-2.5 no-underline group">
        <span class="inline-flex items-center justify-center w-7 h-7 bg-[#8c7b6c] text-[#ffffff] text-[0.5625rem] font-semibold transition-transform duration-300 group-hover:scale-105">A</span>
        <span class="font-sans text-[0.625rem] tracking-[0.2em] uppercase text-[#8c7b6c]/60 group-hover:text-[#8c7b6c] transition-colors">Aura</span>
    </a>
</div>
 
<div class="relative z-10 flex h-screen w-screen items-center justify-center p-6 sm:p-12 lg:p-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 w-full max-w-6xl h-full items-center">
        
        <!-- Left Panel: Frosted Register Form (5 Columns) -->
        <div class="w-full lg:col-span-5 flex items-center justify-center p-4 order-2 lg:order-1">
            <div id="formWrap" class="card-main w-full max-w-sm p-10 rounded-[2rem] glow" style="opacity:0;transform:translateY(40px);">
                <div id="formHead" style="opacity:0;transform:translateY(20px);">
                    <div class="flex gap-2 items-center mb-6">
                        <div class="step-dot active"></div><div class="step-line"></div>
                        <div class="step-dot"></div><div class="step-line"></div>
                        <div class="step-dot"></div>
                    </div>
                    <h1 class="font-sans text-[1.6rem] font-light text-[#111111]">Create <span class="font-medium">account</span></h1>
                    <p class="text-sm text-[#555555] mt-1.5 mb-2 font-light">Join 2,400+ residents. It's free.</p>
                </div>
 
                <?php if ($message): ?>
                    <div class="err-msg mt-4" id="errMsg" style="opacity:0;"><?php echo $message; ?></div>
                <?php endif; ?>
 
                <form method="post" class="mt-8 space-y-6" id="registerForm">
                    <?php echo CsrfProtection::field(); ?>
                    <div class="relative">
                        <input type="text" name="name" id="name" class="inp" placeholder=" " required>
                        <label for="name">Full name</label>
                    </div>
                    <div class="relative">
                        <input type="email" name="email" id="regEmail" class="inp" placeholder=" " required>
                        <label for="regEmail">Email</label>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="regPassword" class="inp" placeholder=" " required minlength="8">
                        <label for="regPassword">Password</label>
                    </div>
                    <div class="flex gap-1.5 -mt-2">
                        <div class="pw-track"><div id="pw1" class="pw-bar w-0 bg-[#964a4a]"></div></div>
                        <div class="pw-track"><div id="pw2" class="pw-bar w-0 bg-[#964a4a]"></div></div>
                        <div class="pw-track"><div id="pw3" class="pw-bar w-0 bg-[#964a4a]"></div></div>
                        <div class="pw-track"><div id="pw4" class="pw-bar w-0 bg-[#964a4a]"></div></div>
                    </div>
                    <div id="pwHint" class="text-[0.5625rem] text-[#888888] tracking-wider uppercase transition-colors">8+ chars · 1 upper · 1 lower · 1 digit</div>
                    <button type="submit" class="btn-accent w-full rounded-xl py-3.5 mt-2" id="submitBtn">
                        <span class="relative z-10">Create Account</span>
                    </button>
                </form>
 
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#e8e6e1]"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-4 text-[0.5rem] uppercase tracking-[0.2em] text-[#888888]">or</span></div>
                </div>
 
                <button disabled class="w-full bg-transparent border border-[#e8e6e1] py-3 text-[0.75rem] text-[#555555]/30 cursor-not-allowed flex items-center justify-center gap-3 rounded-xl">
                    <svg width="14" height="14" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" opacity="0.3"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" opacity="0.3"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" opacity="0.3"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" opacity="0.3"/></svg>
                    Sign up with Google
                </button>
 
                <p class="text-center mt-6 text-[0.6875rem] text-[#555555]">
                    Already have an account? <a href="<?php echo $basePrefix; ?>/login" class="slide-link font-medium text-[#8c7b6c]">Sign in</a>
                </p>
            </div>
        </div>
 
        <!-- Right Panel: Context & Resident Benefits (7 Columns) -->
        <div class="lg:col-span-7 hidden lg:flex flex-col justify-between h-[75%] pl-12 order-1 lg:order-2">
            <div class="max-w-sm" id="rightContent" style="opacity:0;transform:translateX(40px);">
                <div class="font-display text-[4.5rem] leading-[0.9] text-[#111111] font-light">Join<br><span class="italic text-[#8c7b6c]">2,400+</span></div>
                <div class="font-display text-[4.5rem] leading-[0.9] text-[#111111] font-light mt-2">Residents</div>
                <div class="w-12 h-[1px] bg-[#8c7b6c]/20 mt-10 mb-8" id="accentLine"></div>
                <div class="space-y-6">
                    <div class="flex items-start gap-5 group">
                        <span class="w-8 h-8 rounded-full border border-[#8c7b6c]/10 flex items-center justify-center text-[0.65rem] text-[#8c7b6c] shrink-0 mt-0.5 group-hover:bg-[#8c7b6c] group-hover:text-[#ffffff] transition-all duration-500">01</span>
                        <div><p class="text-sm text-[#111111] font-medium">Browse properties</p><p class="text-xs text-[#555555] mt-0.5">Virtual tours, floor plans, and neighborhood insights.</p></div>
                    </div>
                    <div class="flex items-start gap-5 group">
                        <span class="w-8 h-8 rounded-full border border-[#8c7b6c]/10 flex items-center justify-center text-[0.65rem] text-[#8c7b6c] shrink-0 mt-0.5 group-hover:bg-[#8c7b6c] group-hover:text-[#ffffff] transition-all duration-500">02</span>
                        <div><p class="text-sm text-[#111111] font-medium">Book amenities</p><p class="text-xs text-[#555555] mt-0.5">Pool, gym, and common areas — all in one place.</p></div>
                    </div>
                    <div class="flex items-start gap-5 group">
                        <span class="w-8 h-8 rounded-full border border-[#8c7b6c]/10 flex items-center justify-center text-[0.65rem] text-[#8c7b6c] shrink-0 mt-0.5 group-hover:bg-[#8c7b6c] group-hover:text-[#ffffff] transition-all duration-500">03</span>
                        <div><p class="text-sm text-[#111111] font-medium">Track maintenance</p><p class="text-xs text-[#555555] mt-0.5">Submit requests and get real-time updates.</p></div>
                    </div>
                </div>
            </div>
            <div></div>
        </div>
 
    </div>
</div>


<script>
gsap.timeline({defaults:{ease:'power3.out'}})
  .to('#formWrap', {opacity:1, y:0, duration:1.2})
  .to('#formHead', {opacity:1, y:0, duration:0.8}, '-=0.6')
  .to('#rightContent', {opacity:1, x:0, duration:1.2}, '-=0.8')
  .to('#accentLine', {scaleX:1, transformOrigin:'left', duration:0.8}, '-=0.6');

document.getElementById('submitBtn')?.addEventListener('click', function(e) {
    const rect = this.getBoundingClientRect();
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (e.clientX - rect.left - size/2) + 'px';
    ripple.style.top = (e.clientY - rect.top - size/2) + 'px';
    this.appendChild(ripple);
    gsap.fromTo(ripple, {scale:0, opacity:1}, {scale:4, opacity:0, duration:0.6, ease:'power2.out', onComplete:()=>ripple.remove()});
});

const pw = document.getElementById('regPassword');
const bars = [document.getElementById('pw1'), document.getElementById('pw2'), document.getElementById('pw3'), document.getElementById('pw4')];
const hint = document.getElementById('pwHint');

pw?.addEventListener('input', function() {
    const v = this.value;
    let s = 0;
    if (v.length >= 8) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[a-z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    const clrs = ['#9e4f4f','#a6875a','#a6875a','#5d7a4f','#3a322c'];
    const lbls = ['Weak','Fair','Good','Strong','Very Strong'];
    const c = s > 0 ? clrs[Math.min(s-1,4)] : '#9e4f4f';
    bars.forEach((b,i) => { b.style.width = i < s ? '100%' : '0%'; b.style.background = i < s ? c : '#9e4f4f'; });
    hint.textContent = s > 0 ? lbls[Math.min(s-1,4)] : '8+ chars · 1 upper · 1 lower · 1 digit';
    hint.style.color = c;
});
</script>
</body>
</html>
