<?php
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';
Auth::requireStaff();
CsrfProtection::generate();

include_once __DIR__ . '/../../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

if(isset($_POST['update_status'])){
    $id = $_POST['inquiry_id'];
    $status = $_POST['status'];
    if(!in_array($status, ['pending', 'read', 'archived'])){
        $_SESSION['error'] = "Invalid status value";
        header("Location: inquiries.php");
        exit;
    }
    $query = "UPDATE inquiries SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

$query = "SELECT i.*, p.title as property_title 
          FROM inquiries i 
          LEFT JOIN properties p ON i.property_id = p.id 
          ORDER BY i.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{bg:'#f8f8f6','bg-alt':'#ffffff',surface:'#ffffff',ink:'#111111','ink-secondary':'#6b6b6b',graphite:'#6b6b6b',muted:'#999998',clay:'#e5e5e3',border:'#e5e5e3','border-light':'#f0f0ee',accent:'#111111','accent-hover':'#333333',success:'#2d7d46',warning:'#b8860b',danger:'#b22222'},fontFamily:{sans:['DM Sans','-apple-system','BlinkMacSystemFont','sans-serif'],mono:['JetBrains Mono','monospace'],serif:['Cormorant Garamond','Georgia','serif']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,700&family=DM+Sans:opsz,wght@9..40,200;9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../resources/css/neo.css">
    <style>::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: var(--neo-clay); border-radius: 2px; }</style>
</head>
<body class="bg-[var(--neo-bg)] text-ink antialiased">
    <div class="flex h-screen overflow-hidden">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="flex-1 overflow-auto p-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-4xl font-serif font-light italic text-ink mb-8">Inquiries</h1>
                <div class="neo-table overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr class="border-b border-clay/10">
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Date</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Name / Email</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Property</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Message</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Status</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Action</th>
                            </tr></thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="border-b border-clay/10 hover:bg-ink/[0.02] transition">
                                    <td class="p-5 text-sm"><span class="text-graphite/60 font-sans tabular-nums"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span><br><span class="text-graphite/50 text-xs font-sans tabular-nums"><?php echo date('h:i A', strtotime($row['created_at'])); ?></span></td>
                                    <td class="p-5 text-sm"><span class="text-ink/90 font-medium font-sans"><?php echo htmlspecialchars($row['name']); ?></span><br><span class="text-graphite/70 text-xs font-sans"><?php echo htmlspecialchars($row['email']); ?></span></td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans font-light"><?php echo htmlspecialchars($row['property_title']); ?></td>
                                    <td class="p-5 text-sm max-w-md"><span class="text-graphite/60 font-sans font-light"><?php echo nl2br(htmlspecialchars($row['message'])); ?></span></td>
                                    <td class="p-5 text-sm">
                                        <span class="px-3 py-1 text-[9px] font-bold uppercase tracking-[0.1em] font-sans 
                                            <?php echo $row['status'] == 'pending' ? 'bg-graphite/15 text-graphite' : ($row['status'] == 'read' ? 'bg-graphite/15 text-graphite' : 'bg-ink/5 text-graphite/60'); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm">
                                        <form method="POST" class="flex flex-col space-y-1">
                                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="inquiry_id" value="<?php echo $row['id']; ?>">
                                            <?php if($row['status'] == 'pending'): ?>
                                                <button type="submit" name="update_status" value="read" class="neo-btn neo-btn-sm text-graphite">Mark Read</button>
                                            <?php endif; ?>
                                            <?php if($row['status'] != 'archived'): ?>
                                                <button type="submit" name="update_status" value="archived" class="neo-btn neo-btn-sm text-graphite">Archive</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
