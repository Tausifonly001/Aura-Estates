<?php $pageTitle = 'Careers'; $currentPage = 'careers'; ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero bg-bg-alt" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4">Careers</p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink">Join the team.</h1>
    </div>
</section>

<section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 mb-16 lg:mb-24">
            <div>
                <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4">Working at Aura</p>
                <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-ink mb-6" data-split>Built on craft, care, and collaboration.</h2>
                <div class="space-y-4 font-sans text-[0.9375rem] leading-[1.7] text-ink-secondary">
                    <p>We are a small, focused team of property professionals, designers, and operators who believe that great property management is built on great relationships. Every role at Aura Estates is designed to give you ownership, autonomy, and the opportunity to make a real impact.</p>
                    <p>We value attention to detail, clear communication, and a genuine commitment to doing things well. If that sounds like you, we would love to hear from you.</p>
                </div>
            </div>
            <div class="bg-surface border border-border-light p-8 lg:p-10" data-reveal>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-4">Why join us?</p>
                <ul class="space-y-4" data-stagger>
                    <?php $reasons = [
                        'Competitive compensation and benefits',
                        'Flexible working arrangements',
                        'Professional development budget',
                        'Central Berlin office with beautiful workspace',
                        '28 days annual leave',
                        'Annual team retreat',
                    ]; ?>
                    <?php foreach ($reasons as $r): ?>
                    <li class="flex items-start gap-3 font-sans text-[0.9375rem] text-ink-secondary" data-stagger-item>
                        <svg class="w-4 h-4 text-success flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php echo $r; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="border-t border-border pt-12" data-reveal>
            <?php
            $positions = [
                ['title' => 'Property Manager', 'type' => 'Full-time', 'location' => 'Berlin', 'dept' => 'Operations'],
                ['title' => 'Operations Associate', 'type' => 'Full-time', 'location' => 'Berlin', 'dept' => 'Operations'],
                ['title' => 'Client Relations Lead', 'type' => 'Full-time', 'location' => 'Berlin', 'dept' => 'Client Services'],
                ['title' => 'Junior Maintenance Coordinator', 'type' => 'Full-time', 'location' => 'Berlin', 'dept' => 'Operations'],
            ];
            ?>
            <?php if (count($positions) === 0): ?>
            <p class="font-sans text-[0.9375rem] text-ink-secondary">No open positions at the moment. Check back soon or send us your CV.</p>
            <?php else: ?>
            <div class="space-y-4" data-stagger>
                <?php foreach ($positions as $pos): ?>
                <div class="bg-surface border border-border-light p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:border-accent transition-colors" data-stagger-item>
                    <div>
                        <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-1"><?php echo $pos['title']; ?></h3>
                        <div class="flex gap-4 font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted">
                            <span><?php echo $pos['dept']; ?></span>
                            <span>&middot;</span>
                            <span><?php echo $pos['type']; ?></span>
                            <span>&middot;</span>
                            <span><?php echo $pos['location']; ?></span>
                        </div>
                    </div>
                    <a href="/contact" class="btn-primary whitespace-nowrap">Apply Now</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 cta-section" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 text-center relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-white/50 mb-4">Dont see the right role?</p>
        <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-bg max-w-[48rem] mx-auto mb-8" data-split>We are always looking for great people. Send us your CV.</h2>
        <a href="/contact" class="inline-flex items-center gap-3 font-mono text-[0.75rem] tracking-[0.02em] uppercase text-accent bg-bg px-8 py-4 rounded-full hover:bg-white transition-colors no-underline">
            Get in Touch
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 15L15 1M15 1H5M15 1V11" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>