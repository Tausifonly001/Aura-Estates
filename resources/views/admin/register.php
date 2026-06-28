<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::startSession();

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$message = "";
$messageClass = "";

if($_POST){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'] ?? '';

    if(empty($name) || empty($email) || empty($password)){
        $message = "All fields are required.";
        $messageClass = "text-ink/70";
    } elseif($password !== $confirm){
        $message = "Passwords do not match.";
        $messageClass = "text-ink/70";
    } elseif(strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $messageClass = "text-ink/70";
    } else {
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->role = 'admin';
        $adminRoleStmt = $db->prepare("SELECT id FROM roles WHERE name = 'admin'");
        $adminRoleStmt->execute();
        $adminRole = $adminRoleStmt->fetchColumn();
        $user->role_id = $adminRole ?: null;

        if($user->create()){
            $message = "Account created successfully! You can now log in.";
            $messageClass = "text-ink";
        } else {
            $message = "Registration failed. Email may already exist.";
            $messageClass = "text-ink/70";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{bg:'#f8f8f6','bg-alt':'#ffffff',surface:'#ffffff',ink:'#111111','ink-secondary':'#6b6b6b',graphite:'#6b6b6b',muted:'#999998',clay:'#e5e5e3',border:'#e5e5e3','border-light':'#f0f0ee',accent:'#111111','accent-hover':'#333333',success:'#2d7d46',warning:'#b8860b',danger:'#b22222'},fontFamily:{sans:['DM Sans','-apple-system','BlinkMacSystemFont','sans-serif'],mono:['JetBrains Mono','monospace'],serif:['Cormorant Garamond','Georgia','serif']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,700&family=DM+Sans:opsz,wght@9..40,200;9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../resources/css/neo.css">
    <style>
        .diagonal-line { position: fixed; top: -10%; right: -5%; width: 30%; height: 120%; border-left: 1px solid rgba(196,181,160,0.1); transform: rotate(12deg); pointer-events: none; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center antialiased overflow-hidden">
    <div class="diagonal-line"></div>

    <div class="fixed top-8 left-8 z-10">
        <a href="../index.html" class="text-2xl font-serif italic text-ink/40 hover:text-ink/70 transition">Aura</a>
    </div>

    <div class="relative z-10 w-full max-w-md px-6">
        <div class="neo-card p-10">
            <div class="mb-8">
                <span class="neo-badge text-graphite mb-4">Admin Console</span>
                <h1 class="text-4xl font-serif font-light text-ink mt-3 leading-tight">Create Account</h1>
            </div>

            <?php if($message): ?>
                <div class="neo-flat p-4 mb-6 text-sm text-ink/70 flex items-center <?php echo $messageClass; ?>">
                    <span class="w-1 h-6 bg-clay mr-3 flex-shrink-0"></span>
                    <span><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" class="space-y-5">
                <div>
                    <label class="block text-[9px] font-sans font-medium uppercase tracking-[0.25em] text-graphite/50 mb-3">Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                           class="w-full neo-input p-4 text-ink text-sm placeholder-graphite/30 font-sans"
                           placeholder="Your name" required>
                </div>
                <div>
                    <label class="block text-[9px] font-sans font-medium uppercase tracking-[0.25em] text-graphite/50 mb-3">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="w-full neo-input p-4 text-ink text-sm placeholder-graphite/30 font-sans"
                           placeholder="you@example.com" required>
                </div>
                <div>
                    <label class="block text-[9px] font-sans font-medium uppercase tracking-[0.25em] text-graphite/50 mb-3">Password</label>
                    <input type="password" name="password"
                           class="w-full neo-input p-4 text-ink text-sm placeholder-graphite/30 font-sans"
                           placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
                </div>
                <div>
                    <label class="block text-[9px] font-sans font-medium uppercase tracking-[0.25em] text-graphite/50 mb-3">Confirm Password</label>
                    <input type="password" name="confirm_password"
                           class="w-full neo-input p-4 text-ink text-sm placeholder-graphite/30 font-sans"
                           placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
                </div>
                <button type="submit" class="w-full neo-btn py-3.5 text-xs font-sans font-medium uppercase tracking-[0.25em] text-ink">
                    Create Account
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-clay/20 flex flex-col space-y-3 text-[10px] font-sans">
                <a href="login.php" class="text-graphite/50 hover:text-ink transition font-medium uppercase tracking-[0.2em]">Already have an account? Sign In</a>
                <a href="../index.html" class="text-graphite/30 hover:text-graphite/60 transition uppercase tracking-[0.2em]">Back to Site</a>
            </div>
        </div>
    </div>

    <div class="fixed bottom-8 right-8 text-[8px] font-sans uppercase tracking-[0.3em] text-graphite/20 z-10">Est. 1984</div>
</body>
</html>
