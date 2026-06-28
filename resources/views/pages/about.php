<?php $pageTitle = 'About'; $currentPage = 'about'; ?>
<?php require_once __DIR__ . '/../../../src/helpers.php'; ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero bg-bg-alt" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4"><?php echo content('about','hero','heading','About the Studio'); ?></p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink max-w-[20ch]" data-split><?php echo content('about','hero','body','Thoughtful property management, refined through years of practice.'); ?></h1>
    </div>
</section>

<section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">
            <div>
                <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4">Our Story</p>
                <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-ink mb-6" data-split>Built on clarity, materiality, and trust.</h2>
                <div class="space-y-4 font-sans text-[0.9375rem] lg:text-[1rem] leading-[1.7] text-ink-secondary">
                    <p>Aura Estates was founded in 2020 with a simple belief: property management should feel seamless, transparent, and deeply human. We saw an industry bogged down by fragmented communication, opaque processes, and systems that prioritised paperwork over people.</p>
                    <p>Our approach is different. Every property we manage is treated with the same care and attention that goes into the finest architectural projects — from the first conversation through to the smallest operational detail. We combine design thinking with rigorous operational discipline.</p>
                    <p>Today, we manage over 500 properties across Berlin and beyond, serving homeowners, investors, and tenants who expect more from their property management experience.</p>
                </div>
            </div>
            <div class="bg-surface border border-border-light p-8 lg:p-12" data-reveal>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-6">Our Principles</p>
                <div class="space-y-8" data-stagger>
                    <div class="border-t border-border pt-6" data-stagger-item>
                        <p class="font-mono text-[0.625rem] tracking-[-0.02em] uppercase text-muted mb-2">01</p>
                        <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-2">Considered</h3>
                        <p class="font-sans text-[0.875rem] leading-[1.6] text-ink-secondary">Every decision is deliberate. From tenant placement to maintenance scheduling, each action follows a clear rationale.</p>
                    </div>
                    <div data-stagger-item class="border-t border-border pt-6">
                        <p class="font-mono text-[0.625rem] tracking-[-0.02em] uppercase text-muted mb-2">02</p>
                        <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-2">Crafted</h3>
                        <p class="font-sans text-[0.875rem] leading-[1.6] text-ink-secondary">Attention to detail defines every interaction. Systems are built to serve people, not the other way around.</p>
                    </div>
                    <div data-stagger-item class="border-t border-border pt-6">
                        <p class="font-mono text-[0.625rem] tracking-[-0.02em] uppercase text-muted mb-2">03</p>
                        <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-2">Lasting</h3>
                        <p class="font-sans text-[0.875rem] leading-[1.6] text-ink-secondary">Solutions are built to endure. Relationships with tenants, owners, and partners are measured in years, not quarters.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 bg-bg-alt" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="flex flex-col gap-4 mb-12 lg:mb-16" data-reveal>
            <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60">Leadership</p>
            <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-ink" data-split>The team behind the practice.</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12" data-stagger>
            <div data-stagger-item>
                <div class="aspect-[4/5] bg-surface border border-border-light mb-5 flex items-center justify-center overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=600" alt="Lukas Walker" class="w-full h-full object-cover" data-image-reveal>
                </div>
                <p class="font-sans font-medium text-[1.125rem] text-ink mb-1">Lukas Walker</p>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted">Founder & Principal</p>
            </div>
            <div data-stagger-item>
                <div class="aspect-[4/5] bg-surface border border-border-light mb-5 flex items-center justify-center overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=600" alt="Hanna Bennett" class="w-full h-full object-cover" data-image-reveal>
                </div>
                <p class="font-sans font-medium text-[1.125rem] text-ink mb-1">Hanna Bennett</p>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted">Head of Operations</p>
            </div>
            <div data-stagger-item>
                <div class="aspect-[4/5] bg-surface border border-border-light mb-5 flex items-center justify-center overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&q=80&w=600" alt="Emma Hofstetter" class="w-full h-full object-cover" data-image-reveal>
                </div>
                <p class="font-sans font-medium text-[1.125rem] text-ink mb-1">Emma Hofstetter</p>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted">Director of Client Relations</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="max-w-[48rem] mx-auto text-center py-12" data-reveal>
            <svg class="w-8 h-8 opacity-30 mx-auto mb-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M10 11H6a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 011 1v7c0 3-2 5-5 5" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M19 11h-4a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 011 1v7c0 3-2 5-5 5" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p class="font-sans text-[1.125rem] lg:text-[1.25rem] leading-[1.7] text-ink-secondary italic">
                We founded Aura Estates with the belief that property management should feel seamless, transparent, and deeply human. Every relationship is approached with clarity, warmth, and careful attention to detail.
            </p>
            <p class="font-sans font-medium text-[0.9375rem] text-ink mt-6">The Aura Estates Team</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>