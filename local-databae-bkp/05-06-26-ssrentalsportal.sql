-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 05, 2026 at 08:10 AM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ssrentalsportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`key`, `payload`, `created_at`, `updated_at`) VALUES
('portal', '{\"gst\": \"123-456-789\", \"address\": \"Auckland, New Zealand\", \"company\": \"SS Rentals Ltd\", \"gst_rate\": \"15\", \"ruc_rate\": \"0.062\", \"payment_terms\": \"7\", \"invoice_prefix\": \"INV-2026-\"}', '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `director` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nzbn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `truck_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weekly_truck` decimal(12,2) NOT NULL DEFAULT '0.00',
  `weekly_trailer` decimal(12,2) NOT NULL DEFAULT '0.00',
  `mileage_rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `ruc_rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `outstanding` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_terms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joined` date DEFAULT NULL,
  `ytd_revenue` decimal(12,2) NOT NULL DEFAULT '0.00',
  `credit_rating` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `company`, `director`, `contact`, `phone`, `email`, `address`, `nzbn`, `status`, `truck_id`, `trailer_id`, `weekly_truck`, `weekly_trailer`, `mileage_rate`, `ruc_rate`, `outstanding`, `payment_terms`, `joined`, `ytd_revenue`, `credit_rating`, `notes`, `created_at`, `updated_at`) VALUES
('C001', '5 Rivers Transport', NULL, 'Navinderpal Sandhu', '022 309 3166', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 546.91, 0.00, 0.3200, 0.6600, 0.00, '7 days', '2026-04-01', 1093.82, 'B', '2 days in April only. MRU490 + 974J5. Per-day rate $546.91. RUC $0.66/km, Mileage $0.32/km.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C002', 'ATB Enterprises', NULL, 'Gurpreet Dullat', '021 030 1696', '', 'Auckland, NZ', '', 'blacklisted', NULL, NULL, 0.00, 0.00, 0.0000, 0.0000, 0.00, '7 days', '2025-01-01', 0.00, 'F', 'Did not hire. Charged cancellation charges. Unreliable customer — DO NOT RE-HIRE.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C003', 'Baaz NZ Transport', NULL, 'Sarbjeet Singh', '021 549 022', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2300.00, 0.00, 0.3200, 0.6500, 0.00, '7 days', '2026-02-13', 6900.00, 'C', '13/02/26 to 01/03/26. RAP758 + 974J5. $2,300/wk. Had dispute with Riki and parked truck without notice. Caution.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C004', 'Chardikala Limited', NULL, 'Gurpinder Singh', '027 510 0233', '', 'Auckland, NZ', '', 'active', 'T001', 'TR002', 2650.11, 0.00, 0.2100, 0.6400, 5300.22, '7 days', '2026-04-27', 21201.00, 'B', '27/04/26 to current. MRU490 + 974J5. $2,650.11/wk. CAUTION: Travelling ~4,500 km/week — excessive. Monitor RUC charges very closely.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C005', 'Convoy Truckline', NULL, 'Navjot Singh', '021 022 06222', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1676.99, 0.00, 0.2600, 0.4100, 0.00, '7 days', '2025-10-08', 3353.98, 'B+', '8/10/25 to 26/10/25. SSRENT truck only. $1,676.99/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C006', 'Dhadda Transport', NULL, 'Gurpreet Singh', '020 4088 3547', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 290.00, 0.00, 0.3000, 0.4900, 0.00, '7 days', '2025-11-01', 870.00, 'B', '3 days in Nov 2025. Tractor unit RFL73 only. $290/day.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C007', 'Dhillon Linehaul', NULL, 'Prabhjot Dhillon', '', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1344.00, 0.00, 0.1300, 0.1800, 0.00, '7 days', '2025-01-27', 1344.00, 'A', '27/01/25 to 03/02/25. Trailer 2W450 only. $1,344/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C008', 'Empire Trucking', NULL, 'Pawanjeet Singh', '027 826 7001', '', 'Auckland, NZ', '', 'active', 'T003', NULL, 1716.16, 0.00, 0.2200, 0.4500, 1716.16, '7 days', '2026-05-25', 1716.16, 'A', '25/05/26 to 31/05/26. RAP758 truck only. $1,716.16/wk. Short 1-week hire.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C009', 'Jatana Bros', NULL, 'Gurpiar Singh', '027 205 8117', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2398.50, 0.00, 0.3300, 0.6500, 0.00, '7 days', '2025-10-21', 19188.00, 'B+', '21/10/25 to 21/12/25. RAP758 + 974J5. $2,398.50/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C010', 'Kahlon Holding', NULL, 'Karan Kahlon', '022 420 1913', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1874.87, 0.00, 0.2200, 0.4500, 0.00, '7 days', '2026-04-20', 1874.87, 'A', '20/04/26 to 26/04/26. MRU490 truck only. $1,874.87/wk. 1 week.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C011', 'Pangly Transport', NULL, 'Jas', '027 527 7022', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1344.00, 0.00, 0.2300, 0.4500, 0.00, '7 days', '2026-03-16', 10752.00, 'B+', '16/03/26 to 06/05/26. Tractor unit RFL73. $1,344/wk. Reliable payer.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C012', 'PB48 Transport', NULL, 'Hardeep Singh', '022 493 0013', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1676.99, 0.00, 0.2600, 0.4100, 0.00, '7 days', '2025-12-01', 1676.99, 'A', '01/12/25 to 07/12/25. SSRENT truck only. $1,676.99/wk. 1 week.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C013', 'Reet Investment', NULL, 'Harpal Sinh', '021 875 601', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2416.58, 0.00, 0.3000, 0.6500, 0.00, '7 days', '2026-04-13', 4833.16, 'A', '13/04/26 to 26/04/26. SSRENT + 2W450. $2,416.58/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C014', 'Royale Brothers', NULL, 'Harpal Singh', '020 433 0005', '', 'Auckland, NZ', '', 'blacklisted', NULL, NULL, 2319.99, 0.00, 0.3200, 0.6500, 0.00, '7 days', '2025-01-16', 87159.00, 'F', '16/01/25 to 12/04/26. MRU490 + 974J5. $2,319.99/wk. IN DISPUTE — BLACKLISTED. Do not re-hire under any circumstances.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C015', 'S & S Transport', NULL, 'Stefin', '021 293 1794', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1300.00, 0.00, 0.2600, 0.4800, 0.00, '7 days', '2025-12-01', 2600.00, 'B+', '01/12/25 to 14/12/25. Tractor unit RFL73. $1,300/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C016', 'S & Z Enterprise', NULL, 'Zuben', '027 575 9774', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1968.75, 0.00, 0.1600, 0.4100, 0.00, '7 days', '2025-09-01', 23625.00, 'A', '01/09/25 to 21/12/25. MRU490 truck only. $1,968.75/wk. Long-term reliable customer.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C017', 'Satpal & Taran', NULL, 'Karan', '021 059 7275', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1699.76, 0.00, 0.2600, 0.4500, 0.00, '7 days', '2026-02-23', 6799.04, 'A', '23/02/26 to 05/04/26. SSRENT truck only. $1,699.76/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C018', 'Shah Transport', NULL, 'Sharanjit', '021 022 70050', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2646.99, 0.00, 0.3600, 0.6500, 0.00, '7 days', '2025-10-26', 5293.98, 'B', '26/10/25 to 09/11/25. SSRENT + 2W450. $2,646.99/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C019', 'Singh Investment', NULL, 'Harman', '', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 1676.99, 0.00, 0.2600, 0.4800, 0.00, '7 days', '2026-01-12', 5030.97, 'B+', '12/01/26 to 08/02/26. SSRENT truck only. $1,676.99/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C020', 'SS Dhillon', NULL, 'Sukhjinder Singh', '027 777 7470', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2690.58, 0.00, 0.3000, 0.6500, 0.00, '7 days', '2026-05-04', 2690.58, 'A', '04/05/26 to 10/05/26. SSRENT + 2W450. $2,690.58/wk. 1 week hire.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C021', 'Sukh & Charan', NULL, 'Manpreet Singh', '022 069 0029', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2590.58, 0.00, 0.3000, 0.6500, 0.00, '7 days', '2026-05-11', 5181.16, 'A', '11/05/26 to 24/05/26 (ended today). SSRENT + 2W450. $2,590.58/wk.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C022', 'Team Freight Limited', NULL, 'Ajaydeep Singh', '027 540 6084', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2550.11, 0.00, 0.2500, 0.6500, 0.00, '7 days', '2026-03-16', 17850.77, 'A', '16/03/26 to 21/05/26. RAP758 + Q927C. $2,550.11/wk. Reliable payer.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('C023', 'Vertex Haulage', NULL, 'Varinderjeet Singh', '022 452 8071', '', 'Auckland, NZ', '', 'inactive', NULL, NULL, 2273.50, 0.00, 0.2600, 0.5900, 0.00, '7 days', '2025-07-25', 16914.50, 'A', '25/07/25 to 28/09/25. SSRENT + 2W450. $2,273.50/wk. First SS Rentals customer — great history.', '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `type`, `customer_id`, `vehicle_id`, `date`, `size`, `signed`, `created_at`, `updated_at`) VALUES
('D001', 'H001 Hire Agreement — Chardikala Limited', 'Hire Agreement', 'C004', 'T001', '2026-04-27', '284KB', 1, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('D002', 'H002 Hire Agreement — Empire Trucking', 'Hire Agreement', 'C008', 'T003', '2026-05-25', '268KB', 1, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('D003', 'H003 Hire Agreement — Sukh & Charan', 'Hire Agreement', 'C021', 'T002', '2026-05-11', '271KB', 1, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('D004', 'MRU490 COF Certificate', 'COF', NULL, 'T001', '2025-08-13', '98KB', 0, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('D005', 'RAP758 COF Certificate', 'COF', NULL, 'T003', '2025-11-13', '102KB', 0, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('D006', 'SSRENT Rego 2026', 'Registration', NULL, 'T002', '2025-08-11', '76KB', 0, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('D007', 'H007 Hire Agreement — Royale Brothers (DISPUTED)', 'Hire Agreement', 'C014', 'T001', '2025-01-16', '302KB', 1, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('D008', 'Team Freight Limited — H004 Hire Agreement', 'Hire Agreement', 'C022', 'T003', '2026-03-16', '289KB', 1, '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hires`
--

CREATE TABLE `hires` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `truck_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `weekly_truck` decimal(12,2) NOT NULL DEFAULT '0.00',
  `weekly_trailer` decimal(12,2) NOT NULL DEFAULT '0.00',
  `mileage_rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `ruc_rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `bond` decimal(12,2) NOT NULL DEFAULT '0.00',
  `bond_paid` tinyint(1) NOT NULL DEFAULT '0',
  `invoiced_to` date DEFAULT NULL,
  `next_invoice` date DEFAULT NULL,
  `signed` tinyint(1) NOT NULL DEFAULT '0',
  `insurance_verified` tinyint(1) NOT NULL DEFAULT '0',
  `checklist_done` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hires`
--

INSERT INTO `hires` (`id`, `customer_id`, `truck_id`, `trailer_id`, `start`, `end`, `status`, `weekly_truck`, `weekly_trailer`, `mileage_rate`, `ruc_rate`, `bond`, `bond_paid`, `invoiced_to`, `next_invoice`, `signed`, `insurance_verified`, `checklist_done`, `notes`, `created_at`, `updated_at`) VALUES
('H001', 'C004', 'T001', 'TR002', '2026-04-27', '2026-06-30', 'active', 2650.11, 0.00, 0.2100, 0.6400, 5000.00, 1, '2026-05-18', '2026-05-25', 1, 1, 1, 'Chardikala travelling ~4,500km/week. Monitor RUC closely.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('H002', 'C008', 'T003', NULL, '2026-05-25', '2026-05-31', 'active', 1716.16, 0.00, 0.2200, 0.4500, 2000.00, 1, '2026-05-25', '2026-06-01', 1, 1, 1, 'Empire Trucking. RAP758 truck only. Short 1-week hire.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('H003', 'C021', 'T002', 'TR001', '2026-05-11', '2026-05-24', 'completed', 2590.58, 0.00, 0.3000, 0.6500, 4000.00, 1, '2026-05-24', NULL, 1, 1, 1, 'Sukh & Charan. Ended today 24/05/26.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('H004', 'C022', 'T003', 'TR003', '2026-03-16', '2026-05-21', 'completed', 2550.11, 0.00, 0.2500, 0.6500, 4000.00, 1, '2026-05-21', NULL, 1, 1, 1, 'Team Freight Limited. RAP758 + Q927C. Completed 21/05/26.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('H005', 'C011', 'T004', NULL, '2026-03-16', '2026-05-06', 'completed', 1344.00, 0.00, 0.2300, 0.4500, 2000.00, 1, '2026-05-06', NULL, 1, 1, 1, 'Pangly Transport. Tractor unit RFL73. Ended 06/05/26.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('H006', 'C020', 'T002', 'TR001', '2026-05-04', '2026-05-10', 'completed', 2690.58, 0.00, 0.3000, 0.6500, 4000.00, 1, '2026-05-10', NULL, 1, 1, 1, 'SS Dhillon. SSRENT + 2W450. 1 week hire.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('H007', 'C014', 'T001', 'TR002', '2025-01-16', '2026-04-12', 'completed', 2319.99, 0.00, 0.3200, 0.6500, 5000.00, 1, '2026-04-12', NULL, 1, 0, 1, 'Royale Brothers — IN DISPUTE / BLACKLISTED. Long-term hire ended in dispute.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('H008', 'C013', 'T002', 'TR001', '2026-04-13', '2026-04-26', 'completed', 2416.58, 0.00, 0.3000, 0.6500, 4000.00, 1, '2026-04-26', NULL, 1, 1, 1, 'Reet Investment. SSRENT + 2W450.', '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `due` date DEFAULT NULL,
  `period` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `truck_hire` decimal(12,2) NOT NULL DEFAULT '0.00',
  `trailer_hire` decimal(12,2) NOT NULL DEFAULT '0.00',
  `mileage` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ruc` decimal(12,2) NOT NULL DEFAULT '0.00',
  `damage` decimal(12,2) NOT NULL DEFAULT '0.00',
  `extras` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `xero_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `customer_id`, `hire_id`, `date`, `due`, `period`, `truck_hire`, `trailer_hire`, `mileage`, `ruc`, `damage`, `extras`, `total`, `status`, `xero_id`, `created_at`, `updated_at`) VALUES
('INV-0263', 'C004', 'H001', '2026-05-04', '2026-05-11', '4 May – 10 May 2026', 2650.11, 0.00, 1889.28, 1824.48, 0.00, 0.00, 6363.87, 'paid', 'INV-0263', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('INV-0264', 'C020', 'H006', '2026-05-04', '2026-05-11', '4 May – 10 May 2026', 2690.58, 0.00, 1140.00, 1137.50, 0.00, 0.00, 4968.08, 'paid', 'INV-0264', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('INV-0265', 'C004', 'H001', '2026-05-11', '2026-05-18', '11 May – 17 May 2026', 2650.11, 0.00, 1889.28, 1824.48, 0.00, 0.00, 6363.87, 'paid', 'INV-0265', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('INV-0266', 'C022', 'H004', '2026-05-11', '2026-05-18', '11 May – 17 May 2026', 2550.11, 0.00, 918.75, 919.75, 0.00, 0.00, 4388.61, 'paid', 'INV-0266', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('INV-0267', 'C021', 'H003', '2026-05-18', '2026-05-25', '18 May – 24 May 2026', 2590.58, 0.00, 1181.70, 1178.75, 0.00, 0.00, 4951.03, 'paid', 'INV-0267', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('INV-0268', 'C004', 'H001', '2026-05-18', '2026-05-25', '18 May – 24 May 2026', 2650.11, 0.00, 1889.28, 1824.48, 0.00, 0.00, 6363.87, 'overdue', 'INV-0268', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('INV-0269', 'C008', 'H002', '2026-05-24', '2026-05-31', '25 May – 31 May 2026', 1716.16, 0.00, 484.00, 495.00, 0.00, 0.00, 2695.16, 'draft', NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('INV-0277', 'C001', '', '2026-06-05', '2026-06-12', '', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'overdue', NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vehicle_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rego` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `date` date DEFAULT NULL,
  `cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `workshop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parts` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odometer` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_records`
--

INSERT INTO `maintenance_records` (`id`, `vehicle_id`, `rego`, `type`, `description`, `date`, `cost`, `status`, `workshop`, `parts`, `odometer`, `created_at`, `updated_at`) VALUES
('M001', 'T001', 'MRU490', 'Scania Plan Service', 'Scheduled Scania maintenance plan service — oil, filters, inspection', '2026-04-15', 9800.00, 'completed', 'Scania NZ', 'Scania plan included', 348000.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M002', 'T003', 'RAP758', 'Workshop Repair', 'Cambridge Diesel workshop repair — various', '2026-04-10', 8072.59, 'completed', 'Cambridge Diesel', 'Various', 419200.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M003', 'T002', 'SSRENT', 'Workshop Repair', 'Cambridge Diesel workshop — March service and repair', '2026-03-20', 10822.46, 'completed', 'Cambridge Diesel', 'Various', 381000.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M004', 'T001', 'MRU490', 'Tyre Replacement', 'Tyre replacement — drive axle', '2026-03-01', 1841.40, 'completed', 'Tyres Direct NZ', 'Tyres x4', 344000.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M005', 'T002', 'SSRENT', 'Tyre Replacement', 'Tyre replacement — February', '2026-02-15', 4873.42, 'completed', 'Tyres Direct NZ', 'Tyres x6', 378000.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M006', 'T001', 'MRU490', 'Scania Plan Service', 'Next scheduled Scania plan service due — book now', '2026-07-15', 0.00, 'scheduled', 'Scania NZ', '', NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M007', 'T002', 'SSRENT', 'Service', 'Next service due — check with Cambridge Diesel', '2026-07-01', 0.00, 'scheduled', 'Cambridge Diesel', '', NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M008', 'T003', 'RAP758', 'COF Renewal', 'COF renewal due November 2026 — book VTNZ', '2026-11-13', 0.00, 'scheduled', 'VTNZ', '', NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('M009', 'T004', 'RFL73', 'Service', 'Service due before COF renewal in October', '2026-09-01', 0.00, 'scheduled', 'Cambridge Diesel', '', NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_05_000001_create_portal_data_table', 2),
(5, '2026_06_05_000002_create_portal_core_tables', 3),
(6, '2026_06_05_000003_drop_legacy_portal_data_table', 4),
(7, '2026_06_05_000004_add_role_to_users_table', 5),
(8, '2026_06_05_080117_add_director_to_customers_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `monthly_revenues`
--

CREATE TABLE `monthly_revenues` (
  `id` bigint UNSIGNED NOT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `expenses` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `monthly_revenues`
--

INSERT INTO `monthly_revenues` (`id`, `month`, `amount`, `expenses`, `net`, `created_at`, `updated_at`) VALUES
(1, 'Oct 25', 120157.54, 87295.20, 32862.34, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(2, 'Nov 25', 49442.04, 79681.69, -30239.65, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(3, 'Dec 25', 50112.47, 48408.01, 1704.46, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(4, 'Jan 26', 103079.30, 100749.02, 2330.28, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(5, 'Feb 26', 51722.25, 56695.21, -4972.96, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(6, 'Mar 26', 55625.45, 53684.07, 1941.38, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(7, 'Apr 26', 61428.24, 66771.18, -5342.94, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(8, 'May 26', 64345.42, 49712.83, 14632.59, '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pnl_details`
--

CREATE TABLE `pnl_details` (
  `id` bigint UNSIGNED NOT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revenue` decimal(12,2) NOT NULL DEFAULT '0.00',
  `insurance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `navman_ruc` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ruc` decimal(12,2) NOT NULL DEFAULT '0.00',
  `other` decimal(12,2) NOT NULL DEFAULT '0.00',
  `repairs` decimal(12,2) NOT NULL DEFAULT '0.00',
  `flexi` decimal(12,2) NOT NULL DEFAULT '0.00',
  `heartland` decimal(12,2) NOT NULL DEFAULT '0.00',
  `advertising` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gst` decimal(12,2) NOT NULL DEFAULT '0.00',
  `expenses` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pnl_details`
--

INSERT INTO `pnl_details` (`id`, `month`, `revenue`, `insurance`, `navman_ruc`, `ruc`, `other`, `repairs`, `flexi`, `heartland`, `advertising`, `gst`, `expenses`, `net`, `created_at`, `updated_at`) VALUES
(1, '2025-07', 4611.00, 792.00, 0.00, 1916.00, 642.00, 0.00, 0.00, 0.00, 0.00, 0.00, 3350.00, 1262.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(2, '2025-08', 12672.00, 792.00, 0.00, 3833.00, 2649.00, 0.00, 2751.00, 0.00, 167.00, 0.00, 9924.00, 2748.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(3, '2025-09', 47849.00, 2149.00, 0.00, 13406.00, 25440.00, 2962.00, 2751.00, 0.00, 471.00, 1130.00, 48308.00, -458.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(4, '2025-10', 120158.00, 3290.00, 1323.00, 24013.00, 41616.00, 9104.00, 2751.00, 2897.00, 1404.00, 897.00, 87295.00, 32862.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(5, '2025-11', 49442.00, 1584.00, 4854.00, 16523.00, 36148.00, 12568.00, 2751.00, 2897.00, 162.00, 1598.00, 79682.00, -30240.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(6, '2025-12', 50112.00, 1584.00, 2646.00, 12765.00, 17232.00, 7681.00, 2751.00, 7877.00, 672.00, 0.00, 48408.00, 1704.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(7, '2026-01', 103079.00, 1584.00, 3972.00, 20536.00, 54029.00, 10825.00, 2751.00, 4345.00, 909.00, 1798.00, 100749.00, 2330.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(8, '2026-02', 51722.00, 1584.00, 2205.00, 9585.00, 28048.00, 9428.00, 2751.00, 2897.00, 197.00, 0.00, 56695.00, -4973.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(9, '2026-03', 55625.00, 1584.00, 2646.00, 12765.00, 22378.00, 7316.00, 2751.00, 4345.00, 899.00, 0.00, 53684.00, 1941.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(10, '2026-04', 61428.00, 1584.00, 3532.00, 10233.00, 35028.00, 0.00, 2751.00, 11703.00, 940.00, 1000.00, 66771.00, -5343.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(11, '2026-05', 64345.00, 1576.00, 5004.00, 9952.00, 25477.00, 5189.00, 2751.00, 0.00, 86.00, 2110.00, 52704.00, 11642.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('cmAnHIKadk4oXDwglbixtCus5QclF72MWt52rIe4', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJTcnhUMVpFOWk1RHdVaWlqZjNBeThhY2hYdmJrSkh1WFNxWUpIYVdhIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0Ojg4ODhcL3BvcnRhbCIsInJvdXRlIjoicG9ydGFsIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==', 1780646768),
('DZeUZBOIlKVoVVqdtLHfbFA7CpgtSqCe0NGAJx5b', NULL, '::1', 'curl/8.7.1', 'eyJfdG9rZW4iOiJscFBEb3ZjUzVxTlRGQTAxd3VOSGJqaGpkSDR5QkdjdWNGa0dkN3NKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4ODg4Iiwicm91dGUiOiJsb2dpbiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1780642737),
('iTHnaw5sVuxC7GGCWu6fFtbJFdzE4gj2b2gJ9RcY', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJqVlFTSzFhbmVVVmdPQW9XRTdTUW5xNTVDUTBDUmFsY2lnMDZpZEtOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJwb3J0YWwifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1780642280),
('jkRxxvNLLOqS2eUhEkVsNauas05IJtSu5VcF9P7V', NULL, '::1', 'curl/8.7.1', 'eyJfdG9rZW4iOiJFUUdxSXZ5bXpDbFhEMWhkdGtZU1BocWlwbm9vb2xWNXVvcFV4VkVNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4ODg4Iiwicm91dGUiOiJwb3J0YWwifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1780642478),
('kxfHPxTkR3ixPJOKNGj6td58c27d0aSMYQAtKMRR', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJETjJkTTg1ampwZ2dkaTZtVU9xV2pVcjJIdnpVZzd2YXZBQVM3c0syIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4ODg4XC9wb3J0YWwiLCJyb3V0ZSI6InBvcnRhbCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==', 1780643149),
('u1GiDcYqEmt4LLOJuLBO293ObCH4blZhjNL5UeDC', NULL, '127.0.0.1', 'curl/8.7.1', 'eyJfdG9rZW4iOiJpbm51UjUxcndwMzNsUW92UldDRmFTbG1udnZYVllaWGE4VkoyZ1hQIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJwb3J0YWwifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1780642266);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operations_manager',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'SS Rentals Admin', 'admin@ssrentals.co.nz', 'operations_manager', NULL, '$2y$12$65vdq5QpuW9.x.lzC0gQJOJkQRaPkiNDd/Al3PuIP93i4PJE/5F1G', NULL, '2026-06-05 01:26:31', '2026-06-05 01:26:31'),
(2, 'SS Rentals Admin', 'admin@ssrentalsportal.test', 'super_admin_owner', NULL, '$2y$12$NQPPifjLre/0AReHutcl/.Lnsel2qefChdYZ.SrHspAneVtQXLoZq', NULL, '2026-06-05 02:14:52', '2026-06-05 02:14:52');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rego` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `make` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` smallint UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `odometer` decimal(12,2) NOT NULL DEFAULT '0.00',
  `value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cof_expiry` date DEFAULT NULL,
  `rego_expiry` date DEFAULT NULL,
  `service_due_km` decimal(12,2) NOT NULL DEFAULT '0.00',
  `next_service` date DEFAULT NULL,
  `insurance_expiry` date DEFAULT NULL,
  `ruc_balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `hirer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_start` date DEFAULT NULL,
  `weekly_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weekly_km` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rev_ytd` decimal(12,2) NOT NULL DEFAULT '0.00',
  `maint_ytd` decimal(12,2) NOT NULL DEFAULT '0.00',
  `downtime` int UNSIGNED NOT NULL DEFAULT '0',
  `instalment` decimal(12,2) NOT NULL DEFAULT '0.00',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `asset_type`, `rego`, `make`, `model`, `year`, `type`, `status`, `odometer`, `value`, `cof_expiry`, `rego_expiry`, `service_due_km`, `next_service`, `insurance_expiry`, `ruc_balance`, `hirer_id`, `hire_start`, `weekly_rate`, `location`, `weekly_km`, `rev_ytd`, `maint_ytd`, `downtime`, `instalment`, `note`, `created_at`, `updated_at`) VALUES
('T001', 'truck', 'MRU490', 'Scania', 'R620', 2020, '8x4 Curtainside Truck (W-Mezz)', 'on_hire', 352400.00, 175000.00, '2026-08-13', '2026-08-31', 360000.00, '2026-07-15', '2027-01-01', 2100.00, 'C004', '2026-04-27', 2650.11, 'Auckland — On Road', 4500.00, 38200.00, 11641.00, 4, 2751.02, NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('T002', 'truck', 'SSRENT', 'Volvo', 'FH 540', 2019, '8x4 Curtainside Truck (Wtht-Mezz)', 'available', 384100.00, 158000.00, '2026-08-19', '2026-08-11', 390000.00, '2026-07-01', '2027-01-01', 1850.00, NULL, NULL, 0.00, 'Auckland — Yard', 0.00, 31400.00, 10822.00, 0, 3826.10, NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('T003', 'truck', 'RAP758', 'Volvo', 'FH 600', 2017, '8x4 Curtainside Truck (Wtht-Mezz)', 'on_hire', 421800.00, 138000.00, '2026-11-13', '2027-01-04', 430000.00, '2026-08-01', '2027-01-01', 3200.00, 'C008', '2026-05-25', 1716.16, 'Auckland — On Road', 2200.00, 22800.00, 8073.00, 6, 4980.46, NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('T004', 'truck', 'RFL73', 'Volvo', 'FH 600', 2022, '8x4 Tractor Unit', 'available', 183600.00, 218000.00, '2026-10-14', '2026-09-24', 190000.00, '2026-09-01', '2027-01-01', 4100.00, NULL, NULL, 0.00, 'Auckland — Yard', 0.00, 18600.00, 3100.00, 18, 2896.96, NULL, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('TR001', 'trailer', '2W450', 'TMC', '5H 5-Axle Curtainsider (W-Mezz)', 2015, NULL, 'available', 0.00, 62000.00, '2026-08-19', '2026-09-17', 0.00, NULL, NULL, 0.00, NULL, NULL, 0.00, 'Auckland — Yard', 0.00, 0.00, 0.00, 0, 0.00, 'Paired with SSRENT. Instalment covered by SSRENT loan.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('TR002', 'trailer', '974J5', 'Domett', 'E2001 5-Axle Curtainsider (W-Mezz)', 2017, NULL, 'on_hire', 0.00, 70000.00, '2026-11-08', '2027-04-13', 0.00, NULL, NULL, 0.00, 'C004', '2026-04-27', 0.00, 'Auckland — On Road', 0.00, 0.00, 0.00, 0, 0.00, 'Paired with RAP758. Instalment covered by RAP758 loan.', '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
('TR003', 'trailer', 'Q927C', 'Fruehauf', 'RBC 4-Axle Curtainsider (Wtht-Mezz)', 1999, NULL, 'available', 0.00, 24000.00, '2026-09-14', '2026-09-13', 0.00, NULL, NULL, 0.00, NULL, NULL, 0.00, 'Auckland — Yard', 0.00, 0.00, 0.00, 0, 0.00, 'Bought cash. No finance.', '2026-06-05 01:55:07', '2026-06-05 01:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_revenues`
--

CREATE TABLE `weekly_revenues` (
  `id` bigint UNSIGNED NOT NULL,
  `week` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `weekly_revenues`
--

INSERT INTO `weekly_revenues` (`id`, `week`, `amount`, `created_at`, `updated_at`) VALUES
(1, '26 Apr', 13027.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(2, '3 May', 16420.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(3, '10 May', 15890.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(4, '17 May', 17230.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07'),
(5, '24 May', 9059.00, '2026-06-05 01:55:07', '2026-06-05 01:55:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_company_index` (`company`),
  ADD KEY `customers_status_index` (`status`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_type_index` (`type`),
  ADD KEY `documents_customer_id_index` (`customer_id`),
  ADD KEY `documents_vehicle_id_index` (`vehicle_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `hires`
--
ALTER TABLE `hires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hires_customer_id_index` (`customer_id`),
  ADD KEY `hires_truck_id_index` (`truck_id`),
  ADD KEY `hires_trailer_id_index` (`trailer_id`),
  ADD KEY `hires_status_index` (`status`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoices_customer_id_index` (`customer_id`),
  ADD KEY `invoices_hire_id_index` (`hire_id`),
  ADD KEY `invoices_status_index` (`status`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_records_vehicle_id_index` (`vehicle_id`),
  ADD KEY `maintenance_records_status_index` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monthly_revenues`
--
ALTER TABLE `monthly_revenues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `monthly_revenues_month_unique` (`month`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pnl_details`
--
ALTER TABLE `pnl_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pnl_details_month_unique` (`month`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicles_rego_index` (`rego`),
  ADD KEY `vehicles_status_index` (`status`),
  ADD KEY `vehicles_hirer_id_index` (`hirer_id`);

--
-- Indexes for table `weekly_revenues`
--
ALTER TABLE `weekly_revenues`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `monthly_revenues`
--
ALTER TABLE `monthly_revenues`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pnl_details`
--
ALTER TABLE `pnl_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `weekly_revenues`
--
ALTER TABLE `weekly_revenues`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
