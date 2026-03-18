-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 02:27 PM
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
-- Database: `scholarship_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `fullname`) VALUES
(1, 'admin@example.com', '$2y$10$9B39YCd2rgv/I7nUl7z..un61hZRaT4pRykKS.r2YESIBuv0Gw9Ta', 'Super Admin');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated') DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `alt_contact` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `nationality` varchar(50) DEFAULT 'Filipino',
  `religion` varchar(50) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `gpa` varchar(10) DEFAULT NULL,
  `parent_occupation` varchar(100) DEFAULT NULL,
  `family_income` varchar(50) DEFAULT NULL,
  `essay` text DEFAULT NULL,
  `goals` text DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `reviewer_id` int(11) DEFAULT NULL,
  `reviewer_name` varchar(100) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `username`, `last_name`, `first_name`, `middle_name`, `suffix`, `date_of_birth`, `place_of_birth`, `gender`, `civil_status`, `contact`, `alt_contact`, `email`, `address`, `nationality`, `religion`, `school`, `course`, `year_level`, `gpa`, `parent_occupation`, `family_income`, `essay`, `goals`, `document`, `created_at`, `status`, `reviewer_id`, `reviewer_name`, `reviewed_at`) VALUES
(1, 1, 'Aerll Kian Villalon', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9323322097', NULL, 'durangogerald2002@gmail.com', 'Taboc Danao City', 'Filipino', NULL, 'Cebu Technological University Danao Campus', 'BSIT', '2nd Year', '1.0', 'Civil Engineer', 'Below 100,000', 'I need money to sustain my educational needs.', 'To finish college.', 'College.jpg', '2026-02-28 09:06:06', 'Approved', NULL, NULL, NULL),
(2, 4, 'Hannah Lucero', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09109466040', NULL, 'lucero@gmail.com', 'Guinsay', 'Filipino', NULL, 'CTU', 'BSIT', '2nd Year', '1.0', NULL, '100000', 'Because I need money for education.', NULL, 'TheGlobalEconomyQuiz.PNG', '2026-03-11 00:12:27', 'Approved', 2, 'Rose', '2026-03-17 15:17:13'),
(3, 2, 'Alexandra Rose', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09109466040', NULL, 'alex@gmail.com', 'Taboc', 'Filipino', NULL, 'CTU', 'BSIT', '4th Year', '1.0', NULL, '100,000 - 200,000', 'yes', NULL, 'VILLALON GEC-ES NOTES GROUP 1-5.docx', '2026-03-17 13:32:09', 'Approved', 2, 'Rose', '2026-03-17 15:17:06'),
(4, 8, 'Villa', 'Villalon', 'Aerll Kian', 'Manggas', '', '2005-07-17', 'Cebu City', 'Male', 'Single', '09109466040', '', 'villa@gmail.com', 'Taboc Danao City', 'Filipino', 'Roman Catholic', 'Cebu Technological University - Danao Campus', 'BSIT', '1st Year', '1.0', NULL, 'Below 100,000', 'I deserve it because I need money.', NULL, 'Assignment#2.pdf', '2026-03-18 12:55:09', 'Approved', 2, 'Rose', '2026-03-18 14:04:45');

-- --------------------------------------------------------

--
-- Table structure for table `reviewers`
--

CREATE TABLE `reviewers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviewers`
--

INSERT INTO `reviewers` (`id`, `email`, `password`, `fullname`, `created_at`) VALUES
(2, 'rose@gmail.com', '$2y$10$niGY0Gwo/eGAUmjFPfgnK.QR2e1/IlWPLOjZzRpETPfusftVBwaue', 'Rose', '2026-03-17 14:16:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Aerll Villalon', 'durangogerald2002@gmail.com', '$2y$10$Mm83dck8iBbFaSiB.Nl2TuUPFS/dqy6H27ndw4RMml6UTnFhnesTO', '2026-02-28 08:31:12'),
(2, 'Alexandra Rose', 'alex@gmail.com', '$2y$10$AKZmgz926qXi1kXt7HrEneVX4H36bfHPAmIjKEV.yd30N3Vhl5rKq', '2026-03-10 15:41:20'),
(4, 'Hannah Lucero', 'lucero@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$S0kyS0Y4dGQ5eVpyOWd2MQ$fQBmI9/g1TQUhp3dqHbE+1YRX+7eSlGgfPnpNQpERac', '2026-03-11 00:08:35'),
(5, 'Alexandra Rose Sabello', 'sabello@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$UGhCdS4wLnVkSUYyTmxvVA$rlBnXQTqN4Oy1n59JuqK+meSkOgUpWSGrqVgaSdYOFI', '2026-03-11 00:38:37'),
(6, 'Alexandra', 'alexandra@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$S29DdDFFTXYzYVpKNG93eA$9MOqO9MhrRjniPQnjC+WVjEEs/1WOxh1tp22ksX0194', '2026-03-11 00:41:11'),
(7, 'Kian', 'kian@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$NUZwMVNkUmNpbDI4R0tIcg$R1kOAF3OZ8Z3uvGbxwAqx8B1YgZEY6YSXX7Jl+SMNX0', '2026-03-11 00:42:38'),
(8, 'Villa', 'villa@gmail.com', '$2y$10$cckU5ODszl4.0nvanBGnKOVqHHJbbrNY0lCPCroWmmE3HR4R.24ve', '2026-03-18 12:37:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reviewer_id` (`reviewer_id`),
  ADD KEY `idx_last_name` (`last_name`),
  ADD KEY `idx_first_name` (`first_name`);

--
-- Indexes for table `reviewers`
--
ALTER TABLE `reviewers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviewers`
--
ALTER TABLE `reviewers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `reviewers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
