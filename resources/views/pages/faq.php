<?php $pageTitle = 'FAQ'; $currentPage = 'faq'; ?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero bg-bg-alt" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4">FAQ</p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink">Frequently Asked Questions</h1>
    </div>
</section>

<section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[48rem] mx-auto px-6 lg:px-12" data-stagger>
        <?php
        $faqs = [
            ['How do I report a maintenance issue?', 'You can submit a maintenance request through your tenant portal dashboard, or by using the maintenance form on our homepage. Once submitted, you will receive real-time updates on the status of your request.'],
            ['How do I book an amenity?', 'Amenities can be booked through the tenant portal. Select the amenity you would like to use, choose an available time slot, and confirm your booking. You will receive a confirmation with check-in instructions.'],
            ['What is the notice period for moving out?', 'The notice period is specified in your lease agreement and typically ranges from 30 to 90 days. Please refer to your lease or contact our team for specific details about your property.'],
            ['How is rent collected?', 'Rent is collected monthly via bank transfer, direct debit, or our online payment portal. Payment reminders are sent 3 days before the due date, and receipts are generated automatically upon confirmation.'],
            ['Can I sublet my property?', 'Subletting is subject to the terms of your lease agreement and requires written approval from the property owner. Please submit a formal request through the portal, and our team will guide you through the process.'],
            ['How do I access my tenant portal?', 'Your tenant portal can be accessed at aura-estates. You can log in using the email and password you registered with. If you have forgotten your password, use the "Forgot Password" link to reset it.'],
            ['What happens in case of an emergency?', 'For emergencies such as fire, gas leaks, or flooding, please contact emergency services immediately. Then notify our 24/7 maintenance hotline at +49 30 28 04 8000 and we will dispatch a response team.'],
            ['Are pets allowed in the properties?', 'Pet policies vary by property and are outlined in the lease agreement. Some properties welcome pets with a deposit, while others may have restrictions. Please check your lease or contact us for clarification.'],
            ['How are maintenance emergencies prioritised?', 'Requests are categorised as P1 (Critical), P2 (Priority), or P3 (Standard). P1 emergencies are responded to within 2 hours, P2 within 24 hours, and P3 within 72 hours. Our team monitors all requests in real time.'],
            ['Can I schedule a property viewing?', 'Yes. You can schedule a viewing by contacting our leasing team through the contact form or by calling +49 30 28 04 8000. We offer in-person and virtual tours.'],
        ];
        $i = 0;
        foreach ($faqs as $faq):
            $i++;
        ?>
        <details class="group border-t border-border py-5 <?php echo $i === count($faqs) ? 'border-b' : ''; ?>" data-stagger-item>
            <summary class="flex items-center justify-between cursor-pointer list-none">
                <span class="font-sans font-medium text-[1rem] lg:text-[1.125rem] text-ink pr-4"><?php echo $faq[0]; ?></span>
                <svg class="w-4 h-4 text-muted flex-shrink-0 transition-transform duration-300 group-open:rotate-45" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </summary>
            <p class="mt-4 font-sans text-[0.9375rem] leading-[1.7] text-ink-secondary"><?php echo $faq[1]; ?></p>
        </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="py-16 lg:py-24 cta-section" data-reveal>
        <h2 class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] leading-[1.1] text-bg max-w-[48rem] mx-auto mb-8" data-split>We are here to help. Reach out to our team.</h2>
        <a href="/contact" class="inline-flex items-center gap-3 font-mono text-[0.75rem] tracking-[0.02em] uppercase text-accent bg-bg px-8 py-4 rounded-full hover:bg-white transition-colors no-underline">
            Contact Us
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 15L15 1M15 1H5M15 1V11" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>