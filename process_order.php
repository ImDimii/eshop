<?php
define('INCLUDED', true);
require_once 'config.php';
require_once 'includes/send_email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$product_id = $_POST['product_id'] ?? '';
$metodo_pagamento = $_POST['metodo_pagamento'] ?? '';
$indirizzo = $_POST['indirizzo'] ?? '';
$citta = $_POST['citta'] ?? '';
$provincia = $_POST['provincia'] ?? '';
$cap = $_POST['cap'] ?? '';

if (empty($nome) || empty($email) || empty($product_id) || empty($metodo_pagamento) || 
    empty($indirizzo) || empty($citta) || empty($provincia) || empty($cap)) {
    header('Location: checkout.php?product_id=' . $product_id . '&error=missing_fields');
    exit;
}

try {
    // Recupera i dettagli del prodotto
    $stmt = $pdo->prepare("SELECT nome FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Prodotto non trovato');
    }

    // Genera il codice ordine usando la funzione da config.php
    $codice_ordine = generateOrderCode();

    // Inizia la transazione
    $pdo->beginTransaction();

    // Inserisci l'ordine
    $stmt = $pdo->prepare("INSERT INTO orders (nome, email, indirizzo, citta, provincia, cap, codice_ordine, product_id, metodo_pagamento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $indirizzo, $citta, $provincia, $cap, $codice_ordine, $product_id, $metodo_pagamento]);
    $order_id = $pdo->lastInsertId();

    // Prepara i dettagli del pagamento
    $payment_details = [
        'order_id' => $order_id,
        'payment_type' => $metodo_pagamento
    ];

    // Gestisci il caricamento della ricevuta
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/receipts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Formato file non supportato');
        }

        $file_name = $codice_ordine . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_file)) {
            $payment_details['receipt_path'] = $target_file;
        }
    }

    // Aggiungi dettagli specifici del metodo di pagamento
    switch ($metodo_pagamento) {
        case 'amazon':
            $payment_details['amazon_code'] = $_POST['amazon_code'] ?? '';
            if (empty($payment_details['amazon_code'])) {
                throw new Exception('Codice Amazon richiesto');
            }
            break;
        case 'paypal':
            // Nessun dato aggiuntivo richiesto, email PayPal è nelle impostazioni
            break;
        case 'bonifico':
            // Nessun dato aggiuntivo richiesto, dettagli bancari sono nelle impostazioni
            break;
    }

    // Inserisci i dettagli del pagamento
    $columns = implode(', ', array_keys($payment_details));
    $values = implode(', ', array_fill(0, count($payment_details), '?'));
    $stmt = $pdo->prepare("INSERT INTO payment_details ($columns) VALUES ($values)");
    $stmt->execute(array_values($payment_details));

    // Recupera le impostazioni necessarie per l'email
    $stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('smtp_enabled', 'smtp_host', 'smtp_username', 'smtp_password', 'smtp_port', 'smtp_secure', 'bank_name', 'bank_account', 'bank_iban', 'paypal_email')");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Invia l'email di conferma solo se è abilitato l'invio email
    if (($settings['smtp_enabled'] ?? '') === '1') {
        // Invia l'email di conferma
        $emailSent = sendOrderConfirmationEmail(
            $email,
            $nome,
            $codice_ordine,
            $product['nome'],
            $metodo_pagamento,
            $settings
        );

        if (!$emailSent) {
            // Log dell'errore ma non bloccare la transazione
            error_log("Errore nell'invio dell'email di conferma per l'ordine: $codice_ordine");
        }
    }

    // Commit della transazione
    $pdo->commit();

    // Reindirizza alla pagina dell'ordine
    header("Location: ordine.php?code=" . $codice_ordine);
    exit;

} catch (Exception $e) {
    // Rollback in caso di errore
    $pdo->rollBack();
    header('Location: checkout.php?product_id=' . $product_id . '&error=' . urlencode($e->getMessage()));
    exit;
} 