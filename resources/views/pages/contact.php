<?php
require_once __DIR__ . '/../../../src/config/database.php';
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/helpers.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';

Auth::startSession();
CsrfProtection::generate();

$message = '';
$messageClass = '';

if ($_POST) {
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        $message = 'Invalid security token. Please try again.';
        $messageClass = 'text-danger';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $msg = trim($_POST['message'] ?? '');
        $propertyId = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;

        if (empty($name) || empty($email) || empty($msg)) {
            $message = 'Please fill in all required fields.';
            $messageClass = 'text-danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageClass = 'text-danger';
        } else {
            try {
                $database = new Database();
                $db = $database->getConnection();
                if ($propertyId > 0) {
                    $checkProp = $db->prepare("SELECT id FROM properties WHERE id = ?");
                    $checkProp->execute([$propertyId]);
                    if (!$checkProp->fetch()) {
                        $propertyId = 0;
                    }
                }
                $stmt = $db->prepare("INSERT INTO inquiries (property_id, name, email, phone, message, created_at) VALUES (?, ?, ?, '', ?, NOW())");
                $stmt->execute([$propertyId ?: null, $name, $email, $msg]);
                $message = 'Thank you! Your message has been received. We will get back to you shortly.';
                $messageClass = 'text-success';
            } catch (Throwable $e) {
                error_log('Contact inquiry error: ' . $e->getMessage());
                $message = 'An error occurred while submitting your inquiry. Please try again later.';
                $messageClass = 'text-danger';
            }
        }
    }
}

$propertyId = isset($_GET['property']) ? (int)$_GET['property'] : (isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0);
$prefilledName = $_GET['name'] ?? '';
$prefilledEmail = $_GET['email'] ?? '';
$prefilledMessage = $_GET['message'] ?? '';

if ($propertyId && !$_POST) {
    $database = new Database();
    $db = $database->getConnection();
    $pStmt = $db->prepare("SELECT title FROM properties WHERE id = ?");
    $pStmt->execute([$propertyId]);
    $prop = $pStmt->fetch(PDO::FETCH_ASSOC);
    if ($prop) {
        $prefilledMessage = "I'm interested in {$prop['title']}...";
    }
}

$pageTitle = 'Contact';
$currentPage = 'contact';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero bg-bg-alt" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4"><?php echo content('contact','hero','heading','Get in Touch'); ?></p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink"><?php echo content('contact','hero','body','Let us hear from you.'); ?></h1>
    </div>
</section>

<section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">
            <div>
                <?php if ($message): ?>
                <div class="p-4 border mb-6 font-sans text-[0.9375rem] <?php echo $messageClass; ?> border-current/20 bg-current/5" data-reveal><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form method="POST" class="flex flex-col gap-5" data-reveal>
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($propertyId): ?>
                    <input type="hidden" name="property_id" value="<?php echo $propertyId; ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="input-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="input-field" value="<?php echo htmlspecialchars($prefilledName); ?>" required>
                        </div>
                        <div>
                            <label class="input-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="input-field" value="<?php echo htmlspecialchars($prefilledEmail); ?>" required>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Subject</label>
                        <input type="text" name="subject" class="input-field" placeholder="How can we help?">
                    </div>
                    <div>
                        <label class="input-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" rows="6" class="input-field resize-y leading-relaxed" required><?php echo htmlspecialchars($prefilledMessage); ?></textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center mt-2">Send Message</button>
                </form>
            </div>
            <div data-reveal>
                <div class="bg-surface border border-border-light p-8 lg:p-10 space-y-8" data-stagger>
                    <div data-stagger-item>
                        <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-2">Email</p>
                        <p class="font-sans text-[0.9375rem] text-ink">hello@auraestates.com</p>
                    </div>
                    <div>
                        <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-2">Phone</p>
                        <p class="font-sans text-[0.9375rem] text-ink">+49 30 28 04 8000</p>
                    </div>
                    <div>
                        <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-2">Address</p>
                        <p class="font-sans text-[0.9375rem] text-ink-secondary leading-relaxed">Linienstrasse 156<br>10115 Berlin<br>Germany</p>
                    </div>
                    <div>
                        <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-2">Office Hours</p>
                        <p class="font-sans text-[0.9375rem] text-ink-secondary">Monday — Friday: 09:00 – 18:00</p>
                        <p class="font-sans text-[0.9375rem] text-ink-secondary">Saturday: 10:00 – 14:00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-16 lg:py-24 border-t border-border/40" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="flex flex-col gap-4 mb-8">
            <p class="font-mono text-[0.625rem] tracking-[0.1em] uppercase text-muted">Office</p>
            <h2 class="font-sans font-light text-[2rem] lg:text-[2.75rem] leading-[1.1] text-ink">Find Us</h2>
        </div>
        <div id="office-map" class="w-full h-[350px] lg:h-[450px] bg-bg-alt border border-border-light rounded-2xl overflow-hidden"></div>
    </div>
</section>
<script>
(function() {
    function initMap() {
        if (typeof AuraMaps !== 'undefined' && window.__googleMapsReady) {
            AuraMaps.initOfficeMap('office-map', 52.5273, 13.4028, 'Aura Estates');
        }
    }
    if (window.__googleMapsReady) { initMap(); }
    else { document.addEventListener('google-maps-ready', initMap); }
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>