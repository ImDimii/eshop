<?php
define('INCLUDED', true);
require_once 'config.php';
$page_title = 'Cookie Policy';
?>
<?php require_once 'includes/header.php'; ?> 
<div class="container page-content">
    <h1>Cookie Policy</h1>
    
    <div class="cookie-section">
        <h2>1. Cosa sono i Cookie</h2>
        <p>I cookie sono piccoli file di testo che i siti web salvano sul tuo dispositivo durante la navigazione. Sono utili perché permettono ai siti web di ricordare le tue azioni e preferenze per un periodo di tempo determinato.</p>
    </div>

    <div class="cookie-section">
        <h2>2. Come Utilizziamo i Cookie</h2>
        <p>Su <?php echo SITE_NAME; ?> utilizziamo diverse tipologie di cookie per migliorare la tua esperienza di navigazione:</p>
        <ul>
            <li><strong>Cookie Tecnici:</strong> Essenziali per il funzionamento del sito e per fornire i servizi richiesti</li>
            <li><strong>Cookie di Sessione:</strong> Temporanei, vengono eliminati quando chiudi il browser</li>
            <li><strong>Cookie di Analisi:</strong> Ci aiutano a capire come gli utenti interagiscono con il sito</li>
            <li><strong>Cookie di Preferenze:</strong> Ricordano le tue scelte e personalizzazioni</li>
        </ul>
    </div>

    <div class="cookie-section">
        <h2>3. Cookie di Prima e Terza Parte</h2>
        <p>Sul nostro sito sono presenti:</p>
        <ul>
            <li><strong>Cookie di Prima Parte:</strong> Impostati direttamente da <?php echo SITE_NAME; ?></li>
            <li><strong>Cookie di Terze Parti:</strong> Impostati da servizi esterni per:</li>
        </ul>
        <ul class="sub-list">
            <li>Analisi del traffico (Google Analytics)</li>
            <li>Elaborazione dei pagamenti</li>
            <li>Funzionalità di sicurezza</li>
        </ul>
    </div>

    <div class="cookie-section">
        <h2>4. Cookie Tecnici</h2>
        <p>I cookie tecnici sono essenziali per:</p>
        <ul>
            <li>Mantenere attiva la sessione di navigazione</li>
            <li>Ricordare i prodotti nel carrello</li>
            <li>Gestire l'autenticazione</li>
            <li>Garantire la sicurezza delle transazioni</li>
        </ul>
    </div>

    <div class="cookie-section">
        <h2>5. Cookie Analitici</h2>
        <p>Utilizziamo cookie analitici per:</p>
        <ul>
            <li>Analizzare il traffico del sito</li>
            <li>Monitorare le pagine più visitate</li>
            <li>Comprendere come gli utenti utilizzano il sito</li>
            <li>Migliorare l'esperienza utente</li>
        </ul>
    </div>

    <div class="cookie-section">
        <h2>6. Gestione dei Cookie</h2>
        <p>Puoi gestire le preferenze sui cookie in diversi modi:</p>
        <ul>
            <li>Attraverso le impostazioni del browser</li>
            <li>Utilizzando il banner dei cookie sul nostro sito</li>
            <li>Tramite strumenti di terze parti</li>
        </ul>
    </div>

    <div class="cookie-section">
        <h2>7. Disabilitazione dei Cookie</h2>
        <p>La disabilitazione dei cookie potrebbe limitare l'utilizzo del sito e impedire l'accesso ad alcune funzionalità. In particolare:</p>
        <ul>
            <li>Non sarà possibile mantenere gli articoli nel carrello</li>
            <li>L'accesso all'area personale potrebbe essere limitato</li>
            <li>Alcune funzionalità potrebbero non essere disponibili</li>
        </ul>
    </div>

    <div class="cookie-section">
        <h2>8. Aggiornamenti della Cookie Policy</h2>
        <p>Ci riserviamo il diritto di aggiornare questa Cookie Policy in qualsiasi momento. Le modifiche saranno effettive dal momento della pubblicazione sul sito.</p>
    </div>

    <div class="cookie-section last-updated">
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

.cookie-section {
    margin-bottom: 2rem;
}

.cookie-section h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.cookie-section p {
    color: var(--text-color);
    line-height: 1.7;
    margin-bottom: 1rem;
}

.cookie-section ul {
    list-style-type: none;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.cookie-section ul li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.cookie-section ul li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: var(--primary-color);
}

.cookie-section .sub-list {
    margin-left: 2rem;
}

.cookie-section .sub-list li:before {
    content: "◦";
}

.cookie-section strong {
    color: var(--text-color);
    font-weight: 600;
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

    .cookie-section h2 {
        font-size: 1.25rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 