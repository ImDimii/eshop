<?php
session_start();

// Configurazione Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eshop');

// Connessione al database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Errore di connessione: " . $e->getMessage());
}

// Funzioni di utilità
function generateOrderCode() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        // Determina se siamo già nella cartella admin
        $current_path = $_SERVER['PHP_SELF'];
        if (strpos($current_path, '/admin/') !== false) {
            header('Location: login.php');
        } else {
            header('Location: admin/login.php');
        }
        exit;
    }
}

// Recupera le impostazioni dal database
try {
    $stmt = $pdo->query("SELECT chiave, valore FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $settings = [];
    error_log("Errore nel recupero delle impostazioni: " . $e->getMessage());
}

// Configurazioni del sito
define('SITE_NAME', $settings['site_name'] ?? 'ESHOP');
define('SITE_URL', 'http://localhost/ESHOP');

// Configurazioni email predefinite
// Queste possono essere sovrascritte dalle impostazioni nel database
$default_settings = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_username' => 'exmaple@gmail.com',
    'smtp_password' => 'nltv oitr xnpy polt',
    'smtp_port' => '587',
    'smtp_secure' => 'tls',
    'site_name' => 'ESHOP'
];

// Inserisci le impostazioni predefinite nel database se non esistono
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM settings WHERE chiave IN ('smtp_host', 'smtp_username', 'smtp_password', 'smtp_port', 'smtp_secure', 'site_name')");
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result['count'] < 6) {
        foreach ($default_settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO settings (chiave, valore) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }
} catch (PDOException $e) {
    error_log("Errore nell'inizializzazione delle impostazioni: " . $e->getMessage());
} 