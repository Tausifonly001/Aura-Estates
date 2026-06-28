<?php require_once __DIR__ . '/src/config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>body { font-family: 'DM Sans', sans-serif; background: #e8e5db; color: #1c1b18; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-[#faf8f4] border border-[#e1ddd4] p-8 lg:p-10">
        <a href="index.html" class="inline-flex items-center gap-3 no-underline mb-8">
            <span class="inline-flex items-center justify-center w-8 h-8 bg-[#3a322c] text-[#e8e5db] text-sm font-semibold">A</span>
            <span class="font-sans font-medium text-sm tracking-[0.15em] uppercase text-[#1c1b18]">AURA</span>
        </a>
        <h1 class="font-sans font-medium text-xl text-[#1c1b18] mb-2">Set new password</h1>
        <p class="font-sans text-sm text-[#5c5349] mb-6">Must be at least 8 characters with uppercase, lowercase & digit.</p>
        <div id="msg" class="hidden font-sans text-sm p-4 mb-4"></div>
        <form id="resetForm" class="flex flex-col gap-4">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
            <input type="password" name="password" placeholder="New password" required minlength="8" class="font-sans text-sm text-[#1c1b18] bg-transparent border border-[#d6d2c8] px-4 py-3 outline-none focus:border-[#3a322c] transition-colors">
            <input type="password" name="confirm" placeholder="Confirm password" required minlength="8" class="font-sans text-sm text-[#1c1b18] bg-transparent border border-[#d6d2c8] px-4 py-3 outline-none focus:border-[#3a322c] transition-colors">
            <button type="submit" class="font-sans font-medium text-sm text-[#faf8f4] bg-[#3a322c] px-6 py-3 hover:bg-[#2a2420] transition-colors">Reset Password</button>
        </form>
        <a href="/login" class="block text-center font-sans text-xs text-[#5c5349] mt-6 hover:text-[#1c1b18] transition-colors no-underline">Back to login</a>
    </div>
    <script>
        document.getElementById('resetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const pwd = e.target.password.value;
            const confirm = e.target.confirm.value;
            const msg = document.getElementById('msg');
            if (pwd !== confirm) {
                msg.className = 'font-sans text-sm p-4 mb-4 bg-[#fce4ec] text-[#c62828] border border-[#f8bbd0]';
                msg.textContent = 'Passwords do not match.';
                msg.classList.remove('hidden');
                return;
            }
            try {
                const res = await fetch('api/password-reset.php?action=reset', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: JSON.stringify({ token: e.target.token.value, password: pwd })
                });
                const data = await res.json();
                if (data.success) {
                    msg.className = 'font-sans text-sm p-4 mb-4 bg-[#e8f5e9] text-[#2e7d32] border border-[#c8e6c9]';
                    msg.textContent = data.message;
                    msg.classList.remove('hidden');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                } else {
                    msg.className = 'font-sans text-sm p-4 mb-4 bg-[#fce4ec] text-[#c62828] border border-[#f8bbd0]';
                    msg.textContent = data.message;
                    msg.classList.remove('hidden');
                }
            } catch (err) {
                msg.className = 'font-sans text-sm p-4 mb-4 bg-[#fce4ec] text-[#c62828] border border-[#f8bbd0]';
                msg.textContent = 'Something went wrong. Try again.';
                msg.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
