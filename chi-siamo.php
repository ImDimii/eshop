<?php
define('INCLUDED', true);
require_once 'config.php';
$page_title = 'Chi Siamo';
require_once 'includes/header.php';
?>

<div class="container page-content">
    <h1>Chi Siamo</h1>
    
    <section class="about-section">
        <h2>La Nostra Storia</h2>
        <p><?php echo SITE_NAME; ?> è nato dalla passione per il gaming e dalla volontà di offrire un servizio sicuro e affidabile per l'acquisto di chiavi digitali. Dal nostro lancio, ci siamo impegnati a fornire ai nostri clienti un'esperienza d'acquisto trasparente e sicura.</p>
    </section>

    <section class="about-section">
        <h2>La Nostra Missione</h2>
        <p>La nostra missione è semplice: rendere l'acquisto di chiavi digitali sicuro, veloce e conveniente. Ci impegniamo a:</p>
        <ul>
            <li>Garantire la massima sicurezza nelle transazioni</li>
            <li>Offrire un servizio clienti rapido ed efficiente</li>
            <li>Mantenere prezzi competitivi</li>
            <li>Verificare accuratamente ogni chiave prima della vendita</li>
        </ul>
    </section>

    <section class="about-section">
        <h2>Perché Sceglierci</h2>
        <div class="features-grid">
            <div class="feature">
                <i class="fas fa-shield-alt"></i>
                <h3>Sicurezza Garantita</h3>
                <p>Tutte le transazioni sono protette e i dati dei clienti sono gestiti con la massima sicurezza.</p>
            </div>
            <div class="feature">
                <i class="fas fa-bolt"></i>
                <h3>Consegna Istantanea</h3>
                <p>Le chiavi vengono consegnate automaticamente subito dopo la conferma del pagamento.</p>
            </div>
            <div class="feature">
                <i class="fas fa-headset"></i>
                <h3>Supporto Dedicato</h3>
                <p>Il nostro team di supporto è disponibile per aiutarti in caso di necessità.</p>
            </div>
            <div class="feature">
                <i class="fas fa-check-circle"></i>
                <h3>Chiavi Verificate</h3>
                <p>Ogni chiave viene verificata prima della vendita per garantire la massima affidabilità.</p>
            </div>
        </div>
    </section>
</div>

<style>
.page-content {
    padding: 3rem 0;
}

h1 {
    font-size: 2.5rem;
    color: var(--text-color);
    margin-bottom: 2rem;
    text-align: center;
}

.about-section {
    margin-bottom: 3rem;
}

.about-section h2 {
    color: var(--primary-color);
    font-size: 1.75rem;
    margin-bottom: 1rem;
}

.about-section p {
    color: var(--text-color);
    line-height: 1.7;
    margin-bottom: 1rem;
}

.about-section ul {
    list-style-type: none;
    padding-left: 1.5rem;
}

.about-section ul li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
    line-height: 1.6;
}

.about-section ul li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--primary-color);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature {
    text-align: center;
    padding: 2rem;
    background-color: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.feature:hover {
    transform: translateY(-5px);
}

.feature i {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.feature h3 {
    color: var(--text-color);
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}

.feature p {
    color: #64748b;
    font-size: 0.875rem;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .page-content {
        padding: 2rem 1rem;
    }

    h1 {
        font-size: 2rem;
    }

    .features-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 