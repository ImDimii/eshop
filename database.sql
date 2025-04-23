-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 24, 2025 alle 01:05
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eshop`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'admin123', '2025-04-20 22:31:04');

-- --------------------------------------------------------

--
-- Struttura della tabella `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `indirizzo` varchar(255) NOT NULL,
  `citta` varchar(100) NOT NULL,
  `provincia` varchar(10) NOT NULL,
  `cap` varchar(5) NOT NULL,
  `codice_ordine` varchar(8) NOT NULL,
  `product_id` int(11) NOT NULL,
  `metodo_pagamento` enum('bonifico','amazon','paypal') NOT NULL,
  `stato` enum('in_attesa','approvato','completato') DEFAULT 'in_attesa',
  `key_assegnata` text DEFAULT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `payment_details`
--

CREATE TABLE `payment_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_type` enum('carta','bonifico','amazon','paypal') NOT NULL,
  `amazon_code` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account` varchar(100) DEFAULT NULL,
  `bank_iban` varchar(50) DEFAULT NULL,
  `paypal_email` varchar(255) DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `products`
--

INSERT INTO `products` (`id`, `nome`, `descrizione`, `prezzo`, `image_path`, `created_at`) VALUES
(11, 'TEST', 'test prodotto digitale', 15.00, 'uploads/products/product_68096c3a1a4ec.png', '2025-04-23 22:39:09');

-- --------------------------------------------------------

--
-- Struttura della tabella `product_keys`
--

CREATE TABLE `product_keys` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `key_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `product_keys`
--

INSERT INTO `product_keys` (`id`, `product_id`, `key_value`, `created_at`) VALUES
(34, 11, 'test1', '2025-04-23 22:40:10'),
(35, 11, 'test2', '2025-04-23 22:40:10'),
(36, 11, 'test3', '2025-04-23 22:40:10'),
(37, 11, 'test4', '2025-04-23 22:40:10');

-- --------------------------------------------------------

--
-- Struttura della tabella `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `chiave` varchar(50) NOT NULL,
  `valore` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `site_name` varchar(255) NOT NULL DEFAULT 'ESHOP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `settings`
--

INSERT INTO `settings` (`id`, `chiave`, `valore`, `created_at`, `updated_at`, `site_name`) VALUES
(1, 'payment_instructions', 'Inserisci il codice ordine nella descrizione del pagamento', '2025-04-20 22:31:04', '2025-04-20 22:31:04', 'ESHOP'),
(2, 'amazon_instructions', 'Inserisci il codice del buono Amazon ricevuto dall\'admin', '2025-04-20 22:31:04', '2025-04-20 22:31:04', 'ESHOP'),
(3, 'card_instructions', 'Clicca sul link per procedere al pagamento con carta', '2025-04-20 22:31:04', '2025-04-20 22:31:04', 'ESHOP'),
(4, 'bank_name', 'Banca Example', '2025-04-20 22:31:04', '2025-04-20 22:31:04', 'ESHOP'),
(5, 'bank_account', 'Mario Rossi', '2025-04-20 22:31:04', '2025-04-20 22:31:04', 'ESHOP'),
(6, 'bank_iban', 'IT60X0542811101000000123456', '2025-04-20 22:31:04', '2025-04-20 22:31:04', 'ESHOP'),
(13, 'paypal_email', 'email@example.com', '2025-04-20 22:42:18', '2025-04-23 19:30:23', 'ESHOP'),
(21, 'smtp_host', 'smtp.gmail.com', '2025-04-23 20:05:34', '2025-04-23 20:05:34', 'ESHOP'),
(22, 'smtp_username', 'example@gmail.com', '2025-04-23 20:05:34', '2025-04-23 22:41:21', 'ESHOP'),
(23, 'smtp_password', 'nltv oitr xnpy polt', '2025-04-23 20:05:34', '2025-04-23 20:13:43', 'ESHOP'),
(24, 'smtp_port', '587', '2025-04-23 20:05:34', '2025-04-23 20:05:34', 'ESHOP'),
(25, 'smtp_secure', 'tls', '2025-04-23 20:05:34', '2025-04-23 20:05:34', 'ESHOP'),
(45, 'smtp_enabled', '1', '2025-04-23 20:35:28', '2025-04-23 21:59:22', 'ESHOP'),
(56, 'site_name', 'Dimi-Shop', '2025-04-23 20:55:01', '2025-04-23 22:41:21', 'ESHOP'),
(192, 'support_email', 'example@dimishop.it', '2025-04-23 22:10:14', '2025-04-23 22:41:21', 'ESHOP'),
(193, 'support_hours', '', '2025-04-23 22:10:14', '2025-04-23 22:18:54', 'ESHOP'),
(194, 'support_phone', '+39 123 456 78910', '2025-04-23 22:10:14', '2025-04-23 22:41:21', 'ESHOP'),
(195, 'facebook_url', '', '2025-04-23 22:10:14', '2025-04-23 22:10:14', 'ESHOP'),
(196, 'twitter_url', '', '2025-04-23 22:10:14', '2025-04-23 22:10:14', 'ESHOP'),
(197, 'instagram_url', '', '2025-04-23 22:10:14', '2025-04-23 22:10:14', 'ESHOP'),
(198, 'telegram_url', '', '2025-04-23 22:10:14', '2025-04-23 22:10:14', 'ESHOP'),
(233, 'social_facebook', 'https://www.facebook.com/', '2025-04-23 22:18:16', '2025-04-23 22:41:21', 'ESHOP'),
(234, 'social_twitter', '', '2025-04-23 22:18:16', '2025-04-23 22:18:16', 'ESHOP'),
(235, 'social_instagram', 'https://instagram.com', '2025-04-23 22:18:16', '2025-04-23 22:41:21', 'ESHOP'),
(236, 'social_telegram', '', '2025-04-23 22:18:16', '2025-04-23 22:18:16', 'ESHOP');

-- --------------------------------------------------------

--
-- Struttura della tabella `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `codice_ordine` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `messaggio` text NOT NULL,
  `stato` enum('aperto','in_lavorazione','chiuso') DEFAULT 'aperto',
  `risposta` text DEFAULT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_aggiornamento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `tracking`
--

CREATE TABLE `tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tracking_code` varchar(100) NOT NULL,
  `courier` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indici per le tabelle `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codice_ordine` (`codice_ordine`),
  ADD KEY `product_id` (`product_id`);

--
-- Indici per le tabelle `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indici per le tabelle `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `product_keys`
--
ALTER TABLE `product_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indici per le tabelle `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chiave` (`chiave`);

--
-- Indici per le tabelle `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indici per le tabelle `tracking`
--
ALTER TABLE `tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT per la tabella `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT per la tabella `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `product_keys`
--
ALTER TABLE `product_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT per la tabella `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=300;

--
-- AUTO_INCREMENT per la tabella `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `tracking`
--
ALTER TABLE `tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Limiti per la tabella `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Limiti per la tabella `product_keys`
--
ALTER TABLE `product_keys`
  ADD CONSTRAINT `product_keys_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Limiti per la tabella `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Limiti per la tabella `tracking`
--
ALTER TABLE `tracking`
  ADD CONSTRAINT `tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
