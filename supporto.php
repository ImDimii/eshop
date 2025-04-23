<?php
define('INCLUDED', true);
require_once 'config.php';
$page_title = 'Supporto';
require_once 'includes/header.php';

$message = '';
$error = '';
$ticket = null;

// Gestione visualizzazione ticket
if (isset($_GET['view']) && $_GET['view'] === 'ticket') {
    $email = $_GET['email'] ?? '';
    $codice_ordine = $_GET['codice_ordine'] ?? '';

    if ($email && $codice_ordine) {
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE email = ? AND codice_ordine = ? ORDER BY data_creazione DESC LIMIT 1");
        $stmt->execute([$email, $codice_ordine]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            $error = 'Nessun ticket trovato con questi dati';
        }
    }
}

// Gestione creazione ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codice_ordine = $_POST['codice_ordine'] ?? '';
    $email = $_POST['email'] ?? '';
    $messaggio = $_POST['messaggio'] ?? '';

    // Verifica che l'ordine esista
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE codice_ordine = ? AND email = ?");
    $stmt->execute([$codice_ordine, $email]);
    $order = $stmt->fetch();

    if ($order) {
        // Inserisci il ticket
        $stmt = $pdo->prepare("INSERT INTO tickets (codice_ordine, email, messaggio, stato) VALUES (?, ?, ?, 'aperto')");
        $stmt->execute([$codice_ordine, $email, $messaggio]);
        $ticket_id = $pdo->lastInsertId();

        // Recupera le impostazioni email
        $stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('smtp_enabled', 'smtp_host', 'smtp_username', 'smtp_password', 'smtp_port', 'smtp_secure')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Invia l'email di conferma solo se è abilitato l'invio email
        if (($settings['smtp_enabled'] ?? '') === '1') {
            require_once 'includes/send_email.php';
            $emailSent = sendTicketOpenedEmail(
                $email,
                $ticket_id,
                $codice_ordine,
                $messaggio,
                $settings
            );

            if (!$emailSent) {
                error_log("Errore nell'invio dell'email per il ticket: $ticket_id");
            }
        }

        $success = true;
        $message = "Ticket aperto con successo! Ti risponderemo il prima possibile.";
    } else {
        $error = "Codice ordine o email non validi.";
    }
}

// Recupera i ticket esistenti se sono stati forniti codice ordine ed email
$tickets = [];
if (isset($_GET['codice_ordine'], $_GET['email'])) {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE codice_ordine = ? AND email = ? ORDER BY data_creazione DESC");
    $stmt->execute([$_GET['codice_ordine'], $_GET['email']]);
    $tickets = $stmt->fetchAll();
}

// Logica esistente per la gestione dei ticket
$view = isset($_GET['view']) ? $_GET['view'] : 'main';
?>

<div class="container page-content">
    <h1>Centro Assistenza</h1>

    <?php if ($view === 'main'): ?>
    <div class="support-section">
        <h2>Come Possiamo Aiutarti?</h2>
        <div class="support-options">
            <div class="support-card">
                <i class="fas fa-ticket-alt"></i>
                <h3>Apri un Ticket</h3>
                <p>Hai bisogno di assistenza? Apri un ticket e ti risponderemo il prima possibile.</p>
                <a href="?view=new" class="btn">Nuovo Ticket</a>
            </div>
            <div class="support-card">
                <i class="fas fa-search"></i>
                <h3>Stato Ticket</h3>
                <p>Verifica lo stato del tuo ticket esistente inserendo il codice di riferimento.</p>
                <a href="?view=ticket" class="btn">Verifica Stato</a>
            </div>
        </div>
    </div>

    <div class="support-section">
        <h2>Orari di Supporto</h2>
        <p>Il nostro team è disponibile nei seguenti orari:</p>
        <ul>
            <li><strong>Lunedì - Venerdì:</strong> 9:00 - 18:00</li>
            <li><strong>Sabato - Domenica:</strong> Chiuso</li>
        </ul>
        <p>Tempo medio di risposta: entro 24 ore lavorative</p>
    </div>

    <?php elseif ($view === 'new'): ?>
    <div class="support-section">
        <h2>Nuovo Ticket</h2>
        <!-- Form esistente per la creazione del ticket -->
        <?php include 'includes/ticket_form.php'; ?>
    </div>

    <?php elseif ($view === 'ticket'): ?>
    <div class="support-section">
        <h2>Verifica Stato Ticket</h2>
        <!-- Form esistente per la verifica del ticket -->
        <?php include 'includes/ticket_status.php'; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.page-content {
    padding: 3rem 0;
    max-width: 800px;
    margin: 0 auto;
}

h1 {
    font-size: 2.5rem;
    color: var(--text-color);
    margin-bottom: 2rem;
    text-align: center;
}

.support-section {
    margin-bottom: 2rem;
    background: white;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.support-section h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.support-section p {
    color: var(--text-color);
    line-height: 1.7;
    margin-bottom: 1rem;
}

.support-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.support-card {
    text-align: center;
    padding: 2rem;
    background-color: #f8fafc;
    border-radius: 0.75rem;
    transition: transform 0.3s ease;
}

.support-card:hover {
    transform: translateY(-5px);
}

.support-card i {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.support-card h3 {
    color: var(--text-color);
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}

.support-section ul {
    list-style-type: none;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.support-section ul li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.support-section ul li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: var(--primary-color);
}

.support-section strong {
    color: var(--text-color);
    font-weight: 600;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: background-color 0.3s ease;
    margin-top: 1rem;
}

.btn:hover {
    background-color: var(--secondary-color);
}

/* Mantieni gli stili esistenti per i form dei ticket */
form {
    max-width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color);
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input[type="text"]:focus,
.form-group input[type="email"]:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
}

@media (max-width: 768px) {
    .page-content {
        padding: 2rem 1rem;
    }

    h1 {
        font-size: 2rem;
    }

    .support-section h2 {
        font-size: 1.25rem;
    }

    .support-options {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 