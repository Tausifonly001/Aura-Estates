<?php $pageTitle = 'Services'; $currentPage = 'services'; ?>
<?php require_once __DIR__ . '/../../../src/helpers.php'; ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4"><?php echo content('services','hero','heading','What We Do'); ?></p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink max-w-[18ch]"><?php echo content('services','hero','body','Integrated property services. One team, full lifecycle.'); ?></h1>
    </div>
</section>

<section id="residential" class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div>
                <span class="inline-block font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted border border-border px-3 py-1 mb-6">Full Lifecycle</span>
                <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-ink mb-6" data-split>Residential Property Management</h2>
                <div class="space-y-4 font-sans text-[0.9375rem] leading-[1.7] text-ink-secondary">
                    <p>End-to-end management for residential portfolios of all scales. From single apartments to multi-building estates, our residential service is built around transparency, communication, and meticulous care.</p>
                </div>
                <ul class="mt-6 space-y-3">
                    <?php $services = ['Tenant sourcing and placement', 'Lease administration and renewals', '24/7 maintenance coordination', 'Regular property inspections', 'Owner reporting and analytics', 'Move-in and move-out management']; ?>
                    <?php foreach ($services as $s): ?>
                    <li class="flex items-center gap-3 font-sans text-[0.9375rem] text-ink-secondary">
                        <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php echo $s; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="aspect-[4/3] bg-surface border border-border-light overflow-hidden">
                <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000" alt="Residential" class="w-full h-full object-cover" data-image-reveal onerror="this.onerror=null;this.src='resources/placeholders/residential.svg';">
            </div>
        </div>
    </div>
</section>

<section id="commercial" class="py-16 lg:py-24 bg-bg-alt" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div class="order-2 lg:order-1 aspect-[4/3] bg-surface border border-border-light overflow-hidden">
                <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&q=80&w=1000" alt="Commercial" class="w-full h-full object-cover" data-image-reveal onerror="this.onerror=null;this.src='resources/placeholders/commercial.svg';">
            </div>
            <div class="order-1 lg:order-2">
                <span class="inline-block font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted border border-border px-3 py-1 mb-6">Tailored to context</span>
                <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-ink mb-6" data-split>Commercial Asset Management</h2>
                <div class="space-y-4 font-sans text-[0.9375rem] leading-[1.7] text-ink-secondary">
                    <p>Specialised management for commercial and retail assets. We understand that commercial properties require a different operating rhythm — one that balances tenant needs with owner objectives.</p>
                </div>
                <ul class="mt-6 space-y-3">
                    <?php $services = ['Lease administration and compliance', 'Real-time operational dashboards', 'CAM reconciliation', 'Tenant improvement coordination', 'Sustainability reporting', 'Portfolio performance analytics']; ?>
                    <?php foreach ($services as $s): ?>
                    <li class="flex items-center gap-3 font-sans text-[0.9375rem] text-ink-secondary">
                        <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php echo $s; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<section id="consulting" class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div>
                <span class="inline-block font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted border border-border px-3 py-1 mb-6">Advisory</span>
                <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-ink mb-6" data-split>Consulting & Advisory</h2>
                <div class="space-y-4 font-sans text-[0.9375rem] leading-[1.7] text-ink-secondary">
                    <p>Strategic advisory for portfolio optimisation, market analysis, and operational process design. We help property owners and investors make informed decisions backed by data and deep market knowledge.</p>
                </div>
                <ul class="mt-6 space-y-3">
                    <?php $services = ['Portfolio strategy and optimisation', 'Market research and feasibility', 'Operational process design', 'Technology stack evaluation', 'Due diligence support', 'Sustainability roadmap planning']; ?>
                    <?php foreach ($services as $s): ?>
                    <li class="flex items-center gap-3 font-sans text-[0.9375rem] text-ink-secondary">
                        <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php echo $s; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="aspect-[4/3] bg-surface border border-border-light overflow-hidden">
                <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&q=80&w=1000" alt="Consulting" class="w-full h-full object-cover" data-image-reveal onerror="this.onerror=null;this.src='resources/placeholders/consulting.svg';">
            </div>
        </div>
    </div>
</section>

<section id="maintenance" class="py-16 lg:py-24 bg-bg-alt" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div class="order-2 lg:order-1 aspect-[4/3] bg-surface border border-border-light overflow-hidden flex items-center justify-center">
                <div class="text-center p-8">
                    <i class="fas fa-tools text-4xl text-muted mb-4"></i>
                    <p class="font-sans text-[0.9375rem] text-ink-secondary">Maintenance dashboard with real-time tracking</p>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <span class="inline-block font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted border border-border px-3 py-1 mb-6">24/7 Support</span>
                <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-ink mb-6" data-split>Maintenance & Operations</h2>
                <div class="space-y-4 font-sans text-[0.9375rem] leading-[1.7] text-ink-secondary">
                    <p>Proactive maintenance management with real-time tracking, priority-based dispatching, and transparent communication. Every request is logged, tracked, and resolved with clear accountability.</p>
                </div>
                <ul class="mt-6 space-y-3">
                    <?php $services = ['Real-time maintenance tracking', 'Priority-based dispatching', 'Vendor management and oversight', 'Preventive maintenance scheduling', 'Emergency response protocols', 'Digital service records']; ?>
                    <?php foreach ($services as $s): ?>
                    <li class="flex items-center gap-3 font-sans text-[0.9375rem] text-ink-secondary">
                        <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php echo $s; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 cta-section" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 text-center relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-white/50 mb-4">Start the conversation</p>
        <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-bg max-w-[48rem] mx-auto mb-8" data-split>Interested in working with us? Let us hear about your property.</h2>
        <a href="/contact" class="inline-flex items-center gap-3 font-mono text-[0.75rem] tracking-[0.02em] uppercase text-accent bg-bg px-8 py-4 rounded-full hover:bg-white transition-colors no-underline">
            Get In Touch
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 15L15 1M15 1H5M15 1V11" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>