<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::startSession();
if(isset($_SESSION['user_id'])){
    header("Location: " . Auth::getBasePrefix() . "/user/dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#e8e5db',
                        'bg-alt': '#f2efe9',
                        surface: '#faf8f4',
                        ink: '#1c1b18',
                        'ink-secondary': '#5c5349',
                        muted: '#9a9086',
                        border: '#d6d2c8',
                        'border-light': '#e1ddd4',
                        accent: '#3a322c',
                        'accent-hover': '#2a2420',
                    },
                    fontFamily: {
                        sans: ['DM Sans', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background-color: #e8e5db; color: #1c1b18; -webkit-font-smoothing: antialiased; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">

    <div class="fixed top-8 left-8 z-10">
        <a href="../index.html" class="flex items-center gap-3 font-sans font-medium text-[0.875rem] tracking-[0.15em] uppercase text-ink/40 hover:text-ink/70 transition-colors">
            <span class="inline-flex items-center justify-center w-6 h-6 bg-accent text-bg text-[0.625rem] font-semibold leading-none">A</span>
            Aura Estates
        </a>
    </div>

    <div class="relative z-10 w-full max-w-md px-6">
        <div class="bg-surface border border-border-light p-10">
            <div class="mb-8">
                <span class="inline-flex items-center font-mono text-[0.5625rem] tracking-[0.02em] uppercase px-3 py-1 border border-border text-ink-secondary bg-bg mb-4">Tenant Portal</span>
                <h1 class="font-sans font-medium text-[2rem] text-ink leading-tight">Join Aura</h1>
            </div>

            <div id="message" class="hidden p-4 mb-6 font-mono text-[0.75rem]"></div>

            <form id="registerForm" class="flex flex-col gap-5">
                <div>
                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Full Name</label>
                    <input type="text" id="name" name="name"
                           class="w-full font-sans text-[0.875rem] text-ink bg-transparent border border-border px-4 py-3 outline-none focus:border-accent transition-colors placeholder:text-muted"
                           placeholder="Your name" required>
                </div>
                <div>
                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Email</label>
                    <input type="email" id="email" name="email"
                           class="w-full font-sans text-[0.875rem] text-ink bg-transparent border border-border px-4 py-3 outline-none focus:border-accent transition-colors placeholder:text-muted"
                           placeholder="you@example.com" required>
                </div>
                <div>
                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Password</label>
                    <input type="password" id="password" name="password"
                           class="w-full font-sans text-[0.875rem] text-ink bg-transparent border border-border px-4 py-3 outline-none focus:border-accent transition-colors placeholder:text-muted"
                           placeholder="••••••••" required>
                </div>
                <button type="submit" id="submitBtn" class="w-full inline-flex items-center justify-center gap-3 font-mono text-[0.6875rem] tracking-[0.02em] uppercase text-bg bg-accent px-5 py-3.5 rounded-full hover:bg-accent-hover transition-colors">
                    Create Account
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-border-light flex flex-col gap-3 font-mono text-[0.625rem] tracking-[0.02em] uppercase">
                <a href="<?php echo Auth::getBasePrefix(); ?>/login" class="text-ink-secondary hover:text-ink transition-colors">Already have an account? Sign In</a>
                <a href="<?php echo Auth::getBasePrefix(); ?>/" class="text-muted hover:text-ink-secondary transition-colors">Back to Site</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Creating...';

            var msg = document.getElementById('message');
            msg.classList.add('hidden');

            fetch('../api/auth.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    role: 'user'
                })
            })
            .then(r => r.json())
            .then(data => {
                msg.classList.remove('hidden');
                if(data.success) {
                    msg.className = 'bg-success/10 border border-success/20 text-success p-4 mb-6 font-mono text-[0.75rem]';
                    msg.textContent = data.message + ' Redirecting...';
                    setTimeout(function() { window.location.href = '<?php echo Auth::getBasePrefix(); ?>/login'; }, 1500);
                } else {
                    msg.className = 'bg-danger/10 border border-danger/20 text-danger p-4 mb-6 font-mono text-[0.75rem]';
                    msg.textContent = data.message || 'Registration failed.';
                }
                btn.disabled = false;
                btn.textContent = 'Create Account';
            })
            .catch(() => {
                msg.classList.remove('hidden');
                msg.className = 'bg-danger/10 border border-danger/20 text-danger p-4 mb-6 font-mono text-[0.75rem]';
                msg.textContent = 'Connection error.';
                btn.disabled = false;
                btn.textContent = 'Create Account';
            });
        });
    </script>

    <div class="fixed bottom-8 right-8 font-mono text-[0.5rem] tracking-[0.3em] uppercase text-muted/30 z-10">Est. 2020</div>
</body>
</html>
