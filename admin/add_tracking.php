<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

try {
    $order_id = (int)$_POST['order_id'];
    $order_email = $_POST['order_email'];
    $order_code = $_POST['order_code'];
    $tracking_code = $_POST['tracking_code'];
    $courier = $_POST['courier'];

    // Verifica che l'ordine esista
    $stmt = $pdo->prepare("SELECT id, email FROM orders WHERE id = ? AND stato = 'completato'");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Ordine non trovato o non completato');
    }

    // Inserisci o aggiorna il tracking
    $stmt = $pdo->prepare("INSERT INTO tracking (order_id, tracking_code, courier) 
                          VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE tracking_code = ?, courier = ?");
    $stmt->execute([$order_id, $tracking_code, $courier, $tracking_code, $courier]);

    // Recupera le impostazioni email
    $stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('smtp_enabled', 'smtp_host', 'smtp_username', 'smtp_password', 'smtp_port', 'smtp_secure')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Invia l'email solo se è abilitato l'invio email
    if (($settings['smtp_enabled'] ?? '') === '1') {
        define('INCLUDED', true);
        require_once '../includes/send_email.php';
        $emailSent = sendTrackingConfirmationEmail(
            $order_email,
            $order_code,
            $tracking_code,
            $courier,
            $settings
        );

        if (!$emailSent) {
            error_log("Errore nell'invio dell'email di tracking per l'ordine: $order_code");
        }
    }

    echo json_encode(['success' => true, 'message' => 'Tracking aggiunto con successo']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 