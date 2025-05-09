-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 01, 2025 at 09:57 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `invest`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `assigned_to` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_id`, `name`, `type`, `cost`, `purchase_date`, `status`, `assigned_to`, `location`, `created_at`, `user_id`) VALUES
(1, 'AST-001', 'Dell XPS 15', 'Laptop', 145000.00, '2023-05-15', 'Active', 'John Doe', 'Headquarters', '2023-05-14 23:00:00', 1),
(2, 'AST-002', 'iPhone 14 Pro', 'Mobile', 89900.00, '2023-06-22', 'Active', 'Jane Smith', 'Headquarters', '2023-06-21 23:00:00', 1),
(3, 'AST-003', 'Cisco Switch 3850', 'Network', 325000.00, '2023-04-10', 'Active', NULL, 'Server Room', '2023-04-09 23:00:00', 1),
(4, 'AST-004', 'Dell PowerEdge R740', 'Server', 650000.00, '2023-03-05', 'Active', NULL, 'Server Room', '2023-03-04 23:00:00', 1),
(5, 'AST-005', 'Microsoft Surface Pro', 'Tablet', 98000.00, '2023-07-18', 'Active', 'Mike Johnson', 'Branch Office', '2023-07-17 23:00:00', 1),
(6, 'AST-006', 'HP LaserJet Pro', 'Printer', 42500.00, '2023-02-28', 'Active', NULL, 'Office Floor', '2023-02-27 23:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `audits`
--

CREATE TABLE `audits` (
  `id` int(11) NOT NULL,
  `audit_id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `assigned_to` varchar(255) DEFAULT NULL,
  `due_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audits`
--

INSERT INTO `audits` (`id`, `audit_id`, `title`, `description`, `type`, `status`, `assigned_to`, `due_date`, `completed_date`, `created_at`) VALUES
(1, 'AUD-001', 'Annual Hardware Inventory', 'Complete inventory check of all hardware assets', 'Hardware', 'Pending', 'John Doe', '2023-08-30', NULL, '2023-07-15 10:00:00'),
(2, 'AUD-002', 'Software License Compliance', 'Verify all software licenses are valid and compliant', 'Software', 'Pending', 'Jane Smith', '2023-09-15', NULL, '2023-07-20 10:00:00'),
(3, 'AUD-003', 'Network Security Assessment', 'Assess network security vulnerabilities', 'Security', 'Pending', 'Mike Johnson', '2023-09-30', NULL, '2023-07-25 10:00:00'),
(4, 'AUD-004', 'Cloud Resources Audit', 'Audit all cloud resources for optimization', 'Cloud', 'Pending', 'Sarah Williams', '2023-10-15', NULL, '2023-08-01 10:00:00'),
(5, 'AUD-005', 'Mobile Device Management', 'Audit mobile device inventory and compliance', 'Mobile', 'Pending', 'David Brown', '2023-10-30', NULL, '2023-08-05 10:00:00'),
(6, 'AUD-006', 'Data Center Equipment', 'Physical audit of data center equipment', 'Hardware', 'Pending', 'Robert Davis', '2023-11-15', NULL, '2023-08-10 10:00:00'),
(7, 'AUD-007', 'Software Usage Analysis', 'Analyze software usage patterns for optimization', 'Software', 'Pending', 'Emily Wilson', '2023-11-30', NULL, '2023-08-15 10:00:00'),
(8, 'AUD-008', 'Disaster Recovery Test', 'Test disaster recovery procedures', 'Security', 'Pending', 'Michael Thompson', '2023-12-15', NULL, '2023-08-20 10:00:00'),
(9, 'AUD-009', 'IT Asset Disposal Audit', 'Audit proper disposal of retired IT assets', 'Compliance', 'Pending', 'Jennifer Garcia', '2023-12-30', NULL, '2023-08-25 10:00:00'),
(10, 'AUD-010', 'Vendor Contract Review', 'Review all IT vendor contracts', 'Compliance', 'Pending', 'Christopher Martinez', '2024-01-15', NULL, '2023-09-01 10:00:00'),
(11, 'AUD-011', 'Security Policy Compliance', 'Audit compliance with security policies', 'Security', 'Pending', 'Amanda Robinson', '2024-01-30', NULL, '2023-09-05 10:00:00'),
(12, 'AUD-012', 'IT Budget Review', 'Review IT budget allocation and spending', 'Financial', 'Pending', 'Daniel Clark', '2024-02-15', NULL, '2023-09-10 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `investments`
--

CREATE TABLE `investments` (
  `id` int(11) NOT NULL,
  `project_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `budget` decimal(12,2) NOT NULL,
  `spent` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `roi_percentage` decimal(5,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Planned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `investments`
--

INSERT INTO `investments` (`id`, `project_id`, `name`, `category`, `department`, `budget`, `spent`, `current_value`, `roi_percentage`, `start_date`, `end_date`, `status`, `created_at`, `user_id`) VALUES
(1, 'INV-001', 'Server Infrastructure Upgrade', 'Hardware', 'IT', 350000.00, 327500.00, 388000.00, 18.50, '2023-01-15', '2023-03-30', 'Completed', '2023-01-15 04:30:00', 1),
(2, 'INV-002', 'Cloud Migration Phase 1', 'Cloud Services', 'IT', 250000.00, 275000.00, 309000.00, 12.30, '2023-02-01', '2023-04-15', 'Completed', '2023-02-01 04:30:00', 1),
(3, 'INV-003', 'Employee Laptop Refresh', 'Hardware', 'Operations', 185000.00, 167500.00, 205000.00, 22.70, '2023-03-10', '2023-06-30', 'In Progress', '2023-03-10 04:30:00', 1),
(4, 'INV-004', 'Security Infrastructure', 'Security', 'IT', 220000.00, 85000.00, 85000.00, NULL, '2023-04-05', '2023-08-15', 'In Progress', '2023-04-05 04:30:00', 1),
(5, 'INV-005', 'CRM System Implementation', 'Software', 'Marketing', 150000.00, 0.00, 0.00, NULL, '2023-07-01', '2023-10-31', 'Planned', '2023-05-20 04:30:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`) VALUES
(1, 'Vishnu PS', 'psvishnu888@gmail.com', '$2y$10$hoFBILy4A5SbY15m3WOPNex3Ul7RR8iE0TLWeAss5s0BjU.fumlRq'),
(2, 'suhas', 'suhas@gmail.com', '$2y$10$epw4.3UVzTeF1.ppUrBTReI2b8COM2Yc9b0RQd51Q3Wr578iNiUzS'),
(3, 'vishruth', 'vishruth@gmail.com', '$2y$10$lI4hW1sErBrqLkmmc8k.VejLEyTBboTfWGVfTEALCa9CHmJiWYVcC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_id` (`asset_id`);

--
-- Indexes for table `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `audit_id` (`audit_id`);

--
-- Indexes for table `investments`
--
ALTER TABLE `investments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `audits`
--
ALTER TABLE `audits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
