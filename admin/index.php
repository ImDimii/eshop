<?php
require_once '../config.php';
requireLogin();

// Gestione delle azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $nome = $_POST['nome'] ?? '';
                $descrizione = $_POST['descrizione'] ?? '';
                $prezzo = $_POST['prezzo'] ?? 0;
                
                $image_path = null;
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/products/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $file_name = uniqid('product_') . '.' . $file_extension;
                        $target_file = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                            $image_path = 'uploads/products/' . $file_name;
                        }
                    }
                }
                
                if ($nome && $prezzo > 0) {
                    $stmt = $pdo->prepare("INSERT INTO products (nome, descrizione, prezzo, image_path) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nome, $descrizione, $prezzo, $image_path]);
                }
                break;

            case 'approve_order':
                $order_id = (int)$_POST['order_id'];
                $stmt = $pdo->prepare("SELECT o.*, p.nome as product_name FROM orders o JOIN products p ON o.product_id = p.id WHERE o.id = ? AND o.stato = 'in_attesa'");
                $stmt->execute([$order_id]);
                $order = $stmt->fetch();

                if ($order) {
                    // Trova una chiave disponibile
                    $stmt = $pdo->prepare("SELECT * FROM product_keys WHERE product_id = ? LIMIT 1");
                    $stmt->execute([$order['product_id']]);
                    $key = $stmt->fetch();

                    if ($key) {
                        try {
                        // Aggiorna l'ordine con la chiave
                        $stmt = $pdo->prepare("UPDATE orders SET stato = 'completato', key_assegnata = ? WHERE id = ?");
                        $stmt->execute([$key['key_value'], $order_id]);

                        // Rimuovi la chiave dal database
                        $stmt = $pdo->prepare("DELETE FROM product_keys WHERE id = ?");
                        $stmt->execute([$key['id']]);

                            // Recupera le impostazioni email
                            $stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('smtp_enabled', 'smtp_host', 'smtp_username', 'smtp_password', 'smtp_port', 'smtp_secure')");
                            $stmt->execute();
                            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                            // Invia l'email di conferma pagamento solo se è abilitato l'invio email
                            if (($settings['smtp_enabled'] ?? '') === '1') {
                                define('INCLUDED', true);
                                require_once '../includes/send_email.php';
                                $emailSent = sendPaymentConfirmationEmail(
                                    $order['email'],
                                    $order['nome'],
                                    $order['codice_ordine'],
                                    $order['product_name'],
                                    $key['key_value'],
                                    $settings
                                );

                                if (!$emailSent) {
                                    error_log("Errore nell'invio dell'email di conferma pagamento per l'ordine: {$order['codice_ordine']}");
                                }
                            }

                            echo json_encode([
                                'success' => true,
                                'message' => 'Ordine approvato con successo e chiave assegnata'
                            ]);
                        } catch (Exception $e) {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Errore durante l\'approvazione dell\'ordine: ' . $e->getMessage()
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Nessuna chiave disponibile per questo prodotto'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Ordine non trovato o già approvato'
                    ]);
                }
                exit;
                break;

            case 'add_key':
                $product_id = (int)$_POST['product_id'];
                $key_value = trim($_POST['key_value']);
                
                if ($key_value) {
                    try {
                        // Gestisce l'inserimento di chiavi multiple (una per riga)
                        $keys = array_filter(explode("\n", $key_value));
                        $success = true;
                        $added = 0;
                        
                        foreach ($keys as $key) {
                            $key = trim($key);
                            if (!empty($key)) {
                    $stmt = $pdo->prepare("INSERT INTO product_keys (product_id, key_value) VALUES (?, ?)");
                                $stmt->execute([$product_id, $key]);
                                $added++;
                            }
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'message' => "Aggiunte $added chiavi con successo"
                        ]);
                    } catch (PDOException $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Errore durante l\'aggiunta delle chiavi'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Inserisci almeno una chiave'
                    ]);
                }
                exit;
                break;

            case 'delete_key':
                $key_id = (int)$_POST['key_id'];
                try {
                $stmt = $pdo->prepare("DELETE FROM product_keys WHERE id = ?");
                $stmt->execute([$key_id]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Chiave eliminata con successo'
                    ]);
                } catch (PDOException $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Errore durante l\'eliminazione della chiave'
                    ]);
                }
                exit;
                break;

            case 'edit_key':
                $key_id = (int)$_POST['key_id'];
                $key_value = trim($_POST['key_value']);
                
                if ($key_value) {
                    try {
                    $stmt = $pdo->prepare("UPDATE product_keys SET key_value = ? WHERE id = ?");
                    $stmt->execute([$key_value, $key_id]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Chiave aggiornata con successo'
                        ]);
                    } catch (PDOException $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Errore durante l\'aggiornamento della chiave'
                        ]);
                    }
                    exit;
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Il valore della chiave non può essere vuoto'
                    ]);
                    exit;
                }
                break;

            case 'update_settings':
                $fields = [
                    'payment_instructions',
                    'amazon_instructions',
                    'card_instructions',
                    'bank_name',
                    'bank_account',
                    'bank_iban',
                    'paypal_email',
                    'smtp_enabled',
                    'smtp_host',
                    'smtp_username',
                    'smtp_password',
                    'smtp_port',
                    'smtp_secure',
                    'site_name',
                    'support_email',
                    'support_hours',
                    'support_phone',
                    'social_facebook',
                    'social_twitter',
                    'social_instagram',
                    'social_telegram'
                ];
                
                foreach ($fields as $field) {
                    // Gestione speciale per smtp_enabled
                    if ($field === 'smtp_enabled') {
                        $value = isset($_POST['smtp_enabled']) ? '1' : '0';
                    } else {
                        $value = $_POST[$field] ?? '';
                    }
                    
                        $stmt = $pdo->prepare("INSERT INTO settings (chiave, valore) VALUES (?, ?) 
                                             ON DUPLICATE KEY UPDATE valore = ?");
                    $stmt->execute([$field, $value, $value]);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Impostazioni aggiornate con successo'
                ]);
                exit;
                break;

            case 'update_ticket':
                if (isset($_POST['ticket_id'], $_POST['stato'], $_POST['risposta'])) {
                    $ticket_id = (int)$_POST['ticket_id'];
                    $stato = $_POST['stato'];
                    $risposta = $_POST['risposta'];

                    // Recupera i dettagli del ticket
                    $stmt = $pdo->prepare("SELECT email, codice_ordine FROM tickets WHERE id = ?");
                    $stmt->execute([$ticket_id]);
                    $ticket = $stmt->fetch();

                    if ($ticket) {
                        // Aggiorna il ticket
                    $stmt = $pdo->prepare("UPDATE tickets SET stato = ?, risposta = ? WHERE id = ?");
                        $stmt->execute([$stato, $risposta, $ticket_id]);

                        // Recupera le impostazioni email
                        $stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('smtp_enabled', 'smtp_host', 'smtp_username', 'smtp_password', 'smtp_port', 'smtp_secure')");
                        $stmt->execute();
                        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                        // Invia l'email di risposta solo se è abilitato l'invio email
                        if (($settings['smtp_enabled'] ?? '') === '1') {
                            define('INCLUDED', true);
                            require_once '../includes/send_email.php';
                            $emailSent = sendTicketReplyEmail(
                                $ticket['email'],
                                $ticket_id,
                                $ticket['codice_ordine'],
                                $risposta,
                                $settings
                            );

                            if (!$emailSent) {
                                error_log("Errore nell'invio dell'email di risposta per il ticket: $ticket_id");
                            }
                        }
                    }
                }
                break;

            case 'delete_product':
                $product_id = (int)$_POST['product_id'];
                
                // Verifica se ci sono chiavi associate al prodotto
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_keys WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $key_count = $stmt->fetchColumn();
                
                if ($key_count > 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Impossibile eliminare il prodotto: ci sono ancora $key_count chiavi associate. Rimuovi prima tutte le chiavi."
                    ]);
                    exit;
                }
                
                // Recupera il percorso dell'immagine prima di eliminare il prodotto
                $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($product && $product['image_path']) {
                    $image_path = '../' . $product['image_path'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Prodotto eliminato con successo'
                ]);
                exit;
                break;

            case 'edit_product':
                $product_id = (int)$_POST['product_id'];
                $nome = $_POST['nome'] ?? '';
                $descrizione = $_POST['descrizione'] ?? '';
                $prezzo = $_POST['prezzo'] ?? 0;
                
                $image_path = null;
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    // Recupera il vecchio percorso dell'immagine
                    $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
                    $stmt->execute([$product_id]);
                    $old_product = $stmt->fetch();
                    
                    // Elimina la vecchia immagine se esiste
                    if ($old_product && $old_product['image_path']) {
                        $old_image_path = '../' . $old_product['image_path'];
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    
                    $upload_dir = '../uploads/products/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $file_name = uniqid('product_') . '.' . $file_extension;
                        $target_file = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                            $image_path = 'uploads/products/' . $file_name;
                        }
                    }
                }
                
                try {
                    if ($nome && $prezzo > 0) {
                        if ($image_path) {
                            $stmt = $pdo->prepare("UPDATE products SET nome = ?, descrizione = ?, prezzo = ?, image_path = ? WHERE id = ?");
                            $stmt->execute([$nome, $descrizione, $prezzo, $image_path, $product_id]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE products SET nome = ?, descrizione = ?, prezzo = ? WHERE id = ?");
                            $stmt->execute([$nome, $descrizione, $prezzo, $product_id]);
                        }
                        echo json_encode([
                            'success' => true,
                            'message' => 'Prodotto modificato con successo'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Nome e prezzo sono obbligatori'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Errore durante la modifica del prodotto: ' . $e->getMessage()
                    ]);
                }
                exit;
                break;

            case 'add_tracking':
                $order_id = (int)$_POST['order_id'];
                $order_email = $_POST['order_email'];
                $order_code = $_POST['order_code'];
                $tracking_code = $_POST['tracking_code'];
                $courier = $_POST['courier'];
                
                $stmt = $pdo->prepare("INSERT INTO tracking (order_id, order_email, order_code, tracking_code, courier) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$order_id, $order_email, $order_code, $tracking_code, $courier]);
                
                // Recupera le impostazioni email
                $stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('smtp_enabled', 'smtp_host', 'smtp_username', 'smtp_password', 'smtp_port', 'smtp_secure')");
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                // Invia l'email di conferma tracking solo se è abilitato l'invio email
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
                        error_log("Errore nell'invio dell'email di conferma tracking per l'ordine: {$order_code}");
                    }
                }
                break;

            case 'delete_order':
                $order_id = (int)$_POST['order_id'];
                
                try {
                    // Elimina prima i record correlati
                    $stmt = $pdo->prepare("DELETE FROM payment_details WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    
                    $stmt = $pdo->prepare("DELETE FROM tracking WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    
                    // Infine elimina l'ordine
                    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                    $stmt->execute([$order_id]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Ordine eliminato con successo'
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Errore durante l\'eliminazione dell\'ordine: ' . $e->getMessage()
                    ]);
                }
                exit;
                break;

            case 'delete_ticket':
                $ticket_id = (int)$_POST['ticket_id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
                    $stmt->execute([$ticket_id]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Ticket eliminato con successo'
                    ]);
                } catch (PDOException $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Errore durante l\'eliminazione del ticket'
                    ]);
                }
                exit;
                break;
        }
    }
}

// Recupera gli ordini
$stmt = $pdo->query("
    SELECT o.*, p.nome as product_name, pd.amazon_code, pd.paypal_email 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    LEFT JOIN payment_details pd ON o.id = pd.order_id
    ORDER BY o.data_creazione DESC
");
$orders = $stmt->fetchAll();

// Recupera i prodotti con il conteggio delle chiavi
$stmt = $pdo->query("
    SELECT p.*, COUNT(k.id) as key_count 
    FROM products p 
    LEFT JOIN product_keys k ON p.id = k.product_id 
    GROUP BY p.id
");
$products = $stmt->fetchAll();

// Recupera tutte le chiavi
$stmt = $pdo->query("
    SELECT k.*, p.nome as product_name 
    FROM product_keys k 
    JOIN products p ON k.product_id = p.id 
    ORDER BY k.product_id, k.created_at DESC
");
$keys = $stmt->fetchAll();

// Recupera le impostazioni
$stmt = $pdo->query("SELECT chiave, valore FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Amministrativo - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --background-color: #f8fafc;
            --text-color: #1e293b;
            --card-bg: #ffffff;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            height: 36px;
            min-width: 36px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            white-space: nowrap;
            line-height: 1;
        }

        .btn-approve {
            background-color: var(--success-color);
        }

        .btn-approve:hover {
            background-color: #059669;
        }

        .btn-secondary {
            background-color: #e2e8f0;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background-color: #cbd5e1;
        }

        .card {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            color: var(--primary-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            height: 72px; /* Altezza fissa per tutte le celle */
        }

        th {
            font-weight: 500;
            color: #64748b;
            background-color: #f8fafc;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-in_attesa {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-completato {
            background-color: #d1fae5;
            color: #065f46;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #64748b;
        }

        input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
        }

        .tab {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab.active {
            background-color: var(--primary-color);
            color: white;
        }

        .tab:hover:not(.active) {
            background-color: #e2e8f0;
        }

        .amazon-code {
            display: inline-flex;
            align-items: center;
            font-family: monospace;
            background-color: #f8fafc;
            padding: 0.25rem 0.5rem;
            height: 28px;
            border-radius: 0.25rem;
            border: 1px solid #e2e8f0;
            margin-left: 0.5rem;
            font-size: 0.875rem;
        }

        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            height: 36px;
        }

        .payment-method i {
            font-size: 1.25rem;
        }

        .payment-method i.fa-credit-card {
            color: #1e40af;
        }

        .payment-method i.fa-amazon {
            color: #ff9900;
        }

        .payment-method i.fa-paypal {
            color: #003087;
        }

        .actions-cell {
            padding: 1rem;
            white-space: nowrap;
        }

        .actions-cell .btn {
            margin: 0 0.25rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .tabs {
                overflow-x: auto;
                padding-bottom: 0.5rem;
            }
        }

        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-danger {
            background-color: var(--error-color);
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .key-value-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .key-edit-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .key-edit-form input[type="text"] {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .table-responsive {
            overflow-x: auto;
            margin: 0 -1.5rem;
            padding: 0 1.5rem;
        }

        /* Notification styles */
        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: var(--success-color);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Stili per il modale */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto; /* Permette lo scrolling del contenuto */
            padding: 20px;
        }

        .modal-content {
            background-color: white;
            margin: 20px auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .close-modal {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #000;
        }

        .address-details {
            margin-top: 20px;
        }

        .address-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e1e4e8;
        }

        .address-row:last-child {
            border-bottom: none;
        }

        .address-row .label {
            width: 100px;
            font-weight: 600;
            color: #586069;
        }

        .address-row .value {
            flex: 1;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 20% auto;
            }
            
            .actions-cell {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .amazon-code-display {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .amazon-code-display code {
            font-family: monospace;
            font-size: 1.2rem;
            color: #2563eb;
        }

        .view-amazon-code {
            padding: 0.25rem 0.5rem !important;
            margin-left: 0.5rem !important;
        }

        .settings-subtitle {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .switch-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .switch-text {
            font-weight: 500;
        }

        .form-text {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .form-text a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .form-text a:hover {
            text-decoration: underline;
        }

        hr {
            margin: 2rem 0;
            border: 0;
            border-top: 1px solid #e2e8f0;
        }

        /* Stili per le statistiche */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stat-content h3 {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        /* Stili per i filtri */
        .filters-section {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.5rem;
        }

        .filter-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-input, .filter-select {
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            min-width: 200px;
        }

        /* Stili per la tabella scrollabile */
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }

        .table-container table {
            margin: 0;
        }

        .table-container thead {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        /* Stili per i pulsanti azione */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-form {
            display: inline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-group {
                flex-direction: column;
            }

            .filter-input, .filter-select {
            width: 100%;
            }
        }

        /* Stili per il modale di modifica prodotto */
        .image-preview {
            margin-top: 1rem;
            max-width: 200px;
        }

        .image-preview img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }

        /* Stili per il modale delle chiavi */
        .keys-container {
            display: grid;
            gap: 2rem;
        }

        .add-keys-section, .existing-keys-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 0.5rem;
        }

        .keys-list-container {
            margin-top: 1rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .keys-table {
            width: 100%;
            border-collapse: collapse;
        }

        .keys-table th,
        .keys-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .keys-table th {
            background: #f1f5f9;
            position: sticky;
            top: 0;
        }

        .key-actions {
            display: flex;
            gap: 0.5rem;
        }

        .key-value {
            font-family: monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        /* Animazioni per il caricamento */
        .loading {
            position: relative;
            opacity: 0.7;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2rem;
            height: 2rem;
            margin: -1rem 0 0 -1rem;
            border: 3px solid #f3f4f6;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Stili per le notifiche personalizzate */
        .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease-in-out;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .custom-notification.success {
            background-color: #10b981;
        }

        .custom-notification.error {
            background-color: #ef4444;
        }

        .custom-notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .custom-notification i {
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <!-- Contenitore per le notifiche -->
    <div id="notification-container"></div>

    <div class="container">
        <header>
            <h1><i class="fas fa-cog"></i> Pannello Amministrativo</h1>
            <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </header>

        <div class="tabs">
            <div class="tab active" data-tab="orders">Ordini</div>
            <div class="tab" data-tab="products">Prodotti</div>
            <div class="tab" data-tab="tickets">Ticket</div>
            <div class="tab" data-tab="settings">Impostazioni</div>
        </div>

        <div id="orders" class="tab-content">
            <!-- Dashboard Statistiche -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Ordini Totali</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>In Attesa</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM orders WHERE stato = 'in_attesa'")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Completati</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM orders WHERE stato = 'completato'")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Incasso Totale</h3>
                        <p class="stat-value">€<?php echo number_format($pdo->query("SELECT SUM(p.prezzo) FROM orders o JOIN products p ON o.product_id = p.id WHERE o.stato = 'completato'")->fetchColumn(), 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 class="card-title"><i class="fas fa-shopping-cart"></i> Gestione Ordini</h2>
                
                <!-- Filtri -->
                <div class="filters-section">
                    <div class="filter-group">
                        <input type="text" id="orderSearch" placeholder="Cerca per codice o email..." class="filter-input">
                        <select id="statusFilter" class="filter-select">
                            <option value="">Tutti gli stati</option>
                            <option value="in_attesa">In Attesa</option>
                            <option value="completato">Completato</option>
                        </select>
                        <select id="paymentFilter" class="filter-select">
                            <option value="">Tutti i pagamenti</option>
                            <option value="bonifico">Bonifico</option>
                            <option value="paypal">PayPal</option>
                            <option value="amazon">Amazon</option>
                        </select>
                        <button id="resetFilters" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset Filtri
                        </button>
                    </div>
                </div>

                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Codice</th>
                            <th>Cliente</th>
                            <th>Prodotto</th>
                            <th>Pagamento</th>
                            <th>Stato</th>
                            <th>Data</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="order-row" 
                                data-order-code="<?php echo strtolower($order['codice_ordine']); ?>"
                                data-email="<?php echo strtolower($order['email']); ?>"
                                data-status="<?php echo $order['stato']; ?>"
                                data-payment="<?php echo $order['metodo_pagamento']; ?>">
                            <td><?php echo htmlspecialchars($order['codice_ordine']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['nome'] . ' ' . $order['cognome']); ?><br>
                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td>
                                <div class="payment-method">
                                    <?php 
                                    $icons = [
                                        'bonifico' => '<i class="fas fa-university"></i>',
                                        'amazon' => '<i class="fab fa-amazon"></i>',
                                        'paypal' => '<i class="fab fa-paypal"></i>'
                                    ];
                                    $metodo = $order['metodo_pagamento'] ?? '';
                                    echo isset($icons[$metodo]) ? $icons[$metodo] : '<i class="fas fa-question-circle"></i>';
                                    echo ' ' . ucfirst($metodo);
                                    
                                    if ($metodo === 'amazon' && !empty($order['amazon_code'])) {
                                            echo ' <button type="button" class="btn btn-secondary btn-small view-amazon-code" title="Visualizza Codice Amazon" data-code="' . htmlspecialchars($order['amazon_code']) . '">
                                                <i class="fas fa-eye"></i>
                                            </button>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <span class="status status-<?php echo $order['stato']; ?>">
                                    <?php
                                    $stati = [
                                        'in_attesa' => 'In attesa',
                                        'completato' => 'Completato'
                                    ];
                                    echo $stati[$order['stato']];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['data_creazione'])); ?></td>
                            <td class="actions-cell">
                                    <div class="action-buttons">
                                    <?php if ($order['stato'] === 'in_attesa'): ?>
                                        <form method="POST" class="action-form approve-order-form" onsubmit="return false;">
                                        <input type="hidden" name="action" value="approve_order">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-approve">
                                            <i class="fas fa-check"></i>
                                            Approva
                                        </button>
                                    </form>
                                        <?php elseif ($order['stato'] === 'completato'): ?>
                                        <button type="button" class="btn btn-secondary" onclick="showTrackingModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['email']); ?>', '<?php echo htmlspecialchars($order['codice_ordine']); ?>')" title="Gestisci Tracking">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    <?php endif; ?>
                                        <form method="POST" class="action-form delete-order-form" onsubmit="return false;">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="btn btn-danger" title="Elimina Ordine">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <a href="../ordine.php?code=<?php echo $order['codice_ordine']; ?>" class="btn btn-secondary" title="Visualizza Ordine">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                        <button type="button" class="btn btn-info view-address" data-order-id="<?php echo $order['id']; ?>" title="Visualizza Indirizzo">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div id="tickets" class="tab-content" style="display: none;">
            <!-- Dashboard Statistiche Ticket -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Ticket Totali</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>In Attesa</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM tickets WHERE stato = 'aperto'")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="stat-content">
                        <h3>In Lavorazione</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM tickets WHERE stato = 'in_lavorazione'")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Risolti</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM tickets WHERE stato = 'chiuso'")->fetchColumn(); ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 class="card-title"><i class="fas fa-ticket-alt"></i> Gestione Ticket</h2>
                
                <!-- Filtri Ticket -->
                <div class="filters-section">
                    <div class="filter-group">
                        <input type="text" id="ticketSearch" placeholder="Cerca per ID o email..." class="filter-input">
                        <select id="ticketStatusFilter" class="filter-select">
                            <option value="">Tutti gli stati</option>
                            <option value="aperto">Aperti</option>
                            <option value="in_lavorazione">In Lavorazione</option>
                            <option value="chiuso">Chiusi</option>
                        </select>
                        <select id="ticketDateFilter" class="filter-select">
                            <option value="">Tutte le date</option>
                            <option value="today">Oggi</option>
                            <option value="week">Ultima settimana</option>
                            <option value="month">Ultimo mese</option>
                        </select>
                        <button id="resetTicketFilters" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset Filtri
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ordine</th>
                                <th>Email</th>
                                <th>Messaggio</th>
                                <th>Stato</th>
                                <th>Data</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tickets = $pdo->query("SELECT * FROM tickets ORDER BY data_creazione DESC")->fetchAll();
                            foreach ($tickets as $ticket):
                                $stato_class = [
                                    'aperto' => 'status-warning',
                                    'in_lavorazione' => 'status-info',
                                    'chiuso' => 'status-success'
                                ][$ticket['stato']];

                                // Calcola il tempo trascorso
                                $created = new DateTime($ticket['data_creazione']);
                                $now = new DateTime();
                                $interval = $created->diff($now);
                                $timeAgo = '';
                                
                                if ($interval->d > 0) {
                                    $timeAgo = $interval->d . ' giorni fa';
                                } elseif ($interval->h > 0) {
                                    $timeAgo = $interval->h . ' ore fa';
                                } else {
                                    $timeAgo = $interval->i . ' minuti fa';
                                }
                            ?>
                            <tr class="ticket-row" 
                                data-ticket-id="<?php echo $ticket['id']; ?>"
                                data-email="<?php echo strtolower($ticket['email']); ?>"
                                data-status="<?php echo $ticket['stato']; ?>"
                                data-date="<?php echo $ticket['data_creazione']; ?>">
                                <td>#<?php echo $ticket['id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['codice_ordine']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['email']); ?></td>
                                <td>
                                    <div class="message-preview">
                                        <?php echo nl2br(htmlspecialchars(substr($ticket['messaggio'], 0, 100) . (strlen($ticket['messaggio']) > 100 ? '...' : ''))); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status <?php echo $stato_class; ?>">
                                        <?php echo ucfirst($ticket['stato']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="ticket-time">
                                        <span class="time-ago"><?php echo $timeAgo; ?></span>
                                        <small class="full-date"><?php echo date('d/m/Y H:i', strtotime($ticket['data_creazione'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                    <button onclick="showTicketModal(<?php echo htmlspecialchars(json_encode($ticket)); ?>)"
                                            class="btn btn-secondary btn-small">
                                        <i class="fas fa-reply"></i>
                                        Rispondi
                                    </button>
                                        <form method="POST" class="action-form delete-ticket-form" onsubmit="return false;">
                                            <input type="hidden" name="action" value="delete_ticket">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <style>
                /* Stili aggiuntivi per i ticket */
                .status-warning {
                    background-color: #fef3c7;
                    color: #92400e;
                }

                .status-info {
                    background-color: #dbeafe;
                    color: #1e40af;
                }

                .status-success {
                    background-color: #d1fae5;
                    color: #065f46;
                }

                .message-preview {
                    max-width: 300px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .ticket-time {
                    display: flex;
                    flex-direction: column;
                }

                .time-ago {
                    font-weight: 500;
                }

                .full-date {
                    color: #64748b;
                    font-size: 0.75rem;
                }
            </style>

            <script>
                // Funzioni per il filtraggio dei ticket
                document.addEventListener('DOMContentLoaded', function() {
                    const ticketSearch = document.getElementById('ticketSearch');
                    const statusFilter = document.getElementById('ticketStatusFilter');
                    const dateFilter = document.getElementById('ticketDateFilter');
                    const resetFilters = document.getElementById('resetTicketFilters');
                    const ticketRows = document.querySelectorAll('.ticket-row');

                    function filterTickets() {
                        const searchTerm = ticketSearch.value.toLowerCase();
                        const statusTerm = statusFilter.value;
                        const dateTerm = dateFilter.value;
                        const now = new Date();

                        ticketRows.forEach(row => {
                            const ticketId = row.dataset.ticketId;
                            const email = row.dataset.email;
                            const status = row.dataset.status;
                            const date = new Date(row.dataset.date);

                            let matchesDate = true;
                            if (dateTerm === 'today') {
                                matchesDate = date.toDateString() === now.toDateString();
                            } else if (dateTerm === 'week') {
                                const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                                matchesDate = date >= weekAgo;
                            } else if (dateTerm === 'month') {
                                const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                                matchesDate = date >= monthAgo;
                            }

                            const matchesSearch = ticketId.includes(searchTerm) || email.includes(searchTerm);
                            const matchesStatus = !statusTerm || status === statusTerm;

                            row.style.display = matchesSearch && matchesStatus && matchesDate ? '' : 'none';
                        });
                    }

                    ticketSearch.addEventListener('input', filterTickets);
                    statusFilter.addEventListener('change', filterTickets);
                    dateFilter.addEventListener('change', filterTickets);

                    resetFilters.addEventListener('click', function() {
                        ticketSearch.value = '';
                        statusFilter.value = '';
                        dateFilter.value = '';
                        ticketRows.forEach(row => row.style.display = '');
                    });
                });
            </script>
        </div>

        <div id="products" class="tab-content" style="display: none;">
            <!-- Dashboard Statistiche Prodotti -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Prodotti Totali</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b;">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Chiavi Disponibili</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM product_keys")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10b981;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Vendite Totali</h3>
                        <p class="stat-value"><?php echo $pdo->query("SELECT COUNT(*) FROM orders WHERE stato = 'completato'")->fetchColumn(); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3b82f6;">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Valore Catalogo</h3>
                        <p class="stat-value">€<?php echo number_format($pdo->query("SELECT SUM(prezzo) FROM products")->fetchColumn(), 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="grid">
                <div class="card">
                    <h2 class="card-title"><i class="fas fa-plus"></i> Aggiungi Prodotto</h2>
                    <form id="addProductForm" method="POST" enctype="multipart/form-data" class="form">
                        <input type="hidden" name="action" value="add_product">
                        <div class="form-group">
                            <label for="nome">Nome Prodotto</label>
                            <input type="text" id="nome" name="nome" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="descrizione">Descrizione</label>
                            <textarea id="descrizione" name="descrizione" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="prezzo">Prezzo (€)</label>
                            <input type="number" id="prezzo" name="prezzo" step="0.01" min="0" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="product_image">Immagine Prodotto</label>
                            <input type="file" id="product_image" name="product_image" accept="image/*" required class="form-control">
                        </div>
                        <button type="submit" class="btn"><i class="fas fa-save"></i> Salva Prodotto</button>
                    </form>
                </div>

                <div class="card">
                    <h2 class="card-title"><i class="fas fa-key"></i> Aggiungi Chiave</h2>
                    <form id="gridAddKeyForm" method="POST" class="form">
                        <input type="hidden" name="action" value="add_key">
                        <div class="form-group">
                            <label for="product_id">Prodotto</label>
                            <select name="product_id" id="product_id" required>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['nome']); ?> 
                                    (<?php echo $product['key_count']; ?> chiavi)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="grid_key_value">Chiave</label>
                            <input type="text" name="key_value" id="grid_key_value" required>
                        </div>
                        <button type="submit" class="btn"><i class="fas fa-plus"></i> Aggiungi Chiave</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h2 class="card-title"><i class="fas fa-box"></i> Gestione Prodotti</h2>
                
                <!-- Filtri Prodotti -->
                <div class="filters-section">
                    <div class="filter-group">
                        <input type="text" id="productSearch" placeholder="Cerca per nome prodotto..." class="filter-input">
                        <select id="priceFilter" class="filter-select">
                            <option value="">Tutti i prezzi</option>
                            <option value="0-10">0-10€</option>
                            <option value="10-50">10-50€</option>
                            <option value="50-100">50-100€</option>
                            <option value="100+">100€+</option>
                        </select>
                        <select id="keyFilter" class="filter-select">
                            <option value="">Tutte le chiavi</option>
                            <option value="with">Con chiavi</option>
                            <option value="without">Senza chiavi</option>
                        </select>
                        <button id="resetProductFilters" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset Filtri
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Immagine</th>
                                <th>Nome</th>
                                <th>Descrizione</th>
                                <th>Prezzo</th>
                                <th>Chiavi Disponibili</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr class="product-row" 
                                data-name="<?php echo strtolower(htmlspecialchars($product['nome'])); ?>"
                                data-price="<?php echo $product['prezzo']; ?>"
                                data-keys="<?php echo $product['key_count']; ?>">
                                <td>
                                    <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['nome']); ?>" 
                                         class="product-thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($product['nome']); ?></td>
                                <td>
                                    <div class="description-preview">
                                        <?php echo htmlspecialchars($product['descrizione']); ?>
                                    </div>
                                </td>
                                <td>€<?php echo number_format($product['prezzo'], 2); ?></td>
                                <td>
                                    <span class="key-count <?php echo $product['key_count'] > 0 ? 'has-keys' : 'no-keys'; ?>">
                                        <?php echo $product['key_count']; ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <div class="action-buttons">
                                        <button class="btn btn-secondary btn-small" 
                                                title="Modifica Prodotto" 
                                                onclick="showEditProductModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                        <button class="btn btn-secondary btn-small" 
                                                title="Gestisci Chiavi"
                                                onclick="showKeysModal(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <form method="POST" class="delete-product-form">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" 
                                                    class="btn btn-danger btn-small" 
                                                    title="Elimina Prodotto">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <style>
                /* Stili aggiuntivi per i prodotti */
                .product-thumbnail {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    border-radius: 4px;
                }

                .description-preview {
                    max-width: 300px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .key-count {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 24px;
                    height: 24px;
                    padding: 0 8px;
                    border-radius: 12px;
                    font-weight: 500;
                    font-size: 0.875rem;
                }

                .key-count.has-keys {
                    background-color: #d1fae5;
                    color: #065f46;
                }

                .key-count.no-keys {
                    background-color: #fee2e2;
                    color: #991b1b;
                }

                .action-buttons {
                    display: flex;
                    gap: 0.5rem;
                    flex-wrap: nowrap;
                }

                .action-buttons .btn {
                    flex: 0 0 auto;
                }
            </style>

            <script>
                // Funzioni per il filtraggio dei prodotti
                document.addEventListener('DOMContentLoaded', function() {
                    const productSearch = document.getElementById('productSearch');
                    const priceFilter = document.getElementById('priceFilter');
                    const keyFilter = document.getElementById('keyFilter');
                    const resetFilters = document.getElementById('resetProductFilters');
                    const productRows = document.querySelectorAll('.product-row');

                    function filterProducts() {
                        const searchTerm = productSearch.value.toLowerCase();
                        const priceRange = priceFilter.value;
                        const keyStatus = keyFilter.value;

                        productRows.forEach(row => {
                            const name = row.dataset.name;
                            const price = parseFloat(row.dataset.price);
                            const keys = parseInt(row.dataset.keys);

                            let matchesPrice = true;
                            if (priceRange) {
                                if (priceRange === '0-10') {
                                    matchesPrice = price <= 10;
                                } else if (priceRange === '10-50') {
                                    matchesPrice = price > 10 && price <= 50;
                                } else if (priceRange === '50-100') {
                                    matchesPrice = price > 50 && price <= 100;
                                } else if (priceRange === '100+') {
                                    matchesPrice = price > 100;
                                }
                            }

                            let matchesKeys = true;
                            if (keyStatus === 'with') {
                                matchesKeys = keys > 0;
                            } else if (keyStatus === 'without') {
                                matchesKeys = keys === 0;
                            }

                            const matchesSearch = name.includes(searchTerm);

                            row.style.display = matchesSearch && matchesPrice && matchesKeys ? '' : 'none';
                        });
                    }

                    productSearch.addEventListener('input', filterProducts);
                    priceFilter.addEventListener('change', filterProducts);
                    keyFilter.addEventListener('change', filterProducts);

                    resetFilters.addEventListener('click', function() {
                        productSearch.value = '';
                        priceFilter.value = '';
                        keyFilter.value = '';
                        productRows.forEach(row => row.style.display = '');
                    });
                });

                function showKeysModal(productId) {
                    // Implementare la visualizzazione del modale per la gestione delle chiavi
                    // Questo può essere implementato in un secondo momento
                    alert('Funzionalità in sviluppo');
                }

                // Gestione del form di aggiunta chiave nella griglia
                document.getElementById('gridAddKeyForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(text => {
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error('Errore nel parsing della risposta:', text);
                            throw new Error('Errore nel formato della risposta dal server');
                        }
                        
                        if (data.success) {
                            // Pulisci il campo input
                            document.getElementById('grid_key_value').value = '';
                            
                            // Mostra notifica di successo
                            showNotification(data.message);
                            
                            // Ricarica la pagina dopo un breve delay
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            showNotification(data.message || 'Errore durante l\'aggiunta della chiave', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                        showNotification('Errore durante l\'aggiunta della chiave', 'error');
                    });
                });

                // Gestione eliminazione prodotti
                document.querySelectorAll('.delete-product-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        if (!confirm('Sei sicuro di voler eliminare questo prodotto?')) {
                            return;
                        }
                        
                        const formData = new FormData(this);
                        
                        fetch('', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(text => {
                            let data;
                            try {
                                data = JSON.parse(text);
                            } catch (e) {
                                console.error('Errore nel parsing della risposta:', text);
                                throw new Error('Errore nel formato della risposta dal server');
                            }
                            
                            if (data.success) {
                                showNotification(data.message);
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            } else {
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Errore:', error);
                            showNotification('Errore durante l\'eliminazione del prodotto', 'error');
                        });
                    });
                });
            </script>
        </div>

        <div id="settings" class="tab-content" style="display: none;">
            <div class="card">
                <h2 class="card-title"><i class="fas fa-cog"></i> Impostazioni Generali</h2>
                <form id="settingsForm" method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="form-group">
                        <label>Nome del Sito</label>
                        <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'ESHOP'); ?>" required>
                        <small class="form-text">Questo nome verrà visualizzato in tutto il sito e nelle email</small>
                    </div>

                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #e2e8f0;">

                    <h3 class="settings-subtitle"><i class="fas fa-address-card"></i> Informazioni di Contatto</h3>
                    
                    <div class="form-group">
                        <label>Email di Supporto</label>
                        <input type="email" name="support_email" class="form-control" value="<?php echo htmlspecialchars($settings['support_email'] ?? ''); ?>" placeholder="es. support@tuosito.com">
                    </div>

                    <div class="form-group">
                        <label>Orario di Supporto</label>
                        <input type="text" name="support_hours" class="form-control" value="<?php echo htmlspecialchars($settings['support_hours'] ?? ''); ?>" placeholder="es. Lun-Ven: 9:00-18:00">
                    </div>

                    <div class="form-group">
                        <label>Numero di Telefono (opzionale)</label>
                        <input type="text" name="support_phone" class="form-control" value="<?php echo htmlspecialchars($settings['support_phone'] ?? ''); ?>" placeholder="es. +39 123 456 7890">
                    </div>

                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #e2e8f0;">

                    <h3 class="settings-subtitle"><i class="fas fa-share-alt"></i> Social Media</h3>
                    
                    <div class="form-group">
                        <label>Facebook URL</label>
                        <input type="url" name="social_facebook" class="form-control" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>" placeholder="es. https://facebook.com/tuapagina">
                    </div>

                    <div class="form-group">
                        <label>Twitter URL</label>
                        <input type="url" name="social_twitter" class="form-control" value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>" placeholder="es. https://twitter.com/tuoprofilo">
                    </div>

                    <div class="form-group">
                        <label>Instagram URL</label>
                        <input type="url" name="social_instagram" class="form-control" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>" placeholder="es. https://instagram.com/tuoprofilo">
                    </div>

                    <div class="form-group">
                        <label>Telegram URL</label>
                        <input type="url" name="social_telegram" class="form-control" value="<?php echo htmlspecialchars($settings['social_telegram'] ?? ''); ?>" placeholder="es. https://t.me/tuocanale">
                    </div>

                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #e2e8f0;">

                    <h2 class="card-title"><i class="fas fa-credit-card"></i> Impostazioni Pagamento</h2>
                    
                    <div class="form-group">
                        <label>Istruzioni Pagamento Generali</label>
                        <textarea name="payment_instructions" rows="3" class="form-control"><?php echo htmlspecialchars($settings['payment_instructions'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Istruzioni Buono Amazon</label>
                        <textarea name="amazon_instructions" rows="3" class="form-control"><?php echo htmlspecialchars($settings['amazon_instructions'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Istruzioni Bonifico</label>
                        <textarea name="card_instructions" rows="3" class="form-control"><?php echo htmlspecialchars($settings['card_instructions'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Nome Banca</label>
                        <input type="text" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($settings['bank_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Intestatario Conto</label>
                        <input type="text" name="bank_account" class="form-control" value="<?php echo htmlspecialchars($settings['bank_account'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>IBAN</label>
                        <input type="text" name="bank_iban" class="form-control" value="<?php echo htmlspecialchars($settings['bank_iban'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Email PayPal</label>
                        <input type="email" name="paypal_email" class="form-control" value="<?php echo htmlspecialchars($settings['paypal_email'] ?? ''); ?>">
                    </div>

                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #e2e8f0;">

                    <h3 class="settings-subtitle"><i class="fas fa-envelope"></i> Configurazione Email</h3>
                    
                    <div class="form-group">
                        <label class="switch-label">
                            <input type="checkbox" name="smtp_enabled" id="smtp_enabled" <?php echo ($settings['smtp_enabled'] ?? '') === '1' ? 'checked' : ''; ?> value="1">
                            <span class="switch-text">Abilita invio email automatiche</span>
                        </label>
                        <small class="form-text">Quando abilitato, il sistema invierà email automatiche per conferme d'ordine e consegna chiavi</small>
                    </div>

                    <div id="smtp-settings" style="display: <?php echo ($settings['smtp_enabled'] ?? '') === '1' ? 'block' : 'none'; ?>">
                        <div class="form-group">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" placeholder="es. smtp.gmail.com">
                        </div>

                        <div class="form-group">
                            <label>SMTP Username</label>
                            <input type="text" name="smtp_username" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" placeholder="La tua email">
                        </div>

                        <div class="form-group">
                            <label>SMTP Password</label>
                            <input type="password" name="smtp_password" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" placeholder="Password o chiave applicazione">
                            <small class="form-text">Per Gmail, usa una <a href="https://support.google.com/accounts/answer/185833" target="_blank">password per le app</a></small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>SMTP Porta</label>
                                <input type="number" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>" placeholder="587">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Sicurezza SMTP</label>
                                <select name="smtp_secure" class="form-control">
                                    <option value="tls" <?php echo ($settings['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo ($settings['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="" <?php echo empty($settings['smtp_secure'] ?? '') ? 'selected' : ''; ?>>Nessuna</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-secondary" onclick="testEmailSettings()">
                                <i class="fas fa-paper-plane"></i> Testa Configurazione
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn"><i class="fas fa-save"></i> Salva Impostazioni</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal per la gestione del ticket -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Gestione Ticket</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="ticketForm" method="POST">
                    <input type="hidden" name="action" value="update_ticket">
                    <input type="hidden" name="ticket_id" id="ticket_id">
                    
                    <div class="form-group">
                        <label>Messaggio Cliente</label>
                        <div id="customerMessage" style="background: #F3F4F6; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;"></div>
                    </div>

                    <div class="form-group">
                        <label>Stato</label>
                        <select name="stato" class="form-control">
                            <option value="aperto">Aperto</option>
                            <option value="in_lavorazione">In Lavorazione</option>
                            <option value="chiuso">Chiuso</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Risposta</label>
                        <textarea name="risposta" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-paper-plane"></i>
                            Invia Risposta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modale per visualizzare l'indirizzo di spedizione -->
    <div id="addressModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-map-marker-alt"></i> Indirizzo di Spedizione</h2>
            <div id="addressDetails" class="address-details">
                <div class="address-row">
                    <span class="label">Indirizzo:</span>
                    <span id="addressStreet" class="value"></span>
                </div>
                <div class="address-row">
                    <span class="label">Città:</span>
                    <span id="addressCity" class="value"></span>
                </div>
                <div class="address-row">
                    <span class="label">Provincia:</span>
                    <span id="addressProvince" class="value"></span>
                </div>
                <div class="address-row">
                    <span class="label">CAP:</span>
                    <span id="addressZip" class="value"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale per visualizzare il codice Amazon -->
    <div id="amazonCodeModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fab fa-amazon"></i> Codice Amazon</h2>
            <div class="amazon-code-display">
                <code id="amazonCodeText"></code>
            </div>
        </div>
    </div>

    <!-- Modale per il codice di tracciamento -->
    <div id="trackingModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-truck"></i> Gestione Tracking</h2>
            <form id="trackingForm" method="POST">
                <input type="hidden" name="action" value="add_tracking">
                <input type="hidden" name="order_id" id="tracking_order_id">
                <input type="hidden" name="order_email" id="tracking_order_email">
                <input type="hidden" name="order_code" id="tracking_order_code">
                
                <div class="form-group">
                    <label for="tracking_code">Codice di Tracciamento</label>
                    <input type="text" id="tracking_code" name="tracking_code" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="courier">Corriere</label>
                    <select id="courier" name="courier" class="form-control" required>
                        <option value="BRT">BRT</option>
                        <option value="DHL">DHL</option>
                        <option value="GLS">GLS</option>
                        <option value="SDA">SDA</option>
                        <option value="UPS">UPS</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i>
                        Salva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gestione delle tab
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Rimuovi la classe active da tutte le tab
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                // Aggiungi la classe active alla tab cliccata
                this.classList.add('active');
                
                // Nascondi tutti i contenuti delle tab
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.style.display = 'none';
                });
                
                // Mostra il contenuto della tab cliccata
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).style.display = 'block';
            });
        });

        // Gestione del modale per visualizzare l'indirizzo
        const addressModal = document.getElementById('addressModal');
        const closeModal = document.querySelector('.close-modal');
        
        // Chiudi il modale quando si clicca sulla X
        closeModal.addEventListener('click', function() {
            addressModal.style.display = 'none';
        });
        
        // Chiudi il modale quando si clicca fuori dal modale
        window.addEventListener('click', function(event) {
            if (event.target === addressModal) {
                addressModal.style.display = 'none';
            }
        });
        
        // Gestisci il click sul pulsante "Visualizza Indirizzo"
        document.querySelectorAll('.view-address').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                
                // Richiedi i dati dell'indirizzo tramite AJAX
                fetch(`get_address.php?order_id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Popola il modale con i dati dell'indirizzo
                            document.getElementById('addressStreet').textContent = data.address.indirizzo;
                            document.getElementById('addressCity').textContent = data.address.citta;
                            document.getElementById('addressProvince').textContent = data.address.provincia;
                            document.getElementById('addressZip').textContent = data.address.cap;
                            
                            // Mostra il modale
                            addressModal.style.display = 'block';
                        } else {
                            alert('Errore nel recupero dell\'indirizzo: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                        alert('Si è verificato un errore durante il recupero dell\'indirizzo.');
                    });
            });
        });

        // Gestione modifica chiavi
        document.querySelectorAll('.edit-key').forEach(button => {
            button.addEventListener('click', () => {
                const keyId = button.dataset.keyId;
                const row = document.getElementById(`key-row-${keyId}`);
                const display = row.querySelector('.key-text');
                const form = row.querySelector('.key-edit-form');
                
                display.style.display = 'none';
                form.style.display = 'flex';
            });
        });

        document.querySelectorAll('.cancel-edit').forEach(button => {
            button.addEventListener('click', () => {
                const form = button.closest('.key-edit-form');
                const display = form.previousElementSibling;
                
                form.style.display = 'none';
                display.style.display = 'inline';
            });
        });

        // Mostra notifica se le impostazioni sono state aggiornate
        if (window.location.search.includes('updated=1')) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = 'Impostazioni aggiornate con successo';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function showTicketModal(ticket) {
            document.getElementById('ticket_id').value = ticket.id;
            document.getElementById('customerMessage').innerHTML = ticket.messaggio.replace(/\n/g, '<br>');
            document.querySelector('select[name="stato"]').value = ticket.stato;
            document.querySelector('textarea[name="risposta"]').value = ticket.risposta || '';
            document.getElementById('ticketModal').style.display = 'block';
        }

        // Dopo l'invio del form del ticket, aggiorna la pagina
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('', {
                method: 'POST',
                body: formData
            }).then(() => {
                location.reload();
            });
        });

        // Chiusura modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('ticketModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('ticketModal')) {
                document.getElementById('ticketModal').style.display = 'none';
            }
        }

        function showEditProductModal(product) {
            const modal = document.getElementById('editProductModal');
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_nome').value = product.nome;
            document.getElementById('edit_descrizione').value = product.descrizione;
            document.getElementById('edit_prezzo').value = product.prezzo;
            modal.style.display = 'block';
        }

        function closeEditProductModal() {
            document.getElementById('editProductModal').style.display = 'none';
        }

        // Chiudi il modale quando si clicca fuori
        window.onclick = function(event) {
            const modal = document.getElementById('editProductModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Gestione modale codice Amazon
        const amazonModal = document.getElementById('amazonCodeModal');
        const amazonCodeText = document.getElementById('amazonCodeText');

        document.querySelectorAll('.view-amazon-code').forEach(button => {
            button.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                amazonCodeText.textContent = code;
                amazonModal.style.display = 'block';
            });
        });

        // Gestione chiusura modali
        document.querySelectorAll('.close-modal').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });

        // Chiudi i modali quando si clicca fuori
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });

        // Gestione modale indirizzo
        document.querySelectorAll('.view-address').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                
                fetch(`get_address.php?order_id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('addressStreet').textContent = data.address.indirizzo;
                            document.getElementById('addressCity').textContent = data.address.citta;
                            document.getElementById('addressProvince').textContent = data.address.provincia;
                            document.getElementById('addressZip').textContent = data.address.cap;
                            
                            document.getElementById('addressModal').style.display = 'block';
                        } else {
                            alert('Errore nel recupero dell\'indirizzo: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                        alert('Si è verificato un errore durante il recupero dell\'indirizzo.');
                    });
            });
        });

        // Aggiungi questo codice JavaScript
        document.querySelector('input[name="smtp_enabled"]').addEventListener('change', function() {
            document.getElementById('smtp-settings').style.display = this.checked ? 'block' : 'none';
        });

        function testEmailSettings() {
            const formData = new FormData();
            formData.append('action', 'test_smtp');
            formData.append('smtp_host', document.querySelector('input[name="smtp_host"]').value);
            formData.append('smtp_username', document.querySelector('input[name="smtp_username"]').value);
            formData.append('smtp_password', document.querySelector('input[name="smtp_password"]').value);
            formData.append('smtp_port', document.querySelector('input[name="smtp_port"]').value);
            formData.append('smtp_secure', document.querySelector('select[name="smtp_secure"]').value);

            fetch('test_smtp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email inviata con successo!');
                } else {
                    alert('Errore: ' + data.message);
                }
            })
            .catch(error => {
                alert('Errore durante il test: ' + error.message);
            });
        }

        function showTrackingModal(orderId, email, orderCode, trackingCode = '', courier = '') {
            document.getElementById('tracking_order_id').value = orderId;
            document.getElementById('tracking_order_email').value = email;
            document.getElementById('tracking_order_code').value = orderCode;
            document.getElementById('tracking_code').value = trackingCode;
            document.getElementById('courier').value = courier;
            document.getElementById('trackingModal').style.display = 'block';
        }

        // Gestione form tracking
        document.getElementById('trackingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('add_tracking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tracking ' + (formData.get('tracking_code') ? 'aggiornato' : 'aggiunto') + ' con successo!');
                    document.getElementById('trackingModal').style.display = 'none';
                    location.reload(); // Ricarica la pagina per mostrare i cambiamenti
                } else {
                    alert('Errore: ' + data.message);
                }
            })
            .catch(error => {
                alert('Errore durante l\'invio: ' + error);
            });
        });

        // Funzioni per il filtraggio
        document.addEventListener('DOMContentLoaded', function() {
            const orderSearch = document.getElementById('orderSearch');
            const statusFilter = document.getElementById('statusFilter');
            const paymentFilter = document.getElementById('paymentFilter');
            const resetFilters = document.getElementById('resetFilters');
            const orderRows = document.querySelectorAll('.order-row');

            function filterOrders() {
                const searchTerm = orderSearch.value.toLowerCase();
                const statusTerm = statusFilter.value;
                const paymentTerm = paymentFilter.value;

                orderRows.forEach(row => {
                    const orderCode = row.dataset.orderCode;
                    const email = row.dataset.email;
                    const status = row.dataset.status;
                    const payment = row.dataset.payment;

                    const matchesSearch = orderCode.includes(searchTerm) || email.includes(searchTerm);
                    const matchesStatus = !statusTerm || status === statusTerm;
                    const matchesPayment = !paymentTerm || payment === paymentTerm;

                    row.style.display = matchesSearch && matchesStatus && matchesPayment ? '' : 'none';
                });
            }

            orderSearch.addEventListener('input', filterOrders);
            statusFilter.addEventListener('change', filterOrders);
            paymentFilter.addEventListener('change', filterOrders);

            resetFilters.addEventListener('click', function() {
                orderSearch.value = '';
                statusFilter.value = '';
                paymentFilter.value = '';
                orderRows.forEach(row => row.style.display = '');
            });
        });
    </script>

    <!-- Modale per la modifica del prodotto -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditProductModal()">&times;</span>
            <h2><i class="fas fa-edit"></i> Modifica Prodotto</h2>
            <form id="editProductForm" method="POST" enctype="multipart/form-data" class="form">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div class="form-group">
                    <label for="edit_nome">Nome Prodotto</label>
                    <input type="text" id="edit_nome" name="nome" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_descrizione">Descrizione</label>
                    <textarea id="edit_descrizione" name="descrizione" rows="3" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_prezzo">Prezzo (€)</label>
                    <input type="number" id="edit_prezzo" name="prezzo" step="0.01" min="0" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_product_image">Nuova Immagine (opzionale)</label>
                    <input type="file" id="edit_product_image" name="product_image" accept="image/*" class="form-control">
                    <div id="current_image_preview" class="image-preview mt-2"></div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Salva Modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale per la gestione delle chiavi -->
    <div id="keysModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeKeysModal()">&times;</span>
            <h2><i class="fas fa-key"></i> Gestione Chiavi</h2>
            <div class="keys-container">
                <!-- Form per aggiungere nuove chiavi -->
                <div class="add-keys-section">
                    <h3>Aggiungi Nuove Chiavi</h3>
                    <form id="addKeysForm" method="POST">
                        <input type="hidden" name="action" value="add_key">
                        <input type="hidden" name="product_id" id="keys_product_id">
                        
                        <div class="form-group">
                            <label for="key_value">Inserisci Chiavi (una per riga)</label>
                            <textarea id="key_value" name="key_value" rows="5" class="form-control" 
                                      placeholder="Inserisci una chiave per riga..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-plus"></i> Aggiungi Chiavi
                        </button>
                    </form>
                </div>

                <!-- Lista delle chiavi esistenti -->
                <div class="existing-keys-section">
                    <h3>Chiavi Esistenti</h3>
                    <div class="keys-list-container">
                        <table class="keys-table">
                            <thead>
                                <tr>
                                    <th>Chiave</th>
                                    <th>Data Creazione</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody id="keysList">
                                <!-- Le chiavi verranno caricate dinamicamente qui -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Stili per il modale di modifica prodotto */
        .image-preview {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.5rem;
            text-align: center;
        }

        .image-preview img {
            max-width: 300px;
            height: auto;
            border-radius: 0.375rem;
        }

        /* Stili per il modale delle chiavi */
        .keys-container {
            display: grid;
            gap: 2rem;
        }

        .add-keys-section, .existing-keys-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 0.5rem;
        }

        .keys-list-container {
            margin-top: 1rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .keys-table {
            width: 100%;
            border-collapse: collapse;
        }

        .keys-table th,
        .keys-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .keys-table th {
            background: #f1f5f9;
            position: sticky;
            top: 0;
        }

        .key-actions {
            display: flex;
            gap: 0.5rem;
        }

        .key-value {
            font-family: monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        /* Animazioni per il caricamento */
        .loading {
            position: relative;
            opacity: 0.7;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2rem;
            height: 2rem;
            margin: -1rem 0 0 -1rem;
            border: 3px solid #f3f4f6;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        // Funzioni per la gestione del modale di modifica prodotto
        function showEditProductModal(product) {
            const modal = document.getElementById('editProductModal');
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_nome').value = product.nome;
            document.getElementById('edit_descrizione').value = product.descrizione;
            document.getElementById('edit_prezzo').value = product.prezzo;

            // Mostra l'immagine corrente
            const imagePreview = document.getElementById('current_image_preview');
            imagePreview.innerHTML = product.image_path ? 
                `<img src="../${product.image_path}" alt="Anteprima prodotto">` : '';

            modal.style.display = 'block';
        }

        function closeEditProductModal() {
            document.getElementById('editProductModal').style.display = 'none';
        }

        // Funzioni per la gestione del modale delle chiavi
        function showKeysModal(productId) {
            const modal = document.getElementById('keysModal');
            document.getElementById('keys_product_id').value = productId;
            
            // Carica le chiavi esistenti
            loadProductKeys(productId);
            
            modal.style.display = 'block';
        }

        function closeKeysModal() {
            document.getElementById('keysModal').style.display = 'none';
        }

        function loadProductKeys(productId) {
            const keysList = document.getElementById('keysList');
            keysList.innerHTML = ''; // Pulisce la lista
            keysList.classList.add('loading');

            // Richiesta AJAX per ottenere le chiavi
            fetch(`get_product_keys.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    keysList.classList.remove('loading');
                    if (data.success) {
                        data.keys.forEach(key => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td><span class="key-value">${key.key_value}</span></td>
                                <td>${new Date(key.created_at).toLocaleString()}</td>
                                <td>
                                    <div class="key-actions">
                                        <button class="btn btn-secondary btn-small" onclick="editKey(${key.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-small" onclick="deleteKey(${key.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            keysList.appendChild(row);
                        });
                    } else {
                        keysList.innerHTML = '<tr><td colspan="3">Nessuna chiave trovata</td></tr>';
                    }
                })
                .catch(error => {
                    keysList.classList.remove('loading');
                    keysList.innerHTML = '<tr><td colspan="3">Errore nel caricamento delle chiavi</td></tr>';
                    console.error('Errore:', error);
                });
        }

        function editKey(keyId) {
            const newValue = prompt('Inserisci la nuova chiave:');
            if (newValue) {
                const formData = new FormData();
                formData.append('action', 'edit_key');
                formData.append('key_id', keyId);
                formData.append('key_value', newValue);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Errore nel parsing della risposta:', text);
                        throw new Error('Errore nel formato della risposta dal server');
                    }
                    
                    if (data.success) {
                        loadProductKeys(document.getElementById('keys_product_id').value);
                    } else {
                        alert(data.message || 'Errore durante la modifica della chiave');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore durante la modifica della chiave');
                });
            }
        }

        function deleteKey(keyId) {
            if (confirm('Sei sicuro di voler eliminare questa chiave?')) {
                const formData = new FormData();
                formData.append('action', 'delete_key');
                formData.append('key_id', keyId);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Errore nel parsing della risposta:', text);
                        throw new Error('Errore nel formato della risposta dal server');
                    }
                    
                    if (data.success) {
                        loadProductKeys(document.getElementById('keys_product_id').value);
                    } else {
                        alert(data.message || 'Errore durante l\'eliminazione della chiave');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    // Se c'è un errore ma la chiave è stata eliminata, ricarica comunque la lista
                    loadProductKeys(document.getElementById('keys_product_id').value);
                });
            }
        }

        // Gestione del form per l'aggiunta di chiavi multiple
        document.getElementById('addKeysForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Errore nel parsing della risposta:', text);
                    throw new Error('Errore nel formato della risposta dal server');
                }
                
                if (data.success) {
                    document.getElementById('key_value').value = '';
                    loadProductKeys(document.getElementById('keys_product_id').value);
                } else {
                    alert(data.message || 'Errore durante l\'aggiunta delle chiavi');
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                // Se c'è un errore ma le chiavi sono state aggiunte, ricarica comunque la lista
                loadProductKeys(document.getElementById('keys_product_id').value);
            });
        });

        // Chiusura dei modali quando si clicca fuori
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>

    <script>
        // Funzione per mostrare notifiche personalizzate
        function showNotification(message, type = 'success') {
            const container = document.getElementById('notification-container');
            const notification = document.createElement('div');
            notification.className = `custom-notification ${type}`;
            
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            notification.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;
            
            container.appendChild(notification);
            
            // Mostra la notifica con animazione
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Rimuovi la notifica dopo 3 secondi
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    container.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Gestione del form di aggiunta prodotto
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore nella risposta del server');
                }
                // Mostra notifica di successo
                showNotification('Prodotto aggiunto con successo!');
                // Pulisci il form
                this.reset();
                // Ricarica la pagina dopo un breve delay
                setTimeout(() => {
                    location.reload();
                }, 2000);
            })
            .catch(error => {
                console.error('Errore:', error);
                showNotification('Errore durante l\'aggiunta del prodotto', 'error');
            });
        });
    </script>

    <script>
        // Gestione del form di modifica prodotto
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Errore nel parsing della risposta:', text);
                    throw new Error('Errore nel formato della risposta dal server');
                }
                
                if (data.success) {
                    showNotification(data.message);
                    closeEditProductModal();
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                showNotification('Errore durante la modifica del prodotto', 'error');
            });
        });
    </script>

    <script>
        // ... existing code ...

        // Gestione approvazione ordini
        document.querySelectorAll('.approve-order-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!confirm('Sei sicuro di voler approvare questo ordine?')) {
                    return;
                }
                
                const formData = new FormData(this);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Errore nel parsing della risposta:', text);
                        throw new Error('Errore nel formato della risposta dal server');
                    }
                    
                    if (data.success) {
                        showNotification(data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    showNotification('Errore durante l\'approvazione dell\'ordine', 'error');
                });
            });
        });

        // Gestione eliminazione ordini
        document.querySelectorAll('.delete-order-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!confirm('Sei sicuro di voler eliminare questo ordine?')) {
                    return;
                }
                
                const formData = new FormData(this);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Errore nel parsing della risposta:', text);
                        throw new Error('Errore nel formato della risposta dal server');
                    }
                    
                    if (data.success) {
                        showNotification(data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    showNotification('Errore durante l\'eliminazione dell\'ordine', 'error');
                });
            });
        });
    </script>

    <script>
        // ... existing code ...

        // Gestione del form delle impostazioni
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Errore nel parsing della risposta:', text);
                    throw new Error('Errore nel formato della risposta dal server');
                }
                
                if (data.success) {
                    showNotification(data.message);
                    // Aggiorna la visualizzazione delle impostazioni SMTP
                    const smtpEnabled = document.getElementById('smtp_enabled').checked;
                    document.getElementById('smtp-settings').style.display = smtpEnabled ? 'block' : 'none';
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                showNotification('Errore durante il salvataggio delle impostazioni', 'error');
            });
        });

        // Gestione della visualizzazione delle impostazioni SMTP
        document.getElementById('smtp_enabled').addEventListener('change', function() {
            document.getElementById('smtp-settings').style.display = this.checked ? 'block' : 'none';
        });
    </script>

    <script>
        // ... existing code ...

        // Gestione eliminazione ticket
        document.querySelectorAll('.delete-ticket-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!confirm('Sei sicuro di voler eliminare questo ticket?')) {
                    return;
                }
                
                const formData = new FormData(this);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Errore nel parsing della risposta:', text);
                        throw new Error('Errore nel formato della risposta dal server');
                    }
                    
                    if (data.success) {
                        showNotification(data.message);
                        // Rimuovi la riga del ticket dalla tabella
                        const ticketRow = this.closest('tr');
                        if (ticketRow) {
                            ticketRow.remove();
                        }
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    showNotification('Errore durante l\'eliminazione del ticket', 'error');
                });
            });
        });
    </script>
</body>
</html> 