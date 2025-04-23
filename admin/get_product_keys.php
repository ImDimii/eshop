<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID prodotto non specificato']);
    exit;
}

$product_id = (int)$_GET['product_id'];

try {
    $stmt = $pdo->prepare("SELECT id, key_value, created_at FROM product_keys WHERE product_id = ? ORDER BY created_at DESC");
    $stmt->execute([$product_id]);
    $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'keys' => $keys
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Errore nel recupero delle chiavi'
    ]);
} 