-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 02:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `water_billing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `billing_plans`
--

CREATE TABLE `billing_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_rate` decimal(10,4) NOT NULL,
  `min_consumption` decimal(10,2) DEFAULT 0.00,
  `max_consumption` decimal(10,2) DEFAULT NULL,
  `billing_cycle` enum('monthly','annually') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fixed_service_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sewer_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_inclusive` tinyint(1) NOT NULL DEFAULT 0,
  `tiers_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tiers_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing_plans`
--

INSERT INTO `billing_plans` (`id`, `plan_name`, `description`, `base_rate`, `unit_rate`, `min_consumption`, `max_consumption`, `billing_cycle`, `is_active`, `created_at`, `updated_at`, `fixed_service_fee`, `sewer_charge`, `tax_percent`, `tax_inclusive`, `tiers_json`) VALUES
(2, 'Lifeline', 'for domestic use', 150.00, 25.0000, 6.00, 210.00, 'monthly', 1, '2025-07-17 09:41:16', '2025-07-17 09:41:16', 0.00, 0.00, 0.00, 0, NULL),
(3, 'Tiered Test', 'Tiered plan with fees and tax', 100.00, 5.0000, 0.00, NULL, 'monthly', 1, '2025-11-16 15:12:09', '2025-11-16 15:12:09', 50.00, 20.00, 0.00, 0, '{\"tiers\":[{\"rate\":15,\"limit\":10},{\"rate\":18,\"limit\":30},{\"rate\":22}]}');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `meter_id` int(11) NOT NULL,
  `reading_id_start` int(11) DEFAULT NULL,
  `reading_id_end` int(11) NOT NULL,
  `bill_date` date NOT NULL DEFAULT curdate(),
  `due_date` date NOT NULL,
  `consumption_units` decimal(15,3) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL,
  `payment_status` varchar(32) NOT NULL DEFAULT 'pending',
  `billing_period_start` date NOT NULL,
  `billing_period_end` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `client_id`, `meter_id`, `reading_id_start`, `reading_id_end`, `bill_date`, `due_date`, `consumption_units`, `amount_due`, `amount_paid`, `balance`, `payment_status`, `billing_period_start`, `billing_period_end`, `created_at`, `updated_at`) VALUES
(13, 5, 24, 11, 12, '2025-11-20', '2025-12-04', 1.000, 185.00, 185.00, 0.00, 'confirmed_and_verified', '2025-11-20', '2025-11-20', '2025-11-20 18:04:45', '2025-11-20 18:08:49'),
(14, 5, 28, 9, 10, '2025-11-20', '2025-12-04', 2.000, 200.00, 200.00, 0.00, 'confirmed_and_verified', '2025-11-19', '2025-11-19', '2025-11-20 18:04:45', '2025-11-20 18:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `account_status` enum('active','inactive','suspended','pending') NOT NULL DEFAULT 'pending',
  `application_date` date DEFAULT curdate(),
  `connection_date` date DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `user_id`, `full_name`, `address`, `contact_phone`, `contact_email`, `account_status`, `application_date`, `connection_date`, `plan_id`, `created_at`, `updated_at`) VALUES
(5, 8, 'Mulwa Maxwell', 'kitui,90200', '254788981020', 'mulwamaxwell16@gmail.com', 'pending', '2025-07-17', NULL, NULL, '2025-07-17 09:40:04', '2025-11-21 05:46:55');

-- --------------------------------------------------------

--
-- Table structure for table `client_bills`
--

CREATE TABLE `client_bills` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `client_user_id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `bill_amount` decimal(12,2) NOT NULL,
  `bill_status` varchar(32) NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `image_path` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_bills`
--

INSERT INTO `client_bills` (`id`, `bill_id`, `client_user_id`, `sender_user_id`, `bill_amount`, `bill_status`, `pdf_path`, `created_at`, `image_path`) VALUES
(10, 13, 8, 11, 185.00, 'confirmed_and_verified', 'bill_pdfs/bill-13.pdf', '2025-11-20 21:05:31', 'bill_images/bill-13.png'),
(11, 14, 8, 11, 200.00, 'confirmed_and_verified', 'bill_pdfs/bill-14.pdf', '2025-11-20 21:05:50', 'bill_images/bill-14.png');

-- --------------------------------------------------------

--
-- Table structure for table `client_plans`
--

CREATE TABLE `client_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `subscription_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','pending','cancelled') NOT NULL DEFAULT 'pending',
  `last_payment_date` timestamp NULL DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_plans`
--

INSERT INTO `client_plans` (`id`, `user_id`, `plan_id`, `subscription_date`, `status`, `last_payment_date`, `next_billing_date`, `created_at`, `updated_at`) VALUES
(7, 8, 3, '2025-11-19 18:50:02', 'active', NULL, '2025-12-19', '2025-11-19 18:50:02', '2025-11-21 05:15:52');

-- --------------------------------------------------------

--
-- Table structure for table `client_services`
--

CREATE TABLE `client_services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `meter_id` int(11) DEFAULT NULL COMMENT 'Foreign key to meters.id for services related to a specific meter',
  `service_request_id` int(11) DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected','completed','cancelled','pending_payment') NOT NULL DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meters`
--

CREATE TABLE `meters` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `installation_date` date NOT NULL,
  `meter_type` varchar(50) NOT NULL DEFAULT 'residential',
  `initial_reading` decimal(15,3) NOT NULL DEFAULT 0.000,
  `status` varchar(50) NOT NULL DEFAULT 'functional',
  `photo_url` varchar(255) DEFAULT NULL,
  `gps_location` varchar(255) DEFAULT NULL,
  `source` varchar(32) DEFAULT NULL,
  `added_by_user_id` int(11) DEFAULT NULL,
  `next_update_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_collector_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meters`
--

INSERT INTO `meters` (`id`, `serial_number`, `client_id`, `installation_date`, `meter_type`, `initial_reading`, `status`, `photo_url`, `gps_location`, `source`, `added_by_user_id`, `next_update_date`, `created_at`, `updated_at`, `assigned_collector_id`) VALUES
(24, 'S21PR001', 5, '2025-11-20', 'residential', 0.000, 'installed', '/water_billing_system/public/uploads/meters/meter_691e0f8378b13_meter2.png', NULL, NULL, NULL, NULL, '2025-11-19 18:42:11', '2025-11-20 15:45:22', 12),
(25, 'S25PR001', NULL, '0000-00-00', 'residential', 0.000, 'functional', '/water_billing_system/public/uploads/meters/meter_691e0fa279d6f_meter3.jpg', NULL, NULL, NULL, NULL, '2025-11-19 18:42:42', '2025-11-19 18:42:42', NULL),
(26, 'SN109222', NULL, '0000-00-00', 'commercial', 0.000, 'functional', '/water_billing_system/public/uploads/meters/meter_691e0fb945f08_mtr reader.jpg', NULL, NULL, NULL, NULL, '2025-11-19 18:43:05', '2025-11-19 18:43:05', NULL),
(27, 'SN1268903', NULL, '0000-00-00', 'industrial', 0.000, 'functional', '/water_billing_system/public/uploads/meters/meter_691e0fd41c7e2_istockphoto-185266465-612x612.jpg', NULL, NULL, NULL, NULL, '2025-11-19 18:43:32', '2025-11-19 18:43:32', NULL),
(28, 'S00198005', 5, '2025-11-19', 'residential', 0.000, 'installed', '/water_billing_system/public/uploads/meters/meter_691e10740320f_clientmtr.jpg', '-0.700000,34.780000', 'client', 8, NULL, '2025-11-19 18:46:12', '2025-11-19 18:48:47', 12);

-- --------------------------------------------------------

--
-- Table structure for table `meter_applications`
--

CREATE TABLE `meter_applications` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `meter_id` int(11) NOT NULL,
  `application_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `review_date` datetime DEFAULT NULL,
  `admin_approval` tinyint(1) DEFAULT 0,
  `admin_approval_date` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meter_applications`
--

INSERT INTO `meter_applications` (`id`, `client_id`, `meter_id`, `application_date`, `status`, `notes`, `reviewed_by`, `review_date`, `admin_approval`, `admin_approval_date`, `admin_id`) VALUES
(27, 8, 28, '2025-11-19 21:46:12', 'approved', '', 10, '2025-12-02 01:03:04', 3, '2025-11-19 21:46:57', 5),
(28, 8, 24, '2025-11-20 18:42:37', 'approved', '', 10, '2025-12-02 00:58:41', 3, '2025-11-20 18:43:11', 5);

-- --------------------------------------------------------

--
-- Table structure for table `meter_images`
--

CREATE TABLE `meter_images` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `meter_id` int(11) DEFAULT NULL,
  `collector_id` int(11) NOT NULL,
  `image_path` text NOT NULL,
  `taken_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meter_images`
--

INSERT INTO `meter_images` (`id`, `client_id`, `meter_id`, `collector_id`, `image_path`, `taken_at`, `notes`, `latitude`, `longitude`, `created_at`) VALUES
(9, 5, 28, 12, '', '2025-11-19 21:48:47', NULL, 0.00000000, 0.00000000, '2025-11-19 18:48:47'),
(10, 5, 28, 12, 'data:image/jpeg;base64,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3FdqiOImKx5YREXueYREQBERAEREAREQBERAYDb+7TMRLmw2p7j15HzXP9rbBqUj9ZTnlIkewrr6pqMDhDgCOREhaatVKvjqjNdpYWc9zidR7snZ53tbERMgDkAQcotoIUGlgmskhziTzNvSF2LG7sYap9jKfw29yxFbcOn9moR5EfqtsdbDHoYpaKefU5lUarXZrpzdwGcano39VNw24+Gb4szvaAPSJ96mWtrEdFM5Th8C95DWNJJ4AErd93dwXGH4nuj7gu49eDfj0W+4LZ9KiIpMa3oLnqdSpSx26uUuI8GurSxj15LWFwzKbQym0NaNAFXUphwhwBHIiQqkWQ1mKxW7mFqa0mg/hlvwssNi9wqLv8N7m9QHD5LbkXpG6cejPOVMJdUcs2nubVpuaXw6kXsBIPAuAgjUSrW09yWOOekchmY1EguYzoLz7F1PFUA9padDB9oII94Cx1TZZEFpnT3OzH4lYde7rpKS7LB76WNdSaXc49h2Y7A1GkFwYC2SJLctNj85I65rHyWM3+x9SvVpUapGenSY540ipVa2xHlTbTP9Z5rslfCWcHDg3XyLifktK2XuNWxG2MRicTTy4ZlXMyY+tyhopgD7kNEzyjnGOlSk+VyaZtI3H6NdhHCYFjXz2lTvunhm8LfKGxbnK2pEXUjHasGRvLyERFYgIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiAIiIAiIgCIiApfTB1C9DQNF6ijC6gIiKQEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREAREQBERAEREB//9k=', '2025-11-19 21:52:10', NULL, -0.70000000, 34.78000000, '2025-11-19 18:52:10'),
(11, 5, 24, 12, '', '2025-11-20 18:45:22', NULL, 0.00000000, 0.00000000, '2025-11-20 15:45:22');
INSERT INTO `meter_images` (`id`, `client_id`, `meter_id`, `collector_id`, `image_path`, `taken_at`, `notes`, `latitude`, `longitude`, `created_at`) VALUES
(12, 5, 24, 12, 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAAFyCAYAAABP+efyAACAAElEQVR42uxdB3hcxdWlG/duWVbvK2l31WX1asmWm9x7LxgwYBuDbUyzMSWEYEw1JT82PYDpYDCYTiChJEBCT0wNodmhhRIg958zb+5782bfSnISUZI33zff26bdt2WOzj1z7r377OMPf/jDH/7wR0cHEXX7/PPP8999993mZ555Zvhdd9014oYbbhh71VVXjb388stHb968efj5558/HMeLL764ScxycV/W9u3be/mfnj/84Y9OHW+//XbXJ598snbbtm0LfvHzn9926OGHPjNz1qxPJk2dSsNHj6b64SOoaeQoGjF6DI1sHUejx4+nUa1ijhsn58ixrV+L298bM2Hic2MnTHpo/NSpD85bvPjKZStWrDj++OPHnH3uuY07bt4xyP+k/eEPf/xL46GdO0sEM1q2ZMmSeyZMmrSrqr6BKuvqqbqhkYaNGEGjxo4VQDSWWgRgDW9poWZxW31jI5VXVlJJaSkVFhWLWUQFhYWUl59PoXCYgqEQ5QZDFMrLo7C4LTcUphxxW1g8pry6+t26puYXp8+cdeeaNWtWXnvttVN27doVI9jc/v634Q9/+MMr1Ot69RVXzB0/ceIjBaVDvxFTAEkNNTQ10UjBnsYKttQyahQ1DhtGFVVVVFxSIsEoJyeHsrKyKBAIyMvBYJBCAogAUhKoxPXcnFx5H9+fmyuu54q/E3+TnpFBaenplJyaSnGJiXJm5QaptLJyz9y5c5+5ZPPmM1/dtatJnF9v/1vyhz98oDrgyiuvnDduwoQ/ZAFMBMiUlpVTrWBUw0W419w8nCoEcwJbKigoEExJPEY8LiwelyfYEm7jWSgeU1RUSCUCzEpLMUupuLiYigXbKi4ukpdxu7xNzCJxe6F6XoAfZlA8b0AAGwAsMSWFBsTGUkg8Zt68ua/fefudG99///08/1vzhz/+B8eVW7aMaZ0w4TdZIlQLgNUIoCodOpSqa2qovl6EgDW1ki0hnAM4FRY6wMSgg1lWVkbl5eVUUVFBVYJ91VRXy2OlADrcpk/cLmd1lbyOvy0VbI6fCyElwDFfgmOIcgQ4ArwGDh5MgxMSqKa+/tszzzjjjj/96U/1/jfoD3/8D4xt128bNm78+HsBVNliDhVgUylApqa2lhoaG6lRzJraGglO+RKoLIBi5jR0aKkAmqESbHAcKkCuSDwmJMAlEMiidBHipYIhifAuXoBMgjFTBHPKyMyQISKYGpgWngdAWa3ADqwODE2yNzw3mFd2Ng2Oi6N+MTGUK85r5YoVd7z88h8r/W/UH/74Lxzbt29vnjl9+t05YrFnZudQsQAEAFWdYFMAq2HDhskwsK6uToITWBWOAKQyAWo4Arhwe0ZGBiUlJVKMAI+ePXrSwV0Opm5du1F3Mbsd3JUOPvhg6nLQQdSlSxd5H64fLC4fdOCBdMABB9D+++9P++23nzx2796devfuTYNjBkswg8YFoCoD21MABuZXIBheoXh9sK4UAYq9BwygQDD43fLly7e89tpraf437A9//BeMp59+umrRokV35+bl20AFgKoSYNDQ0CgBqqmpiZqbm6lW3A7GA8AAWJUp9gTWFCfYTa+ePSX4AJy6desmwaZHjx5i9pT39erVW4BPH+qD2Uc7ysu4r5cEp759+8rZu1cvCXh4nm5duwqQA6gdJIGtX99+8nXD4ZA4l2IZYpaKc4LgHxagmSfOMT0zk/oOHEjh4uKPTj311OXQ5Pxv3B/++AmOnTu35xx99PLLyqqqvw0XFlG+ACIZ/lVVy/CrVgDVsGFNklmNgDVBMK2i4iKpVWFWwaYgwCFm0CDJkgAsABkAiZz9+1H/fv2c2/r1F7OfNq3r/fv3pwH9B1jHAdZR3q//Td9+9hHg1ksAGdgZGBhADK+B3USEpzJkrKiQISPYHsLFpNQU6ivOs7ml5aGdO3eG/W/fH/74iYwtW7aUTJ027Zf5xSV/F5Nq6uppmGBQI1pGitkiQQpg1dDQIELA4XKCWYFVIeQbKoCAdaohQ4ZIFsQgxaAzcMBAGiiYDY64jSfu06d9W/8BEffx/fq0wauvBYRgaz0Fc+stmNt+++4ngRM6GLQvKeqLCS0Nvi/sXg4R96UGAp+vXbt2qf9L8Ic/fsTjvPPOax7Z2nqLCI/+USCAqkqwqEYBVBDSwaBGjRpFI0eOFMfRNGbMGBozerS8vUY8DkwK9oNCAVrY8UMoGBcfTz26dbcZ0wABKBKoxBw00AKsQYLV6MBjXubHD5DTAin8XQSgaX/nsDJrStYlgAu39+3TV4aM0MEGDx4sdxPBFksFwBYJsIUpNS0jg2Li4mnc+PHXP//88339X4Y//PEjGUR04Pnnnz+xaUTLg9nhPAJQVQv2VCvCu2bBnJqamgUotUiQYsBqHTuWxo8fT5MmTZLaVaHyQ4FdVQiwgoYFvap7t24WUCmQYYAypw5A+hzIINXfuK5dZkBzgM16vM3gBjJADrIv4wgQ23fffWXIGC+AVZ67CBMBvHgvwVCYBgl2mFdY+Ow999zje7f84Y8fGqg2b968YNjw4c8E8wuooKhYalMwfMKWAAEdoZ4EqtGjqKWFmdVYyawQDrKgjlAQIIXFXlFRLgAgztarGFQAGAxQ2Bm0Lxvghfv4fjCgASpkdEBqoM2mADz6483nwd/z5MfFKICUoCXAtJc4z/1lqNhFuu2rpSWigvJhQg2FpA0iMzd39+WXXj7a/9X4wx8/wLjxxhurWyeMf0IsRCoqLZWpMtj1g2gurQkizAOramkRU4WCY5CcLI71AqggVofzwtIUWiAWNsDKsi6USX1IMiulI3FY5sWcXOxoYOSU7EsDKC9ta5AGQObf6qDlBWyS2SkxH7uMwHE8FqEt9K0ilc+YkpZGQ5KS/nH22WdP9X89/vDH9zjmz5+/KhAMfRMUQINkY+lKFyAEIV2Gf4JVSZ1KzNGCSWECuNiuAHMn8vmsNBorFCyB0C4AKzk5iQ7Yb38psrNmNEDt8NlTD9kGGOGaETYCYHRR3hTZvUJME7BsoNIva/cBoCRTE7fJMFGAFnYYIcpXyZxHKxE7NT2d4lNS/rnxrLMW+r8if/ijk8em006LySsqujk3nCdCHjCqOglUDY3DZL4fh4BSXBcg1draKpkVPFZYsGBTnLdnO9dVOgySlwFOCK3AVOCnYu8U+6Ucu0Fv20dlAZpiXBoI6UDkyZ4UwA2KEk7qIaV+mzkt9hVrgxb+Bud04AEHypkp3hcAHaAF3xZSfBIE27rooovm+78of/ijE8auXbuytt12W0FWbu79A8UizRFhIJzpMuQTLApHBquRilXBulBXX6d8SlbohxQbNoTKKRhadna2tC0AaCBcp4nFnCGYCLQgrqiAYzCYK5kZUmngcEfaTVJSEsUKoABAgI316tlLAhqHaP2UP4vDPhOYvMR7CUIx3uFfVE3LCBv5sTChgm3hXKXhVDBIgFZaZialZgW+ueaaa6b5vy5/+OM/NODYXnXMMccXl5V9XlpR+Y8SseAQ4qCiAQAFC5AZFUK+sWPGyoldP+T7ISTCZEEduo4VAhbIkDBTLFxMABIEaot95UsjJsyjuN0qG+OUjgnZSdBWOAnBHhPPB0f6kCGxtukT9gN7h7EdrYunG4QcMNKByQuozNtj1W3wbAG0kpKT7URrnHt6pnhPofA/HnnkkXH+L80f/vg3x913350s2NOOdAESUlSvtER1tikgzANgIGUFHiqwLAAXAA3gI4FKHDlhGTNfMSpMgJEEtLywLBcjxXckOudbgAVm1VcAT1ex4JEbiDARbvOugrUgXER4OFgAQ0pKsgQ1/B1SZ1BCBueEGlcQ78GwAGD2LqFmS4hkWzFthn9e4NUWmMXGxsqJtCGAFhgkV5UA6MclJlFZZeVnb7zxRoH/i/OHP/7F8bPTfjY8v6TkbalVyTIsKPViaVUAKyQny90/MVFMjyslgDkAOBD6AaxYqwqHLVDKycm2Qz2euF1OVd8KplEAVm6uVZQvOztggxxCQSQmw/IAgOkpQsCuB3eVIn1PAQq4DfeDieF8AJIh8dzJgt0A4GCTsEJF925hhE1CCeyD2mNSuK7CRwATQlsJUuIID9kQdQTbAuByeMiJ1EjniRH3T58589k9e/b4xQH94Y+9HfPnzz8uKT39m0Kx2KsFUNXW1kmXOqwKACuI6wxY8FTBWwXPldSmAFIqtMO0gMfSn3SQyg1qU94ftCfrXWBYUvfKz1Mz3zjmSSYFUIPuxaDQ5cCDJKMB8GSIcJN3IPHcKDWj5xjqqT3snPfaBTQZFEDKBDDXdTEBVPrE7RDhAVo43wpVyhnlmvuJ+9avW7fN//X5wx8dHLfddlvPqtraazIFO0H4hzw/sCowKAlWzQqspF41Uu7+wXNVKOtEhVxVPwEmABuAEZiRLE2sACjMJYsVcAWDDmDhtpAsb2xNriiKaQFUWAKadT3fLuaH+xgcwaYgutvVFgQ44TZoYwgXoXMBPHiHceDAQS5dKypISSCKvoNogVisPMYagAXWhSOADOVsAFp4nwilC6SelUkZ4jN68MEHF/m/RH/4o52xYcOGrLySkidCAgQqlQEUu4Bc7oUnBHbsCuIydr0AIFIwF2BRrPxUABKADUI5BipMu866AjI7JPRgWQxcYFB5SrjXwQuXGfwYzPA4FuBxH4T6OBGSIWSE8A1GhZASTBDni9Cxv8oRlOxK5SPqIWG0XUAGKADRYBUWIgwcok0AFDQrPmJHEwwPoAjAwjkhZMXniFI1qGY6cvSoj8VI9X+R/vBHlLHgkAX1mcHgewViIcMvxN4qi1k1KTNokxTUR6rwr1iFWHK3rkCZPwusEBAAxM0eMHUwcoWFubkR9zGoOQDn/hsJUMyy9MvMuvKc8JHBDbuQvFuH+lkAE6lxFRXKcBLgIxOd+znAFS0cjFUMytoFhFYVq0DL0q/i4uNs7Qog5TWxEYDXA2gNFK8DER7ldlDlIVYA2i/OPPNO/1fpD394jFFjxhyZmZP7pbQroDuNACMU0wNYNQmgYssCHyEWIwwDUODIOYDQrQAyCP8gquthoA5KoZAbnLyATAKW1vnGvK+9abMxMS29ywIvhIJgUz2695C7hmBYDHxgP+x89/Jd2QzL8GjJMFBdh51CF9ptkJKXecbLI+5HAUKAFs6rtrZGstNAjnif4nxffuHlVv/X6Q9/aKOiru4XAQEKSK3htJoGxaz0MHCkCgGxE8jhWZHyQFmMKkgBDv8Uo2KgigSskAuoTMAyGZYJZgxETojogJObceXZz6OL9wBT6EuyOqkALoALHg/QTUlOsR3z0VgW7wzatgXeFVQ7hCZgsYZlg5Y6AiBRNgdVHqSeJc4PlVdhdUAtrcMOPfRZJJb7v1J//M+Pa665pm9pRcW1aCAKywK0KmlZEMwKoR/rVpJVwa2u/FZs2CxWTnUJSPBTSaBSx9ycCJBh0DIBzCtUNMNDT8DKy7PBKayHhyoE5PtZ1Ne1Lr4dIjy8XAgRAUBW/fZ8W7BHyIbUHZNJxcZa+hSHgrqwrmtXTkgYZ4OX3gAD+ZKoRc95h2B88GaVV1TIfEMklD/88IN+krQ//rfHGWeckZqbX/i7cEGhBCr8V29sHCZ1KdaqbIFdM4GyqbNIAyvpp7JF9VxPfUoHKh24dFBy3RaMBC396IR91nSHgSHX/dbjnVBUZ3cAL1gLIMSjJjzACGEs/i4F7bwGWsnSMTGDXak6nDPI07QuSMBSwBWvmJUOWNy9B+I7WJbuhIc/a2jZUFkMEDW05s+b9xv/F+uP/9mxevXqUFYo9Dr8VdgJBHPidloMVE3Nlm4FjxX+44eVDiTrVBUXy8tgUxCy2a1uhn/RQIrDRdssaoSEbquDCViOhqXvEDJImWDF2pepnclzkuk9QVuQ5447ACnrPeVIMBmokqbdwnus7bPyBqxYNU3AinMJ74mJSVJDA8vC81i7hl2kjgWWlSXOIy0r8M2jjz461P/l+uN/bhx55JHN6YGc9yGuy75/DQ3SQ4UjMyqAFvQqTOhV7IOyTKAFVuKxcp8HxNQtC22Blf4YOyQMRTIsm12FvHcU2Qlv7hSGPXYL2W5hgqMOnPyYjIx0m+mgPAzeH5gjWBDCQ13LYq2KQUkPA02mxSGhvjuoT4AiWBXCQwAm5xvKZHHBsvqLc1q5cuVM/9frj/+pcfgRRyzMCga/4p3AelUNlJ3rzK54NxDeIAYHTq+RvqbsgLMDqHYB9cuejnZDu9IBKyIc1EFLZ1hBt4ali+0uANOmDljyOTk05OfWnhPnDqaFKqQoGggw4pAXwMMGUTaEssiui+0uBqWYFQNTnPRgJUp9DIAEoEJomJhksSyEpuzNOmD//eW5I21niADMSVOn+tUc/PG/Mw457JBj08TCKxlaJj1W0K0cvcphVigHg8uo2cTgU6D8VVhAMGFyPp8NUmxdwFThoYsRMbvC8/HuYU47gGXYHYIGwHTE1iCnwdwYCK37ndBQPzewHatHYW8JNAh9uQ+ibRgdHGOHg6jkoO8OMljZO4PQrDR2BcaWmMhHS8NKVgAGkV98XTbLQspOgjguXDjPByx//G+MxYsXH5eWFaChZeVWRVCk2TRaZtAmzbmOFBuwLYR+WLwIqTjFxlrYDpvK1kFKhYR69QWv0NDr9lAo6LkjqDMtr+naJVQhYDjPCA9VLmI05qaHhvZ5K+CSSdICPABaYD64H0BmloyJ1YR39l/FaZqV7sOKU4BlAVSSPKakJMvXwsTfo24WfFlIJQLbg/M9ITmF1q5e7QOWP/77x5IlSzaki8WGMLBSWRekIVSxq2EqP3A4Ny6Fp0qBQaEq8YKQCKks2bZ1IcfNqvTrOBr2BROkvCo0RNsxjAZYnGPIQrpLuwpr9oawO2Q0dyD5tbJzsh22pcBX1pPv3l2GggAPsEu5oxczOKp2Fa/vBCqgYvaE0C9JTQYs3AdAxOPA6vB6VgpRF3kuAKw48bhT1q3zAcsf/72DiPZdsmTxeQCr0rIy2ROwrs4S1wFWnMjMJYyRfCuTl8UCzld6FdeicjnWTaDymG3tEHql4nixqwiGZYCXzY6U34t3DHV7g+525xkyXoMtDvq5M1OE6A6w6tOnrwSlzMwMqxqEF1DZO3+WZpWodKt4xah4JiYlWlOFg3g+6FeoDgGwguhupQ51lRVJiwRgIU3nrLPOGuP/qv3x3wpW+69YduSlKSipUlxsa1YOsxrmEthhacDuH1sXipVtAazDqvIZsICnA2DFqTRmQrNrl1BL1dFZkleJGZf47gFYTlWHjqXqRANHW4vT0opwniyEw9AJgIGWBSBy2FWsBliOyM66laNZRU4AmATA2FgZegKs+vTuLYELnjBZbbWomOJESHjZ5s11/i/bH/+VzOqYlSsvTxILq6jY8lnVaszKASsrJxDdayT7UIZQ7AYijJIlizMy5aJ1Maz2wkFNdDd1K9Pe4KTgeABUKOjJqryADIDltTuYZ6bvSNbl1rIYKPVwlgHLCgMDElxQ8RTaFQDL0psEWMU6YaHtZFciuxkScgjohITJtv9K5jX26CGbaQAYAVwoQigrXoiZmJb27bXXXlvu/7r98V83Tjv11AtlKeOSUuler5O1rBx2hQmBHezKDgNVqg2XYwGrYs0GCzZyZkVnWBp4ZWs2hwh7g+HdskK7XM8dwQgDqVH0z66X1Y4Py/VcDFjytXJdIGy/T/E5ZGVlSv0OO4JobgHwyhTXZQ5gXLy2M+jkB+o2BreGlSiBC0I7wAoiPtgVas5j9kYrM3QDAnCJKW0l2JkNhb597LHHyvxftz/+q8Zll112RiAUJpSHqdSambIxlHMEAVgy1SYvz95tA7OCZoIFmyUXatvTE6wUOzFZmAlUXmxLLyNjV3KIUo3UFOCDbYaEwQjdzNP5rlk1AFb6ewV4S/OoKu4ndScBNpzUnGAbQy2WleBiWE5IyDuEOmBBIwO7QpOM/v36U18BWMhtxGvJss5I3hb/SF566SU/JPTHf8+45ZZbVoUEUAXFD7ysvJyqBbuqVYBl6la4D+Ef59LxZTAnsImMzAybZcgFDKNotsawsgNudmQAl8unFQWwvER5r1w/E7C8WFZIvQ9zR5CF92hCvwuwchywZcACUElwVjXkwYbAgFgst42f4nqcaWPQQkIzFMRMVYAF5oYQEG3JuN9iD3EdzG2oagVWJv65vPLKK+P9X7k//ivG07/97aGFpaWUlZ2jGkVUy3CvTqsWKvMEBViBaRXZYWBYhYTcZivDbqUVAUA5aqrLDFQuT5YOYKxn5eR0MBk6xwYX6ERcrkYP38yQzoth8Y5hngFaVpJ02A1YGvvSgVdnWQG16YDPB0DDHXYQ2uE6g5VXcT5vwLJmitKw8Di9jyImbA14HRT0A1tOycikW266ye8Q7Y+f/nj99deHNg0f/lWq+FGXlVdQuZg1tTWyDjual9az2C5Ai8vDOG21rKqcYEwQ2L20qiw1o2lZVjeb7Db1LK80HS+rAwMPQEBeDociNa2gtxCvp9h42Rk49PXyepmlbji01UNC6Fbc4AKVHeLjLCDiHcN43hnUdgjjNMBy7Q7KHcJEW4hHOWZoWLBP4Ij2ZT1FWIh/JmDL8UnJtHzZkVf4v3Z//KTHF198kTBj5oxd8eK/dXFJqUzjACjVMVipcLBWCe+wK3D4JFNtxBELMzMrU05eoAxE+nWvad/PIaIHYOXmcAG+tsvOyKnc9QBDi2WFnR3EUGSKTtDQuszSMxG2BsXA9NDQDEl15miDlZoALAAUmlUgPJQApMJCm10ZoCUrMkjASrB1rEStrAxYFgBP7hL26i2PAKsDDzhAngMAC40pyquq/BIz/vjpDtgXVq5ccV9Caqr0Wg0V4UNNTa0MBRsaLKGd2RWm1ZcvZO8IYsoFKZgDJutVezMDWp9AVygYxZcVzUCqh5IAKGhqyWIxc3dnt6s95O3P8irulxdZ1E+GnbqlwetcDMCCfoWjVVEhWabhICwE6HBYGBEOaj4sL1sDTzwnjgAqsCt4sBAW7rvPvrI4IL7XAHS1YHDX008/7fcr9MdPc1xyySU/RzJztlhkYFbY9atW2hUAi+0MACvZXVgtXMvBHpaLMUMBlQ48bQNUJFjxsU2Plp4gHSWn0MwvxPlBmLY6R4cikqojHPKhoHdqj25ebaOKqSudSHO6M7tC6RnLN2WFgQjj9AJ8EezK5cPSU3HcMzXNAkH4vHr16m3pWQK40NkaXix0sC4UbDg2IeG7Tb/4RYn/y/fHT2488MADrfmCVWUGsgVYDaWKigobrLAzKAvyNVhgBSCzfEohuxED7wbawrKmRznHQIfsDWyubIth6b4sBhAvwNJvwzmDfVhhYtgFdtFKKEcDrLbSgsyQMFc7Vx2wEKLqgAWGhYqiSSrNxgVSZkioQkG36926jOdkMd/SsfpIPxac7kjPwXlxiZllRxwx3P/1++MnNd55553M2oaGD9Mys+R/XoQMFljVyo4rAKwaWZGhTgIZlzHm/EAwIrAFaV3IDkQR0/dmahpWO6k7DmAF2wETK6xDuWUs6JCmPUUAlmEENSszmOFiREqOC7By7eRuvCcXw1KAxeAE3QmhIQDLdLYnRBHd8VieTlkZt45lAVZvqWOhASzADJ2hU9LSqK6hYYO/AvzxU9Kt9lu0cOFOZO/n5RdIQKqsdEJBiOsNIgyEnYFrsGMxcmljsBYwK2ZQtkVB907lGLdpl6Oxpo7kF+rsS3e6R9s1xHVuyYVzxW6mrodFS9MJetgedNDySrAOGkZWvGcJ5gZgsYaVqAAoRupYCXbNqwQjf9DdaCJy4na43/G8eDz8V9gp5N1ChIVgcgjpEfqH8vNv9leBP34yY9PGjetTxCKCORTMCoAFYELH4GqZhmM52gFe2BG0XewFVmKzDlZsBs0KWFM3irIPKWJGYWQu0b0joKUBnZkcretIcoNA9Qp0ysO4y9JEKxejM6h2K0JEEd0DmuieIW0NqZQqmA53ugFg2RVGNZBiMZ6risqZ6K7SwAwLt7EJta9iV1w8sEe37pJpoYU9ZkpG5hOXXHKJ3/LLHz/+8fgjj9eGi4q+SheLqEiAEba7AVQstjPLggcLtcB5NxANI6ABZfBuoId1IbAXmpXXtAErNye6+J7jfV80fYkZGLS31JRUef6oKJGrCuzx7qFpd/AqT+MltJu7hBYz8w4JHcCyUnJ0/xXsDXzZBiGPygx6DXcX+8JOodSxkmQfROhXMiwUgIXWY9CxuC7WoLi4v5966qlJ/mrwx496fPjhhz1Hjx7zcpz4oRcWWRaGSglUNRZIqQnQAuti+4LcERSsJMuuPJBllzX2siaYu4XMwqI9Tr/fLB0TFaxyTVNptPLIbjOpVao4R6UQBWywCpmAFWoHsDy69ESI7gqwHIaVboeEKSp84zLICNlwbuiiw74qZlZsZ0hOih4S2qk6Kk1H7hSKkJCFd/ixAJRl5WU0JDGJVqxYEfJXhD9+1GPt2rWXJYofNDL3IbRj1wjsqlKFhNWqKkNlVaVkVSyys4YFHYjz4phR2XWutPAs2+Oy12zrsTqIZUfTuDoYPjKIoLEpGI7VUt4yu0YT7nNyctotsxytI4/NsIxdQj0kZMDiCg1gWAAvbqbK7Cne6IrjBVY6aHGaDvuxUJ4ZmhY6QieI58Q/qZS0dJo2c+YCf0X440c7tm/fPiZLLCz0p7PAqlSKsFJwr0JIWCn9Vu6KoVZ5YwAD2AHSblg81ne+vELBgBYium0PAddtXg54r3CRC+F1RIzXhXy9PjzrV1jY2UrbMu0RJsi5fFlRigOGgh0zjiKUZobF7bg4f5ABy+5BqLveNVtDm1N10gFg9RRgBd0K1RuQFN3lwIPkbmSJzBXNRq/CTf6q8MePcnz88cd9a+vrdyWJRYIuwNCmkBBbrpgVT+hWVv/AkN0/EJc5nNHZVRZf1gDLBh0bhARD8rIv6EBmglXA+Vv3fR6VHTqys8hVPxVoQYcDc8PiNu0LEb0OuQqDHjaGnNI1IVcoqYefua5z9NKwpDNdgAsX67MBK3aIzbCiVWtgjStJK5OcpHXNSZSJ0L1tloVj967dqLe4jO9fMeyr/ZXhjx/lWL1q1aWx4kcMC0NRseW5KtfEdhzBrABWBaphBO8IovICwIorDZi5gXoTVDu0y9GOHuGeyX7s6g1mmGhaJNoIAfk1vab0d2ndbPD+sLhx7pY3K9cu/NcRU2nQo9ehCXZ69x+30z3D0rDgxUpKdHXGidda0yea5lEpsicZLb3cAMZerCRV2ZQBCxUcILx37XKw3CVEbaxgfv4Lr7/++sH+6vDHj2rce++9zemBwD8RBmBXEOzKCgXLZRiIo7Q1qNpWnHaD0jGokmkBVoZtZZCgpdrKm+CTFQh46k+2jSHbsDxka1YIXZiPYoNwPZfN0CLDzLaYF54DO4YAFSxyBh4rRAx6txTTdiFNDc1Mw9EB2gxv8Vk6TvdEV013W8+Ki7d3DBNM1zu3+YqL11iWW89iUyq0KxkS9u0rpwSsgw+2ciHFP65AMPTZzp07+/srxB8/mvHee+91b25peTFe/JhRDwlgNbRsqAwFAVJ8hNiOhqfcPIIFd07WNZmVY2FwhHcvzakti4Oue0VYI7C4VdUHdz2paK/lYY+IEh7yDh6bSTEZsNqqtRXNmGq39/JoButVcdRyuie7LA1Wak6SzbSGRAMsz0aqhidLGlITpbUB7ApgxfXd0fIL54od4pTMzK9vu+GGLH+V+ONHM9avX386SsYEQ2HJrhDyVSqQklOGgtWScem6FXQeLGRoLZmKTXFIyNMrtHMxKWN6JTpHY2Ku6qTZbbAs47ltA6rRG9AVSmqhGsAZjMRMrTG7TXsBlGmb8AxFTcD2yCVkhmXVdleAFUV01+tjsamUdweT1GU8NyYaXQCoAFhWWNiTuhx0kGSkaNc2WDzX+Zs2TfVXiT9+FOPJJ5/MzQnnfYUaSNCtAEooDWOBlRUGIl8Q7MqqZBC29atssfgBVsywwG6yPcrGeHVv1vWriMdrGpd5v2mLiEjzMViTne7Df5tjPIeRFmSWWsYxLz/P1pVQ5M/V4LW9PolR/GA2KOZku0ok8y6hBCzNNIrwDw1VZbMJJbZjNy+CYRksSzeQWlMZSxWQoUAgAAulkmXn6d69JcPC94pE98HiOU466YTV/krxxw8+UONq9uzZ9yEzPw/5f4pdwTTI7MoS3CsliEG3gmbFNdnNgnP6TqBc+OYOH4eAWe5dPr4tWrJzdrQk6GgJ0lnuXcPIx2rnkRX5OA4XXeFdMFeGaFz4LyLtx6OmfDCY6wnWbQFWhgIruUOoGBYABuFfzKAYuxGFzC0U7EgHo3gdvPTrhral5xzGqu48ACuUsAF4gWEBzMCwkEd6/HHHrfVXiz9+8HH99ddPSUxLl7tBRUq7KlM7gwAt7AwCtIpluk2eXTkUorPumeIwhjWsbI+yxrqdIUvXtTR9ynXf3lZwaBfIstr8+6yAt5+LwUbaNrIyKS09zQaithq7uqozMPMyzaxt5BE6yc8WYIFhMUDpgJVogpRHWMjXTWADGMIxD8Dq37+/xbLEPPDAA+XfDJWAlUxr1qze6K8Wf/ygY8+ePb3rGxv/nCB+tCHBnPDjBLvinUEcAVg4chv5fNUpBgvc7vBitOkyF7pXyBcZMnas4mjOXl6PlgIUdbZzHgCtlNQUV4t5u+6WR0hog1I0931uTkTXnEyNYSEkTNZCQk7LgeNdZ1gRYOUFUnqjCo1hDeaQsH8/q4uOYFoHCcAaJJ4bGQ7QNlcde6zvxfLHDzvOOO20U0H3c5CKIsI8hHwyHBTAhcneKxkKqqanENqxyDKNXcFooJHjIaBHS6ex/VuaO97VlMGY/6n7dHNrjqpyap2fc5kZEIAarIlTdnRxX4aNXmVwDLDSK67qO6Rsuk3nHEIliksbg3K4o+ie7mTXQ0Iv/UoHJy8QA8PCc0hLQz8LsMC2YGsAOMLtHp+cQiuWH3Wjv2L88YON1196KbmwtPSTZLEw0BgCYjuACSEhUnEYuHCUmlV+vqxcAIbB7Epf8F7WhIA23ZVF20jLMdJ59DzEqGDTwSqlrud0hX/G+8hyt9syfV4WYCXL50Bo7Kq31YFp2j2yAwFXOOjoV6kOYCmHuwuwBOC4AKsDIBVvMCwdsBAKsrUBFRscwEqmlcuXX++vGn/8YOPYY465Ars/qDku8wVLLXZlM6zyMtvNzroVLmer6qFWIwm3dSFqTSuPWlY6+9KZllfCtAmCbd3f1gx0ANDarC+vQl0W0xFO4bJkV1FsEdHCv8g6YM55SM+XSsnZK4bl4XiPjwZaWtkZPAfvEMo+hWBYXbsJwIqRu4QJgmEtX758i79q/PGDjN/85jdlwfz8f0obQ1GREwqWl7n0Ky7IZ3munF3BDFXjylVMr41OzGZT1LYE8fYA6t8FMd6djPb4juw+yjSdYNAWxm0tK9dI+zF2Dl0hoeG4N7Urq6yMExLGRwGsQaoCaYKhVXmJ72aFUkd0VxpWP5NhxchdY8gGK1es2OyvHH/8IGPpYYfdM1iEGLAxSO0Krvahzu4gXO0IDZldcasqXmC6s3xvdvH0nbC2tKVooBQwS9W08ZiooNahooHeoSfra2CWnFsIlsW5h15J2GaSdrZmqo1wtnPuoAIqXcPisjKegKUV6DNBKsFjpzBSdI9V5WX6W5VHe/e2Q0L8PpBbunrVqrP9leOP73088sgjjRligaWJxYGKksywZCqO2iEEcOF2AFWhyhkM2AX5NEe5B0i49KmAO+lZ12qy/oVwLrAX9wf2Ihzs0GsYqUV4DwApZkM5Wmjn7QfzKJWjWUKYYaWmWYBlhYPJdgMKpOTALwXAYvc6l0xmhmV6rPR+hQnGDmKCAVi9pQ/LASx0gsYuodXuKxE+rPX+6vHH9z7mzp37CNgVSodwgjPvCnI4COCCfQGhoOW5CjlOdq29Vkebn+q7Zl7C/N7qTVn/wb8xw8G9eX58HlyZVAKYCvfM92y68b02JPTW9LIGVpoFWOy5ArtiwLLKx1iAxc1V9bZfCR5alXyeqIA1WAEW7xL2lruEMYNjZMPcwfEJdMLatcf5q8cf3+t46KGHWtAINUMsEABWsazT7na0A7BkjqBqJlEsAEuyK7GY2itvHM37ZO7ydYjtBNrf9ctUBks9nUWGVpnOkS/bJVvM2zIiH+N6Pu1+L5Eeul6G0p1Y3zPDW6+/dZ2T0q4yVII1gxac5vFaHXcACPuwGMz4ekI0Z7sXw9L7FSrQYx/WgP4DJHChew5eE3IBNmdOP/XUNf4K8sf3NmS7rkWLJLsKqtrr0r6g2BUL7lK7UlUYUEU0bJhEI8M+dxqMqTGZidD/EkMKRAcs3Q9mgpIXEEW7Tb/d82+1Zho8uclGruplmKEue1Vadf1dpvu82dnuEtxVs9MEDgeRR6ja1VuAlSLBJ8YDsOKjCe9G6o6ugwGw4HSXbncBWNCw8Heo3JEozmnL//2fD1j++P7G448/PqJAgBHKhaA8jGRPgmHp+lUZPFfiByprswtAQ11zt7i+tykzexdmBfYyXIsm3rc3vQCqI3+XZeQn4jpAhsM8aXNAWOj13jRAjwaI0jCqhHauW2WFhEPsxGcdsHCMACwPl7sJWp6A1dsNWGhZj6oO8Ohlivd09913+YDlj++PXR2+dOnD6WKRofFpY+Mw6WAHYGEWFRVKwAJ45WvsCuGOLBuTleV0qvGo8tlWETx9Ae+NuB6IFgZGcbFHAJAK5ey8PG1G3MchJN+nHV1/l5UZAaLsTocvC2k0MHualUPbAlozvEWol6w64Ogdn2MVu2LNCoAFTQrWA72FV7xHN2ivXcP4eKew38CBKiTsJwBLzAECtLp3707J6IANhi1+B+Ifnq9h+eP7Gc8880xD0dAySe+rqqpl49OWlpHU3NxsAVeJw7qgXYXzwlY984DFIKTvqg0TaFvmUF680QvzmbaDNkRuU7OKFuoptsLTAR39tsip385/53qOzMgwzg4XAxaoA2y8QlYzlNT/Xn8dDgVtwFJ2Bpg7IbgDsLjjDYAMt8uCfl46ljbjNKE9TlV74MqjKOAHVsXJzzgiJJT1v9BrsrCQXn7tteX+SvLH9zKOOeaYu9Iys2gokpmrqxXLaqRRI0fRyJEjadiwJhkWctdmeZQdj4OSVdjdbrLc5WECHiFbIKJCaCAirUYHpsBe7PBlZHRck2KQyTSZVUaGBmLptmvfud8ENA202god7Q7XWVKDcoWfGRme56ZrV9gVlMnOMiRMtis0SMOoYFcAJoAVwsIUpXFB0wJoyY46Zg6hqWkZu4UMWNhpBKNCxxwAIsrL9BWghf6E8jtCP0bBut977y/T/ZXkj04fL774YmFJefl3YFdohIoGEmjRBZbV0NggWdaIlhYZGnLnZgYtTJn7FiVtJaeDFRE6mvvXHmjpu39t6Ux2I1IDqBiQbObjMb0el9EGs+PzsTMAVEFDU1TXGRvux0zXdgV1oygDlmROyuFuA5YAKSnMC4DDbbhf1rQaEhu1gJ95mw1YyZbPC6yqT9++8vkBWgAvAFYONhAEYJVXVv7z0z17Kv3V5I9OHxs2bPhleiBbbk9XC7ACu2LAAsvCZW7XhZmfl29N1Q0Hyb02oEQBJL0iQbSmqDl6M4kO5grulZDu0pvSPa/bAJTuDvns6+kmSKV7aGDpEUedffH7A/CAbQFccLStEbZelW6fNxfqS0lJtdlTsgITMCjeHWQPFh6fnJwitSe2JyB09Nwh9NgxtNuCqctgWAgLB4nnQzgIPQutvuC3SxHvbdKkSV8QUZy/mvzRqeOdd96Jr21s/CQ3FKYyNJAAuwKzEhMlj8GuAFboDIPcuDzVWILBKl+l5nDaiS46R1QINRzdZrUDvaBftDQcU8cyvVXRrApuwIrGrBzAYoajT4RxNjMyGFeGJ4i5dS8GLU5Bglsdtzv2B+e9uHQxMCytlAzXXjfLIjNgAWC4IzWuJ5iCewfKJTNgydrw4siAhXCQE6CRqgP9KkG8zpFHHvGZACy/a44/OndcdNFF6zOyc6hYsCuI62gtD6AaNmyYBCv4sLCLJ8EqL6x2Bx3Awm0wkRYUFgjQynFrWW01PY1SMdRV80pLRHaXKg5Eeq3aCP34sg4iGQZA6QwrIwpgcYhmA6JnmOiAlglsDEAcOuN9ILRj60OGpltxN2fWrawyyMmujjYMMDrDsgT3JJvBDYSlQZWeYfCJjzK9SijjtaGP6YCFiZZfAMP8gnwakpBIp6xb95IArK7+ivJHp423336766ixY1/PEf8lK1TzUwAWwAqhIER27uoCoJKMSoWCPLloX7FKz+Gw0FUixqtgnVG0zmyS2tFcwo56qVjYdsRyDVj0HT1N5E73CAs5JMyIOk32FinMZxpmUjAmnVXpehWAKlUlOidpO4MJigFxSg7rVwARgGBAer6SJWAlKqBiV3x8B+q7s4aVnp5m7T6qcBDP319ZGvC6YFgo3nfpJZdc568of3TquPnmmyfnCtCR7Kq6WmpVACtbs1L1xq2yx/ku7UpOxbYKpIHUMpHqAjw3NjXrRZkVRV0ttqIU+esoYLlAyqVFpXsAlsOqGCjSNa1KByn9tmhTT5uJ9hidrcnsAPHaAB4GMgarVDXTVBhq5Q06ulW8KoHMtgXd0oDH4jMA4IB5yZ0+ZXOw2niZDVOTbQBkphWnmBsey8yNQRE5hRDccR7Z8NGFw/TEE0+c768of3TqmDlz5o40sejLBbtCe65GxawAVgAqFtn1MNACKjfL0kELu4b6bqEX8LSVA6iHfh3ZIYyaJqN5rbxEcJMJ6RpVKmtWKhz7Vyb+1g7nPJ6HSxxzOIjFr9e4YqDS03BsdqWSnYcY6ThgQdgJ5M9CJkOrHoUJWnkZr4ap0QykkqUNGGiDFu8Qom09Gm3AMJwrfh/vvPPOEn9F+aPTxqOPPpoZzM//Kq+gUNa2gtDe1NQkGwrkKM2K+wo67CrPDV4acBXY4GWFjjkqrHOVmvGo1+65q2iI7t6g5zAxrxQax6Vu7P4Z2pWuL0UwpgyDYUVjbFF2E00flev+DKufoGRW4nwlGCVZjUslUCkNixlXYqLjaucGqQxWzHyQlCxZj/gM8ZoQ4nmXj9vXsxHUPHpVarDE/WQJUMywpOjet5/s/IzPPlWcf6P43ezZsyfsryp/dNpYu2bNOvQZLJVVRMvtMJA1q3zWrBQQFarmEgU2m1JHD6aFI7a7zXbvdreZnMjGpgEDtNoq1tcRY6gbtDIMx7rTIivDDPu0Bg+SJaW2MzvwGJ0p6bfp4IbbAEacK8iPc7naudCeZFex9u5gbOxgmS8IMIF+BcDH87PjncEoGljxZVRlYMYFgOMGrdCsOOTEa6AWFnYKUTo7Xty/bNlRu4loiL+q/NEpQ/y4ulTV1LyUJH6QqBaJSgwQ2BH+BRWz4hpXaK2FI8of47ZC1SDVnvlulsVhIffaY6uDq6BfQCvW55Us3U6ZmbYqMLiOHmFgppbq4hWmRYjeqc7RAhFHDNeNnKnG452/8bg/VelTdqiYKs+H28VbYaEFVgmqvIsOWEMAWLGxNsMarGpgDRaggvPHZ4bHA7Dkc2h+KglKSYmeYWGi9lrs5QLQsTY2CIAlWFzvXr3ka0JwR2WPU9evfwDNdv2V5Y9OGbfdfHNTHHamsgIy0x6A5aVZ5SibAtgQrgOwJGgpsCqUu4Z5RmhYYO8ehjV/lquKZkQz1KyIcspt7RJ2JFcwIh8vwsaQEQFW+pEBQ2pJOmPS2A+SmOVk1pTqcd3428jnSrHLzjBA6OI65/cxiNihYMxgW2wHmCBMg9bEOZ1gSPg7vAZrUi5giqJlydsVaOGzsJiVEtyxQ9ivP/Xq2UuK+GDCSJa/6aabLvFXlT86bRx+6KEXx4gfdCgvLKsvcAUGrs9udizGAgCQsX3BYVmWZiVnnsayNBE+FA5Z4nvAW69iS0N2OyFhwENo96zAYBhIvaowZGohYbTdP5sJpTqgFQlAboBy3ZZiAp0BaCmp0omerHbpAFLYXeV2XfpOHYONXvIlXqsyivLFMYLxyOqvxUXyM5L2Bvm8ya52XeY0Q0SeCHXxOSAcBGCx6G6VlelJ6TLRO0smPe/atWuMv6r80Snj3Xff7VZUVvYOxNJ8EeYBrABEsjZ7oQUyMpxTOhMYEgOFBC3Udy8utuq4S50rX15m1mUBWL5L/8oN5roF+GwvAb590T2gVzLI9K4c6lWBwdNbZVxmvchhUCluzSlVBxpLjE4RgBMxVZ6fPNq3JUc+JtlxrfOR2R2AiHU/mHYBRFbXHRVSS3tIlivvEIAE4+/cuXPlP5S+ffrI26OBlJeWxffzjiXOG8wN/qsY3iFUvQnxmaMbeENj4xefffZZtr+y/NEp4/prrx2DlkxBBT5IaGZPFetPeq88bk+FBYLr3H9QByl9cqt63jHEEc+JxdYRM6hn+NjBkJDNl7q4nmlYF6LNFA/tyWFVKS7AAtgw0Djgk2wkJ3dgaq/J2hUmAIN9bAy8jts9Sf2t2yrB543NE2ZYeC4vO0OCZmVIjHIZr4kjRPZBSsPSBXeAJhzuK5Yvfw611PyV5Y9OGYcccshV6NIbFkACdoX/xjKhWYVyLJRzU08AFjcCzRQMBrfhb2R4CKZliPA6YCFth0ErGAo6aTumyG7bHjrGsFx6lOHDSred6OkRupVp6tQv26xKc5d7MS3JxJLbA6JUDfxSOwRerFmxboZz0sv44DuySvuE7Q7SXEefWR3Cx36qow0DnA5ESZrYbrMp/TbtdnzO8HBBZNctDdCv8Do4h1gRql580UV+L0J/dM54/fXX+5SUlb+TIn7MhQpwbJARoIKqCxyWMVBZHYyDyoZghXFSK7EBy82wdAE+L88dHmLRMVszm6eaDVbbaq3llSvoJDhHN4d6eab4uh0SGszKZlURIJNsJCNHn/rjvB6vlzvm6/KfhtLy2BLBBlA7f1AJ72BTA9UOniyyJ6ZuNnVeywr9UrTkaXPy7iBed6AKAyHmxwyKkXoWAEvqbdAfxe/g+eefn+yvLH90Tjh4zfXDE8QPGf4Z2Bkg0NqAIsBFdiVWYKWzK31yek2+Ymjcnt4GLAlUbgFe+rjU/c6uoVb73KiDbnbQiVpmJpAVpSKDdzUGMz3GLbKr0C/N0azStN08F8Awa1JdaxI1sOE0GBsQ1G32fZ6aUrL6G6f0cUSTU817xcnOSEqGlWFQjONCHyDABY/De9Jf09Hdku0SyzbTUnYGaSAVr5WpANxiVzG20x1giCl1NnFejcOGfbJnz55Ef2X5o1PG0cuXXwyzKPcbLFRie76qz86A5AVUPGWuoAALABrbHPRcQtMNbwOZFjLmGqZSN3AFIjo/t9WO3jN/MEoBvmgpNHpKjB4GpmnmTVNz0gHLBJ9kW2tyACga8zJZV4oqzJdgJCnrzMplHNWc7pINwXuVarnlE43XNc8jQW/lpcJRvDa+C7yWnpKDCcEdr4nvvr+4vvTwwx71V5U/OmWgMkNlTc0fkmEWLSiw9SsueSx38xQguQCKBXilaSFs5DZVOLIDvlBjWK4kaSXmW1YHi2khxMzStKyIXoWBwF5Va3CVF3YlORulX4yE5gytrDFrPpaOpVkUOghYkYAUeVtKBGB5gZh15NbxXNY4UavOwHmEAC1YGqxaWE7yMz4XC7AS2wAsR7/iMsgc6mKHWC9Xw5ex8yj1LfH9xMYn0NatW/2mE/7onHHPPffkZQJosgJSeyqvKJdABV0JYAIQ8dKvbNDSRHhda2I9q6jQHRoW6uBlpO2w1yvTKKusWx308jNtdc6J8GBpKTgdyRfkXL4UTcNi64LOgHQxPckI4UxQiBCzbQaWGNVmkODRsYYTkeM0oMLUDaS6DwuMCP0J8X5StUarkYCVFBEq8rkiJMY/DNvdPsipBIGifRniucEgc0Kh75577rkyf2X5o1PGSSecsHpIYhLlCcBAsjMYFrMrC7ByowKWCV42E1NggufR9axCl56VZyRI5ymgDEoR3ylm57Yx6LuIbfUojDCPZqRHNHDIbMfWwEZRFtwlMDFQpRoie6oFWInGgtcXvh5qmZe9bASJiU4dda+mpgxQ8QqsHE1LaVkCtKzUmUG2YJ6kQj4JTCkO43OEfTeQpaqWYPhe8RzSzqCFg1Y+4WALzASAjR079hUiOshfWf7olDFh0qTbEsWPEmI7wMViOUHN3e4wLFNw18NEnW1xgrMU4cVz4HlNAT7fpWfl274sfk3H1uAAVbZWoVRvUJHVgfIyeiiY6VHzyjSP2h1pbFuD47VKieKtSvIAKw73khK9bQLtzQSjrroNdKxfaWClAxaEd1yWuYSDB6sqptY5JrjsC84GgNcmAN4rjvhuZElkDbC4pAz+Bs/fT9x3+qmnn+uvKn90yoCdIb+oaHeaWNSFggUhdxDgwUnNun6lAxYDFdiXbnHg60FV4I9BC89lNV0tsis7cMNVBi8rxzBsHznks7WsHKP5KthXG4BlWhu8+wm6NSyvYnq2BytVB6yUCEuCXj3BBq0kr6J4Sa7qoGbOHt/H/ihXc1OjzIuuXbnAaoilX7HGhOfkUjVOZx2PabwH3pXEZ4nvlcNBvTAgBHer1Zj4rMRnuX3HjgZ/ZfmjU8YNN9xQm5qR+c8cATTojIMJA6Jj9gzb4GSDlcainMTooLoctC/bbb6yrCRpbm3P4SHrWNJFLwEr7AIx5Bq6m6a6yyu7XO9taFhOOZn0qGGhCVamp0pPbNYBymVN8NCsEqMAUoJKXo5vb2o1rlxalQZQcVJkHyKPtqVBHdmHxWwxqplVs2PwjiBbL3AO+C7w3tHdmZ9bGkb7D7DLzQwS1+vq61//4IMPevgryx+dMlYfe+wJg8UPP6zCNoAJuuBweGabNnM9Qr8cB7CYUemT3dgAFiwYi7UVugFLTTCqsGJZebYb3rJTmC3es4xmqybD4uYU0XxY7dka7N2/1BSXmz1NE6ttRpWS7B0Gpjh1qkxtSrciJBgdabyal8oWXAAmCWIMVhpgxQ2xgYxLygBMWL/CkU2mUXc1I9z1Sa7CgfgnAmAC+DlWiUEyHMTzQ3Dv1acPrVq1yne3+6MT9asJE25BGgXrV+w6t3oKWgxHty6YQrsOUEhk1o/ZqjoppgQN8VwAIU7bMQErzwassKtChN4F2kyOZsAKROlLmJGR4dl7sL1dQhdIGVUXUlLaCAE9Hermzpu1K+jVYTnBY7pAyhTYbf+VBVqxyoPFDAiCOKcpyXBQS+ROaSPHUU92RloUfgu806iHg9Cz8H0A2BLEe7vlllvq/FXlj04Zf/zjHw8K5uW9i1SK4pJiWVVUbx7BznMcQ0qfcgGUAVjBkPt21risEFFVFBDPl6fYXLFWPysvP09jWFaOnN5FmgHI7LZjM6yA0cY+4ACW29aQ4ZQ39gAsDhl1dpViV1RIVos82e21inCsW9qTWe1Ab0oa0QfQtdun2JR+Od4dEg5RYSDXbrdqtMfaplG9LlZAVRltC6BcjEuzPOC18B3w7qBuSIWzPTZ2iPweevXuTVU1Na8+/fTT3fyV5Y9OGffee29+Snr6V0jHKVL6EsCB9Su5UydAJhhya1M6o5JsKhTJrszL+A/N5WjYVKqHhuE8B7DCMqE36E7qRUKv3SbM2THMMppXROQVagzL1K68bA26huVKaLarMCRbHZZ1hqXpV57NHFQlz7b6/UVoVBwGxmlHFQaaYIUUHBbYGUw4IRnnD0CRFR9SOlY5Qtff8Df4rnAOspEFVzGFnaFff/lY/HPoKQDr6KOP/pm/qvzRaeOUdevmoDoDUmOK4W5XIMXpMlyJwQQhc5rhoBeQ8eQmCEGtkQWHfjrDssDKAiwGLd0q4dV5x+z+rIeEej2saMnPXEs9VSvJ4l1RITUifNJTWswKCPpum+ml8gQsdrEb4R/vEDJYMdOKGeyAlZyq0zNAizcnJAjroGtbM5KdzQUNmBmwLH3S2R2EERUgCbE9JmYQpYvPEdeT09K+vuGWW/L9VeWPThsLFsw7DfoVOvSiizP7nwoLCiPzB3XQynXAyAQk1+18WYWIzLK4YB+zORbYbbFdAym+HM6zWJe5U9hW9xwZCkaEhN7G0QyjMaql86R6AlayZhw101rMIngJ2s6gl2ZlAZEREmrAZbEq3WflhIS6hcGeKmSTTVLF6+LzxnvA++LzTk3xrj3vVThwaGmpfB4ZDvLuIwBLXEf9+DTxWR108MHoBP64v6L80amjafjwXyN/EGwK+pWdjgPGoypZRuhUbYjsDGz2UWdgubl2WGl3gQ5YTngdsJhhMUiFVb0n1scQ3nCYl9UBD5ZX1xxHkE9v09bglI/RdwVTIhOa9Rw8rV1Wopd1wXSoewjqpkbFlgXXdftxsTaImAnPzEbZe2WHuO11+1HvEewaGyRWEwvnNTCxQ4jkcITCB3fvTqeccsqh/oryR6cK7jnh8KsBVSmUHe5WaeNCuxicW0A3WFSuw6RcmpaHhmVfVoAF4AFgALSK1I6hfP18S8Pix8H/ww0wcJsFWNFBKqDpWZ7dchjA2kjN4U7KbTGsJM/E5qSIFBuzAWmcKycwzs2q+DY8TiUwe4GXVcvd3X/QVZlhwED5evgcUVjR2e1MceVEugoQ6mGuqq+Fctdc+4pL1+ivh8+vmwCr3FDok127dsX4q8ofnTZ27twZTgsEvswRoAPBHfWv8lT1BFzXAUsHLfsyh3wGC4t4jMeuomRZKvUGP3rczqk5vFsY1kNBBVqWVSK7/UaqbTCsyGaqTtqOXnKYGUmaB2AlRckVdFscNIYVxbYQbxhBdQ3L1Kl04DJ3C9nGwEACKwOnLtn5kC4zbEpUPxa/B3w3+CeG87SeV2dXA1Wtr0QSPyU69JBDtvoryh+dOs7beF5zUlq6rN8O/Qo7dux/4tAwV8sT9AwBg272ZV6PEOq12yGo866hbiqFnibDxFCY8kLW0bosQCs3KM+J9Sa5W5gdiF4uuY1W9RkeJZIj8gfVIk7TnOBeOXdtAVZEGo2R8xexI6hY1hCPENC57tgYYnUbw2ALTKRYLj5fBqsMowS0ZJLpaRG5k9b9aXbeIOZAZWWIVa8LK4NkV+IzxWtBAxX//Kr9FeWPTh1HLV26KFYsIuwOwoPlFOzLt1NtbIe7uRuohYA5ml7Fj9WZli7Y689jMya7bb2lZ3GeYX5QgFQwZAFWWISK4nowO5eyswJ2Ywi2N7j8V1EYVgTT6kC1BtnOK9mdkhMpsLsv64nNrhbvXtYF0wwaBbBM71WM2gmMUVVFB2s9CAEiXNOd23GlG+DkNbkUNPcchKYJ0MVz6kAJwEIFCTz/gV0OojFjRj1FRAf4K8ofnTpmzpq5NUkV7CsvL5cgwfWrJMNS4RfnC0bTqCJYlkf46HVdv11PckYIKBOjBVjJKQCrQAJWSIJVZnqGXIgAB4CQV+ecLG2X0IthOY1UnW7PXo1TU1JTXGK1Vyljs7ie7r9KMI2i8YanKs7ttYp0r3uHgVKvUpoVm0O5zAvOCd+fmcDd3mSdC3/PjXGZXZnlapDBgPeEyqJbtmyZ568mf3T+DmFz872ZAiCQksM7hNymi+0NzLB0B3uExSEYyaBMoIrqjA86u4YswoMlhRVIYYJdFQjWF84NUSAjk9JTLSDBwrJE+0AEYOnJz1HbfkVJfnaXlEl1AxbrV4azXa8gqhfi0/MFExXDijfc7LqL3XG1t61hWQJ4rF1CBkcu98IZAGZd+nSjkqrZfxHvE0d8fpWVFfK9xKjnxjmx2A4gxveG10pITvnuySefzPVXkz86dQgKv39eYeFvMtW2NSZ7ojjh2BXeeZlCo4CV2yyaGxWobAHervYQsoVw3FaYX0BFaG0PdiUADPoVMywAFkAAi9JV4M8r+dnIJXTc7uneJZIN0d3WsrSSMmZTCL22VXyUQns2w4o3QCtKus2QqDYGi+lwiMZJzijxAmEdmxcyxBXnjd1Ur0Rv/bPQcycBvBDa8Q8MlRf4dTmcxeuy8bdnz54UzM//02uvvdbLX1H+6NTxyCOPxAaCwQ+gP5WWlso6WDZg5St2xdpVbtsA5eV2d2Yku/IMGUNOZQdk/WcIUJL6FRhWyNKygjm5FMjMonSELWJhYQFhkdmt7tup6c5O94gSyQrUXBVGo6TmeDnbwajMZhBebvZ4BVi2ITSK9yoacOm3cxqOBCu1KwgjZ1htljBjbKtCBWtWqdwJKD1NMsiamhp5HxgUbwJY5tQh8n0gXATLOqhLFyotK/MbTfij88ett94aSgGTET/wsvJyu+EEt5OPyopMB3u0lJ3cXE99y9SzdKMpV2vAZQAWwCksWBXE9lCO+JtAth0Swj2O3TEAh1kXqy1bg86qMtLTXc1WzTDJ7VGKbmvQmzXou4JmGk6CDlhGaBgtBDSZl86wdN8V2sPjPWCXVzfVciK3A9QZLsCWIJ3qdAXC949/YLIkjfKAsZ6G27h3ZMzgGGlnaBw2bJu/mvzR6ePSSy/NTxI/VnR4BmBxG/oi1anZtDKYupSXKbQtw6gXG5MmUC3stJOrkX6DsEMsspzsHFk9Qnq20O0Fu1hK0B6iACuQFV10jyzgp093IT9T74GIntpG6ZVoJWRcYKWxK84XdLEqm2nFewrs0cJC1pNwG5gVboP2yL42k2HaMzPDVb2CQ0F+z9XV1fJ2Zlf6eeD8i4uLJEAPFq8PwGoZNeoYfzX5o9PHSSedNCoBRdkEOLlLIluAJSuM6laF3LbDPy9Qipa2E82/5QZEqxQNQj15DnC3oyMLJxeLRYOFZBkcHWe7J2B5hIQcMnHfQbN5alpaqktIB3jpBkunakNKRFVRHag8dwnj49wiexTA8goH9V3CWFX6GEnIVnpTniu3klmnzjCzjE5C/J7xPqBjgl2Z1gs+B7ah4LF4nwCsCRMmjPdXkz86fSxcuHBZfFKyXcOd9aN8raWXmbgc4VpvA7yCIeNxuR75haGgS8PSnw+mUhgfWeBlNqB3JHYAK9A2YGkL1NWP0KzbrsCL7QBW84goDMsQ3fV0nPh2nO3xEeFgnNEEdUhUR7utYamQEKEgzqe0tMROUpfvXQcnQ2x3+dC0VmZ1tbXyCCDUq0eAzTGgMRtjwJo1a9YofzX95DfgYr794rPJX3z28eQvPtldRn//eyw25cTc70dzktOmTTk3RfzwYBotHTpUAgTXUuf/0hH5giYwGQDksi9oYZ9eyM/VtEIDKPZ6eZlT2SPF/QA5BHMDVqBdwDK75mQYO4N6iMjgGBWwVAiYkpIcUVE0Tqtxpde7cgFWfHTAMtmUGRZyTp8sHyPABWG83SCE+zRqqUsuD5rRPBZAj3PGrmBFZaUAwRj5mgxYLPAHFdPm9xirQsIFCxY0+0v+pzt2/2XXMa/89oH3n7j9KrruvHV00frl321ce/iHF5xy7Os7fnX5q2/96cUrBHAdJWbmD3qiU6ZM2ZqWkSl+qGXS5S71K8G28F+UfU1mQnNUsd0jbYdv45Zdsnpo2F1FVKb+eDjk+e84RYg9RXolBBxlDSbVWsorLOQO0SarMFNx9LQUbudl7gqaZZC92nRJ0FJdmc2KDGauYCRoxbdpY7BYVayd08dCO0CKtSuzQ7ZZmVVvOqt71PC51dfXy8sQ2pktcnUIXMZrYDd0oKqx1bt3b6kzTpw85Q9bt26d4i/9nxyrOmjPu29c9fIT99Ifd26jx68/n7advYrOXzWXjp/bQkeMq6D5wwto+aQauv6sVfTqo7d89bcP/3qn+LvJYh78vZ/w+AkTLk8XP9ihIhxkDxZXAOUfNQvg0cDJa1fQK/3Ga8qSMQZL0/9WPkbbNeQQTTdkArDAurjVV7RdQtMsamtaHk7wFNXOy+zo7JQ6dioyeDY+1X1YHmWP9XLHDmDFtym6y7BMqyIqwapfP3ku+L7s/o1aD8cso9mszkL5s8BnhHOGjQEhJawRcdr54P1iYwPfAQvxBx14IO23774S2CdMnCg3bXr17UsLFy7cvmvXO3k+FPw0xjuvPrvlmftvo+fuu5EevuYcuvWc1XTzWcvF8Vi647zj6L6Lj6c7zj6K/m9FK50zr4J+eVg9/eq0w+ixX22iD1797e+Jvl0ggKvL93bCo0aPvi5TLHSEgwgLARBc+ZMXdEiCVbBtZ3uu986gU//KqRiqVw7l7s6egJXrgBZrazpgyf/+SlvB7TkqhNV3CyPKy6iuOxZgOWyLK4zq7vaIFvQR1RmSIlp2uXr4efQMdAntcW7RPRpguTSr2FhX+g1CwULVRdtuEMIhod4lW9W2Z8BygNsKsyEFgF3FJ8SrkjXxDsMS54DPhethscWjd69eNGrUKBo3bpzcoQTb2me//ai8suqLbdu2nSF+yH6Lrx/x+OyN5098+fHt9OIjt9LD119IV56+nM48bDydNKeJVk6toRXjS2nTkmZ6eOMS2n3vWfTpzp/Rri1L6dc/n0bnTAvRmtFhuvpnR9Mbf35FABe1fi8nPXzEiHuyBTCUKYbFRfugh8gqCJJhhdq1LXgBjS6yM+hw1VCdZXmVojHNpQxw3PFFDwexwGRHHw2wdAbh2TXHqMjgCO5pLsBywkGn8qaza5jkAiqz56CrfZfGsLxCwWg7cl6TG6JyKFgm/tlwHqa+u6qHgLq2JROiBQPj9Bt8Bo0NDfL7GayEdm5fLz9jcRuYF94/cgpxX64ARvxOpk2bJj9PdOpBGAmX/T777kPQRVetWvXUe+/tLvOh4cc3Pv3LGwvf+u12euXRW+nZe66hB68+lx6+7nx68Kpz6JZzT6Cr1h9CFyybQKdOK6VzZxfSTUfX09NnTqS3rj6K3r/1ZHr2vEV0xsR8mpg3iBa3lNAdV5xHX+1+c5sArpxOPfG6xsbHc8QPvLyiQoaCAJAi1eYLCxj/jfXmqNEYlL7LZ3q2pBk0rDOskKtNWHtJ0RxScl9DuaAUIPACy8nJtuwPutDuSj9xGyfd1gWnUB+AKF2Cl+ZsjwCsZK1Fl7uNfJxHN2bb0hDn7W7naqHtudq5lAwYVp8+feyyxbINm9qwcH+ubuDn/pH2UXxmeI+wMDQ3N9seK8mwFLvC67EHDmCE94HQGN8FAJCFd72OPcC0R48e1EOc49jW1q+efPLJeT5E/HjGJx/9tfTl39z/5R/v+xU9dN2FdMUZx9BZK+bSifPH0snzx9HPDptCN62bSc9es47+cu9meu3OTfTAxkNp27Ej6f8WD6VNs4bSVSvG0I6zDqXrTphJi6tTaHhKb1o3q5ZefXzn3wRoHdFpJ19ZW/t8rgKsIpWWY4nuRXJBYuFz1U+3fuVdfTSa293UrqL1M4yYIacyaVgxLLdfydpq5zIqEYDlmTuY4WJUZtNUAFa6VqHBy8aQkhKpYUWk49g7g4mRPQVdSc5xnh4rr5CQW2rhCNaDSqB29dZwyO7MrU+7S3eOFi5qn09LS4sUzhl0mV05JtFi+7XxGeFv8BwAygP231+GiRw2YqIxB67LEHGffahA/PO7YsuWU3yo+OHH3//+99hXnv3Na39++hF65aFb6LGbLqX7rtxEN246gS47/lD6xZHTaPWUOlo7JpcuOKSO7t94GL157wX02dPX0/s7zqPb1rbSsS1ZNL9kEB09PJMuOrKFnrp2A129bh4trkmlGdWZdP156+irTz66VQBX///4GxhaUfFqrgADZlgAJ9kjEO2cxELCj5pbbZmg5ZVW45lbGNJ2CVVYGAzmepZdjsgvNABL7mCpMHAIyp0IUOBKpQGj1ZdetE93tXs1S3VKyCiGlZoWUSo4RdO1zCJ9rnQczwao8RFWBr0qQ3tGUWZXAAcwHXweCOOdzzXsgJICLVeHbhUS2g07xOcDVgndCq52LvbH54v3BvbKzwuQlJ+V+GzYXgJmhsci+RnghJAQz2ndn2o1rBDP26XrwRSXlExLly49yYeMH3b86fmn7n76gTvokVuuoJvOO5nOW3MIbTp6Dl147Fy6ZsNSuvv84+nF2zfTM1evo7vPPpx+ddxEumJZM113zEjatnY8PbjxELrlrCPplFlVNLWgH00M9qUTJofoxR0b6av3n6DLTplLM6tS6KKjptHHzz7+En366X+2oGNpRcUb+QKcKisrpXk0T6XlgGVhkeGHqRs620xgDnoL5y5jaBvNV72uMxvjzQCADrOXWFXiBJoVe8baqiqanp4etaqoXaQvJdLNnigBSU+/cRqjJni2k1dF+jwqiXrWwYpv29kO+4IU2mEj6NVLvtfy8jK7QYhkwOoz07sM5WohoPw8NTsDi+ijRo2WrIk9V6zP4T3gdvwO8NqyhI/6/Fj/4lJAAFDUjoemBvEdf6vrgHjubt27UXeI9GPGnObDxg8zPvrTH07981M76aVH76THbttKt154Cl0kWNVpi1vp+KnVtHpcIZ00sYiuWTuVXtm2gb545R76/IU76fdXnUi/PKKJVjYm0poRaXTmouF06zlH090XrqSTZwylacWDaE55HF13+nSib/9Erz99hWBnFbR9/Wz64tkdX369+51J/7E3UVZZ9dcC8aOsrKqyK40CsPBjTkiwfrR2Wy4PVuTlejcd66aeFU3/8qqfpQMWtC9YDbg2OqoG4Pz0rfqOlEHmVBSzKkPEzmCqA1h2vqBmXTCriOp5g/FGzauIRGcjl7CtZOdY1aoLLAbPjxpVJWqDxCljHXQ1nA0bnY6YYXFrNXxWY8eOlaA3YEB/Vy0v3kgAWOGz6ScAiT8rveghnhO7k9CsoKmBhWFy30KrTplVCWJQzCDJxBAiVtfXX/3ggw/6lUm/x/HuK8+Mf0mEgM/dfRXdf/Um2nbBOrpx41q69dwTafv5J9C9F6yme85eSlccM4m2HDmcrjq8im5fO44ePe9w+v2VJ9AzV5xEly8fQ0vrkmhKuC8tqUuma9bNpk/feoxeePCXdPTEPBqbP5g2Lqumv791I9FH2+iZ62bS24+eSp/8+Y7vvvvsxcP+MxpWTc17YFhwN0vAUgzLAqwEm2G5dgo90mtM0d1mZKEo5Wdyo6TpeLjjJXPIs5z3LLSzhoUFweyqrVZeXnXbdcOo7b+KYmXgxaw3RE00a15p4WCi6WY3Zpzhx/IyiMYpN7ulW/WVwIWKsIVF1j8WfF+FhapbtrbzGlagpRdF5L6SuI7Nibq6OmpoaJDWCBbYuYeitQuYK8EM54HXwueQoVgTJ1Xj88Lr4z4wv0EKqGReo2BayG3kQn94LN4LXqtGvO70WbNuQbcmH0q+B7B6/fXAq7+++4OX77uGHr3mbNqy4UjacOgkWj2tmVZNqad1sxro3CXD6eFzj6S/PXkDffXqDnrr7o308Fnz6YK5JXTiqAzaOLeCrj95Dt36s0Np07w6mlcwiMZn9aH1s0voz8/fTl9//Re6cfMqOn5kJt25vpbo08uJvtxMH76+lv75xW1EXz9I33z3t5X/9pupqa9/C4BVVV1lA5a0NYj/rvjhgtGElAZlpthE9CD0Sno2PFbRStVEE9x5d1K2qRILBQuaa4vj/LK0cDCiIkOmu86VXq0gogqnYlh641Dd2a6zD6vdfGRjiUjdykNkj4sELCskjHVAKz7O7n7DInvfPn0lcIBd4bvhprNgOFwKiLUsx9/mNufK8jsCrAAyra3j5OeA1zS1OJw7gBHvC+k4o0ePlik70qum1dfCY5m9IRxEWDhATDCsAfLyAHm79IuJ94G/bR4+nKZMm0YN4rj+5JOvJqJ9fUiRjvODt23btuyIZcvunz1n3v2LFi++d+NZG4996oknKv+dXD7U2H/16Yee+OCPD9P7T91Bbz7yK3rtoevo2Tsvp3svPo1uOG05bVk9i86cU0Wbl9TSTWtb6bebj6R37j6HPnpoMz120VF01pyhNLd0IC2pTabTZ5bT7648mXY/dxdtPXEmzSkdQoeUJdJDlx5L9N0H9P5jV9BDZ4yk3U8eSfTFJfTxnl/Qe389i+iNq+jTN56mbz5+b82/9UE1jRjxOkLCmtpaqySyEnGxCFiPYQ+VWUU0okxMBwv7mVvueq14s6O01dbLYgmwGHBOmxMOejvZnV3ByE4xGaoqQYrWCcdVp90oe2y2mI+wLrRV4yrOXY0hzrjNaTQR68oR5JbzWPRY8Pj8qwQLlju4NmBZ3bGl0VcetQa0+XmaQTfP/rzxWY0aOVLqlXhNfB54X8wwcVuhyknE6/bs0UN+nq0ifCwpKXZ95uzTw2uwL2ygAqxBA62eiGBZuB3OeDTIgMl09pw5VFJWRg1NTfTWW2/V+GBF+1944YU314jPo6qunppbRlLTiBYaLv5RjJ04iQ5duvSFyy655JTf//73+Xv73Lv/uGPjV2/9lv7+5m/ow5fupw9euJf2vPogff3Gr+ndR66jm9YtoKvXTKcXbjmX3nxoCz1++fG07aRptPWwRrpwcR1dddIMuuP8ZbT5mPG0pCaZJqX1pKVlQ+i2zcvpi68/oDcf305njyumUyrT6XcXzCb6x1P09Z+vpvduPoS+/eBi+vzTG+njj/6P3tw6nx5a2UzPX7aSvtvz538dtEaNHftKXkGhTMso9AAs/IABKGG1s6eHaV4WBjPH0NO9nuuthZk1sjiUwWujIqpdTkUsaICIFICjFejL1ER2TprmJqJpVpG6VJV+o9dsd5KZUzwbopo6VYJRRTSitXxEm3l3KRlXownNugB2NUAABovsDfX1VkVYbIio0j+sXzFg5eXlW52ytQwCDgt5d7CpuYmGNTZSjAAY6/0m2jW/8D4A7mXlZfIcoDnhPCdMmEgVFRXS1Q67i9SysqzPGc9hhYzJ8ly54qkELmZZgiFCfxNUSmqF48aPl/0uUYftjjvuuOCHBoynn376wOeee27Q448/HvfYAw9k3Xvvvc3b77hjzl23377izttvX3v77bedctcdd2zYcdddG+7bsWPDAzt3rv31o48e/tRTT0168cUXy3bv3p0gQCdOzJ7/yuvffPPNS5vFZ1srQuVJk6fQ1OnTadr0GTRz9mwaP2kyjRk3nppHjqbWSZO/Pu644x685ZbbZr399ttd23vePe/uqv5292vf3nf9Zlo+q4WmNxfQ9KYwLWkdSmeumEbXn72S1s6spjlVSTSrJJY2zKilR7b+nN5/4la677RFtLopjQ6vTaDjx4fpquOm0tNXnUZ3nnkUHd+cRZPT+tHphzbQm28+Ql9+9WfavnEuPbqkgj569QSir7bRP1+6lL5+ToSFH90nwsHttPuFo+j6o/JobW0aXbZmNn368UfH/Utf1oQJE54OiR+6BCwluuuAhR0qNo/a2lRbYV3IO/nZ/DtPHUybjoBssTtYCgZrLa3sksgGu3JdN/IFXY0ltG7Oun6VqKXepKS4DaJy98wwgybompXZcl43ieo7hDaAxXswrFi5GwhA6dmjpzxf7ODCyMtJ6TpYyf6Refn2dW44q4eE3KYNYd14ARb4LC3NyipMaDWItdgkvF24r8tBB1Ev7OoJNgZWxEwbni08t/RjKUGdPXC9e/eRgDVQ07Ls8FDc3qN7DwlaYckA86lbr950/HHHXf59AdP999+fdNVVV+Vt2LChedmyI086/PDDtyw65JBfTZkx48mWMWP2CGbzVVNLyzeNI0ZQo2A4AJERY8YIpiPmqNHyepP4PIaJ+8XjxLGFRonQesGiRV8fc8zKr1asWPHqySeeeMuWX/5yy44dO4773e9+V/7uu+8G2grpPvzww55HLF/+RrFgnJOmTKFZgn1OnTZd/GNpppaRo2iiAKxpM2bQnLlzaOLkSdQyZjSNbB1LCxct/MMll1yyqK22al988dk1l/ziRMpN6knxPfehQQfsQ9mD96GUXvtQbuw+VJzZnSpz+1JLQSyNCsdQQ+JB1BoYQCdMrKY7T11Id562mNZPyKfZef1oTv4AOn9BA334hwfpy49eoJtPX0QLgoNp9cRs+tPvLiH65nn66LHT6Z0drUQfXyyY1kO0+6Ur6bHN8+mly6bTX28bRy9fPo0uX1hBU/IG0znHLaavv/7yxL3+EqdMm/YQyiMjJMRiAIjI1k4CvOTujvjR4ceIhcH/rXXwaatYnxkuRnXHm80owiFb6AfjA4uyqmpaVQpYbMc2Pf7TZ7i636Tb2pXZHSbDqCJqloxJ0trO66ViTOuCWYFBrxTKgOS2LcRHpOTYepXqRON4sSyvFdgKzrNWfC8ALLArfD8lqlEIJgDM0bAU28pzwkQGMGapkydPlv+UWP8DYHE7L1zH8+H7AqgMEeeDoo4AK/wOJKsVj8F3UltXZ4Xo4jNjbZC75wCwmGUxaLEIb4eG4jsEY0Y0NHHChF92BjgJ5hY4bf364cesWHHs3HnzbheRxKMCdD4GALWMGSsBaKS4PHb8BJowaZIEC8lsZs6UgDFj1izJcHCcLm6TR74NjxEMaPLUaTRFPBbPU9/ULMFruAA1hHJ4fhynTJ/xzbJly55ad9KJt27duvWQ3z35ZK3erEOwusmtYLBVVSTOU57D/Pnz5fc7CDpmfAKViH8izcObxHlNseb0qTSmdYwAzGaatWDBzieffHKw12dwzZWXPZcZ140Cid0olNKDitJ6UUV2fzH7UlmgN5WEB1AorTuFk7pQYUIXqkjoRq3hRJpSmEqHVQyhn88YSrdvWETXr51Ja1pyaHqwPx01LI3uv1qwqO8+pF2PbKGL5xbR7asriN7ZQv/8cgd9/tiJ9M2bmwRg3SuY1T30wnXz6JpJ2XTeyHTaekgxPXTmLDp3YTWNCQ+iizaI8JBo2V59sfPnz705KH7Y+BGivAw7mPHjxY8ZPzaAQZ5yu+vli6PpVJ45h7lR2n6pVvU6cDFLQOdnXMdiQg4bmJU0JyorQ4TQrnmt5GXVvThdKxmTporzOWVj3M1PuYqmPuP0Ls1e1oUI57oBUNGK9Jn1rVQTiV4iFMN7rq6uoqqqSmkQRUfuEgOwwLpwLGRNSzEtBi09qwDAg8J8OA+8J2aWAGW8tvVPKU+CVZr4vDZu3EiLliyhIvEaB3fpYu/2yVSd3Bz5+8i0ne3J8jbc30ewLGZYONpzgGV4xXvr0b271CABWILBXfifCOlu/tWv8lYdc8zCefPmXdk8evTzApQ+B2i0CHY0buJEKfSDqQAQcHm6uDxj5iwJRLMECM0Uxzlz59KCBQvBmGiuuDwNADZtusyXnDJ1Kk2eMlUAxjT5WEwJarNmitunyIoVcwTg4DYA2vQZM+XrTRT3jUZIpwBstGC4AgBfF6Hd4xdfdNEFa4877rVqsfZaxfezYOFCCZ5nnnkmffzx3+jyyy6T1pPExDjq3mU/ShgSQ5VlJdQ6uoVmTJ1E0yaNpwbBxMZPnfH8m2++2df8XJbOn/5C3333oZLMAVSVO5iaipNoVFkKtZan0sihiVRfMpgaS+KpOjSAqgWQVWWJmdafGgKDaXZ1Ih0uQsJlDal09aoptPvVR+k3t51Py8fm0KFpsfTLk1rpb589TrTnHvrj5VPo/Z3LiL64lejznfTsM8fRUw/Mpi+eWknf7lxHr/xiIa2ZWEJzhsbQutZcuuvU2XTeknoBWjF0w2VnC8yiiR3+shcsWLAlW4BCfUOj/C9ubWfnuwBLtjvHTmFeOAKoTA2qvftNw6nO1nQvEQRkHLGwuDoBJte9kl2iM6J3cM7w6H5j61UpqZ7sincCE+zqC96VQ72sC5GsyjuxOd7oNciTWQi0HlwHq0KYjt06JDczOHlN6EFgTk6YmGfvFIKFDhvWKENB3jwAwADsWbPDsUR897379JGAtfzII+nLr/9Bm849lyaJhYpa/wAbgBUXNOQ8U7BffK4BJcLDjwVwwneGfzK2AD/QMpZCzzrwgAOkkRSANXP69Fn/Ckj99re/7X/6hg0TFi1adI5gNa82trR8NUKAwljxPieoMArAMUOxIwYZgJOcIvQCQGGC2Sw+5BA6QrxvXAYTHT58uMwCqK9voPq6evkPHRN2kPqGBteEPWTYsGE0QoSJowXbam1tRcloAXLTtNeeTbPFa4GhAUDHi9doxVFMAONCAVYASWxI4PFXXnEFfbR7N2G8/qdX6biVy6k4P0QHCQDq3XV/GtvSTHOmT5ZAWlLTQOefe+E68zM6csGUG4cctA8VJR1EYwVQTWvIodlN2TSrPoNm1KXR7GHiWJ9JY8oSaHhJHNXnx9JQERIWZ/YVbKwLjSzsT4tr4mhheQydOr+K3v3z4/T1l+/QHesX09qJAdp+6UQR/t1E9MEvafdvjib6SISG/7ifPv94G91+9jC6fk6Y7j+0hh7ZMIduvvgIWj2zToSDA+j4URm0/eSpdMascpo4NI0evvPmL8XbrOzQF79mzZqt4YJCqhNfjA1YKiRkwAKrYS+UXhsrarmZUNtlk80ifXqFAbuqQ55VSoY7G3PnFq7fZDm2vRuiZno0k0jVSsN4VQ61qoq2nW4Tb+hW8Vpt9jg9PDQZlWdlUXczVCxwsA+wLjCquvo6qqgot9hV6VCLWakdQh2sOFTk8JCrxQK8wJYBfFPEf3l8HjC9yl1RTbPDewTYJSQmQGihgPibN954Qy6Uv/3tb3TKhg00b9FiuRD1jQs2kXIqEGtaMjFbsCwZEgqgGqTV7sKOYf9+/alr164SrMT8p2ATWXsFUqefPkEs7GuHjWj5sHnkKBGOjRXsZpIEB7Cm6SJUAyDNxhSLH5cBBAiz5i9YYIPC3LnzxPX5ktUsOXQJHb50qWQ5+DyxwVBRUSnD8QbxjxzAVSfBS58NzmUFWpBVJLCJI/7Z4Ij7hjU1CTAbQSMFwwIYAqAAmLPnWGC5UDC6xYsPoTlzrPMEYLUKwJst7jv55JPpnu130tfffkfffvct3XD1VmptaaKWpnqaPQ3vezJVNAyj448//gHz8zpnw5o6Qbu+HS6AaHZTkJaMLqClYwvosFFhWjI8m5a2BOnwUSE6rDWf5rbk0djKNGosGkI1hbFUkN2T8gXQNWR1p6nF/WhK8QCa25xMD9x0hvxtvPXEVfSHX0yhvz90Mr33yFH01ftn0yu/W0GfvC3CwU9upi+fuYC2rZxAx9QH6Mi6FNp6WDndce6xtGZaHc0p6EfrR2fQHSdPp6NbSwR45tNf/vzKC+Jp2+9rufnCCzcViQWBDxphB0CITYlcrM3SHHIikpxtRhWKbnEIehX/i9C2HKsD+4gylUdIvn6M1RUmVS0SU1CXYrsmrOsdjGVPQVfDCE2vMjo16xUHItpy2d1uPJiUIbLHGYK7yxCqude5Iw3YB8AK2/7wWTU2NopQsEqaebFjZ4NSYZEKBYvlPxQAFJiXvC4ACzM/L98W3PEdThX/gdnCwGDDnw/ePxhYSITgYFb77L8/XXvNNfIH+ennf5fHd//6Lq1bv56OXb2aJopwhU2gklmp5+MdSAAsrkNcB5Ni86i9ayjeJwCrW9du1uvts8/vt593XrvF3y7YtGnorFmzLhFM6s2G5uESpKbLsGuGPDJzkmxJTVwW4aEEAIDCPH0q8JojQAPM5qhly2jkyJGUIL6vRAHc+DwZcIYNa5KVLMCgGjHFd6NPABomX5ePaxRT3S5ZGABPrK9q+Zx18n4wOPjbEEriOwLA4jwBoJhghghdx4pwEhrXfHHbhRdcSH9596/05bdEJ528jiZMniLAbyqV11bQmrVrNnl9dmNK0n43t7mElo6vpSPGV9PyCTW0bEwFLR9XRkdNKKblk4fSyqnltHxSCS0eIdhXTRJNrUqkxrIkKsnuS4WpXako4QAam9ePZg8dRLNLB9NGESL+7r6t9OELW+jTl8+n7/62TTCrB+jFF35Gt/58Bj168hh6c9N4enPzPLpxzUw6rD6XFhXF0rmHNtIdm5bScePCNLskhtZPK6dfrVtAi2rTaN2iMfTlV19d0i5g/eqGGw4rGlomAKte/mcJqXw0/PjxI2TAylCF/MxSM20yq1zv29yVSC0fFtfhwmIDG8BrshaChQxgsROa9VrsGqvyrBqqWRYiBHbDa8UhU4JHa654rRZ7REt5k1mZda88SsjEqBLDWMxY4LiMz78JXhwBVmBGMhwUDMtiWSUKrEpdrEoClrqMz69A064mCYDBYuECh7Bz8OYD3me6quV/8MEHSwA5/LDDJEh9/Omn9NGePfTJ55/L6x98tJsmT5ksdSecZ4oKCfUqFwgPYUgFEMO7hdAWu4w4co4h9DmEjAgJ8Xp9Bw48L9rvcseOHd1Xrlgxt2XMmMfrBGiMEiCFrX4sZIDTDIR8M2YooJqj5lwHtMTit4/q8jx1WQIZ2BZCQcFsFi9eLD+P/v37ye8Yn52VCVBPDQKEAFjNTRZoec5GNcVlfH8AOWtaIMePs9maAMJqZmCCqVl/I0LKESNkOAmdbB4AVZzzrNlzpB6G0G+cYJJgZitWHI0a+pbwP2USlVaWfXvxxReXm58h0p8On1D20pzhYTpcANSyyZW0amYtrZpaRcfPrqdjZ1XT2vkNdOLCJlo+RdwvQGtpax4dMjpIMwULG1OTTPXhflSa3IVKEw+ghoyeNK0yhaZWJ1NhzD5UldyD7r90AX37l61yV5D+8Wv64O7j6dKZubRqeAadM6OAHj5xDN21ppUW12XTHAGE5y1tpjvOPoKWC3CcV55Ap81toCtPmENTawK07YqLoWfVt7fVW1MoFkEtsvbFh8h1q7AQAF7cWRgLOyS7Mocd0DIqOJgpPM5tjlAfCrld7HydTY+W+zrWtcPE/iAvc6jpWNd7C/KWPYc/en6gbl3wCvu8ut3E65qVkcgcF9e+adTdPMJ6j72V1wn/0WEZAFhBbAd4IcEZYAXggiUBoIWjBWClil2V2ODFhRfxOSKpGayBE9jNyhTwoJXIVl7xMhSsEq+/528f0z+++Ybe//BD+vSzzyRYfSjA6qgjjqDuAoTgyxoSZ70HvLekRKdsDnfRwWvj+bt362bpVsgvFIAFDQyXwST33XdfCVjTpk1r/H/23gJMqqvZGiY4CUkgCe4Og7vrwAgwg467DzMM7u5OAkQIFtxDghMsSHAP7u7uEEJSf606e58+3dND8r7fvX/u+323n2c/7d1H9l5nVdWqqmTRvQULPmMA6dLI3f1MQ170bdiECgoJFR+QdozDpAsJCTUBKdxhiPnHw3geYT43By92fR8f346CGATTpE5tRqB1ZyDDJGzEgOWuWFaTlAGrsQ2w9LB9vokCLMPXhd9qwgDoarI24z2MBspPBgaGbASYj9hfOPQBzgAuLX/A41ZsOjZ0dT3gTDrRPrTphJAmxSmxTVVq37YSdfKrQr0Ca1Lv4BrUN6w2dQ2oToPjmtDQdu7UM6QW9YuoT33D61JX/2qU4FOZOvB9TMuK5Fk1D9UslpnK5k5LFQtkoqEJfjR9UCy1KJ+bourmpdvbBtOldV3p2rY+9HZdRzo3sz0Ni29JwTXz0wDXfLSulwfNH5pAEfULUXS9vPR1By9aOqIdxTcqQdENitOXHVrRuKS21N7fje5cv7TzXVKNVMeOHStXj1G+Vu06cqDAqnRNLNzrxYVJWrpMGbvKAM7kCdZaWc7Kxej3rblu2kEMn5QuoQKgxILOq8rHWDvdFLN0uinmsBBtbboKmw1QdZkYW2/BQnZ9Bs1IYJ7kreXzOmqmnACUo/8qj1N2ZZiBOrUIbOODDz6QBa8nKK689evXk3IvNQSsDHAywMsALbxugFc1k11pbZZmqFgUcPrqKKBVHFtconqFxFwsyo8BVtl5237Zvt3wWzG7ev7ylTw+evRXWVDwN2XIkN5s1Iptz8pMCdvUhk0a+BM/5H3B78NHk5CQIBcuMKycikUitQjPUdgPYJU9V87Le/bsMX0WS6ZM+djX17dnnQYNr7kz0PrATGJA0owK/h0MmHPmcAColEZkRKR8XrMszbTAUtoxq2RzUyKhYL36mGn/X9269UyTECBkbwo2Mk1C0zRUAGUDLgOchKUp0JPfUMBlMLcmprmpgQtMDBexhgKYbgJOAGlroAAg5unZjNrFxiUT33ZtFzIguHll6uhbmTr5V6UeITWoe2AV6h1UhQaE16A+IVWpuz8e16ahsY2oD4NY78Bqxn1QNfnMwKg6wsDa+9Ym7zrFqaZLTipf6ENqVcOFVkweRWu+7EExdXLTlHYN6eKSLrQgsTwtiCpDq3o2pg1jYmlMQmtqW70gJXmWpaV9WtOi/swca+emDp6laEbfEJrcLYAiGhSjTl6VaeHwdrwdDGRTxmHqeacIWJcvX87l6+t/p2p1w4+FXDPDPDNC5DqiBQAB7ddA46xInF39pdL2heQcC8tpsNLpI47MCj4PPNfOXWfAVNSh6ak2/aw+K7ucQAcz0Nr5RsDKWf9AZ12a8+RJuQOOY/qN5X0cQ+3PAVjh/xs0qM+Ttoko2TFBreagZlN4rkELz6tXr2YClsms4L9ilgpWAJNC2naJv6mwkncUNcFZm4zKj0TTpkwx/FbMqjRYrf/pJ0Ktf3wGZh2qRaD+GEw6LGw4obt27Ua9+/aT0D7mBrYrLj5efEKhvLjwH9hfXcsri4pCYgweOHCBNlvYdI2rXb/BOQAVdE1aZiALNNTmj7INGxg5Ahfek2EFLAamSGVihVl8W2BY0dExPKJl+7CfMr/AynnguOJ84NzAPATwAEjqy4WlvnKu8309i5NdOeKFKSmHPBiUBjt7s7GxhYElZ2SujRuboIjfBHAhSwD+rsjoKAGtkCA2Gf2Czm7YsKGYPp5xoS3GBDevTt3CGlCP4FrUM5hNwIh61IuBaGB4HRoZ24D6B1en3gAy30rUi4Grh08l6htYnYZGNqBRDGBfdWlMY9s1oNHtGtOopGbUP6YZhTWtTPXL5KDy2VNR4zJ5qFPbBsyQilLbEh/SN2HVaf/4QBrgV5W61M9DU/1K0ppxSdQ9MYaa1SpL/dzy08pBATSzbxAF1shFnVtWoblD4ml0vDfFuZWlKZ1b06ZJXWhCzzh6cOv6mnfmmPLJ/KVi5Sp8lfcwgESZaDAvsKA1y4IUwOrDcgZMyapcpnAPZ6+Yl2yC4qqP5gq4chuAlUMWRyHFCByBKqWhW3NpRmF1stt1blaO9WSVQp3IFswSMk6c6HaP8zpPbNbvS7SV9+kznZ/H+wVW5enhIRMdExyTHmV+rKCFUau2AivFrrR5WFkVWoTsAecFzCwoKFguNEYl1oJ2AQgcE7AhfC9NmjQCHFG8aN/88Qe9/v0tPXvxnH7/k2jKt98yAzJ0UlnYlNOpQll4QWNfEHXs0bMntWMmBZOqY6dODACRovWKi4ujADZjsMDA/vQFL7vy2aHTDt7bunX7AAaOGg1cXbfDsQxNFMApWJl+RiQvBX+UZViBLMwR0GD6WUeEcQ9HO4AK0TmAFSKEAFyYq5IfyY9xrDBHcWGA1QGmFRgYIPsG3yCYJYIQ2Gcvb2/RS+Ee6Usw7ZHkLcwJrKuRjTm5CoC5Go55V1c7H5cGqyYW0MLQoIffwTkG4MEU9PX3o9CgMAryDeNtCrrZqVOn8ZHBrXdGtqlLXYJqUf+IujQ4yo3Ns6Y0Mr45g09zGh7dhAYE16bBbBIOCqvBgFWBurYpS11alaE+gVVpVFxjmtCpKU3t6c7g0oIWDw+hGX3aMIB509CwehTVsAh5VspNVQt9QKVzvEc+9UpRoocLBZfLSiO8S9LScYnUl83P2GrZaJhfNVo6vjcNjWezvkZ+6tmyAn0/Io5GxbhTSN1CbJo2pCVju9KQiOb0TbwHHZ3Zg2YMTKStq5fCeZojRcAaNHDgYqQGNOYrCCJKWhmNCQdmo217DFPtXiZlkHIGZI4F+iSlhBcWJgd+N4eSL8Ac1Hof7Ui3mn+OkUBr6WNt9iRztFsSm3Wqjc7hs0YEnTU8zeckRzCPM/DK69ynZZUtgGEg5QXHxtPTQwALk1NftTEZAVR16tQ1/Vh61FD+rOpKk2U64UWDVVHegxO6Jt/nVrmWYAnW8jlFixWV72b95BMBKziUofV5w2CF2x8MVr179aK0mTLRe++l4s9lpU9EtY7IXiZZuGAqYFYwpWJjYymWASo6JlrAC1om6L1QvRYZB9YyQBIhFKf7xwLKLVu3ft7Gx+etRMUio4RNBTNoGQzIZvYlM+0czMBI5Y8yWJQRYYvSA3IB3qYYtZ24x3YnJSWh+im1b9+ekjp0oM6dO8txMbILDEav55Ahls1Jnsz+uvfoQQwK8j2AXCLfC2jzb+qB5/gv/H8E7wfYEKKAMNEBagAzAJVmYo1EHtHIADbXxqZJaQUuV4t/DCYkPofvIi+0bSsfigiKppDgSP6PVhTc1pPifOtTzzBmUomeNDqhJY1M8Kbhsc1oFEArpin19a9NQ0Lr07iERjSczb7BYTWpH5uL/YOqMvtqSNMYoH4YHUibvomnrVOTaNEgX5rWxYNGsxk52KccRTZ1ocYVc1CVIh9RiZxpya9uaWrvVooCXT6kbs2K09zxHahjYGMKZyY1Oag8bRkXT0lhfuRXuwQzsJq0ZHQS9fCvRXFNy9G0vtG0YuJAmprgQXsmxdLueZ/TrEkjiWdkUIqANX/+wvCavEggHq2k+hEi2qSlDVhsuiee2fZLRfacmYSO5qFRgM/WJEGDF8wWM9k3Vy5hVrg36i0VN9vIWzVVViDSjl4d+SrsTLpg0VbZVV3Imy9Zz0A75mTNAXRQsFt7CuZJKTqo/F7Qj+VQlUJxBYeZgUgQmBXMjAYW3Y4hZagl6vbaImkwGJa8XsvGssCQAFym2r1qFVnEtfl7uVVE0OrrMxincrLnNZzsBdhU/PXIEWFUYg4+fSpRQqWPoo8++lDKG8NZDrBCqB+LvRMvbuiWomMMUyoqKloWJyJtie2T5DNgEgAmgCL+S7r8IN2IGRr2A4s4Ni5efsPKlsB+/q5fSj4bafNLGawpWrYDABqrwalDR4mqdejYgYEmSRgg5AxtGEBwwcCxxbHEHNIBHt3aDCwVgQJsP8wy/A5+U4OkoxMfr8txUQPmsQa0dgkMaPwYr+P/4UwHiDVr3lyAyFHjZZVJWNmWjigK6Mn8aSggiHSicIhPI6MpOCyCgsL5/6PDqH9iSxrSqSUN7ehBA2Lq0aCIRjQ6qgUNCHKjniFVaGB0HRoUXp0BqxyNialGkxJr0eJBXrT+60TaMDmJVk+Ip8UjwphxtaBhIdVokG856t6iOAU3KES1y+amiqXyUqXiOSm4STnq0rIStSyXmbq1LkuLRidQzzaVKbxmXvo82oOWj+5KHVvUoaD6JWlskh8tGJFIfRg4JyY2p0OLRtOywSH0w+BgOrV+Di2f9TU9eXAv5XLahw4dqlK3QcPfGzFyY5HgSqorj8L3ZOTxGUxB10DSOWrOmFbyigxGvfVSLqWU36qs/AcWtCFdyGHWuDId607MQFvSsgasQjYhY+Ei6nXHOlYFTT+VaQI61LHK+zdEn85YVErmoS68Z4BwdqO4Hd9jsbZp3UbASk868XXAJFQMq44yCQ2QqilDfFha5gCmJb6s6kYBPwYvMBNM2rx58kqAwZqepI8HgBIXIgDI+wwcy3/8kfTtyZMnAiIarD5R9avACJFGg+0GK+nYsZO5GMFetGZIsxq8DuYCxoL9y4Bmq8pnhXkApoHfADuJUj4YcR5btFPOzD6r6QfWYo3yAaBiY+MEoCBRAEB069adunXvLtsCE86ruZeAu774pkuT1vSlWYeukmpeoMWE/4yfZ5dzkJhoMCvTBAVYhtnLJsAOw95hlgJUAWD4HdzjueQQsnkH8xLSBmemoqudv8umCYMoFcAF/7M7zwFUd4iIiaewmAQKDgmmuCBvSoryogEdvGlIQhM2EevT0NBGNJDNsb5RjahnELOrkJr0eZInfd3Jg+b09uLRnNYz21k3MY7mDvSneUOC6MtOzZiNNWSQqUI9W7lQu+ZlqI1rGWpU3YUqFM1GZXOlpmhmWT3aVKCWFZhptSxPC4fHUZfmFSimblH6NqkNzRwYT7FNq1CiV1UBLDRkHR/nSbu+G0i/LhhGs3r70sEV02nvxuV09+b1CSkC1haitGyTn6knV/sGBiNiUJIyJsy0cFWWapGKZWEhwMelRZ/vrPOuAUuc+YY5iYkDoLD5rAytFyQH1iTmok7MP51aY6bYFEpZa2X6rFSHmwJ2DCtvshZc7wKpPE7KGps+Kgvg5barEvqJsBOALyYizCUjpG2IEQWwlIPW1OcwaIFtGeBlMK06yulumoU8KlepzKZXTVmQ8JuAyUmLMrMGWFFTzoHqCLjwaCf7F+M/N8Hq4cOHwjbwOhKTJWeTtx1RQDBCbDfMICxWmFwxFrDSi1DMMcWyRDnOLAZMDBHDKlWqyr7Fq9cAKFH6u6bT3F6G4Ohg1+/r/9G+JzHx+PcApl26dKFuXbvKZ7y8mou+DOdEszw9NDhBxpBO+fHgz8uQPoPIL6wNQaTED5vDn/J5xFoAKHZgE1L7y8JCbUJV6wCDCg11LrsQgMb7bAKHK5U7jhfSggBgOB4QxELSIOZjI1d71qXByqLt0lKJhsq8hGbNLyCIGRefjwg+V8G+1C6sOXUNb0T9IurSkPDaND6+CbOthjQgtC593dmbZvf1pTm9WjHT8WOm40OrRgfR8pEh9F3PljS5ewsa174Z9QtrTB3bVqOOzYpShxZlqZ1fPWrrXo0aVCpI1dk8rJI3NSW1qEwdWleixsUzMnhVo9kDoqida0lq39iFvusbRVP6RlKSV2X6okMb2jF7FM3sG8bmZ3u6tXU2/Tg6kdZOH01Xju+nVy+evVtEyid8Yi02CxswnXdRQGOU4K0ki1pfdXKoJFhrx2ZHU1A3PtCmoE5uRi4imBV+B1cu7WDXfe20dCGZM72IVmgrFmVpcuqoUrcBVkGHTszJE5rzWQrx5XES3XOaXuOkcqgemlHB/IGDGgwF+w09lLd3C7nXzlhN7Q01tDIFlOPdFoGqZ5qDxjAYlqG9qizOb/wuujUD7AsoQad0BuJjieMBsKpQqaIAEhZnR14Y+nb37l30DZT3ELXUVRYgP4AiHUpsgEyCMmUiLQBlZQ3hFjDBPRz58OMYvqL28htYjAA0gxHF2syoiMgUTT49ovTvAhSVPwp+JIAUHP74X5ihYJAZMmRwClBoRwbpAi4guMdA8AMyC5Txef/99+UCo4MEOJcS1f0U+Z0fifsD2yUBBpXio8FJPzb0YSHymr5PBmihtu9BTiGfUSk6kZqBKVMSxwrg1ax5MwEoDUja16UvfPreXeZWYzETMcdatPYnvzC+0EQnUmSIP8X588UnqA71j6pLEzu604T4ujS5YxNaPKAVrR3lT+tH+dG6oS1pLY+VI4NpwcC29G1XLxrN4Da8XVPqGlSPknxqUq+2ZagLm33t2aQL9a5B3g1KUt3Sn1HVgpmoZpEP+XMNKdKjPNUr8B6NjmlGM/tEUDSzrN783SVjOtCXnX1pFL++e95Y2jR1EM0ZFEXn1k2hw8u+op/mTqILR/dher67WcmMKTPcatdv8GcT3mlt7pllZtDxN1t2M7UEixOgpOssSa871e+ulHosDQ9cXEwhqW4XBaYD8BNBKrRW6Mii0j10u/jCFoGnNb1Guto41Fv/O23knTU5TSb0TIE55U0hAujMsQ7zFvuWKWNGAS4wJHG2esHZ2lSumJhUepKZdF85YesrwDJC4g3McDnYFoZmWfC5gFkBUPQ+AqSkvhUiqyUMBluGjz3ALZVavG2Z8aAKAG4PHjygNm0MZpWRF/Fn2cMEPqAAAIAASURBVD4TZ3zmzB/IvoB1YXFqp7odKwqPSGb+WKUGhpkYafqVDF+X4VyPsPh/TAmCw/etgIXvaHMPpl+CAGAXMffALOFqAOg4AhTYIs4DQAmBjrTMqFI7mIAALZi8ELlmyphJtGLWBHWM7FKXLLPMbRzzjh07GonKoQZDsoIS1Pa2yKYBSiHBKq9RD8tzsCwZGuDUYwxENOEDE6Dn/QcYwmQUZq5YF+aNlkjgdbBhLUpFkcb6jdyogWdrau3P5yiMmVx4MMUGNqWE4CbUN7oxTe3uSfOH+NCykQG0ZrQ/rRvtSxvG+dAKBqwp3VrRpI7NaWyCGw2NZlMwpBZ1C6xO3QJqMHNyoS5tSlNiqwoU7lWR2jYuTY0q5aUqxT6i0rnTUpWin1LPyGbkX6cYeZbKQl8ltaVJCS0pwc2FJnfzpZ+nD6FvugTQ8s+70rkNs2jBsATat/hzunPwJzq4YRldOnkYUzT0nYC1a9euTPVdG99wc/eQUK7WXOkUHd0LUNgWzDcGiGQdhR2GTsMxHrvIgrKl2xj1ynWjC61/MUw9m5lXJFnk7x2ApfxWml05VgrNY4kA5nlHhM8umTlvypUXBKhyG1fi7CofEItEyhA3MxJdMcmaNW0mkhFMKHcFVibDcrXlopn6HVUZQIOVONyZXVVTglH4guCsza+kF9L8VQUkdN17NEaF6fiBEmoCAG/fvm2k3jx+LOxMzEBezJAu4LwArGCaI1zfmdmLARQxAj4apOykBo7DQS9l1Uppk8/RL5XMR2UBLO0Xg9kEhzfAAqwExwDmmxWkkO6TKUNGASewqDRKTZ861Xti+mVWujfMZSSTly1bzkwRgk8L4AVWXFgdS91QF+cXgIVjC/8jQDxalZ/RgBXqxCzUx8MEJwVKthGqhg3ENIDpYxIcFCSsC4AVr8ALxwTnHxe/+sqV0ESJUg2RqhKsMog1dG1ItRrUpgZN3MnL259/O44ZLV84QoMoNMCLonwbUvtQT+oU1ZT6t2tBY7v60ZRBETRjWDR92SOAxnduTUPjGtPQqHrUy7cC9fIpR51bFKfuDFjd2jJo+ZSnJN+qFO5dmXzcKlDt8nmpfNFPqEDWVFS/XH4aENOCfKoVoICq+Whuv2gaH+tOX8R70v6FY2j1hO40s38knds4l7bPGUNbZ4+mW4c20rHta+nu9UvneZpm/su8QkbxGQ2ZbiJFxyjKVlqclWBHuNoAYLTfB4sVC9OIFLqkYA4a7EpEjDxZdAkVa8qNNaIFBqVZglnO+F2AVTi5zyp51QUjUpjP0iTUmmLjyKocTT9rNNAerHKp6qc5RFv1Qab3zXxAH17wPihti2JxPLEAWJhEml1pk1BSMizRIJ0sa4sc1jWlDmBVDeo3kGoELVu2kH3Mb2GmIrBFRQbeZxx/LOqsWQ35QoVKlenChQsCVi9evpBFL8yKwfVTdT6wyHFRApABrKCvAqOJ0kwnItI035xF8sIt5p1VkhAekVzk6UyJbjP/7P1THTt1FpCA/69kyVKUnkFGAxWABr6njDzSp00nA/sLZ78hwTG0abh4gB0ZbC1OooUYqEAB4MJFJrX4tdIY5YtUNRAXFRgCYOH3wGixLQBxDUo2X1WY/b0y+xwByx64Qi3PbfdiIkKThtI4gUFmYjTAClFKsEtsgy6DIyxd6b20ot7djS+OHmwaulUVl0OjRs0Z2PyojU8MBYW3J9+IGGoTFkk+oZHUBjmJ/j4U6N+GQnhEhbahToENqXuYKw2Jd6fB4TWpZ+uSNMTfhXp456eurYpTH/+ylNSyOLVvXZ5iW1el4Ja1yL1+eapatgCVLfIJFWHQal6tGI1u14Y6N69CkxJb0/pJXelbZm3rJnSmC+un09zBcbRn4Rd0a/cyOr5qBl3ew2B1+sDb31+9+nvNV0ePGOFdhxcFEBsMy8jxqyA6HwEcxYh07p0RMSxt+qg0YJmP1XN8XtdGgspb19jSLaOKWQrsGY70graKoAVtkT+tp3KMADp2YdaVQuHbyeeQYuOsEYQjm7I+z+NE3a6d6iifggggJjT2GRPalycRFpcWEcJvhQHgEkGhmy1NQ4OWo2mIAd+VTfluRA0xcdEMAvtS0JKArKtW6DbyAE30AcQiLMTHdu/evQJWEIe2ZxAAWKVjJgLfjS5rg3ODKzdU6oZsIdrUNTkFmHf5n5xoqN6ZOqN8NzD78DheySeSkjqIQBIXPG3GYdszZUzPjCkTpX4vlRnZ/PTTrGwRlGGwr8ffacmAEUIJCe2YlSUx8MXzQo/n/YImC2AYJffyOo+goEDy9oa8wJXN6NJ8TAuZLg9Ur8X5xQUaOYNwurdjMNesMNTixwp1wqi0ieeMaWnnuyNghaiaXc5+A0AWKT7CWDOAAfPd8F01UHmN2qcF4GosrL4xs6wGDeH7cqeWrX0oSEkfgsKiKCAkgvx5+AaGkl9gGAWEhZNvlC81b9WMWnh4UscgP2ZUjahXy1I02Lcw9WtThTo2KyeRwk4hdSnBrxaFMyiFe1aiFjWLUdVCWahMvsyUM10qimxRlxaO70aHV0+myz/Po4VD42j2oFi6uG0xrZk6lH6aNYZuH1pLv+7aTGcuXCb647eBf7so2tq1az9q5OZ2y41NF5QcgQ9KGhxUNDou61w4UwrAw9BllbVrk67NQdBvCRNrUagCq7zKjDF1VJbiekYuV8FkvQEd5Qr2/ioFWBbpgrUFlx1YWVXrKfULdBCJWpOWwaqwuLNIFC2zgGN9BhYwEyigobGCQBDgpUHKHB4eJsvSwyYWbGzmkCFyWFsBFgSkuAcrgnhRH38EIHRtK7ABBCZwjmAGwnmOxf0Zb+9P69aZTvYB/fqazvfPlL8ta1ajdhWqMWhmFWnnr3IuL7Az6SwmYriTz6UkU9CmX6SpoYoVMSY0UwH+/mZkUwPVh5kz0fvvp2dT7j15njt3Tmb/fKHwasoLN4KZRzwldcD32zNYwYSKE0CKb8e/m2iAV+cuHalr187Ut28vGjx4IA0aNIAGDOhPgwYPop49u/PFoQb/r+EOgRQEc1vSkfgCALaWkJAoQKHL1IQ6iRA6mn7J2ZX1uf3nrQCmAQ0gZf08krXFzxVp+Lm01MLb28u88DUyU4JsZqKHyGlcRf/nzo9RRwxpXMEhOH8InkTz4wjyCQiigET+Xz5urbx9qLUrm40BLWlUohf1CSxLPb0rUhevihTfrAx1C61HXYPrUYRHGYpyK02BDUtTvZLZqXTeD6hY9rRUIf+H9EXPYDq5aRb9eXWfONa/7RlBe5d9S6e2LKWtSyfT1X1r6OzRQ3Tn8Yuf/uVKjoGBgd+58kKCWhkLQSvScbWRkK9aMNYaUWBj1pxBSBhAqwEiWBQ5VPMIMJI8qnqANudMiYLV7CtYKEU/lX0544JmgrP2W9k1OXVQqztjVHmcmIV2oGWRKQBsEe6HCYH/gU8JPioozM30DDYzAFbN0cDAA34rTxkALNyLur2JW3LHu0rBaKiqZsA8rAsxLz+H/wiftdVhL2xGVYupZGYcU4AcUpwk/49Ba9n335tgNWXyZEqbMYMsdIhBdbkX7A+2WXxWcLBHx9hVOwhzkBfYyw3Cnb5mA6UIJ2kz9mAF/xT+F5FIyCew6DHfTDnCe+9RxgxpKX261EYSdvq0PM9K8fF0F5AC+HTv3o0XbTthUAAmsKmkpER+vSv1Y5AexMA0atQIGjtuDH3xxXj68suJPCbRpEkTaPzn42n0mFE0bPhQBrBBzFLq8oW6lAQroHtDRgGkDfBv4TVx/PO2hulaW9bUIaeA5ZxZJX8vObBpwDL8ZLbXYCYCtIIl3zLEMBV5m7BdeI55B/+WswoS2kmvU30QtYYF0LJVaynfI8DF58U/0pfCo1HNogMFtg4gb56HHZl19engQ31861BPn+oU7V6CopuWpJhmpSjctQjFNC5OfnVLUrOqhal8wY+pYNb3KDuz4MAmFWjl1IF0/8g6enxsMy0c2YXWTBtBVw9uplM719Dt47/Qm2cPrvA0zfsvA9aoUaPcUdYDV3nUwLIW9AMQwAmpndi6xjcWj7W0DBy+YgYif0z66+USsMJnC1mcmvZRwMLmcPRNWf1WBS2ljK0Apc1AE7DyF7BPs7ETh+Z1UKTbA5kke+fMJZUGrE0UEI3CPTRQACo/Xz9qzeYfooA2sGomYCXmoKeHCVRWhqXD0NaSJboCgDYJcTXH5zFBYQ5iH/R+ArB0iR0NXNVrVJdjjUX+AS+u+fPmmWC1Zs0aysbH3sgNVJ1teD9g6mAbunTtqqKBMSLMBIhE/Asm3d8xCc1IoCX6Bz8VGAL8VDAD4bdDxE470iG10Owqb97cbOrWYAYQxNvbmfr06S1ABUYFcEpMhL+rA/Xv35dGjBhG48eNFUD6+usvZXz77Tc0ffpUmj6Nx/QpNI0fT5s2hSZP/lrAa9z4cTSGgatlSy8xLWvUqGnmdGLewseHdQBwkFLKqoqpNtdCLfqrd4FVcjMxNGVgc4gi2kDNkEQEKVlEkPJ14Zgaeq54auvT1rwg2uaZUT1CV4ww557ScsFRj7kKpuYb4E1hEShrw6ZnfBfy9w9DGRuKiAmnMZ3DpHxMQstKFNu0BCW2KE1JXqUprklxCm1SlrxrFqeKhT6hkrnZLMyQioplTUUT+kTR4TUzmGUdoAOrvqP18ybR2b0b6dyBX+jR9bOYps3+rYL+O3bs+NDLu8V5IK9u31Re1cfCwsiZM4cptMynSrJohbqOGgJYsqmKC9L0NJfqdsOfB/iIv0qJPgtpRmVtCGEddm23lHRBSxXy57M4xm2NIvI5pttYzT8ndazwXBfn04JB3GdTIIWFDf9dxUoVTT8V5AotYf4xWEFJjdebKaCSoZ/z1UuzLCvDMkxDd7N0iVX5joWC34MfA85zbE9hxaLsG8Marc6wqABWEqrnbZ0xbZoJVqdPn6FSpcvIezBtsqpOzBnTpxcW3alLF9FKaelCsrQTB2GnlX0lB6RwJ3IF2+dEcKoAS8oSMyMAWMKMxoVGMyo403WUrxDPEze3xsyg4ql3n17Uq3dP6t6jOzNCZlY9ulGvXj3FtBs/fiwDE5sbUybTVAai6TOm0YwZ02nmzBk0e/ZMmjtnFs2fP5fmzZsr9xh4He9PmfItTWLWBbYVGhYiaUgokYzzgPOB4w4dF5z+AApEK/V+Bf8Fi3oXs7J3utv7tfTQZZ41mwq21qXXz9V7GJBDSBoQyubwc8xBxzpc1nI31qGZl2EyNpJk78jIeAateIpt15XaBkZQfbfmFBfoQ70Tgii2TW1q16oCJfGIa1qa2nmVZ8bFF/NaxalqsWxUrnA2KpqDzXg+jz6uFWnj3PH08MQWunV0m8gXTh/aQfeuX6Y/fv832n3ZFf5q3/7zxryYpDOK8lGBYQGQNKuy1jTPpVpwAdwALNl1DW8pEZPb1uLdoaa4Lq5XWIlAtRDUXvBZIFntKtPxr0oW2/mqLGagXb0qZ9G+3AxQuXLb+6hUe3ij4FxW+U0cB7AdNBQFUGGBYSJ4KV+VCVKoMd7MGPo1cbh7GJIGT9HJGMJRDVjax6AlDrqWVWhoiBx35AZaSxvDwe7CJguOBy4ohoM9hxE1y/Q+MwgbWEFrhcmKhW80gcgq5wUapYp8PuEvwoAZqIHKUabgLFQf5sR3ZS91sP98uBJFSkIyA6OReJwkZgyi0NpPBUmCBirkejbnCwE0V/AvDRg4gMGqF/VkwAK7GjlyuJh20wWUvqO58+bQggXzZSxctJCWLl2sxhL6/vul9MOypXy/hJbxvR4LFy2Q7+H7ALpJzLQ6MGND2hOqM4DxevJFBucB24VzgIBKB6XF0gwrZUAKccqinDMwmwPeZG0WU9AKSo6ApV/DvTYVda0vsOZWbA3ogI6hjDeSqN0sTN86D8333TzYTAzi84d9jaP4hM7U1jeU56kn/1cg9e4UTnEBdSmieTmKal6Bwas2JbasTr71S1CNkjmpQvHcVLpQNsr1QSoqki01TR/eic7sWkH3zuyhE7s20ZVzKONOa9/Vt/Fv3RYt+qF8Y3eP3+FTwRXdqLVu5Bairno2ybnKb1SetDAagJFZ3cBSssVo2lnAkrRcxHS26/SRggWSR/tMZpViJDB/8oJ7zgDL4o/Svf90j0PdmFUX1YOvQrc2g6wAaR4+KvIH8AEIQR2ejFEpNtW8WXMBLhkKsDwUYHlYAMtgVzbAwoTC+yhYB70PjgtMV+ynblWm05XALLGIkF8I57nkB370Ec2cPt0EK1ReQDoMzCmouD/O8rEpgsTxxmSG+aCTmCMiI99ZAM/KrGzyBiemoBP9lY4CYpEDrMBQEKAoyOdR5AQMBmlTG2kyuLB4MWvt2asPDRsxioYMHS6NMIYMGUzDRwynr776kpnRLAGbZcu+px9+WCZjxcoVtO6ndbR6zWpas3YNrV23lk3h1fJ89epVMtauXc1jDa3j93C/fMWPBnAtnG+AFpuNAwb0kwR0qMWhnWvVspVISrCd0Lx58IWmfVIHU61v1Vq9G7TeDViOICXDYjqa4OQgRDUZVrANsGAeajkEzMPEhESJAGthqc5DdFfqeOswAIwfM5Nq3Bjzs7G0FItHYCQknBJiEqh5UALV92jB4B5OvTq0prAWlSiyVQ1K9HdnwKpGfvWZYZXITuWL5SGXwjmpaO73KQMfvx4RXnTn+Da6f2Yf3ThziF4+e/QrT9XP/ksaUUZGRe2A6h3CT5gdWCCVK1cSbQqu1tZonD3AFBCAMVhTyhIEHSE0yxlbug9rlbq19Iv+L9MfZe0V6NAnULMlK0hJMrJqc29XKFCVPYFvCr6zShUrifMbQIN8LgCTIUsw5AnNFTABjCQSiPeUbEEArakDu5LveZrSBg/lxzJMRA/THIRZicnp7uEuxwLbDGApZsmrxD2ODxgfzBWwQPH1sKk3a+YsASp0V8Ft4hdfGAr31KkF1DCQcgMzNygwkDp16myUh4mNVewq8t0+KQVEkX/zc9oEjFb+MDirExORQN1Z0kvSpUkvos7MmVHV4T368OMMfNzrMlB1pTHjRtHY8SNp1JjhNGbsCPr8izE0a/Y0BpdFDEQrad2G1bRx41r6ect6Hhto67aNcr9580+0ZesGeYz7LVttr2/evE5e2/zzenm+cdNPAlxr1q4S5gXAmvHdNBoxcpiIbOHDxTmEZAD6N+i/dNUGSBsg5DSKC4b+JQilaA5afFTBVvZkYVNWRqVByfF9K4jp90KVnwuPcQ50ZBNWgasl/1Cbh7o2m76wQsel3RUNG9QnP18fikFlDj7/UbzvLX38yJ3ndqfOnahX11gKb1uHwr3LUlTr2tSsTikqVygLm4SfUrFcmahornSUkediWKtGdO3sIXpw7TT9/ur5Q56mLv9lnXOnT58e4oYa4/XqmRUadAleLRy1a9UOcy9Z+eFClnbv1i41BQQctIljNkTV4CVRsAImG9JpNeJvUiAmVSxV9+TcDupzcZgrCYbO75OW6ejgwuCEnDGAFN7H/yGEDYcvnOZeGqCsJ8/dw3ScN1dApYGsqadNY9W0qT3j0hosA8gM8PI0TUM3mfy4hwmIRQ35AkxAMI8iRQrbJ38r0xmmOa74Oh3lE963+XMNB/vLN7/J/e7duykXHyeJFmYxGpsC3ODQ9mTw7NKlqwCVlVmFO1GfO1OlO09QdiJXUCk6uiZV+6SOFBPTjo91Rd7uNMyooANDBdI0VKFiZZ747ejziaNo/Bcj2TQbR5O+Gk/ffDuR5i+cSWvWLaet2zfQ9l820Y4dm2nX7m20/8BO2r9/B+3jsXffLzL27N2uhvEYn9u582f6hb+3ffsm+f6WrQC5dQJkGzdtpA0b1jM7W07z2DSET2vChM/ZpG8qDAvnEOcG5wjRVDSpwDlKbJ8oDNW5xuqvo4TJ/FUqr9D0T1n8UsEWv5UVoIKtYBVsD2bW7yIXEawL5xnyB5x37JMO7ghQiT7QPZlPS0cUJbrIz1ESx9DlBVC7xHhq0SqAmri3or69e1Pfjrw/zcpRSKt6VL9KMcqXNRUVzZmeXPJnJpd8H9DHqVNRK4/adP70Efrt1WMUNWr5X9rqe8+5cx+FhIbdhCZL2miVLGF2ttEJzLkUmKA1kg65Wxs8WJmX1l1ZcwG1yl1+n0FLStDw0M5+vK51WVYTT7q3KDMuh+57h8adACYVmcyuaolnU5/RLbWw+JHcjfbfYCkAJPicjJPUhK8sthNndYyLD8rT004EaoymZp6gycDU0O9Zgcv8HX4dvy9tz5mu44IAkIX5V6SwrbuNrsGOY4hKDfA9aZ9PAf4cIoCiYH9llDZ++OgR1ahVy0xozpLFiAqiJjsqk/bo0VPYgTVx2RnopKi5+othJ1lQlT07dOgoiwfnP7XyVWH7c4qy3p++mDCJvpk8QQDq62++oClTv6IlS+fTpk1rGWw2CwAdOLibDh/ZR8dOHKYTJw/TydO/0rHjB+U57o/we4cP76WDh/byZ3cxmO20gdauLQJcuN/B99t/2cisbDMzrY3M1DbQylUrDLNw1ncSNUSEDVVLoJDXPktcJCFnwcUCKnPNWEJDrOJPB9AKdnDAh4QmN/9CLY71FHxTJpgFO2FZwRYQczAZdXchPz9/qUSLcy1lbfj8Qzdoa1dmE5s6ApdjbXoAYFQkRjjFxHUgr5YB1KJpaxrTvz8NaBdB3q41KP9n6Sk9gjxpeY5mS09liuSgTzOmosjAFvTba+nEFJLqv+M2bdq0Yc28W0jht4LMlKCv0q3QsYi0iBG+JAxHs88a8dMVF3SuoAYta4v5Egq4DPAqodTGRv1xPMbrtghZEbuEZw2SiD5qIMTnNTNElBMOakR9mpjdTlxNkZ01mVTeNzPhLZTZNOm0WedhAaympo9L+7A8PTztXgdL04AHWQT8H5BB4HjozAGzhj2bfyhiiNeQslSnbh0BWg1W5Xmf9uzZY9Szev6cnivA6tSpo1GAT1WLALPKlDGDXADgP4LwUVJuoqJS9FuF/42mDhEpVAM1TcEYQ1+FKCD8f9gOUdinNWQKlatUlOjet1O/FYnBlGlfsdk3lZb9sIg2sfkGkDp4aA8dPXqAzpw9zuMEnTl3nC5dPkuXr5yji5fO0NlzJ+j8hVN07vxJAbDjJ48IgOE7AC+AnIAXs7F9+5iJCYBtZeD6mYFrK23buoW2btkspiF8WXPnzmazcLoEPOrWqy/nDOcJPszixYqLrw3zH/uJyGqEqhMfGhL6l052a9RPg5SVYaXoTHfCvEzwcnzfyX+GKNASGYQSnMaqtCd/f3/D5GMT3Yxeq7lusy6Mx9Z1EBHiSxHhIRQeFUdxiT2peVM/CmoVQpMGfU7RgT6UL1tmtL0xx/vMrkoXL0BrVy6D1so31X/X7ciRI3mYzj9pxItXg4Cu9Q4Aya1MMu1b0onHpklo1qgqaNFaGZUXwBzyieLdqOFU3AJWGFLtwSJG1TovLWSFPw0pQ5BcGGr8ilIuuDqDK5gERH9oSqrrSUn7LFGO17NTkEs99YYNzA6+DVXRf8eGAcYJc7cAlacdc7JPwbGZf9q81I0zsQgCRF0cIgCqI5RFitgigfpY5+eLgNQT5+1EsEODlWtjV16k541E5idP6BEP3FauXEkZP/pIiUONZhEALejJAI6IbsE80/KCv5M+Y2fiRdh/x2mpYgZCvA7FPMSo2F/k++kUmowfpCEf31Y0eerXNHPOdAap6cxsptGiJXNp/YZVAiwHGWwAPqdOH2VgOk2Xrpyny1fP09XrF+n6jUsyrvDzy5fPCIBdYvA6d/4EnTpzjE6cMkDr2LED9OvR/cLKDhzYxb9rA63de2AqbhPA2vLzZlq/fh0tX/6DihjOoLi4WAlogGEBbMGA4QpJmya1AD8WOkora6e7Y35gSr4sO8BxlCY4AJadhMHhe5pRhSpWFaSaycoIClav2R7rgXxKgBd8b7hg4eIVGBRo6gIN+YOu/GDvjIdJ2ER9ppmbKwNWKIVFxVJEfBKbhz2ohYcfxQUk0oihw/7csmXj0HGjRvTp1rnD9x2TEtb16Npp2qb16/x5imZL9d99mz137lgvPmmVGQwKMtBoISn8PmBLWt5gRAILOijR7X1YNvFnAbPbC/xKOom3ePFiNtNQlacxS9ZYQcsCUroXn+7Np8sGQ7+ku85IM4daRulha0VPXSxPF9Azwr725Wmt5TtSAqxmdoBle+xtySfUTTIxcfE6AAlmqm6WYStSWFSOAY4h9hHOX/QC1AJKXO3v3LlDL16/pkePH0vfwFe//UZXr16lyrzf76mKoTp9CM5iXDVRgdNwsEfZfFaOcgXHSgw6mTclE9DShQZApiOB7doliBmI46llCtj2InyV7dqtI82eN5MWLppL8xfOpgWL5tCatSvEdPuVmRFAB4zqCrMogNKNm5fNcev2VRl4fI3BS0ALgz974cJJZlzHGbDAtBiwThwSpnXk1/3C1PYfMJjWXvFvbaNdu7YzYP0sgAU/FiKGCxfOo5mzvxNBal2+kEFLB4YFgMJcypg+nZwjmIkALBxLe8BykCWE2CvVg1IAIKdmoAN7CnEAM6tjXTpgo7FsUKDZYFYP6+v6c/o3MR9Qswzv6TLNpknYxNGfZZE91GtITdlaiIzli1S7GPILCaZqVWpStg9yshVWmoYNG74t1T91O331au7uPXo8bMgbWlgprKUaKZpVMIhgYemyM1pt7siw5LnldZg/AAbY0TlUaWS8jkUMcDIBS5Wu0YClCwICLAGaGjhhogp4MXBp8HIELdSRqiWglRywdHsm3dlEV3G0pjW4OVBlZwyrmQW8cHVG5A++KgAVMu9xJcS2aGYKpgmwdqwSiufYdrDDzB9+KH4ftJHv3q0bvXz9m4DV3fv36d69e9KlGRIGvCcmV7p0UtcKYAigQB4oZATwW0SriqApC0PtxZ8pCUSTva7zAaVAX6wkLeMiYK09BbfChC/H0sIl82jeglnCqH5csYQ2/7xOWBAY1YWLpwwA4nGTgenmrct05+4NBuWbdPceBh7fYMC+bgEuZlvXLjDAnWXQOkWnGLDAsk7APDx+0AAt/n2A1oGDe8S3BQf97t2/0PbtW2n7ti20adNGcbwvXbqIZs+ZRUOHDSWUCzcixS3kvDVkFg61O/y1OLcdBLAUw3Lqx7JXqoda/FSO4KPbmgU7SBWcRQOtEUMNWEFqbhnD9l3NsvRrkDoIaDHT0vc4fwJa/F3Mc2FZro3N+W4vdzBMRq/GzalJoybk3daL3No0obRZ36NUbPbly52LiUFdqtOwCU2bNnPkPwZaGzZs6OHHO1uBQQELzCjIZ4AEGAIWn/iRLGkzJmBZxaHKHIQsAYCBq7GWGMgCVi3PS5UyAEvMw5IlTMDSZWs00zKAq5yYqZptIYpmY1r2wKUrdwIIZNSpLaBl9pMDWLk2kgRRV7t+cbbqjloA6liFQfu3xPGuEp8BXmgHJYnL/Bz7hUwBbQZbfXq4EOB17Cec/9gP3XQ0Dx/zqap3IPoGoqzxrdu3eAHfM3oIrl8vddrfs9Rjfz9jJgFGLCiwAWE/OioY+X9ebSHcEgnEfYxqutClS2fxt5mliNO8J4xy5uxZ9P2PixmkFtOyHxfRqjU/0M5dW+no8UMCLueZIcHUu3P3Ot3mce/BLXrw8DY9eXKPnjy9L+PpswdsAt+X9wBkADV859q1i2IaXrh4Wvxap5VpaPi0DglzO8xM6xBAS8xDsKydzLJ20rZtW8XxvmbNKvp+2VKav2AujRk7RswfLFQwrIAAf/E/ItkdF2Z30WIlCUPRFUOdFeNz9GMFOwEqR/9TcEjKTnVHDZYBWCEGKCmT0Ap2AlQhIRbwMkAtUA8xD0ONChl87vBZMQ0bNjIv1BqwxI+lfblN4OZgq6GpB9WqX5OKlCjEa6kmz3teA3yBrlm7LtVt5Pp6+/bttf8RwDp37lyGCZMm7XBn86YYg0gRRNpUX0GARwFVGcFaglir2rXeylrjCmwLUTrkrWnlvHbg431tBgK8NNsyCwJamJYNtMqbJqIVsHAPv5YGLLPPnyo3LOClQEubhY4moVnsv7FmWjaT0NPTHrCscgYsUjQY9fX1kW3Jp45PYUuEFCawIeUoIoCP1B/8P/YPTmoUmPuMwXz7tm2GbIHZ1YNHj6S08e07t+jZ82fSQAJmo5l6w+wK5uD7GTPKdsKPJGVbHCowOFYMTUmy4Mi8IvT7Ft+V+MWiohkYk3hb6pn+qvQZ0jBQRjJA/SAMBmAFVgU9FEw1mG7nmVVdZYZ04+YVAaLHCqCePn9IL18+olevn/J+PhSwev7iEd/fFyAD27qlAOvq1QsGYF06wyzrtDjoAYIatI4eOyj/dwiOeAAWpBAMWHv27DYB66f162jZD99LtHDChC+oKbMoBF4kZ9TPTy48+ZWcB/MG+w/HtZkAbQUti7ZKO8idRQGDHViVNdpnB1jBKVcsdWRYgYplma/Zve/g27Jsiy7rg311VXmFukabtirwHFUePDw9TEV882Ze0gQlLj5BmKduSwb3RIvWrY9du3Yt0z8CWmfOnCncrVu3u1Wr1xBwAfsBeFRWrcDy5LVPg7El6xaUMH0h3TxCRfYAMDjZMAMhQ9CyBQO0CkvE0Op01wzLLA5YxhlgGa3Jqiig0qYhhgYsDVSaaWnTUDcztbZbamRpeqn9WFamJdosO5OwmThpfdjUha8Jv2vWDlN9ArVYtrhFMIvnUvuKP4/915UUAEJw/m7YsFHASpzsT58xA7nLwPVQno8bO1bAIS2bggAsSdRldoVjlCBRwQSzR19Kuik7H1Z42Dt1WI7SBSxcqd/OLA6TOG2aVCrJ+iM2RdvTmnVrafXaNXy/2mBVO7dIRA9M6DKDzPWbl4VVAawAUs8VSL35/Tn99uY5/f72Jb169UTA6tnzRwJeYF33GbTuMGjdvHlJ/FkwIwFaF8UBf5JZ1nE6ffa4OO4BjNqfdUj8WXDA7xLA2rlzO23d8jMf459o+Y/LBLCmTpsq5w/nHkJLgBWU+ZhnyDfVrdp0kUN9/KzqdENQGurckZ6CuecISNbnwSlouwA+hr/KgT0pBmV9bvq6lBI+wOLXwmelsimDFvZXsi8ErDxFPIv1gTkOhoXjoq0LfA4X53DVpg2v47V69RtQ2UqVESWe94+Zhpu3bvUMDA5+A5aFhWVos8oYMofChURHpNtbwdSzyR6M6KFmWbrwH0wSLCwIOa0RR21ioja5i8XxnrzVvT1gVVC9FCsrsErmy3IALKtPS4NWfVWi2ACvBgZoKZPQ3c3dzqeFk6n1VjhpuDph4Dsor6MzAgwJhgHcElRQpXfwGlgg/heSBaPEciYprAfQAnPy5d8ryuCNK/2hg4fMtBv0E0SEsFadOmZlA63ah5odjmI0PIWZhqEnlLVyqH7sLJ3mXRKGcGvRvdhY6UeoTSaAVbbsn1Hfvn1o4+ZNtGHTBlr70zpay4AFXdTps8cEVK7duCg+KvinwKoARgCq1zwAVG9+fyH3egjTYpYlbIvHw0d3GeRu0q1bV5idGX4s7YDH70PuoE1E0WydOCKgdUi0WjALdwtg7dq1g375ZZuwLHG8L1rA5utMYSbI7RQxsSoZhOgzBLy4UCOI4+vrp5qmRpssVdTvoSlLFZIxKqfiT/sooVPmZfkdK3sKdmIW2vm1LK+LL0sPBVzwQ2KfEFjAGsDcF8E07z+OA5qe4OKt1w3ACw56ADu+58fmM2Q/rvyZGnyxrchr77tZ33X+x0Br/qJF/kjZyaNyBwEq6DMIELGWnXHM5dPmEBYvHoNh4KokkcLPPjUEqErLpVNy8BzsDeBmdb7bwMoesAy/ms00FNCqapiEeuioob4HaBkSBwBWPWm1ZZU6wKdljRhqPZYeOIm4GuMKjM9gG+GnsuuZaOlko4WgMHUhV8D/A5w+yJTJlIoAgACwEF2i6w18WVJihY/N4IED6f59m+8qO18cpC67Eoiidjmy7Xv16m2kkLSLl4lka/gZ+W+XiwlXydEwRXT3HDjYcUXWaUI5c+ekYUOH0NZfttPPP2+mdT+tpQ3rf6IjRw7SuQsnBVDgLIcTHawKAPTbm2f09o+XzKZeCKPS483bV/Tnn6/lMUALYPbi5WMFWHfk+7fvXDd9WZA9XL120WBuDGSIIsIpDyBD9BBsCz6tI0cYtA7uZbNwjwlYmxlckaaDZOl58+dTNIMQWIIO6cM0wrzAHMO5xLzDgsYFRRptxBh1xHSxveSiT3sTzxR5BoeYJqNVFGo16xz9WHYmXXBwiiOZA97J69ZtA8vCve5uhIuktjS8eZ5rV4cu/Y05inWntYl+fr6ivwP7lO7UfMyqoqO8h+cbPt7N/zHQGj9+fGI53lCoyaEPQr6hzQFuAIZR9K+sObCQdYQQAIaFi6szgAMLVgNWAQVYYFjSq1CVo5Ea25bO0ZpdacCS/yxv73y3MiyDZTkAloVlmZosaWyq/VkNFVi5WsrOGgI73WSiTZu2chXCdmlGqbVmWq1u5AIWERCDuYttwn9hH1CHHMwIYAbdGP4PJiLabKGmlxwLPia4QKTPYOiZAHSbNm6k1avXUB5+P6dKPwJoIf0GvwstUddu3Y1OxRFG/alwJxVEHauF/lWVUDCJGLU4UekBNeZxTgFWOXg7Bg8aQLuYuWzdtlWib+sZrE4cP8pm2hlmQeeFEcH/pH1VACEA0ts/XqnxWoAKjwFWf/75mzzWoGX4tB7x9+/SfXG+GxFDsDUNXPcZzObP+478g32pfftYYXb4zOkzR+kk/FoMWocP76N9AKydO2jHjm0icVi7bo34seYvmC+C15pSYqauzBOcM83EcbHBOdWCZCxQ8eXxBcZsTGHRZgVr1vQO887WiMKmUNemmm4Hpk1Mm2zBwQTUZqAyBQO0SWj9rNV8DLSPLGo2ho5AMaoBCZiVIbZuYlYpadmqlbwOQDPrbanIooAfpDv82Xo8xxG5z8nzIyEh4eaLFy9y/WOg1bdv/z4FGUjgfyoOBXrxYg7sp5wFUMoJgOB1LGL4AQBEmPR1ateRjjPWJGr9GIJH+HWqqVbiTs1BK2CZjndDm2U43BmwqtszLOnvV6OGzY+VzIfVwN75rmUNytkORoErDoAPyeA659GqN9NJywJWRQywwjbD5ASQousLNFLCtJjhGeFkV5XD6C6va1NP12+HDg7ABNAqULCQ1CIvzcdCkqJR1gcVURXTATCi7DFqowNY4HiH6WLfZiv8nc1L7Wpahdt3szEqhcbzeS+hopOfMVgNpu07fqHNzKw2bdpEP2/eTCdOHKdLly7SlSuXmA1dE5B5xGADVgU/FQCJ6I3c68fGc76X116bIIbPA+C0813MQjYpjcjiNRlPXz5iljSX/IJ8aN3a5TRlyiR5/OMPC+kh/y8EpidOInJ4kA4e2Ee7d+8QPxYkDus3/EQ//riMGdY8GjhwEFWqXEXcCzqQ00AxLoAWjm8hpUusWrWaoQAXLZo9aNlFC4NTqNgQbJMpaC2X1ZEe7FDz3XSuK3Mw0E7WYHWwJ38vZTYWZEQc+bEvM6Vg/h+wc1zQwJ6QqoS5qWu9CdPiARBDNF5MQ14jADLMD1zMMadBQkqXKUuRsXHMtDcsTPVP3gb06zckF6MnQulFlDNZCz/lsWpPr31Q0pxCUmyKCxsBuIBZSFdpVXFB18TS5WrAJmAWlVK9Dl0cooRaSFq+gqHLqqic74Yeq7IcTABVcjFpDTsxqZVh6XZbhsTBqIWtqTBOHtT0RaSiQi5Tra9ThXRTCJ1CZFRjLS3/AR8angOoID3A/ssVyt1NpT8YZWZgmsJEBWt7T5ouZFJqeOM/cGzAyj7++GN5HccQfjGp8qpelxpYzFzxu3C8J6hOK45arJR8VCk1Ng1XqTfx/JuovIr/+fDjzGyC9qA9+/fQ7r07eeGvlXH8xFE6f+EMm2WGCfiEQQZOdYDVn3++sgDU7zzeOhm/0x9//m6yLHxPAOvlI4kaAvgePLwjv/3gIYMgg9ov27cwQPnRRv7/O3dv0qUrFxmEllBAsB99880kunv/NpuGxxi0jrFZuJ8O7N9Le3bDLDQAyxCQLqCJkyaJdqwIM2PMK5SckXrorkbIH3NI+yaR/4qLEF4PkdSXOLMFfZjyaYHNOPM/2eUCWqOKTkw2R8e9M9Mu2Im8QQ/D2R7oFND067iHmQsfKMAK/k8wNW0aGlVKDL8eGBYAyijH3MQsAoigE+Y2+mDiQukO5zzPw+69eqF7U7N/FLQGDRo08hNeJOjCghImn/AisZZ8MXLhDMEpgEw3rECuoI6GSV0qVV0hj9lGLJeADHa8pNJlWQWl1iFsq7zB5DRoVYICHvIGFTGUwVdCsK3qZuqOLUooTsS6xpBebzz5tLMReip8Vhco1AEFq1RDj0KWVmQAKm1yAmjBquBQx++gcQVEeLqTjhbnwVeCAnIAIIAu9j9TpkyGyYWms8g3VP+bv0ABM7XHGrDAf2RIl54ysQn5CZuJ2D9RNSs9luE0jzL9UlZmZc++IpIlM2snO+QdUr4mTSpqlxBF+w7uZlOQzcCf19KGjaslNebylbNsAl4Wf9PTFw/p+asnAjiGb+qNyaYMwHrX7Y0JWNokfPb8AT1+eo+ePn1Mjx49pDd//EFnz56hiOgoWsSA8+YPolNnTotpeoZf38mmXxS/N2TwIDYbrzPTOsvs7xjt37dHsaxfJFK4ConQi+bTrDmz2PRpQUWLFaZaNavzhbM2L0zMk1rSxQgXN4AWHPBgzyUVaGFRw4mtVf8pVVtwlCzYSRGCrKJP++eBds+DkjnczdeDk8sYsF3+Af5SdcF4bHO6+zs44LUpClZuOOFbyb7hQq6DEPoiruUP4i5RF3ijYm6w5Fri91D1pVXbtnzR+OY0n9CP/lHQmjhx4iA2k3bzot5Wply5VSVdXJaWLFt6XZFiJXbxotqfO3eeN7mUmr2o0nDBnAMQYZFlV2r3vKpJK9J1UBceEwITQecWWuUNBsMqk0z5rs1Dk2FVcc6wYBbWMTvTGIr3RgAq5VyHeA6fg3wC/igdAc1ndvwpZDIrQ51uiECxP9heMDgAH7YNkg+ADcAZzAsghcJw8IPpvnJ6wOwAw8L3AIwAPewLIqlp0qSRY4OLgfjJihQ2/xPbhwsAuhfjc2JOvv++qLMRfcQx0apm3VberDDqrDGq1bdlUbNDYAi6r0syt23bivYc2EP7D+0VoEI+IHL5Ll+9IM51yA9evnpMrxlswIDevH2tmNXvlvH2LwHLcL6/kN959fqJsKxnLxA1fMKvv2VgvEkx8bE0beoUevn6Fd24fYuGDRtC3bt1od379tLJUyeY8R3n/UeV1QQ6xc8vsZkKloUB0Nq4aQOtWr2SFi+G4n22NK+oVBnmYEU+n9WZWeOiV1nmEJi/LmmtK/GCWSMQhLmEY6T1aaFO1PCOI9BBYuA0xSbZY0c/lcW3pR5bQQ2fNYBJ/Y7KLbQ+tr6Hz4Mx6u7dHmjeykCNIARMQcxj3Os5rC+8YFkAshBhloE8fyJF11aX53Y8X+z27No1JNX/5NuoUaPKMuoufP+DD6ShaSF1RcKiBEMC7cYJ1/XUYRaCAQGkdPkZ3SfO3odV1pIQXV6GRAwVu6qqne7V7YHK8FvVMU1AuXIwuOA/cZXUuXxokooCgIZvLb+ZNqNrdokoVpV6xmNsm/aNYbvyOmjSACqodwUQ0vW3UCTQ1n6pkUx2bCO2H34SABWOFZ7D3AMYwbmOYwWGpRt66Lbqki2gEsjhuEc55DSpUwu44DgiHUp8WrGxJmi9U9WugApmYJwqS1OihOFfq1e/Lm3aspn2HWB2tXu7ANZRZlaI1sEBjigeHOu/vX0uDInoD8swa6M6PHb2mbfiz0IkUWQOCrhev3nKr72l5y9eSAOK0aNGsgn5Bz1+/oyB61tTwBobG03bmWH9euwoXbx8kXr37ilsa+/e3WyynqPDhw7QPn6MvMI1a1fTkiWLadbsmVIbHp15SpYsLp16MCpVqiCt1GrWrCXnEOcNcwj+0wLqfGA+Yo6hhySAP1o1XnXepCLUITJob9q9S66Qkj8qJWW8mZZjASo7ULSAlr9/gJiGeC1E5R3itzVI67QlHT20lqPxVCJTdBAPUwwL4FWbiUFTL28aMnTYM76VSvU//cY73/vjrFn/xMLVffTKqCasWOxiZvHrWLhgQwAIfDavag2GhesiJqGL2TdOnOyVDTYF86+yGgArHQ2U4WAGAsB0IAALXJd3zqVErLpcTmGVD6kjfLZmr4XMZrDwsVVVLA5+O8P5bfT7092ydbFD1IIC2ACkdCDAqiTGhMD2AThLqc7D+B0sBOwrAFCDFgoVCtsSk7uYgDXSetAcoy7/hpZI6JbsyEkEuBqNChLeWWLG1GmZbMwoAIcABX6rBG/bD8t/pENHDkuplo2b1tHBQ7tFSoCI3aPHbAY+YzPwxWMBGROSGGBk/PkH/f3bGwVYr+nN7y/ZrARYMQi+NUrqDBli9BR88eolPf/tJS37cRkVY5ApzqNA4YICWq1bt6TNW36mg4cP8jZeoQmfjyMff18pKwOmdYhBa/v2LfTTujX0/dIlRnMKBr1x/LkGDevzOf2UL5olGJjKSat7zCPjYtdQFirYNOaTTrmCqYhzi8UMs1q6RSvmGmwp+eIMhBzlCO/ySzn6razsKPBvDgCUI4iJmejvb7I0bDdMQy11wFyFSBosC+tVuzRs3c0NMxG/E6Qih2iKi1ptIeERtG7dujWp/hNuPXr0aFuwcOGXaDel03YMBlVKFp2IPytWksWKxa6br+ZUnWx0VQitENdCTCnjXKaMybo0CFrV8tIdWQGNtbOOY1qRliUU1kAlfqrCJnjpRG0ABMAQ4AlzNkOGDGYOHbq/pGWgQDLyh2ySoRMzNFL4vq4CCYYEMMJ/wMwFiGIhgKFhUWAftKMdnwNYAbRgikraDr8GBgYnOyKF/nxFBAhBvArQgykqJV0yZGSmZTR4AGiB2QF8rJUbwp0U77NptyIkIohwNszaTLwfI0aMoJOnT9G+/XtowyajfhXEoGIGMrN6+eqJApfnwoIePrgnKUX/1k1FCzF+e/OSXrwEszKU/5O/+Yo6du5Ijx89ZOb1lnbu2kG169Vh86MuzZk7h5Yu+54vaBXlnCAN60cG2a3btoiJOHPGdGrq7UVfTpxA5y+eF9NwIyo3MODNmmU0pvjyq0k0cOAAPi81+SKWV1gWLk5SWw0MnYfUUZO6Ug1lPuj0K8xpgJgUamQzLUqV3tEt7gMt9deTSRQCHe6DgpJLFIICLfIEw3dl80cF2gtDHcy/AIfPOLIs60A0Wgr3oSwRj2bNm4lkAcAEP5ZmVEZytLvZSgyvIaKoWRZMY+Trunk2pYGDB9Pz58/d/yNAa+bMmWzO1j+ZMWNGs69h0WJFzegagAEAA1DR9dYBXtpkNJT0tm43uk2XNHq1OKHzOTSksL6uQcqUUlgYVZHCRdR9YbNcDoBVm7GYhKD92NZs2bNJ3XQNVOkzZXqU9dPPTn+QJcul99KmfajNEgxopoKQYMoT+FNVAkaX6JG2Yvw8r66KaomqFlbsD+9j36E3w/tZPs5COIZoMgFQApiFSGunppJoLd1/smSVe9SkQplf7IvOG5PaVZHOne5mXXb+jDRcYIaF7cF+4PVjp87Q4SNHaOvWLVIjHeJMiDghMUD0DuYaAEabdqjFvnPHLtq6ZQtdv3btX0QsLXt4bT7Gbd7c7ygqLppu3rghz0+eOiVt6us1qEezGax27N5J48aNoQ5sLjZt5inbXrREMVq+YjmzwQN0/cY1aUbR2qct9evbh06cPEm7du2iVStX0Lz5c6X91+dfjJeehejYg3Zjpcu4CPsFW9cBG2sCPR6L3IXPG3JmMUfA+GEqGU7sKBlQxFslBYHWiJ0FqGw5gfafsXfMW2teBaZo8qXkGwv4SwYWIBdBaWoRHy+vIW0HzMpmHTRJVqVXl6xBBVdsG1wPiCDWrF2Hghi8fvzxx/182tL+R4DWpk2bPk1IiF9QpGgxSp8+nbAo3foL6nmcaCxKXX1TSx30sCrodeqP2aRCDWtjDCuDcmwjZvsNo8igBi4d2TQayRr17GHyoYZ8urTp7MqnZMuR43BAgF/C6tWrC1+6dCnjli2Hs6xcubLQ119/XWtAnz6uEWFhA/wCAja6MPvTicrYD2mECqBmdqmd+wBond6kWWVeBcYAH+w3FgHuM2bKJCZi+nRpxVeFjkQwRfC7YF/QcYGB6TJASDfp3LmzgJUAloVhmWxLd8fRjnYGN0w+bHfN2rVo997dDA4nRSW+afMGOn/htAgzUfrlybMH4l8iAZe39PrVc7p9+xodOLCHtm3bRhs2bKA9u3eZUPTs2VM6f/4cM7HXhrfq7e9iMjozCY2IovHeujUrKCAsgE6eOCbPb925xYsigKpUr8YLMIBZTyNhrQApuAyWfL+UChcvKqbhhUsX6dr1q3T1+hU2By/QNjYFQ8OC2UyOpb37dtGWrZulEw8aU3z++XgaOXIEDYUDv0c3Nom8eS5UlouWrvohroeahusBIIagCRiY7tEJKwAXOrwHZ7xuzGGk8YS909RzOlIALZ1uE/g3QChl/5V9pFCbhhhoax+miv9p0SiCU6hY6ubmbhYHMIv+CWAZ5qL23cE8RNpOfX69fadOdO7cueBU/0m36TNn+rdu1eo8VPOoO4468TppWqeylFQlkxFVtHbZSV43vqBd+WW7VmOKQTk2Z9VmHgIB2sQ0oo3ljI5BqgcggASmnY7AqaJ0T4uVLLmgffv23sePH0//d/aXr1DumbNkOYiFhEib3haAtFFippANnFTderQik3ZkfIykiza/phtpaIADg4LZh6HZ6McffaTM1PeUv7C0gBDMQamWqXVCyWqNGxFCfAZ+C/hc8D9ZPvmUvps5nc5cOC75eD9tWCVVPaFcx3j6/IFEBOEcR/Tvt9cvpA7Vps1rpZTMvbt3xMH97JlRIfXVyxe0d+8u0T7t3bPT5uNK5t96YwdWhw/uo7DoENq/zwC+R08fMAiEUXGXkpQ9V04+L+9RZ14MPXt0p8JFC0vZmI4dk3gR+tPla1eZCd6hG7eushl4kn7ZuZm27dgsCdLt2kXyomwlbHA9m4ZzmaV98/VXNHbsaOrfvx916dpJIoxgCZKWwowWbEt8qRUqyIXCmlSPAAqATfR5Ss9Vm19HGpfurq1NuWAHfZWddOEdOYD+luHM/HuX38oKbFZQ8lOPRQKhH/v7yev4bQRsoIYHEOlcW2vtOD2sBTDhgNcKfBw/+LJ8eRtmz559ik9hxv8o0GJWknXs2LH93T087gBcMmXKSJ/ygtTsyHB42zesKG4qyYuZbdv1cw1quv6WMWFs7+mqngBAW+UHm88L6UZY8ACFDCodxgJSb3LkybPHy8srYdq0aXn/TZM4o0u5cuP44Z+fMtjoWu5aZyblkSVqmss0ZbOrTtoQg2ZT+jU0Rv1U1b/CAGsTn1nmD6X5hmx/eptfDYr9Ll26qITdKPsqo6FhpsgR7ErXzgK7qlqtunwfJXXPnD9DR47to/UbV0shPPiroDYXM1ASl1+YJtuL509pxYql9OPyJdKW69ixX+1g6CUD1v4D+6RbDRTmB/bvS8Ek/F0BlgFkw0YMYhAZbkDZ29fUrXsHOUcJie2oB4NU7jx5hYVUq16VJk36gkaPGkGt2ramU6dP0v2H9+nmrZt05dol+vX4QVq+8nv6/odFDFxb6fS549SnT1fybOom8ghED', '2025-11-20 18:46:16', NULL, -0.69633391, 34.78636470, '2025-11-20 15:46:16');

-- --------------------------------------------------------

--
-- Table structure for table `meter_installations`
--

CREATE TABLE `meter_installations` (
  `id` int(11) NOT NULL,
  `meter_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `installer_user_id` int(11) NOT NULL,
  `initial_reading` decimal(10,2) DEFAULT 0.00,
  `photo_url` varchar(255) DEFAULT NULL,
  `gps_location` varchar(64) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('waiting_installation','submitted','approved','installed','rejected','cancelled') DEFAULT 'submitted',
  `submitted_at` datetime DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meter_installations`
--

INSERT INTO `meter_installations` (`id`, `meter_id`, `client_id`, `installer_user_id`, `initial_reading`, `photo_url`, `gps_location`, `notes`, `status`, `submitted_at`, `reviewed_by`, `reviewed_at`, `review_notes`) VALUES
(13, 28, 5, 12, 0.00, NULL, NULL, 'Installer assigned by admin', 'waiting_installation', '2025-11-19 21:47:04', NULL, NULL, NULL),
(14, 28, 5, 12, 0.00, '/water_billing_system/public/uploads/installations/install_691e10f072369_mtr reader.jpg', '-0.7,34.78', 'installed', 'installed', '2025-11-19 21:48:16', 5, '2025-11-19 21:48:47', ''),
(15, 24, 5, 12, 0.00, NULL, NULL, 'Installer assigned by admin', 'waiting_installation', '2025-11-20 18:43:44', NULL, NULL, NULL),
(16, 24, 5, 12, 0.00, '/water_billing_system/public/uploads/installations/install_691f377cd33f0_meter2.png', '-0.6963634574400146,34.786351778852286', '', 'installed', '2025-11-20 18:45:00', 5, '2025-11-20 18:45:22', '');

-- --------------------------------------------------------

--
-- Table structure for table `meter_readings`
--

CREATE TABLE `meter_readings` (
  `id` int(11) NOT NULL,
  `meter_id` int(11) NOT NULL,
  `reading_value` decimal(15,3) NOT NULL,
  `reading_date` datetime NOT NULL DEFAULT current_timestamp(),
  `collector_id` int(11) DEFAULT NULL,
  `gps_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude of the meter reading location',
  `gps_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude of the meter reading location',
  `meter_image_id` int(11) DEFAULT NULL,
  `meter_condition` varchar(50) DEFAULT 'normal',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meter_readings`
--

INSERT INTO `meter_readings` (`id`, `meter_id`, `reading_value`, `reading_date`, `collector_id`, `gps_latitude`, `gps_longitude`, `meter_image_id`, `meter_condition`, `notes`, `created_at`) VALUES
(9, 28, 0.000, '2025-11-19 21:48:47', 12, NULL, NULL, 9, 'normal', NULL, '2025-11-19 18:48:47'),
(10, 28, 2.000, '2025-11-19 21:52:10', 12, NULL, NULL, 10, 'normal', 'no issues detected', '2025-11-19 18:52:10'),
(11, 24, 0.000, '2025-11-20 18:45:22', 12, NULL, NULL, 11, 'normal', NULL, '2025-11-20 15:45:22'),
(12, 24, 1.000, '2025-11-20 18:46:16', 12, NULL, NULL, 12, 'normal', '', '2025-11-20 15:46:16');

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_requests`
--

CREATE TABLE `mpesa_requests` (
  `id` int(11) NOT NULL,
  `checkout_request_id` varchar(64) NOT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `transaction_id` varchar(64) DEFAULT NULL,
  `result_desc` text DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mpesa_requests`
--

INSERT INTO `mpesa_requests` (`id`, `checkout_request_id`, `bill_id`, `amount`, `phone_number`, `transaction_id`, `result_desc`, `status`, `created_at`, `updated_at`) VALUES
(4, 'ws_CO_19112025222249481795331020', 7, 200.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-19 19:22:48', '2025-11-19 19:23:15'),
(5, 'ws_CO_19112025223548503795331020', 7, 200.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-19 19:35:48', '2025-11-19 19:36:14'),
(6, 'ws_CO_19112025224644466795331020', 6, 200.00, '254795331020', NULL, 'Request Cancelled by user.', 'failed', '2025-11-19 19:46:43', '2025-11-19 19:47:02'),
(7, 'ws_CO_20112025085902315795331020', 8, 200.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-20 05:58:59', '2025-11-20 05:59:25'),
(8, 'ws_CO_20112025094611299795331020', 9, 200.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-20 06:46:08', '2025-11-20 06:46:34'),
(9, 'ws_CO_20112025184936161795331020', 10, 185.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-20 15:49:36', '2025-11-20 15:50:01'),
(10, 'ws_CO_20112025190519815795331020', 12, 200.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-20 16:05:19', '2025-11-20 16:05:45'),
(11, 'ws_CO_20112025201535125795331020', NULL, 1500.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-20 17:15:34', '2025-11-20 17:16:01'),
(12, 'ws_CO_20112025202652590795331020', 11, 185.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-20 17:26:52', '2025-11-20 17:27:18'),
(13, 'ws_CO_20112025210648299795331020', 14, 200.00, '254795331020', NULL, 'No response from user.', 'failed', '2025-11-20 18:06:48', '2025-11-20 18:07:14'),
(14, 'ws_CO_20112025210734456795331020', 13, 185.00, '254795331020', NULL, 'Request Cancelled by user.', 'failed', '2025-11-20 18:07:34', '2025-11-20 18:07:35');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('service_payment','plan_renewal') NOT NULL,
  `reference_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'M-Pesa STK Push',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(32) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `type`, `reference_id`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `status`, `created_at`, `updated_at`) VALUES
(60, 8, '', 14, 200.00, 'Mpesa STK-Push', 'ws_CO_20112025210648299795331020', '2025-11-20 18:06:44', 'confirmed_and_verified', '2025-11-20 18:06:44', '2025-11-20 18:08:05'),
(61, 8, '', 13, 185.00, 'Mpesa STK-Push', 'ws_CO_20112025210734456795331020', '2025-11-20 18:07:31', 'confirmed_and_verified', '2025-11-20 18:07:31', '2025-11-20 18:07:57');

-- --------------------------------------------------------

--
-- Table structure for table `plan_upgrade_prompts`
--

CREATE TABLE `plan_upgrade_prompts` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `meter_id` int(11) DEFAULT NULL,
  `current_plan_id` int(11) NOT NULL,
  `recommended_plan_id` int(11) NOT NULL,
  `consumption_units` decimal(12,2) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `description`, `cost`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Water Guage Installation', 'Installation for water guages', 2400.00, 1, '2025-07-17 09:42:23', '2025-07-17 09:42:23'),
(3, 'Water Guage Fix', 'All services to do with the water guage system', 1500.00, 1, '2025-07-17 16:33:33', '2025-07-17 16:33:33'),
(4, 'Meter Installation', 'Schedule meter installation', 0.00, 1, '2025-12-01 22:03:04', '2025-12-01 22:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `service_attendances`
--

CREATE TABLE `service_attendances` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `collector_id` int(11) NOT NULL,
  `attendance_date` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `photo_data` longtext DEFAULT NULL,
  `status_update` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_attendances`
--

INSERT INTO `service_attendances` (`id`, `service_request_id`, `collector_id`, `attendance_date`, `notes`, `latitude`, `longitude`, `photo_data`, `status_update`, `created_at`, `updated_at`) VALUES
(7, 18, 9, '2025-12-02 01:08:22', 'good', -0.70000000, 34.78000000, 'data:image/jpeg;base64,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', 'completed', '2025-12-01 22:08:22', '2025-12-01 22:08:22');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `meter_id` int(11) DEFAULT NULL COMMENT 'Foreign key to meters.id for requests related to a specific meter',
  `collector_id` int(11) DEFAULT NULL,
  `request_type` enum('new_connection','meter_repair','billing_inquiry','disconnection','other') NOT NULL,
  `description` text NOT NULL,
  `admin_notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `assigned_to_collector_id` int(11) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `completion_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `client_id`, `service_id`, `meter_id`, `collector_id`, `request_type`, `description`, `admin_notes`, `status`, `assigned_to_collector_id`, `request_date`, `completion_date`, `created_at`, `updated_at`) VALUES
(18, 8, 4, 28, NULL, 'new_connection', 'Meter application confirmed. Schedule installation.', NULL, 'completed', 9, '2025-12-01 22:03:04', NULL, '2025-12-01 22:03:04', '2025-12-01 22:08:22');

-- --------------------------------------------------------

--
-- Table structure for table `sms_notifications`
--

CREATE TABLE `sms_notifications` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `status` enum('pending','sent','failed','delivered') NOT NULL DEFAULT 'pending',
  `reference_id` int(11) DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_notifications`
--

INSERT INTO `sms_notifications` (`id`, `phone_number`, `message`, `type`, `status`, `reference_id`, `response_data`, `created_at`, `updated_at`) VALUES
(1, '254788981020', 'Your meter application has been confirmed. We will schedule installation for meter S00198005.', 'installation_confirmation', 'sent', 27, NULL, '2025-12-01 22:03:04', '2025-12-01 22:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `role` enum('admin','client','collector','support','commercial_manager','finance_manager','meter_reader') NOT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `full_name`, `address`, `contact_phone`, `status`, `role`, `last_login_at`, `created_at`, `updated_at`) VALUES
(5, 'Mulwa', '$2y$10$QWTEF/nqxYGNULp/dJYIAeB5JcvPpqgMNId1J0pdwcx2p5Fy10zdm', 'max.harllan@gmail.com', NULL, NULL, NULL, 'active', 'admin', '2025-12-02 00:30:18', '2025-07-16 11:58:32', '2025-12-02 00:30:18'),
(8, 'Milanoi', '$2y$10$/fCDWMgdSso.xYiSKSk1RuYHGy0cTD6JaobHBIPLHMf.Fy3jTZOvW', 'mulwamaxwell16@gmail.com', 'Mulwa Maxwell', 'kitui,90200', '254788981020', 'active', 'client', '2025-12-02 00:32:40', '2025-07-17 09:40:04', '2025-12-02 00:32:40'),
(9, 'Collector1', '$2y$10$eSFgms6UomAPAD6j//BQROndzGFU1UBJ043Jl97TGTum9dXY7gOMe', 'hardin.lanoi01@gmail.com', 'Peace Ngii', 'kitui', '0793661931', 'active', 'collector', '2025-12-02 00:14:50', '2025-07-21 12:40:05', '2025-12-02 00:14:50'),
(10, 'Mwilwa', '$2y$10$mxoBagVeeRa9Ed4bNBYbgeoztkrs6bHBSFwODoWHPDQagaRGNSUxa', 'mwilwa123@gmail.com', 'Mwilwa Millan', 'Kitui County', '0795331020', 'active', 'commercial_manager', '2025-12-02 00:39:51', '2025-07-29 13:09:32', '2025-12-02 00:39:51'),
(11, 'Kadenga', '$2y$10$wdgzhJXwlVusR1sqtcunPu1OwF8b17LcapI1cqVeuoukAvIsSjSZ2', 'kad123@gmail.com', 'Post Kadenga', 'kitui,90200', '254788981020', 'active', 'finance_manager', '2025-12-02 00:32:29', '2025-08-31 11:51:44', '2025-12-02 00:32:29'),
(12, 'MTR1', '$2y$10$4iUbdYP2xvzSW8K7AzJX4ugP.r/tYwVw/YCiJ0Ukz8XXJ9/DjvqRC', 'met123@gmail.com', 'Mulamua Kalenga', 'Kisii,40200', '0788567081', 'active', 'meter_reader', '2025-12-02 00:32:54', '2025-08-31 13:37:53', '2025-12-02 00:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `verified_meters`
--

CREATE TABLE `verified_meters` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `meter_id` int(11) NOT NULL,
  `meter_serial` varchar(255) NOT NULL,
  `meter_status` varchar(64) NOT NULL,
  `initial_reading` decimal(10,2) DEFAULT 0.00,
  `current_reading` decimal(10,2) DEFAULT 0.00,
  `verification_date` datetime NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verified_meters`
--

INSERT INTO `verified_meters` (`id`, `client_id`, `client_name`, `meter_id`, `meter_serial`, `meter_status`, `initial_reading`, `current_reading`, `verification_date`, `admin_id`, `admin_name`, `created_at`) VALUES
(9, 8, 'Milanoi', 28, 'S00198005', 'installed', 0.00, 0.00, '2025-11-19 19:46:57', 5, 'Mulwa', '2025-11-19 21:46:57'),
(10, 8, 'Milanoi', 24, 'S21PR001', 'installed', 0.00, 0.00, '2025-11-20 16:43:11', 5, 'Mulwa', '2025-11-20 18:43:11');

-- --------------------------------------------------------

--
-- Table structure for table `verified_payments`
--

CREATE TABLE `verified_payments` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_type` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verified_payments`
--

INSERT INTO `verified_payments` (`id`, `payment_id`, `client_name`, `amount_paid`, `payment_date`, `payment_type`) VALUES
(4, 61, 'Mulwa Maxwell', 185.00, '2025-11-20 21:07:31', 'bill_payment'),
(5, 60, 'Mulwa Maxwell', 200.00, '2025-11-20 21:06:44', 'bill_payment');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billing_plans`
--
ALTER TABLE `billing_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_name` (`plan_name`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `meter_id` (`meter_id`),
  ADD KEY `reading_id_start` (`reading_id_start`),
  ADD KEY `reading_id_end` (`reading_id_end`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `contact_email` (`contact_email`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `client_bills`
--
ALTER TABLE `client_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_user_id` (`client_user_id`),
  ADD KEY `idx_bill_id` (`bill_id`);

--
-- Indexes for table `client_plans`
--
ALTER TABLE `client_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `client_services`
--
ALTER TABLE `client_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `fk_client_service_request` (`service_request_id`),
  ADD KEY `fk_client_service_meter` (`meter_id`);

--
-- Indexes for table `meters`
--
ALTER TABLE `meters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `meter_applications`
--
ALTER TABLE `meter_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `meter_id` (`meter_id`);

--
-- Indexes for table `meter_images`
--
ALTER TABLE `meter_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `collector_id` (`collector_id`);

--
-- Indexes for table `meter_installations`
--
ALTER TABLE `meter_installations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_meter` (`meter_id`),
  ADD KEY `idx_installer` (`installer_user_id`);

--
-- Indexes for table `meter_readings`
--
ALTER TABLE `meter_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meter_id` (`meter_id`),
  ADD KEY `collector_id` (`collector_id`);

--
-- Indexes for table `mpesa_requests`
--
ALTER TABLE `mpesa_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checkout_request_id` (`checkout_request_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `status` (`status`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_transaction_id` (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `plan_upgrade_prompts`
--
ALTER TABLE `plan_upgrade_prompts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client` (`client_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `service_attendances`
--
ALTER TABLE `service_attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_request_id` (`service_request_id`),
  ADD KEY `collector_id` (`collector_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to_collector_id` (`assigned_to_collector_id`),
  ADD KEY `fk_service_requests_collector` (`collector_id`),
  ADD KEY `fk_service_requests_client_id` (`client_id`),
  ADD KEY `fk_service_request_meter` (`meter_id`);

--
-- Indexes for table `sms_notifications`
--
ALTER TABLE `sms_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_number` (`phone_number`),
  ADD KEY `status` (`status`),
  ADD KEY `reference_id` (`reference_id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `verified_meters`
--
ALTER TABLE `verified_meters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_meter_id` (`meter_id`);

--
-- Indexes for table `verified_payments`
--
ALTER TABLE `verified_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_payment_id` (`payment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing_plans`
--
ALTER TABLE `billing_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `client_bills`
--
ALTER TABLE `client_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `client_plans`
--
ALTER TABLE `client_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `client_services`
--
ALTER TABLE `client_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `meters`
--
ALTER TABLE `meters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `meter_applications`
--
ALTER TABLE `meter_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `meter_images`
--
ALTER TABLE `meter_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `meter_installations`
--
ALTER TABLE `meter_installations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `meter_readings`
--
ALTER TABLE `meter_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mpesa_requests`
--
ALTER TABLE `mpesa_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `plan_upgrade_prompts`
--
ALTER TABLE `plan_upgrade_prompts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_attendances`
--
ALTER TABLE `service_attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `sms_notifications`
--
ALTER TABLE `sms_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `verified_meters`
--
ALTER TABLE `verified_meters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `verified_payments`
--
ALTER TABLE `verified_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`meter_id`) REFERENCES `meters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`reading_id_start`) REFERENCES `meter_readings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bills_ibfk_4` FOREIGN KEY (`reading_id_end`) REFERENCES `meter_readings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clients_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `billing_plans` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `client_plans`
--
ALTER TABLE `client_plans`
  ADD CONSTRAINT `client_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_plans_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `billing_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_services`
--
ALTER TABLE `client_services`
  ADD CONSTRAINT `client_services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_client_service_meter` FOREIGN KEY (`meter_id`) REFERENCES `meters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_client_service_request` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `meters`
--
ALTER TABLE `meters`
  ADD CONSTRAINT `meters_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `meter_applications`
--
ALTER TABLE `meter_applications`
  ADD CONSTRAINT `meter_applications_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meter_applications_ibfk_2` FOREIGN KEY (`meter_id`) REFERENCES `meters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meter_images`
--
ALTER TABLE `meter_images`
  ADD CONSTRAINT `meter_images_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meter_images_ibfk_2` FOREIGN KEY (`collector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meter_readings`
--
ALTER TABLE `meter_readings`
  ADD CONSTRAINT `meter_readings_ibfk_1` FOREIGN KEY (`meter_id`) REFERENCES `meters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meter_readings_ibfk_2` FOREIGN KEY (`collector_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_attendances`
--
ALTER TABLE `service_attendances`
  ADD CONSTRAINT `service_attendances_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_attendances_ibfk_2` FOREIGN KEY (`collector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `fk_service_request_meter` FOREIGN KEY (`meter_id`) REFERENCES `meters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_service_requests_client_id` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service_requests_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`assigned_to_collector_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
