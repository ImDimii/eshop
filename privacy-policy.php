<?php
define('INCLUDED', true);
require_once 'config.php';
$page_title = 'Privacy Policy';
require_once 'includes/header.php';
?>

<div class="container page-content">
    <h1>Privacy Policy</h1>
    
    <div class="privacy-section">
        <h2>1. Introduzione</h2>
        <p>La presente Privacy Policy descrive come <?php echo SITE_NAME; ?> raccoglie, utilizza e protegge i dati personali degli utenti che visitano il nostro sito web e utilizzano i nostri servizi.</p>
    </div>

    <div class="privacy-section">
        <h2>2. Dati Raccolti</h2>
        <p>Raccogliamo i seguenti tipi di dati:</p>
        <ul>
            <li>Nome e cognome</li>
            <li>Indirizzo email</li>
            <li>Informazioni di pagamento</li>
            <li>Cronologia degli ordini</li>
            <li>Dati di navigazione</li>
            <li>Cookie tecnici e di analisi</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>3. Utilizzo dei Dati</h2>
        <p>Utilizziamo i dati raccolti per:</p>
        <ul>
            <li>Processare gli ordini e le transazioni</li>
            <li>Fornire assistenza clienti</li>
            <li>Inviare comunicazioni relative agli ordini</li>
            <li>Migliorare i nostri servizi</li>
            <li>Prevenire frodi</li>
            <li>Rispettare gli obblighi legali</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>4. Conservazione dei Dati</h2>
        <p>I dati personali vengono conservati per il tempo necessario a fornire i servizi richiesti e rispettare gli obblighi legali. In particolare:</p>
        <ul>
            <li>I dati degli ordini vengono conservati per 10 anni come richiesto dalla normativa fiscale</li>
            <li>I dati di navigazione vengono conservati per 12 mesi</li>
            <li>I dati per il marketing vengono conservati fino alla revoca del consenso</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>5. Condivisione dei Dati</h2>
        <p>I dati personali possono essere condivisi con:</p>
        <ul>
            <li>Fornitori di servizi di pagamento per processare le transazioni</li>
            <li>Fornitori di servizi IT per la gestione del sito</li>
            <li>Autorità competenti quando richiesto dalla legge</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>6. Diritti degli Utenti</h2>
        <p>Gli utenti hanno il diritto di:</p>
        <ul>
            <li>Accedere ai propri dati personali</li>
            <li>Richiedere la rettifica dei dati</li>
            <li>Richiedere la cancellazione dei dati</li>
            <li>Opporsi al trattamento dei dati</li>
            <li>Richiedere la portabilità dei dati</li>
            <li>Revocare il consenso al trattamento</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>7. Sicurezza dei Dati</h2>
        <p>Adottiamo misure di sicurezza tecniche e organizzative per proteggere i dati personali, inclusi:</p>
        <ul>
            <li>Crittografia SSL per le trasmissioni dei dati</li>
            <li>Accesso limitato ai dati personali</li>
            <li>Monitoraggio regolare dei sistemi di sicurezza</li>
            <li>Formazione del personale sulla protezione dei dati</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>8. Contatti</h2>
        <p>Per qualsiasi domanda sulla privacy o per esercitare i propri diritti, è possibile contattarci a:</p>
        <p>Email: privacy@<?php echo strtolower(SITE_NAME); ?>.com</p>
    </div>

    <div class="privacy-section last-updated">
        <p>Ultimo aggiornamento: <?php echo date('d/m/Y'); ?></p>
    </div>
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

.privacy-section {
    margin-bottom: 2rem;
}

.privacy-section h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.privacy-section p {
    color: var(--text-color);
    line-height: 1.7;
    margin-bottom: 1rem;
}

.privacy-section ul {
    list-style-type: none;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.privacy-section ul li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.privacy-section ul li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: var(--primary-color);
}

.last-updated {
    margin-top: 3rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    font-style: italic;
    color: #64748b;
}

@media (max-width: 768px) {
    .page-content {
        padding: 2rem 1rem;
    }

    h1 {
        font-size: 2rem;
    }

    .privacy-section h2 {
        font-size: 1.25rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 