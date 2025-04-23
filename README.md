# ESHOP - Sistema di Vendita Chiavi Digitali
![eshop](https://github.com/user-attachments/assets/d33cc704-5c86-482c-82a9-d8c3cd03dd0e)

[LIVE-DEMO](https://dimitricotilli.it/eshop)

## Descrizione
ESHOP è una piattaforma completa per la vendita di chiavi digitali, progettata con un'interfaccia moderna e responsive. Il sistema offre funzionalità complete sia per gli utenti che per gli amministratori.

## Requisiti di Sistema

### Windows
- XAMPP 8.0 o superiore (include Apache, MySQL, PHP)
- Composer
- Git (opzionale)

### Linux/Mac
- PHP 7.4 o superiore
- MySQL 5.7 o superiore
- Apache/Nginx
- Composer

### Estensioni PHP Richieste
- PDO
- PDO_MySQL
- OpenSSL
- GD
- Mbstring
- JSON
- Curl
- Zip

## Installazione

### Windows

1. **Installare XAMPP**
   - Scaricare XAMPP da [apachefriends.org](https://www.apachefriends.org/download.html)
   - Eseguire l'installer come amministratore
   - Selezionare i componenti necessari (Apache, MySQL, PHP, phpMyAdmin)
   - Installare nella directory predefinita (C:\xampp)

2. **Installare Composer**
   - Scaricare da [getcomposer.org](https://getcomposer.org/download/)
   - Eseguire Composer-Setup.exe
   - Selezionare il PHP di XAMPP durante l'installazione

3. **Configurare il Progetto**
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/tuouser/ESHOP.git
   cd ESHOP
   ```

4. **Configurare il Database**
   - Avviare XAMPP Control Panel
   - Avviare Apache e MySQL
   - Aprire phpMyAdmin (http://localhost/phpmyadmin)
   - Creare database "eshop"
   - Importare database.sql
   - Configurare config.php

5. **Installare Dipendenze**
   ```bash
   composer install
   ```

6. **Configurare Permessi**
   - Assicurarsi che le seguenti cartelle siano scrivibili:
     - uploads/

### Linux/Mac

1. Clonare il repository:
```bash
git clone https://github.com/imdimii/ESHOP.git
cd ESHOP
```

2. Configurare il database:
- Creare un nuovo database MySQL
- Importare il file `database.sql`
- Modificare `config.php` con i parametri del database

3. Installare le dipendenze:
```bash
composer install
```

4. Configurare il web server:
- Puntare la document root alla cartella pubblica
- Assicurarsi che il modulo rewrite sia abilitato
- Configurare i permessi delle cartelle

## Struttura Directory

```
ESHOP/
├── admin/             # Pannello amministrativo
├── docs/             # Documentazione
├── includes/         # File di inclusione PHP
├── uploads/        # File caricati
│   ├── products/   # Immagini prodotti
│   └── receipts/   # Ricevute pagamenti
├── db.sql            # file da caricare nel DB
├── varie pagine del sito
├── composer.json
└── README.md
```

## Configurazione

### Impostazioni di Base
1. Accedere al pannello admin
2. Navigare alla sezione "Configurazione Sistema"
3. Configurare:
   - Nome del sito
   - Email di supporto
   - Orari di supporto
   - Telefono di supporto
   - Social media links

### Configurazione Email
1. In "Configurazione Sistema" > "Email":
   - Abilitare/disabilitare il sistema email
   - Configurare SMTP:
     - Host
     - Porta
     - Username
     - Password
     - Crittografia (TLS/SSL)

### Configurazione Pagamenti
1. In "Configurazione Sistema" > "Pagamenti":
   - Configurare i metodi di pagamento
   - Impostare le chiavi API
   - Definire le commissioni

## Funzionalità Principali

### Area Pubblica
1. **Catalogo Prodotti**
   - Visualizzazione prodotti con immagini
   - Descrizioni dettagliate
   - Prezzi e disponibilità
   - Filtri prodotti
   - Vista dettaglio

2. **Processo di Acquisto**
   - Selezione prodotti
   - Inserimento dati cliente
   - Selezione metodo pagamento
   - Conferma ordine
   - Email di conferma

3. **Sistema di Tracking**
   - Verifica stato ordini
   - Tracking spedizioni
   - Storico ordini
   - Notifiche email automatiche

4. **Sistema di Supporto**
   - Apertura ticket
   - Verifica stato ticket
   - Comunicazione diretta
   - Notifiche email

### Area Amministrativa
1. **Gestione Prodotti**
   - Aggiunta/modifica/eliminazione prodotti
   - Gestione chiavi
   - Gestione stock
   - Caricamento immagini

2. **Gestione Ordini**
   - Dashboard ordini con filtri
   - Verifica pagamenti
   - Assegnazione chiavi automatica
   - Tracking spedizioni

3. **Gestione Ticket**
   - Dashboard ticket
   - Sistema di priorità
   - Risposte integrate
   - Notifiche email

4. **Configurazione Sistema**
   - Impostazioni generali
   - Configurazione pagamenti
   - Configurazione email
   - Gestione template email

## Caratteristiche Tecniche

### Sicurezza
- Protezione SQL Injection
- Protezione XSS
- Protezione Brute Force
- Crittografia dati sensibili
- Validazione input/output
- Sanitizzazione dati

### Performance
- Ottimizzazione database
- Sistema di cache
- Compressione immagini
- Minificazione risorse
- Lazy loading
- CDN support

### Manutenzione
- Sistema di log completo
- Backup automatici
- Monitoraggio errori
- Aggiornamenti semplificati

## Troubleshooting

### Problemi Comuni

1. **Errore 500 Internal Server Error**
   - Verificare i permessi delle cartelle
   - Controllare i log di Apache
   - Verificare la configurazione del database

2. **Errori di Connessione al Database**
   - Verificare le credenziali in config.php
   - Controllare che MySQL sia in esecuzione
   - Verificare che il database esista

3. **Problemi con l'Invio Email**
   - Verificare le impostazioni SMTP
   - Controllare che l'estensione PHP openssl sia attiva
   - Verificare la password dell'app per Gmail

### Best Practices
- Eseguire backup regolari del database
- Mantenere aggiornate le dipendenze
- Monitorare i log di sistema
- Utilizzare HTTPS in produzione
- Implementare rate limiting per le API

## Supporto
Per assistenza tecnica:
- LIVE DEMO: [ESHOP-DEMO](https://dimitricotilli.it/eshop)
- DOCS: [ESHOP-DOCS](https://dimitricotilli.it/eshop/docs)
- Contattami: [Mio sito web](https://dimitricotilli.it/)
- GitHub Issues: [Segnala un Bug](https://github.com/imdimii/ESHOP/issues)
- [Changelog](https://github.com/imdimii/ESHOP/releases)


## Contribuire
1. Fork del repository
2. Creare un branch per la feature (`git checkout -b feature/AmazingFeature`)
3. Commit delle modifiche (`git commit -m 'Add some AmazingFeature'`)
4. Push del branch (`git push origin feature/AmazingFeature`)
5. Aprire una Pull Request

## Licenza
Tutti i diritti riservati

## Autori
- Dimitri Cotilli
