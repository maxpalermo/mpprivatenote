-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Ott 31, 2024 alle 11:12
-- Versione del server: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- Versione PHP: 8.1.30
SET
    FOREIGN_KEY_CHECKS = 0;

SET
    SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
    time_zone = "+00:00";

--
-- Database: `ps_dl_80`
--
-- --------------------------------------------------------
--
-- Struttura della tabella `ps_customer_messages`
--
CREATE TABLE IF NOT EXISTS `[[PREFIX]]customer_messages` (
    `id_customer_messages` int(11) NOT NULL AUTO_INCREMENT,
    `id_customer` int(11) NOT NULL,
    `id_employee` int(11) NOT NULL,
    `message` text NOT NULL,
    `deleted` tinyint(1) DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    `deleted_at` datetime DEFAULT NULL,
    `date_add` datetime NOT NULL,
    `date_upd` datetime DEFAULT NULL,
    PRIMARY KEY (`id_customer_messages`)
) ENGINE = [[ENGINE_TYPE]] DEFAULT CHARSET = latin1 COLLATE = latin1_swedish_ci;

SET
    FOREIGN_KEY_CHECKS = 1;

COMMIT;