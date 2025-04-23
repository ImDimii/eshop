# Guida Installazione Linux

## Requisiti di Sistema
- Linux (Ubuntu 20.04 LTS o superiore raccomandato)
- PHP 7.4 o superiore
- MySQL 5.7 o superiore
- Apache2/Nginx
- Composer

## Estensioni PHP Richieste
```bash
sudo apt-get install php-pdo php-mysql php-openssl php-gd php-mbstring php-json php-curl php-zip
```

## Procedura di Installazione

### 1. Installare LAMP Stack
```bash
# Aggiornare il sistema
sudo apt update
sudo apt upgrade

# Installare Apache
sudo apt install apache2
sudo systemctl start apache2
sudo systemctl enable apache2

# Installare MySQL
sudo apt install mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
sudo mysql_secure_installation

# Installare PHP e le estensioni necessarie
sudo apt install php php-cli php-fpm php-json php-common php-mysql php-zip php-gd php-mbstring php-curl php-xml php-pear php-bcmath
```

### 2. Installare Composer
```bash
# Scaricare e installare Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Configurare il Database
```bash
# Accedere a MySQL
sudo mysql -u root -p

# Creare il database e l'utente
CREATE DATABASE eshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eshop_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON eshop.* TO 'eshop_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importare il database
mysql -u eshop_user -p eshop < database.sql
```

### 4. Configurare il Progetto
```bash
# Copiare i file nella directory web
sudo cp -r ESHOP /var/www/html/
cd /var/www/html/ESHOP

# Configurare il file config.php
sudo nano config.php

# Esempio di configurazione completa
<?php
// Configurazione Database
define('DB_HOST', 'localhost');
define('DB_USER', 'eshop_user');
define('DB_PASS', 'password');
define('DB_NAME', 'eshop');

// Configurazione Email (opzionale)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_USER', 'user@example.com');
define('SMTP_PASS', 'your_password');
define('SMTP_PORT', 587);
define('SMTP_FROM', 'noreply@example.com');

?>
```

### 5. Configurare i Permessi
```bash
# Creare le directory necessarie
sudo mkdir -p uploads logs temp
sudo chown -R www-data:www-data /var/www/html/ESHOP
sudo chmod -R 755 /var/www/html/ESHOP
sudo chmod -R 775 uploads logs temp
```

### 6. Configurare Apache
```bash
# Creare il virtual host
sudo nano /etc/apache2/sites-available/eshop.conf

# Aggiungere la configurazione
<VirtualHost *:80>
    ServerName eshop.local
    DocumentRoot /var/www/html/ESHOP
    <Directory /var/www/html/ESHOP>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/eshop_error.log
    CustomLog ${APACHE_LOG_DIR}/eshop_access.log combined
</VirtualHost>

# Abilitare il sito e mod_rewrite
sudo a2ensite eshop.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Troubleshooting

### Errore 500 Internal Server Error
1. Controllare i log di Apache:
   ```bash
   sudo tail -f /var/log/apache2/error.log
   ```
2. Verificare i permessi:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/ESHOP
   sudo chmod -R 755 /var/www/html/ESHOP
   ```
3. Controllare la configurazione di PHP:
   ```bash
   php -i | grep "Loaded Configuration File"
   ```

### Errori di Connessione al Database
1. Verificare lo stato di MySQL:
   ```bash
   sudo systemctl status mysql
   ```
2. Testare la connessione:
   ```bash
   mysql -u eshop_user -p
   ```
3. Verificare i permessi del database:
   ```sql
   SHOW GRANTS FOR 'eshop_user'@'localhost';
   ```

### Problemi di Permessi
```bash
# Correggere i permessi delle directory
sudo find /var/www/html/ESHOP -type f -exec chmod 644 {} \;
sudo find /var/www/html/ESHOP -type d -exec chmod 755 {} \;
sudo chown -R www-data:www-data /var/www/html/ESHOP
```
