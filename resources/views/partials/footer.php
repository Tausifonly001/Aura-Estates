</main>

<footer class="py-20 lg:py-28 border-t border-border/60 mt-16">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-14 mb-14 lg:mb-20">
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <span class="inline-flex items-center justify-center w-8 h-8 bg-accent text-surface text-[0.75rem] font-semibold leading-none">A</span>
                    <span class="font-sans font-medium text-[0.8125rem] tracking-[0.15em] uppercase text-ink">Aura Estates</span>
                </div>
                <p class="font-sans text-[0.875rem] lg:text-[0.9375rem] leading-[1.7] text-ink-secondary/80 max-w-[30ch]">Architecture-led property management — thoughtful, transparent, built to last.</p>
                <div class="mt-6">
                    <p class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-muted mb-1.5">Contact</p>
                    <p class="font-sans text-[0.875rem] leading-[1.6] text-ink-secondary">hello@auraestates.com</p>
                    <p class="font-sans text-[0.875rem] leading-[1.6] text-ink-secondary">+49 30 28 04 8000</p>
                </div>
                <div class="mt-4">
                    <p class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-muted mb-1.5">Address</p>
                    <p class="font-sans text-[0.875rem] leading-[1.6] text-ink-secondary">Linienstrasse 156, Berlin</p>
                </div>
            </div>
            <div>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-5">Pages</p>
                <div class="flex flex-col gap-2.5">
                    <a href="/" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Home</a>
                    <a href="/about" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">About</a>
                    <a href="/services" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Services</a>
                    <a href="/properties" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Properties</a>
                    <a href="/blog" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Journal</a>
                    <a href="/contact" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Contact</a>
                    <a href="/faq" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">FAQ</a>
                    <a href="/careers" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Careers</a>
                </div>
            </div>
            <div>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-5">Services</p>
                <div class="flex flex-col gap-2.5">
                    <a href="/services#residential" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Residential</a>
                    <a href="/services#commercial" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Commercial</a>
                    <a href="/services#consulting" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Consulting</a>
                    <a href="/services#maintenance" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Maintenance</a>
                </div>
            </div>
            <div>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-5">Account</p>
                <div class="flex flex-col gap-2.5">
                    <a href="/login" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Sign In</a>
                    <a href="/register" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Register</a>
                    <a href="/user/dashboard" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Dashboard</a>
                    <a href="/contact" class="font-sans text-[0.875rem] text-ink-secondary hover:text-ink transition-colors no-underline">Support</a>
                </div>
            </div>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-border-light font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted gap-4">
            <span>&copy; 2026 Aura Estates. All rights reserved.</span>
            <div class="flex gap-6">
                <a href="/privacy" class="hover:text-ink transition-colors no-underline">Privacy</a>
                <a href="/terms" class="hover:text-ink transition-colors no-underline">Terms</a>
            </div>
        </div>
    </div>
</footer>

<script>
document.addEventListener('scroll', function() {
    var nav = document.getElementById('mainNav');
    if (nav) nav.classList.toggle('nav-compact', window.scrollY > 80);
}, { passive: true });

window.addEventListener('load', function() {
    if (typeof AuraAnimations !== 'undefined') {
        AuraAnimations.initPage();
    }
    document.body.classList.add('page-entered');
});

// Page transition fade
(function() {
    var body = document.body;
    body.style.opacity = '0';
    body.style.transition = 'opacity 0.5s ease';
    requestAnimationFrame(function() {
        body.style.opacity = '1';
    });
    document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([href^="http"])').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var href = this.getAttribute('href');
            if (href && !href.startsWith('#') && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                body.style.opacity = '0';
                setTimeout(function() { window.location = href; }, 350);
            }
        });
    });
})();
</script>
</body>
</html>
