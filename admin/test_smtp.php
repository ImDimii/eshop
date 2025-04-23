<?php
require_once '../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_POST['action'] !== 'test_smtp') {
    die(json_encode(['success' => false, 'message' => 'Accesso non consentito']));
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';

try {
    $mail = new PHPMailer(true);

    // Configurazione server
    $mail->isSMTP();
    $mail->Host = $_POST['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $_POST['smtp_username'];
    $mail->Password = $_POST['smtp_password'];
    $mail->SMTPSecure = $_POST['smtp_secure'] ?: PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_POST['smtp_port'];
    $mail->CharSet = 'UTF-8';

    // Mittente e destinatario
    $mail->setFrom($_POST['smtp_username'], SITE_NAME);
    $mail->addAddress($_POST['smtp_username']); // Invia l'email di test allo stesso indirizzo

    // Contenuto
    $mail->isHTML(true);
    $mail->Subject = 'Test Configurazione SMTP - ' . SITE_NAME;
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background-color: #4a90e2; color: white; padding: 20px; text-align: center;'>
            <h1 style='margin: 0;'>Test Configurazione SMTP</h1>
        </div>
        
        <div style='padding: 20px; background-color: #f8f9fa;'>
            <p>Questa è un'email di test per verificare la configurazione SMTP del tuo sito.</p>
            <p>Se stai leggendo questa email, la configurazione è corretta!</p>
            
            <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Dettagli Configurazione</h3>
                <p><strong>Host:</strong> {$_POST['smtp_host']}</p>
                <p><strong>Porta:</strong> {$_POST['smtp_port']}</p>
                <p><strong>Sicurezza:</strong> " . ($_POST['smtp_secure'] ?: 'STARTTLS') . "</p>
                <p><strong>Username:</strong> {$_POST['smtp_username']}</p>
            </div>
        </div>
        
        <div style='text-align: center; padding: 20px; background-color: #f1f3f5; color: #6c757d;'>
            <p style='margin: 0;'>Email inviata da " . SITE_NAME . "</p>
        </div>
    </div>";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Email di test inviata con successo']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
} 