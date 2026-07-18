<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/config/auth.php';

// Load environment variables manually if not using a library
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$clientID = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectUri = getenv('GOOGLE_REDIRECT_URI');

if (!$redirectUri) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $projectRoot = str_replace('\\', '/', __DIR__);
    $basePrefix = rtrim(str_replace($docRoot, '', $projectRoot), '/');
    $redirectUri = $protocol . '://' . $host . $basePrefix . '/google_auth';
}

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            header('Location: ' . Auth::getBasePrefix() . '/login?error=Google authentication failed');
            exit;
        }
        $client->setAccessToken($token['access_token']);

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email =  $google_account_info->email;
        $name =  $google_account_info->name;
        $google_id = $google_account_info->id;
        $avatar = $google_account_info->picture;

        $db = (new Database())->getConnection();

        // Check if user exists by email or google_id
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? OR google_id = ?');
        $stmt->execute([$email, $google_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // User exists, update google_id and avatar if needed
            if (empty($user['google_id']) || empty($user['avatar'])) {
                $updateStmt = $db->prepare('UPDATE users SET google_id = ?, avatar = ? WHERE id = ?');
                $updateStmt->execute([$google_id, $avatar, $user['id']]);
                $user['google_id'] = $google_id;
                $user['avatar'] = $avatar;
            }
            Auth::establishSession($user);
            header('Location: ' . Auth::getDashboardUrl($user['role']));
            exit;
        } else {
            // Register new user
            $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = 'tenant'");
            $roleStmt->execute();
            $tenantRoleId = $roleStmt->fetchColumn();
            
            $randomPass = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, google_id, avatar, role, role_id) VALUES (?, ?, ?, ?, ?, 'tenant', ?)");
            $stmt->execute([$name, $email, $randomPass, $google_id, $avatar, $tenantRoleId]);
            $newId = $db->lastInsertId();
            if (!$newId) {
                $fetchStmt = $db->prepare('SELECT * FROM users WHERE email = ?');
                $fetchStmt->execute([$email]);
                $newUser = $fetchStmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $fetchStmt = $db->prepare('SELECT * FROM users WHERE id = ?');
                $fetchStmt->execute([$newId]);
                $newUser = $fetchStmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($newUser) {
                Auth::establishSession($newUser);
                header('Location: ' . Auth::getDashboardUrl($newUser['role']));
                exit;
            } else {
                header('Location: ' . Auth::getBasePrefix() . '/login?error=Registration failed');
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("Google OAuth Error: " . $e->getMessage());
        header('Location: ' . Auth::getBasePrefix() . '/login?error=' . urlencode('Google Auth Error: ' . $e->getMessage()));
        exit;
    }
} else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}
