<?php
define('INCLUDED', true);
require_once 'config.php';
$page_title = 'Checkout';
require_once 'includes/header.php';

// Verifica se è stato fornito un product_id
if (!isset($_GET['product_id'])) {
    header('Location: index.php');
    exit;
}

// Recupera i dettagli del prodotto
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$_GET['product_id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Recupera le istruzioni di pagamento
$stmt = $pdo->prepare("SELECT chiave, valore FROM settings WHERE chiave IN ('bank_name', 'bank_account', 'bank_iban', 'paypal_email')");
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="container page-content">
    <h1>Completa il tuo Ordine</h1>

    <div class="checkout-grid">
        <!-- Riepilogo Prodotto -->
        <div class="checkout-section product-summary">
            <h2>Riepilogo Ordine</h2>
            <div class="product-card">
                <?php if (!empty($product['image_path'])): ?>
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['nome']); ?>">
                </div>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($product['nome']); ?></h3>
                <p class="product-description"><?php echo htmlspecialchars($product['descrizione']); ?></p>
                <div class="product-price">€<?php echo number_format($product['prezzo'], 2); ?></div>
            </div>
        </div>

        <!-- Form Checkout -->
        <div class="checkout-section checkout-form">
            <h2>Dati Personali</h2>
            <form id="checkoutForm" method="POST" action="process_order.php" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_GET['product_id']); ?>">
                
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required placeholder="Inserisci il tuo nome completo">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="La tua email per ricevere la chiave">
                </div>

                <div class="form-group">
                    <label for="indirizzo">Indirizzo di Spedizione</label>
                    <input type="text" id="indirizzo" name="indirizzo" required placeholder="Via, numero civico">
                </div>

                <div class="form-group">
                    <label for="citta">Città</label>
                    <input type="text" id="citta" name="citta" required placeholder="Città">
                </div>

                <div class="form-group">
                    <label for="provincia">Provincia</label>
                    <input type="text" id="provincia" name="provincia" required placeholder="Provincia (es. MI, RM)">
                </div>

                <div class="form-group">
                    <label for="cap">CAP</label>
                    <input type="text" id="cap" name="cap" required placeholder="CAP" pattern="[0-9]{5}" title="Inserisci un CAP valido di 5 cifre">
                </div>

                <div class="form-group">
                    <label for="metodo_pagamento">Metodo di Pagamento</label>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="bonifico" name="metodo_pagamento" value="bonifico" required>
                            <label for="bonifico">
                                <i class="fas fa-university"></i>
                                <span>Bonifico Bancario</span>
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="paypal" name="metodo_pagamento" value="paypal">
                            <label for="paypal">
                                <i class="fab fa-paypal"></i>
                                <span>PayPal</span>
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="amazon" name="metodo_pagamento" value="amazon">
                            <label for="amazon">
                                <i class="fab fa-amazon"></i>
                                <span>Amazon Gift Card</span>
                            </label>
                        </div>
                    </div>

                    <!-- Campi Dinamici per Metodi di Pagamento -->
                    <div class="payment-details-container">
                        <div id="bonifico_details" class="payment-details" style="display: none;">
                            <div class="payment-info">
                                <h3><i class="fas fa-info-circle"></i> Istruzioni per il Bonifico</h3>
                                <div class="payment-info-grid">
                                    <div class="info-item">
                                        <label>Banca:</label>
                                        <span><?php echo htmlspecialchars($settings['bank_name'] ?? ''); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Intestatario:</label>
                                        <span><?php echo htmlspecialchars($settings['bank_account'] ?? ''); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <label>IBAN:</label>
                                        <span class="iban"><?php echo htmlspecialchars($settings['bank_iban'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <div class="payment-instructions">
                                    <p><i class="fas fa-exclamation-triangle"></i> Importante:</p>
                                    <ul>
                                        <li>Inserisci il tuo nome e cognome nella causale</li>
                                        <li>Carica la ricevuta del bonifico dopo il pagamento</li>
                                        <li>La chiave verrà inviata dopo la verifica del pagamento</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div id="paypal_details" class="payment-details" style="display: none;">
                            <div class="payment-info">
                                <h3><i class="fab fa-paypal"></i> Istruzioni PayPal</h3>
                                <div class="payment-info-grid">
                                    <div class="info-item">
                                        <label>Email PayPal:</label>
                                        <span><?php echo htmlspecialchars($settings['paypal_email'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <div class="payment-instructions">
                                    <p><i class="fas fa-exclamation-triangle"></i> Importante:</p>
                                    <ul>
                                        <li>Invia il pagamento come "amici e familiari"</li>
                                        <li>Inserisci il tuo indirizzo email nelle note</li>
                                        <li>Carica lo screenshot della transazione PayPal</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div id="amazon_details" class="payment-details" style="display: none;">
                            <div class="payment-info">
                                <h3><i class="fab fa-amazon"></i> Gift Card Amazon</h3>
                                <div class="form-group amazon-code-input">
                                    <label for="amazon_code">Codice Gift Card:</label>
                                    <input type="text" id="amazon_code" name="amazon_code" placeholder="Inserisci il codice della gift card" pattern="[A-Za-z0-9-]+" title="Inserisci un codice gift card valido">
                                </div>
                                <div class="payment-instructions">
                                    <p><i class="fas fa-exclamation-triangle"></i> Importante:</p>
                                    <ul>
                                        <li>Inserisci il codice della gift card Amazon</li>
                                        <li>Carica lo screenshot della gift card come prova</li>
                                        <li>Il codice verrà verificato prima dell'invio della chiave</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="receipt">Ricevuta di Pagamento</label>
                    <div class="file-upload">
                        <input type="file" id="receipt" name="receipt" accept="image/*,.pdf">
                        <label for="receipt">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Scegli un file</span>
                        </label>
                        <div class="file-info">Nessun file selezionato</div>
                    </div>
                    <div class="upload-info">
                        <small class="file-types">Formati accettati: JPG, PNG, PDF. Max 5MB</small>
                        <small class="optional-note"><i class="fas fa-info-circle"></i> La ricevuta non è obbligatoria, ma caricarla velocizza il processo di verifica e l'invio della chiave.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <span class="btn-content">
                            <i class="fas fa-lock"></i>
                            <span class="btn-text">
                                <span class="btn-title">Completa Ordine</span>
                            </span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
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

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
}

.checkout-section {
    background: white;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.checkout-section h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}

.product-card {
    background-color: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.product-image {
    margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    border-radius: 8px 8px 0 0;
    overflow: hidden;
    position: relative;
    padding-top: 56.25%; /* Aspect ratio 16:9 */
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #1a202c;
}

.product-description {
    color: #4a5568;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.product-price {
    font-size: 2rem;
    font-weight: 600;
    color: #2563eb;
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
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input[type="file"] {
    width: 100%;
    padding: 0.5rem 0;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #64748b;
    font-size: 0.875rem;
}

.payment-details-container {
    margin-top: 1.5rem;
}

.payment-details {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-top: 1rem;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.payment-info h3 {
    color: var(--primary-color);
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.payment-info-grid {
    display: grid;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-item {
    display: grid;
    grid-template-columns: 120px 1fr;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background-color: white;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

.info-item label {
    color: #64748b;
    font-weight: 500;
}

.info-item span {
    color: var(--text-color);
    font-weight: 500;
}

.info-item .iban {
    font-family: monospace;
    font-size: 1.1rem;
    letter-spacing: 1px;
}

.payment-instructions {
    background-color: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

.payment-instructions p {
    color: #9a3412;
    font-weight: 600;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.payment-instructions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.payment-instructions li {
    color: #7c2d12;
    padding-left: 1.5rem;
    position: relative;
    margin-bottom: 0.5rem;
}

.payment-instructions li:before {
    content: "•";
    position: absolute;
    left: 0.5rem;
    color: #9a3412;
}

.amazon-code-input input {
    font-family: monospace;
    letter-spacing: 1px;
    text-transform: uppercase;
}

@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }

    .page-content {
        padding: 2rem 1rem;
    }

    h1 {
        font-size: 2rem;
    }

    .checkout-section h2 {
        font-size: 1.25rem;
    }

    .info-item {
        grid-template-columns: 1fr;
        gap: 0.25rem;
    }

    .info-item label {
        font-size: 0.875rem;
    }
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.payment-method {
    position: relative;
}

.payment-method input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.payment-method label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background-color: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method label i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.payment-method input[type="radio"]:checked + label {
    border-color: var(--primary-color);
    background-color: #eff6ff;
    box-shadow: 0 0 0 2px var(--primary-color);
}

.payment-method input[type="radio"]:hover + label {
    border-color: var(--primary-color);
}

.file-upload {
    position: relative;
    margin-bottom: 0.5rem;
}

.file-upload input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-upload label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    background-color: #f8fafc;
    border: 2px dashed #e2e8f0;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload label i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.file-upload:hover label {
    border-color: var(--primary-color);
    background-color: #eff6ff;
}

.file-info {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
    text-align: center;
}

.upload-info {
    margin-top: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.upload-info small {
    color: #64748b;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.optional-note {
    color: #0369a1 !important;
}

.optional-note i {
    color: #0ea5e9;
}

@media (max-width: 768px) {
    .upload-info {
        gap: 0.25rem;
    }
}

.form-actions {
    margin-top: 2rem;
}

.submit-btn {
    width: 100%;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    border: none;
    border-radius: 1rem;
    padding: 0;
    cursor: pointer;
    overflow: hidden;
    position: relative;
    transition: all 0.3s ease;
}

.submit-btn:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.submit-btn:hover:before {
    opacity: 1;
}

.submit-btn:active {
    transform: scale(0.98);
}

.btn-content {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 1.25rem 2rem;
    color: white;
}

.btn-content i {
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.submit-btn:hover .btn-content i {
    transform: scale(1.1);
}

.btn-text {
    display: flex;
    align-items: center;
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .btn-content {
        padding: 1rem 1.5rem;
    }

    .btn-text {
        font-size: 1.125rem;
    }
}

/* Aggiunge un effetto di brillantezza al passaggio del mouse */
.submit-btn:after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent,
        rgba(255,255,255,0.1),
        transparent
    );
    transform: rotate(45deg);
    transition: all 0.3s ease;
    opacity: 0;
}

.submit-btn:hover:after {
    opacity: 1;
    left: 100%;
}
</style>

<script>
document.querySelectorAll('input[name="metodo_pagamento"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Nascondi tutti i dettagli
        document.querySelectorAll('.payment-details').forEach(el => el.style.display = 'none');
        
        // Mostra i dettagli del metodo selezionato
        if (this.checked) {
            document.getElementById(this.value + '_details').style.display = 'block';
        }
    });
});

// Gestione del file upload
document.getElementById('receipt').addEventListener('change', function() {
    const fileInfo = document.querySelector('.file-info');
    if (this.files.length > 0) {
        const file = this.files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
        fileInfo.textContent = `File selezionato: ${file.name} (${fileSize}MB)`;
    } else {
        fileInfo.textContent = 'Nessun file selezionato';
    }
});

// Validazione del form prima dell'invio
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const metodoPagamento = document.querySelector('input[name="metodo_pagamento"]:checked');
    
    if (!metodoPagamento) {
        e.preventDefault();
        alert('Seleziona un metodo di pagamento');
        return;
    }

    // Validazione specifica per Amazon Gift Card
    if (metodoPagamento.value === 'amazon') {
        const amazonCode = document.getElementById('amazon_code').value.trim();
        if (!amazonCode) {
            e.preventDefault();
            alert('Inserisci il codice della gift card Amazon');
            return;
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 