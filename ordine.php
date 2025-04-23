<?php
define('INCLUDED', true);
require_once 'config.php';
$page_title = 'Stato Ordine';
require_once 'includes/header.php';

if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit;
}

$codice_ordine = $_GET['code'];

// Recupera i dettagli dell'ordine
$stmt = $pdo->prepare("
    SELECT o.*, p.nome as product_name, p.prezzo, pd.payment_type, pd.amazon_code, pd.receipt_path
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN payment_details pd ON o.id = pd.order_id
    WHERE o.codice_ordine = ?
");
$stmt->execute([$codice_ordine]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Recupera le istruzioni di pagamento
$stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('bank_name', 'bank_account', 'bank_iban', 'paypal_email')");
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Mappa degli stati dell'ordine
$stati = [
    'in_attesa' => ['label' => 'In Attesa', 'color' => '#f59e0b'],
    'approvato' => ['label' => 'In Elaborazione', 'color' => '#3b82f6'],
    'completato' => ['label' => 'Completato', 'color' => '#10b981']
];

// Verifica se esiste un tracking per questo ordine
$stmt = $pdo->prepare("SELECT tracking_code, courier FROM tracking WHERE order_id = ?");
$stmt->execute([$order['id']]);
$tracking = $stmt->fetch();
?>

<div class="container page-content">
    <h1>Dettagli Ordine</h1>
    
    <div class="order-grid">
        <!-- Riepilogo Ordine -->
        <div class="order-section">
            <h2>Riepilogo Ordine</h2>
            <div class="order-details">
                <div class="detail-row">
                    <span class="label">Codice Ordine:</span>
                    <span class="value"><?php echo htmlspecialchars($codice_ordine); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Prodotto:</span>
                    <span class="value"><?php echo htmlspecialchars($order['product_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Prezzo:</span>
                    <span class="value">€<?php echo number_format($order['prezzo'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Data:</span>
                    <span class="value"><?php echo date('d/m/Y H:i', strtotime($order['data_creazione'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Stato:</span>
                    <span class="order-status" style="background-color: <?php echo $stati[$order['stato']]['color']; ?>">
                        <?php echo $stati[$order['stato']]['label']; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Dettagli Spedizione -->
        <div class="order-section">
            <h2>Indirizzo di Spedizione</h2>
            <div class="shipping-details">
                <div class="detail-row">
                    <span class="label">Indirizzo:</span>
                    <span class="value"><?php echo htmlspecialchars($order['indirizzo']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Città:</span>
                    <span class="value"><?php echo htmlspecialchars($order['citta']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Provincia:</span>
                    <span class="value"><?php echo htmlspecialchars($order['provincia']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">CAP:</span>
                    <span class="value"><?php echo htmlspecialchars($order['cap']); ?></span>
                </div>
            </div>
        </div>

        <!-- Dettagli Pagamento -->
        <div class="order-section">
            <h2>Dettagli Pagamento</h2>
            <div class="payment-details">
                <div class="detail-row">
                    <span class="label">Metodo:</span>
                    <span class="value">
                        <?php
                        $metodi = [
                            'bonifico' => 'Bonifico Bancario',
                            'paypal' => 'PayPal',
                            'amazon' => 'Amazon Gift Card'
                        ];
                        echo $metodi[$order['metodo_pagamento']] ?? $order['metodo_pagamento'];
                        ?>
                    </span>
                </div>

                <?php if ($order['metodo_pagamento'] === 'bonifico'): ?>
                <div class="payment-info">
                    <p><strong>Banca:</strong> <?php echo htmlspecialchars($settings['bank_name'] ?? ''); ?></p>
                    <p><strong>Intestatario:</strong> <?php echo htmlspecialchars($settings['bank_account'] ?? ''); ?></p>
                    <p><strong>IBAN:</strong> <?php echo htmlspecialchars($settings['bank_iban'] ?? ''); ?></p>
                </div>
                <?php elseif ($order['metodo_pagamento'] === 'paypal'): ?>
                <div class="payment-info">
                    <p><strong>PayPal:</strong> <?php echo htmlspecialchars($settings['paypal_email'] ?? ''); ?></p>
                </div>
                <?php elseif ($order['metodo_pagamento'] === 'amazon' && $order['amazon_code']): ?>
                <div class="payment-info">
                    <p><strong>Codice Gift Card:</strong> <?php echo htmlspecialchars($order['amazon_code']); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($order['receipt_path']): ?>
                <div class="receipt-section">
                    <h3>Ricevuta di Pagamento</h3>
                    <?php
                    $extension = strtolower(pathinfo($order['receipt_path'], PATHINFO_EXTENSION));
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])):
                    ?>
                        <img src="<?php echo htmlspecialchars($order['receipt_path']); ?>" alt="Ricevuta" class="receipt-image">
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($order['receipt_path']); ?>" class="btn btn-secondary" target="_blank">
                            <i class="fas fa-file-pdf"></i> Visualizza Ricevuta
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($order['stato'] === 'completato' && $order['key_assegnata']): ?>
        <!-- Chiave Prodotto -->
        <div class="order-section">
            <h2>Chiave Prodotto</h2>
            <div class="key-details">
                <div class="product-key">
                    <?php echo htmlspecialchars($order['key_assegnata']); ?>
                </div>
                <p class="key-instructions">
                    La tua chiave è stata attivata e può essere utilizzata immediatamente.
                    Conserva questa chiave in un luogo sicuro.
                </p>
                <?php if ($tracking): ?>
                <div class="tracking-box">
                    <h3><i class="fas fa-truck"></i> Informazioni Spedizione</h3>
                    <div class="tracking-content">
                        <div class="tracking-row">
                            <span class="tracking-label">Corriere:</span>
                            <span class="tracking-value"><?php echo htmlspecialchars($tracking['courier']); ?></span>
                        </div>
                        <div class="tracking-row">
                            <span class="tracking-label">Tracking:</span>
                            <span class="tracking-value code"><?php echo htmlspecialchars($tracking['tracking_code']); ?></span>
                        </div>
                        <?php
                        $tracking_url = '';
                        switch($tracking['courier']) {
                            case 'BRT':
                                $tracking_url = 'https://tracking.brt.it/tracking.aspx?reference=' . urlencode($tracking['tracking_code']);
                                break;
                            case 'DHL':
                                $tracking_url = 'https://www.dhl.com/it-it/home/tracking/tracking-express.html?submit=1&tracking-id=' . urlencode($tracking['tracking_code']);
                                break;
                            case 'GLS':
                                $tracking_url = 'https://www.gls-italy.com/?option=com_gls&view=track_e_trace&mode=search&numero_spedizione=' . urlencode($tracking['tracking_code']);
                                break;
                            case 'SDA':
                                $tracking_url = 'https://www.sda.it/wps/portal/Servizi_online/ricerca_spedizioni?locale=it&tracing.letteraVettura=' . urlencode($tracking['tracking_code']);
                                break;
                            case 'UPS':
                                $tracking_url = 'https://www.ups.com/track?loc=it_IT&tracknum=' . urlencode($tracking['tracking_code']);
                                break;
                        }
                        if ($tracking_url): ?>
                        <div class="tracking-actions">
                            <a href="<?php echo $tracking_url; ?>" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt"></i> Traccia Spedizione
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="tracking-box pending">
                    <p><i class="fas fa-truck"></i> Appena spediremo il prodotto riceverai il tracking tramite email!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($order['stato'] === 'in_attesa'): ?>
        <!-- Istruzioni per il Pagamento -->
        <div class="order-section">
            <h2>Istruzioni</h2>
            <div class="instructions">
                <p>Per completare l'ordine, segui questi passaggi:</p>
                <ol>
                    <li>Effettua il pagamento utilizzando il metodo selezionato</li>
                    <li>Carica la ricevuta di pagamento se non l'hai già fatto</li>
                    <li>Attendi la verifica del pagamento (24-48 ore lavorative)</li>
                </ol>
                <p>Riceverai la chiave del prodotto non appena il pagamento sarà verificato.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-content {
    padding: 3rem 0;
    max-width: 1200px;
    margin: 0 auto;
}

h1 {
    font-size: 2.5rem;
    color: var(--text-color);
    margin-bottom: 2rem;
    text-align: center;
}

.order-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.order-section {
    background: white;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.order-section h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}

.order-section h3 {
    color: var(--text-color);
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.order-details,
.payment-details {
    background-color: #f8fafc;
    padding: 1.5rem;
    border-radius: 0.5rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.detail-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.label {
    color: #64748b;
    font-weight: 500;
}

.value {
    color: var(--text-color);
    font-weight: 600;
}

.order-status {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    color: white;
    font-size: 0.875rem;
    font-weight: 500;
}

.payment-info {
    margin-top: 1rem;
    padding: 1rem;
    background-color: white;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

.payment-info p {
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.payment-info p:last-child {
    margin-bottom: 0;
}

.payment-info strong {
    color: var(--text-color);
    font-weight: 600;
}

.receipt-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.receipt-image {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.key-details {
    text-align: center;
}

.product-key {
    background-color: #f8fafc;
    padding: 1.5rem;
    border-radius: 0.5rem;
    font-family: monospace;
    font-size: 1.25rem;
    color: var(--text-color);
    margin-bottom: 1rem;
    word-break: break-all;
    border: 2px dashed #e2e8f0;
}

.key-instructions {
    color: #64748b;
    font-size: 0.875rem;
    line-height: 1.6;
}

.instructions {
    color: var(--text-color);
    line-height: 1.6;
}

.instructions ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.instructions li {
    margin-bottom: 0.5rem;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #64748b;
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.btn-secondary:hover {
    background-color: #475569;
}

.tracking-box {
    margin-top: 2rem;
    padding: 1.5rem;
    background-color: #f8fafc;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

.tracking-box.pending {
    text-align: center;
    color: #64748b;
}

.tracking-box h3 {
    color: var(--primary-color);
    font-size: 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tracking-content {
    background-color: white;
    padding: 1rem;
    border-radius: 0.5rem;
}

.tracking-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.tracking-row:last-child {
    border-bottom: none;
}

.tracking-label {
    color: #64748b;
    font-weight: 500;
}

.tracking-value {
    color: var(--text-color);
    font-weight: 600;
}

.tracking-value.code {
    font-family: monospace;
    background: #f8fafc;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    border: 1px solid #e2e8f0;
}

.tracking-actions {
    margin-top: 1rem;
    text-align: center;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #1e40af;
}

@media (max-width: 768px) {
    .page-content {
        padding: 2rem 1rem;
    }

    h1 {
        font-size: 2rem;
    }

    .order-section h2 {
        font-size: 1.25rem;
    }

    .order-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 