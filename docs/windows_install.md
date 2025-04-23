# Guida Installazione Windows

## Requisiti di Sistema
- Windows 10 o superiore
- XAMPP 8.0 o superiore
- Composer

## Estensioni PHP Richieste
Incluse in XAMPP, verificare che siano abilitate in php.ini:
- PDO
- PDO_MySQL
- OpenSSL
- GD
- Mbstring
- JSON
- Curl
- Zip

## Procedura di Installazione

### 1. Installare XAMPP
1. Scaricare XAMPP da [apachefriends.org](https://www.apachefriends.org/download.html)
2. Eseguire l'installer come amministratore
3. Selezionare i componenti:
   - Apache
   - MySQL
   - PHP
   - phpMyAdmin
4. Installare nella directory predefinita (C:\xampp)
5. Al termine dell'installazione:
   - Avviare XAMPP Control Panel
   - Avviare Apache e MySQL
   - Verificare l'accesso a http://localhost

### 2. Installare Composer
1. Scaricare Composer da [getcomposer.org](https://getcomposer.org/download/)
2. Eseguire Composer-Setup.exe
3. Durante l'installazione:
   - Selezionare il PHP di XAMPP (C:\xampp\php\php.exe)
   - Lasciare le altre opzioni predefinite
4. Verificare l'installazione:
   ```bash
   composer --version
   ```

### 3. Configurare il Database
1. Aprire XAMPP Control Panel
2. Assicurarsi che MySQL sia in esecuzione
3. Aprire phpMyAdmin (http://localhost/phpmyadmin)
4. Creare un nuovo database:
   - Nome: eshop
   - Collation: utf8mb4_unicode_ci
5. Importare il file database.sql

### 4. Configurare il Progetto
1. Copiare i file del progetto in C:\xampp\htdocs\ESHOP
2. Configurare il file config.php:
   ```php
   // Configurazione Database
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'eshop');

   // Configurazione Email (opzionale)
   define('SMTP_HOST', 'smtp.example.com');
   define('SMTP_USER', 'user@example.com');
   define('SMTP_PASS', 'your_password');
   define('SMTP_PORT', 587);
   define('SMTP_FROM', 'noreply@example.com');

   ```

### 5. Configurare i Permessi
1. Assicurarsi che le seguenti cartelle esistano e siano scrivibili:
   - uploads/

2. Se necessario, modificare i permessi:
   - Tasto destro sulla cartella
   - Proprietà
   - Sicurezza
   - Modificare
   - Aggiungere permessi completi per l'utente SYSTEM

### 6. Verificare l'Installazione
1. Aprire il browser
2. Visitare http://localhost/ESHOP
3. Verificare che:
   - La home page si carichi correttamente
   - Il database sia accessibile
   - I form funzionino

## Troubleshooting

### Errore 500 Internal Server Error
1. Controllare i log in C:\xampp\apache\logs
2. Verificare i permessi delle cartelle
3. Controllare che mod_rewrite sia abilitato
4. Verificare la configurazione del database

### Errori di Connessione al Database
1. Verificare che MySQL sia in esecuzione
2. Controllare le credenziali in config.php
3. Verificare che il database esista
4. Testare la connessione con phpMyAdmin

### Problemi di Permessi
1. Verificare che l'utente SYSTEM abbia accesso completo
2. Controllare i permessi di Apache in httpd.conf
3. Verificare che le cartelle siano scrivibili
