<?php
// Recupera le impostazioni se non sono già state caricate
if (!isset($settings)) {
    $stmt = $pdo->query("SELECT chiave, valore FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Il tuo marketplace di fiducia per l'acquisto di chiavi digitali.</p>
                </div>
                <div class="footer-section">
                    <h3>Link Utili</h3>
                    <ul>
                        <li><a href="chi-siamo.php">Chi Siamo</a></li>
                        <li><a href="termini-condizioni.php">Termini e Condizioni</a></li>
                        <li><a href="privacy-policy.php">Privacy Policy</a></li>
                        <li><a href="cookie-policy.php">Cookie Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Supporto</h3>
                    <ul>
                        <li><a href="supporto.php">Centro Assistenza</a></li>
                        <li><a href="supporto.php?view=ticket">Stato Ticket</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contatti</h3>
                    <ul>
                        <?php if (!empty($settings['support_email'])): ?>
                        <li><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($settings['support_email']); ?></li>
                        <?php endif; ?>
                        <li><i class="fas fa-clock"></i> Lun-Ven: 9:00-18:00</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>
    <style>
        .footer {
            background-color: #1e293b;
            color: #f8fafc;
            padding: 3rem 0 1.5rem;
            margin-top: auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: white;
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }

        .footer-section p {
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: white;
        }

        .footer-section ul li i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .footer-bottom {
            padding-top: 1.5rem;
            border-top: 1px solid #334155;
            text-align: center;
            color: #cbd5e1;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
            }
        }
    </style>
</body>
</html> 