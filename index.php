<?php
require_once 'config.php';

// Recupera le impostazioni
$stmt = $pdo->query("SELECT chiave, valore FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Recupera i prodotti con il conteggio delle chiavi disponibili
$stmt = $pdo->query("
    SELECT p.*, COUNT(k.id) as available_keys 
    FROM products p 
    LEFT JOIN product_keys k ON p.id = k.product_id 
    GROUP BY p.id
");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Acquista le tue chiavi digitali</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            color: #111827;
            line-height: 1.5;
        }

        .header {
            background-color: #2563EB;
            padding: 1.5rem 0;
            color: white;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            opacity: 0.9;
            transition: opacity 0.15s ease;
        }

        .nav-links a:hover {
            opacity: 1;
        }

        .hero {
            background: linear-gradient(to bottom, #2563EB, #1D4ED8);
            padding: 4rem 0;
            color: white;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        @media (min-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .products-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .product-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border: 1px solid #E5E7EB;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            display: flex;
            flex-direction: column;
        }

        .product-image {
            width: 100%;
            padding-top: 100%; /* Questo rende l'immagine quadrata */
            position: relative;
            background: #F3F4F6;
            overflow: hidden;
        }

        .product-image i {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            color: #2563EB;
        }

        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .product-description {
            color: #6B7280;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563EB;
            margin-bottom: 1rem;
        }

        .product-availability {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .product-availability.available {
            background-color: #d1fae5;
            color: #065f46;
        }

        .product-availability.unavailable {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background-color: #2563EB;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background-color 0.15s ease;
        }

        .btn:hover {
            background-color: #1D4ED8;
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid #2563EB;
            color: #2563EB;
        }

        .btn-outline:hover {
            background-color: #2563EB;
            color: white;
        }

        .features {
            background-color: #F9FAFB;
            padding: 4rem 0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background-color: #EFF6FF;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .feature-icon i {
            font-size: 1.5rem;
            color: #2563EB;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-description {
            color: #6B7280;
            font-size: 0.875rem;
        }

        .testimonials {
            padding: 4rem 0;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border: 1px solid #E5E7EB;
        }

        .testimonial-text {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            color: #374151;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 48px;
            height: 48px;
            border-radius: 9999px;
            background-color: #E5E7EB;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .author-avatar i {
            color: #9CA3AF;
            font-size: 1.5rem;
        }

        .author-info h4 {
            font-weight: 600;
            color: #111827;
        }

        .author-info p {
            color: #6B7280;
            font-size: 0.875rem;
        }

        .faq {
            background-color: #F9FAFB;
            padding: 4rem 0;
        }

        .faq-grid {
            display: grid;
            gap: 1.5rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border: 1px solid #E5E7EB;
        }

        .faq-question {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #111827;
        }

        .faq-answer {
            color: #6B7280;
            font-size: 0.875rem;
        }

        .footer {
            background-color: #111827;
            color: white;
            padding: 4rem 0;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 4rem;
        }

        .footer-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .footer-links li i {
            color: #9CA3AF;
            width: 16px;
            text-align: center;
        }

        .footer-links a {
            color: #9CA3AF;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            color: #9CA3AF;
            font-size: 1.25rem;
            transition: color 0.15s ease;
        }

        .social-links a:hover {
            color: white;
        }

        @media (max-width: 768px) {
            .header-content {
                position: relative;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 2rem;
            }

            .mobile-menu-button {
                display: block;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: #2563EB;
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links a {
                padding: 0.5rem 0;
                width: 100%;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav-links a:last-child {
                border-bottom: none;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .container {
                padding: 2rem 1rem;
            }

            .section-title {
                font-size: 1.5rem;
            }
        }

        .btn.disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn.disabled:hover {
            background-color: #9ca3af;
        }

        .mobile-menu-button {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo"><?php echo SITE_NAME; ?></a>
            <button class="mobile-menu-button">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="nav-links" id="nav-links">
                <a href="#products">Prodotti</a>
                <a href="#features">Caratteristiche</a>
                <a href="#testimonials">Recensioni</a>
                <a href="#faq">FAQ</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Acquista le tue chiavi digitali in modo sicuro</h1>
            <p>Ottieni l'accesso immediato ai tuoi prodotti preferiti con le nostre chiavi digitali verificate</p>
            <a href="#products" class="btn">Scopri i prodotti</a>
        </div>
    </section>

    <section id="products" class="container">
        <h2 class="section-title">I Nostri Prodotti</h2>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['nome']); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <?php
                        $icons = [
                            'spotify' => 'fab fa-spotify',
                            'netflix' => 'fas fa-play',
                            'office' => 'fas fa-file-word',
                            'windows' => 'fab fa-windows'
                        ];
                        $icon = $icons[strtolower($product['nome'])] ?? 'fas fa-key';
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['nome']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($product['descrizione']); ?></p>
                    <div class="product-price">€<?php echo number_format($product['prezzo'], 2); ?></div>
                    <?php if ($product['available_keys'] > 0): ?>
                        <div class="product-availability available">
                            <i class="fas fa-cube"></i> Quantità: <?php echo $product['available_keys']; ?>
                        </div>
                        <a href="checkout.php?product_id=<?php echo $product['id']; ?>" class="btn">Acquista ora</a>
                    <?php else: ?>
                        <div class="product-availability unavailable">
                            <i class="fas fa-cube"></i> Quantità: 0
                        </div>
                        <button class="btn disabled" disabled>Non disponibile</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">Perché Sceglierci</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="feature-title">Consegna Istantanea</h3>
                    <p class="feature-description">Ricevi la tua chiave digitale immediatamente dopo la conferma del pagamento</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Pagamenti Sicuri</h3>
                    <p class="feature-description">Transazioni protette con metodi di pagamento affidabili e verificati</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">Supporto 24/7</h3>
                    <p class="feature-description">Il nostro team è sempre disponibile per aiutarti in caso di necessità</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="feature-title">Garanzia di Qualità</h3>
                    <p class="feature-description">Tutte le nostre chiavi sono originali e garantite al 100%</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="testimonials">
        <div class="container">
            <h2 class="section-title">Cosa Dicono i Nostri Clienti</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Servizio eccellente! Ho ricevuto la mia chiave in pochi minuti e funziona perfettamente. Consigliatissimo!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Marco R.</h4>
                            <p>Cliente verificato</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Prezzi competitivi e servizio clienti molto disponibile. Tornerò sicuramente per altri acquisti."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Laura B.</h4>
                            <p>Cliente verificato</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Ho avuto un piccolo problema con l'attivazione ma il supporto mi ha aiutato a risolverlo immediatamente."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Alessandro M.</h4>
                            <p>Cliente verificato</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="faq">
        <div class="container">
            <h2 class="section-title">Domande Frequenti</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3 class="faq-question">Come funziona il processo di acquisto?</h3>
                    <p class="faq-answer">Seleziona il prodotto desiderato, completa il checkout con il tuo metodo di pagamento preferito e ricevi immediatamente la tua chiave digitale via email.</p>
                </div>
                <div class="faq-item">
                    <h3 class="faq-question">Le chiavi sono originali?</h3>
                    <p class="faq-answer">Sì, tutte le nostre chiavi sono 100% originali e provengono da fornitori autorizzati.</p>
                </div>
                <div class="faq-item">
                    <h3 class="faq-question">Quanto tempo ci vuole per ricevere la chiave?</h3>
                    <p class="faq-answer">Le chiavi vengono consegnate istantaneamente dopo la conferma del pagamento.</p>
                </div>
                <div class="faq-item">
                    <h3 class="faq-question">Cosa fare se ho problemi con la chiave?</h3>
                    <p class="faq-answer">Il nostro servizio clienti è disponibile 24/7 per aiutarti con qualsiasi problema tu possa incontrare.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Informazioni</h3>
                <ul class="footer-links">
                    <li><a href="chi-siamo.php">Chi siamo</a></li>
                    <li><a href="termini-condizioni.php">Termini e condizioni</a></li>
                    <li><a href="privacy-policy.php">Privacy Policy</a></li>
                    <li><a href="cookie-policy.php">Cookie Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Supporto</h3>
                <ul class="footer-links">
                    <li><a href="supporto.php">Centro Assistenza</a></li>
                    <li><a href="supporto.php?view=ticket">Stato Ticket</a></li>
                    
                    <?php if (!empty($settings['support_email'])): ?>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo htmlspecialchars($settings['support_email']); ?>">
                            <?php echo htmlspecialchars($settings['support_email']); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['support_hours'])): ?>
                    <li>
                        <i class="far fa-clock"></i>
                        <?php echo htmlspecialchars($settings['support_hours']); ?>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['support_phone'])): ?>
                    <li>
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $settings['support_phone'])); ?>">
                            <?php echo htmlspecialchars($settings['support_phone']); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Seguici</h3>
                <div class="social-links">
                    <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_facebook']); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-facebook"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_twitter']); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-twitter"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_instagram']); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['social_telegram'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_telegram']); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-telegram"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll per i link dell'header
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
                // Chiudi il menu mobile dopo il click
                if (window.innerWidth <= 768) {
                    document.getElementById('nav-links').classList.remove('active');
                }
            });
        });

        // Toggle menu mobile
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });

        // Chiudi il menu quando si clicca fuori
        document.addEventListener('click', function(e) {
            const nav = document.getElementById('nav-links');
            const button = document.querySelector('.mobile-menu-button');
            if (window.innerWidth <= 768 && !nav.contains(e.target) && !button.contains(e.target)) {
                nav.classList.remove('active');
            }
        });

        // Gestisci il ridimensionamento della finestra
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('nav-links').classList.remove('active');
            }
        });
    </script>
</body>
</html> 