-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 07:05 PM
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
  `review_notes` text DEFAULT NULL,
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

INSERT INTO `applications` (`id`, `user_id`, `username`, `last_name`, `first_name`, `middle_name`, `suffix`, `date_of_birth`, `place_of_birth`, `gender`, `civil_status`, `contact`, `alt_contact`, `email`, `address`, `nationality`, `religion`, `school`, `course`, `year_level`, `gpa`, `parent_occupation`, `family_income`, `essay`, `review_notes`, `goals`, `document`, `created_at`, `status`, `reviewer_id`, `reviewer_name`, `reviewed_at`) VALUES
(5, 9, 'Kian', 'Villalon', 'Aerll', 'Kian', '', '2026-03-19', 'Cebu City', 'Female', 'Single', '09109466040', '', 'kian@gmail.com', 'GXR3+M3W', 'Filipino', 'Roman Catholic', 'Cebu Technological University Danao Campus', 'BSIT', '2nd Year', '1.01', NULL, '100,000 - 200,000', 'ye', 'wrong', NULL, 'GEC TCW - Market Integration Notes.pdf', '2026-03-18 16:20:56', 'Approved', 2, 'Alexandra Rose', '2026-03-18 19:00:57'),
(6, 8, 'Villa', 'Villalon', 'Aerll', 'Kian', '', '2005-05-19', 'Cebu City', 'Male', 'Single', '09109466040', '', 'villa@gmail.com', 'GXR3+M3W', 'Filipino', 'Roman Catholic', 'Cebu Technological University - Danao Campus', 'BSIT', '4th Year', '1', NULL, 'Below 100,000', 'asdasd', '', NULL, 'AP 3 - ASSIGNMENT #2.pdf', '2026-03-18 16:28:51', 'Approved', 2, 'Alexandra Rose', '2026-03-18 19:00:50');

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
(2, 'rose@gmail.com', '$2y$10$niGY0Gwo/eGAUmjFPfgnK.QR2e1/IlWPLOjZzRpETPfusftVBwaue', 'Alexandra Rose', '2026-03-17 14:16:31'),
(3, 'lucero@gmail.com', '$2y$10$xHPjBxgBdgB7kuFFo3b9Yu1Nhrbvhtir15MBNgnZ9W2FegxiIlbfG', 'Hannah Lucero', '2026-03-18 17:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `status`) VALUES
(8, 'Villa', 'villa@gmail.com', '$2y$10$cckU5ODszl4.0nvanBGnKOVqHHJbbrNY0lCPCroWmmE3HR4R.24ve', '2026-03-18 12:37:28', 'Approved'),
(9, 'Kian', 'kian@gmail.com', '$2y$10$/CnKOMxWZ6N9HklHiYPV6.9dtC1973C/94PGSD3D9ctFfplqH/fYq', '2026-03-18 16:19:42', 'Pending');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviewers`
--
ALTER TABLE `reviewers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
