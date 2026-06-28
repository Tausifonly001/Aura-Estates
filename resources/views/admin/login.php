<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::startSession();
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$message = "";

if($_POST){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = Auth::login($email, $password);
    if($result === false) {
        $message = "Invalid credentials.";
    } elseif($result['role'] !== 'admin') {
        Auth::logout('login.php');
        $message = "Access denied. Admins only.";
    } else {
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { bg: '#e8e5db', 'bg-alt': '#f2efe9', surface: '#faf8f4', ink: '#1c1b18', 'ink-secondary': '#5c5349', muted: '#9a9086', border: '#d6d2c8', 'border-light': '#e1ddd4', accent: '#3a322c', 'accent-hover': '#2a2420' },
                    fontFamily: { sans: ['DM Sans', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #e8e5db; color: #1c1b18; height: 100vh; overflow: hidden; }
        .glass-card { background: rgba(250,248,244,0.85); backdrop-filter: blur(24px); border: 1px solid rgba(214,210,200,0.25); box-shadow: 0 24px 80px -16px rgba(28,27,24,0.15); }
        .input-line { background: transparent; border: none; border-bottom: 1.5px solid #d6d2c8; padding: 0.75rem 0; font-size: 0.875rem; color: #1c1b18; outline: none; width: 100%; transition: border-color 0.3s, padding 0.3s; }
        .input-line:focus { border-bottom-color: #3a322c; padding-left: 0.25rem; }
        .input-line ~ .input-label { position: absolute; left: 0; top: 0.75rem; font-size: 0.875rem; color: #9a9086; pointer-events: none; transition: 0.3s; }
        .input-line:focus ~ .input-label, .input-line:not(:placeholder-shown) ~ .input-label { top: -1.25rem; font-size: 0.625rem; color: #3a322c; letter-spacing: 0.05em; text-transform: uppercase; }
        .btn-accent { position: relative; overflow: hidden; background: #3a322c; color: #faf8f4; border: none; padding: 0.75rem 2rem; font-size: 0.6875rem; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer; transition: transform 0.3s, box-shadow 0.3s; width: 100%; }
        .btn-accent:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(58,50,44,0.25); }
        .btn-accent .ripple { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.12); transform: scale(0); pointer-events: none; }
        .morph-shape { position: absolute; pointer-events: none; opacity: 0.04; }
        .float-shape { position: absolute; border-radius: 50%; opacity: 0.06; pointer-events: none; }
        .link-underline { position: relative; text-decoration: none; color: #5c5349; font-size: 0.6875rem; transition: color 0.3s; }
        .link-underline::after { content: ''; position: absolute; left: 0; bottom: -2px; width: 0; height: 1px; background: #3a322c; transition: width 0.3s; }
        .link-underline:hover { color: #1c1b18; }
        .link-underline:hover::after { width: 100%; }
        @media (max-width: 640px) { .hero-panel { display: none; } }
    </style>
</head>
<body>

<div class="float-shape" style="width:50vw;height:50vw;top:-15%;right:-10%;background:radial-gradient(circle,#3a322c 0%,transparent 70%);"></div>
<div class="float-shape" style="width:30vw;height:30vw;bottom:-10%;left:-8%;background:radial-gradient(circle,rgba(92,83,73,0.5) 0%,transparent 70%);"></div>
<div class="morph-shape" style="width:40%;height:40%;top:10%;right:5%;border:1px solid #3a322c;border-radius:40% 60% 70% 30%/50% 40% 60% 50%;animation:morph1 20s ease-in-out infinite;"></div>
<div class="morph-shape" style="width:25%;height:25%;bottom:15%;left:12%;border:1px solid #5c5349;border-radius:60% 40% 30% 70%/50% 60% 40% 50%;animation:morph2 18s ease-in-out infinite reverse;"></div>

<div class="fixed top-6 left-6 z-20">
    <a href="../../index.html" class="flex items-center gap-2.5 no-underline group">
        <span class="inline-flex items-center justify-center w-7 h-7 bg-accent text-bg text-[0.625rem] font-semibold transition-transform duration-300 group-hover:scale-105">A</span>
        <span class="font-sans font-medium text-[0.6875rem] tracking-[0.2em] uppercase text-ink/50 group-hover:text-ink/80 transition-colors">Aura Estates</span>
    </a>
</div>

<div class="flex h-screen w-screen items-center justify-center p-6 relative z-10">
    <div id="formWrap" class="glass-card w-full max-w-sm p-10" style="opacity:0;transform:translateY(40px);">
        <div class="mb-8" style="opacity:0;transform:translateY(20px);">
            <span class="inline-flex items-center font-mono text-[0.5625rem] tracking-[0.02em] uppercase px-3 py-1 border border-border text-ink-secondary bg-bg mb-4">Admin Console</span>
            <h1 class="font-sans font-medium text-[1.75rem] text-ink leading-tight">Sign in</h1>
        </div>

        <?php if($message): ?>
            <div class="err-msg" style="opacity:0;"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="relative mb-7">
                <input type="email" name="email" id="email" class="input-line" placeholder=" " value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                <label for="email" class="input-label">Email</label>
            </div>
            <div class="relative mb-8">
                <input type="password" name="password" id="password" class="input-line" placeholder=" " required>
                <label for="password" class="input-label">Password</label>
            </div>
            <button type="submit" class="btn-accent" id="submitBtn">
                <span class="relative z-10">Sign In</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-border-light flex flex-col gap-3" style="opacity:0;">
            <a href="register.php" class="link-underline">Create Account</a>
            <a href="forgot-password.php" class="link-underline">Forgot Password?</a>
            <a href="../../index.html" class="link-underline text-muted hover:text-ink-secondary">Back to Site</a>
        </div>
    </div>
</div>

<div class="fixed bottom-6 right-8 font-mono text-[0.5rem] tracking-[0.3em] uppercase text-muted/30 z-10">Est. 2020</div>

<script>
const tl = gsap.timeline({defaults:{ease:'power3.out'}});
tl.to('#formWrap', {opacity:1, y:0, duration:1.2})
  .to('#formWrap > div:first-child', {opacity:1, y:0, duration:0.6}, '-=0.6')
  .to('.err-msg', {opacity:1, duration:0.4}, '-=0.2')
  .to('.link-underline', {opacity:1, duration:0.6, stagger:0.1}, '-=0.2');

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
</script>
</body>
</html>
