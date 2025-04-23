<?php
define('INCLUDED', true);
require_once 'config.php';
$page_title = 'Termini e Condizioni';
require_once 'includes/header.php';
?>

<div class="container page-content">
    <h1>Termini e Condizioni</h1>
    
    <div class="terms-section">
        <h2>1. Accettazione dei Termini</h2>
        <p>Utilizzando il sito <?php echo SITE_NAME; ?>, l'utente accetta integralmente i presenti termini e condizioni. Se non si accettano questi termini, si prega di non utilizzare il sito.</p>
    </div>

    <div class="terms-section">
        <h2>2. Prodotti e Servizi</h2>
        <p>2.1. <?php echo SITE_NAME; ?> vende chiavi digitali per software e giochi.</p>
        <p>2.2. Le chiavi digitali sono consegnate elettronicamente all'indirizzo email fornito durante l'acquisto.</p>
        <p>2.3. Una volta che la chiave è stata consegnata, non è possibile richiedere un rimborso a meno che la chiave non risulti non valida.</p>
    </div>

    <div class="terms-section">
        <h2>3. Processo di Acquisto</h2>
        <p>3.1. I prezzi sono indicati in Euro e includono l'IVA ove applicabile.</p>
        <p>3.2. Il contratto di vendita si considera concluso solo dopo la conferma dell'ordine da parte nostra.</p>
        <p>3.3. Ci riserviamo il diritto di rifiutare ordini in caso di sospette attività fraudolente.</p>
    </div>

    <div class="terms-section">
        <h2>4. Pagamenti</h2>
        <p>4.1. Accettiamo pagamenti tramite i metodi indicati sul sito.</p>
        <p>4.2. Tutte le transazioni sono processate in modo sicuro.</p>
        <p>4.3. In caso di pagamento non andato a buon fine, l'ordine verrà automaticamente annullato.</p>
    </div>

    <div class="terms-section">
        <h2>5. Consegna</h2>
        <p>5.1. Le chiavi digitali vengono consegnate automaticamente via email dopo la conferma del pagamento.</p>
        <p>5.2. In caso di problemi con la consegna automatica, il nostro team di supporto interverrà entro 24 ore.</p>
    </div>

    <div class="terms-section">
        <h2>6. Garanzia e Supporto</h2>
        <p>6.1. Garantiamo che tutte le chiavi vendute sono originali e non utilizzate.</p>
        <p>6.2. In caso di problemi con una chiave, il nostro servizio clienti è disponibile per assistenza.</p>
        <p>6.3. La garanzia è valida solo se la chiave non è stata attivata.</p>
    </div>

    <div class="terms-section">
        <h2>7. Privacy e Dati Personali</h2>
        <p>7.1. I dati personali vengono trattati secondo la nostra Privacy Policy.</p>
        <p>7.2. Non condividiamo i dati personali con terze parti se non necessario per il servizio.</p>
    </div>

    <div class="terms-section">
        <h2>8. Modifiche ai Termini</h2>
        <p>8.1. Ci riserviamo il diritto di modificare questi termini in qualsiasi momento.</p>
        <p>8.2. Le modifiche entrano in vigore immediatamente dopo la pubblicazione sul sito.</p>
    </div>

    <div class="terms-section last-updated">
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

.terms-section {
    margin-bottom: 2rem;
}

.terms-section h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.terms-section p {
    color: var(--text-color);
    line-height: 1.7;
    margin-bottom: 1rem;
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

    .terms-section h2 {
        font-size: 1.25rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 