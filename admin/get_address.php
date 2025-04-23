<?php
require_once '../config.php';
requireLogin();

// Verifica che sia stata fornita l'ID dell'ordine
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID ordine non valido'
    ]);
    exit;
}

$order_id = (int)$_GET['order_id'];

try {
    // Recupera i dati dell'indirizzo di spedizione
    $stmt = $pdo->prepare("SELECT indirizzo, citta, provincia, cap FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($address) {
        echo json_encode([
            'success' => true,
            'address' => $address
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ordine non trovato'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore nel recupero dei dati: ' . $e->getMessage()
    ]);
} 