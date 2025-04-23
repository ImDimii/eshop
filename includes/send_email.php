<?php
if (!defined('INCLUDED')) die('Direct access not permitted');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendOrderConfirmationEmail($to, $nome, $codice_ordine, $product_name, $metodo_pagamento, $settings) {
    $mail = new PHPMailer(true);

    try {
        // Configurazione server
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Mittente e destinatario
        $mail->setFrom($settings['smtp_username'], SITE_NAME);
        $mail->addAddress($to, $nome);

        // Contenuto
        $mail->isHTML(true);
        $mail->Subject = 'Conferma Ordine - ' . SITE_NAME;

        // Genera il link all'ordine
        $order_url = "http://{$_SERVER['HTTP_HOST']}/ordine.php?code=" . urlencode($codice_ordine);

        // Corpo HTML dell'email
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #4a90e2; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>Conferma Ordine</h1>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Gentile {$nome},</p>
                <p>Grazie per il tuo ordine! Di seguito trovi i dettagli:</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p><strong>Codice Ordine:</strong> {$codice_ordine}</p>
                    <p><strong>Prodotto:</strong> {$product_name}</p>
                    <p><strong>Metodo di Pagamento:</strong> " . ucfirst($metodo_pagamento) . "</p>
                </div>";

        // Aggiungi istruzioni specifiche per il metodo di pagamento
        switch ($metodo_pagamento) {
            case 'bonifico':
                $body .= "
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #856404;'>Istruzioni per il Bonifico</h3>
                    <p><strong>Banca:</strong> {$settings['bank_name']}</p>
                    <p><strong>Intestatario:</strong> {$settings['bank_account']}</p>
                    <p><strong>IBAN:</strong> {$settings['bank_iban']}</p>
                    <p>Inserisci il codice ordine nella causale del bonifico.</p>
                </div>";
                break;
            case 'paypal':
                $body .= "
                <div style='background-color: #cfe2ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #084298;'>Istruzioni PayPal</h3>
                    <p>Invia il pagamento a: {$settings['paypal_email']}</p>
                    <p>Usa l'opzione 'Invia a familiari e amici' per evitare commissioni.</p>
                    <p>Inserisci il codice ordine nelle note del pagamento.</p>
                </div>";
                break;
            case 'amazon':
                $body .= "
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='margin-top: 0;'>Verifica Gift Card Amazon</h3>
                    <p>Il tuo codice Amazon Gift Card verrà verificato il prima possibile.</p>
                </div>";
                break;
        }

        $body .= "
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$order_url' style='display: inline-block; padding: 12px 25px; background-color: #10b981; color: white; text-decoration: none; border-radius: 5px; font-weight: 500;'>
                        <i class='fas fa-shopping-cart'></i> Visualizza Ordine
                    </a>
                </div>

                <p>Riceverai un'email di conferma non appena il tuo pagamento sarà verificato.</p>
            </div>
            
            <div style='text-align: center; padding: 20px; background-color: #f1f3f5; color: #6c757d;'>
                <p style='margin: 0;'>Grazie per aver scelto " . SITE_NAME . "</p>
            </div>
        </div>";

        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Errore nell'invio dell'email: {$mail->ErrorInfo}");
        return false;
    }
}

function sendPaymentConfirmationEmail($to, $nome, $codice_ordine, $product_name, $key, $settings) {
    $mail = new PHPMailer(true);

    try {
        // Configurazione server
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Mittente e destinatario
        $mail->setFrom($settings['smtp_username'], SITE_NAME);
        $mail->addAddress($to, $nome);

        // Contenuto
        $mail->isHTML(true);
        $mail->Subject = 'Pagamento Confermato - ' . SITE_NAME;

        // Genera il link all'ordine
        $order_url = "http://{$_SERVER['HTTP_HOST']}/ordine.php?code=" . urlencode($codice_ordine);

        // Corpo HTML dell'email
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #10b981; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>Pagamento Confermato</h1>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Gentile {$nome},</p>
                <p>Il tuo pagamento è stato confermato! Di seguito trovi i dettagli del tuo ordine e la chiave del prodotto:</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p><strong>Codice Ordine:</strong> {$codice_ordine}</p>
                    <p><strong>Prodotto:</strong> {$product_name}</p>
                    <p><strong>Chiave Prodotto:</strong> <span style='font-family: monospace; background-color: #f8f9fa; padding: 5px; border-radius: 3px;'>{$key}</span></p>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$order_url' style='display: inline-block; padding: 12px 25px; background-color: #10b981; color: white; text-decoration: none; border-radius: 5px; font-weight: 500;'>
                        <i class='fas fa-shopping-cart'></i> Visualizza Ordine
                    </a>
                </div>

                <p style='margin-top: 20px;'>Conserva questa chiave in un luogo sicuro. Non condividerla con nessuno.</p>
                <p>Grazie per il tuo acquisto!</p>
            </div>
            
            <div style='text-align: center; padding: 20px; background-color: #f8f9fa; color: #666;'>
                <p style='margin: 0;'>Questo è un messaggio automatico, non rispondere a questa email.</p>
            </div>
        </div>";

        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));

        return $mail->send();
    } catch (Exception $e) {
        error_log("Errore nell'invio dell'email di conferma pagamento: " . $e->getMessage());
        return false;
    }
}

function sendTrackingConfirmationEmail($to, $orderCode, $trackingCode, $courier, $settings) {
    try {
        $mail = new PHPMailer(true);
        
        // Configurazione del server
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = $settings['smtp_secure'];
        $mail->Port = (int)$settings['smtp_port'];
        $mail->CharSet = 'UTF-8';

        // Mittente e destinatario
        $mail->setFrom($settings['smtp_username'], 'ESHOP');
        $mail->addAddress($to);

        // Contenuto
        $mail->isHTML(true);
        $mail->Subject = "Codice di Tracciamento per l'Ordine $orderCode";
        
        // Corpo dell'email
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #333;'>Aggiornamento Spedizione</h2>
            <p>Gentile Cliente,</p>
            <p>Il tuo ordine <strong>#$orderCode</strong> è stato spedito!</p>
            <p>Puoi tracciare il tuo pacco utilizzando le seguenti informazioni:</p>
            <div style='background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                <p><strong>Corriere:</strong> $courier</p>
                <p><strong>Codice di Tracciamento:</strong> $trackingCode</p>
            </div>
            <p>Puoi visualizzare i dettagli completi del tuo ordine cliccando sul seguente link:</p>
            <p><a href='http://{$_SERVER['HTTP_HOST']}/order.php?code=$orderCode' 
                  style='background-color: #007bff; color: white; padding: 10px 20px; 
                         text-decoration: none; border-radius: 5px; display: inline-block;'>
                Visualizza Ordine
            </a></p>
            <p>Grazie per aver scelto i nostri servizi!</p>
            <p>Cordiali saluti,<br>Il team di ESHOP</p>
        </div>";
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body));

        return $mail->send();
    } catch (Exception $e) {
        error_log("Errore nell'invio dell'email di tracking: " . $mail->ErrorInfo);
        return false;
    }
}

function sendTicketOpenedEmail($to, $ticket_id, $codice_ordine, $messaggio, $settings) {
    try {
        $mail = new PHPMailer(true);
        
        // Configurazione del server
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = $settings['smtp_secure'];
        $mail->Port = (int)$settings['smtp_port'];
        $mail->CharSet = 'UTF-8';

        // Mittente e destinatario
        $mail->setFrom($settings['smtp_username'], 'ESHOP Support');
        $mail->addAddress($to);

        // Contenuto
        $mail->isHTML(true);
        $mail->Subject = "Ticket #$ticket_id Aperto - ESHOP Support";
        
        // Genera i link
        $ticket_url = "http://{$_SERVER['HTTP_HOST']}/supporto.php?view=ticket&codice_ordine=" . urlencode($codice_ordine) . "&email=" . urlencode($to);
        $order_url = "http://{$_SERVER['HTTP_HOST']}/ordine.php?code=" . urlencode($codice_ordine);
        
        // Corpo dell'email
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #4a90e2; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>Ticket Aperto</h1>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Gentile Cliente,</p>
                <p>Abbiamo ricevuto la tua richiesta di assistenza. Ecco i dettagli del tuo ticket:</p>
                
                <div style='background-color: white; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <p><strong>Numero Ticket:</strong> #$ticket_id</p>
                    <p><strong>Ordine:</strong> $codice_ordine</p>
                    <p><strong>Il tuo messaggio:</strong></p>
                    <div style='background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 5px;'>
                        " . nl2br(htmlspecialchars($messaggio)) . "
                    </div>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$ticket_url' style='display: inline-block; padding: 12px 25px; background-color: #4a90e2; color: white; text-decoration: none; border-radius: 5px; font-weight: 500; margin-bottom: 15px;'>
                        <i class='fas fa-search'></i> Verifica Stato Ticket
                    </a>
                    <br>
                    <a href='$order_url' style='display: inline-block; padding: 12px 25px; background-color: #10b981; color: white; text-decoration: none; border-radius: 5px; font-weight: 500;'>
                        <i class='fas fa-shopping-cart'></i> Visualizza Ordine
                    </a>
                </div>

                <p>Ti risponderemo il prima possibile. Riceverai una notifica email quando il nostro team risponderà al tuo ticket.</p>
            </div>
            
            <div style='text-align: center; padding: 20px; background-color: #f1f3f5; color: #6c757d;'>
                <p style='margin: 0;'>Grazie per averci contattato!</p>
            </div>
        </div>";
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body));

        return $mail->send();
    } catch (Exception $e) {
        error_log("Errore nell'invio dell'email di apertura ticket: " . $mail->ErrorInfo);
        return false;
    }
}

function sendTicketReplyEmail($to, $ticket_id, $codice_ordine, $risposta, $settings) {
    try {
        $mail = new PHPMailer(true);
        
        // Configurazione del server
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = $settings['smtp_secure'];
        $mail->Port = (int)$settings['smtp_port'];
        $mail->CharSet = 'UTF-8';

        // Mittente e destinatario
        $mail->setFrom($settings['smtp_username'], 'ESHOP Support');
        $mail->addAddress($to);

        // Contenuto
        $mail->isHTML(true);
        $mail->Subject = "Nuova Risposta al Ticket #$ticket_id - ESHOP Support";
        
        // Genera i link
        $ticket_url = "http://{$_SERVER['HTTP_HOST']}/supporto.php?view=ticket&codice_ordine=" . urlencode($codice_ordine) . "&email=" . urlencode($to);
        $order_url = "http://{$_SERVER['HTTP_HOST']}/ordine.php?code=" . urlencode($codice_ordine);
        
        // Corpo dell'email
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #4a90e2; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>Nuova Risposta</h1>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Gentile Cliente,</p>
                <p>Abbiamo risposto al tuo ticket di supporto.</p>
                
                <div style='background-color: white; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <p><strong>Numero Ticket:</strong> #$ticket_id</p>
                    <p><strong>Ordine:</strong> $codice_ordine</p>
                    <p><strong>La nostra risposta:</strong></p>
                    <div style='background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 5px;'>
                        " . nl2br(htmlspecialchars($risposta)) . "
                    </div>
                </div>

                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$ticket_url' style='display: inline-block; padding: 12px 25px; background-color: #4a90e2; color: white; text-decoration: none; border-radius: 5px; font-weight: 500; margin-bottom: 15px;'>
                        <i class='fas fa-search'></i> Verifica Stato Ticket
                    </a>
                    <br>
                    <a href='$order_url' style='display: inline-block; padding: 12px 25px; background-color: #10b981; color: white; text-decoration: none; border-radius: 5px; font-weight: 500;'>
                        <i class='fas fa-shopping-cart'></i> Visualizza Ordine
                    </a>
                </div>

                <p>Se hai altre domande, non esitare a rispondere a questo ticket dalla sezione supporto.</p>
            </div>
            
            <div style='text-align: center; padding: 20px; background-color: #f1f3f5; color: #6c757d;'>
                <p style='margin: 0;'>Grazie per la tua pazienza!</p>
            </div>
        </div>";
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body));

        return $mail->send();
    } catch (Exception $e) {
        error_log("Errore nell'invio dell'email di risposta ticket: " . $mail->ErrorInfo);
        return false;
    }
} 