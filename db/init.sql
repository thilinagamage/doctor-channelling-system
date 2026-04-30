-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 06:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `channelling_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','receptionist') DEFAULT 'receptionist',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `full_name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@channelling.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', 'superadmin', '2026-04-27 04:29:31'),
(2, 'Receptionist User', 'reception@channelling.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', 'receptionist', '2026-04-27 04:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `slot_id`, `booked_at`, `status`, `notes`) VALUES
(1, 1, 1, '2026-04-27 04:29:31', 'confirmed', 'Regular checkup'),
(2, 2, 10, '2026-04-27 04:29:31', 'pending', 'Skin consultation'),
(3, 3, 16, '2026-04-27 04:29:31', 'confirmed', 'Headache issues'),
(4, 4, 21, '2026-04-27 04:29:31', 'confirmed', 'Child vaccination'),
(5, 5, 33, '2026-04-27 04:29:31', 'pending', 'General health check'),
(7, 11, 24, '2026-04-27 09:59:28', 'confirmed', 'retert'),
(8, 11, 23, '2026-04-27 10:00:11', 'cancelled', 'ytrtyrty'),
(9, 11, 22, '2026-04-27 10:01:00', 'cancelled', 'fdgdfg'),
(10, 11, 49, '2026-04-27 10:02:44', 'cancelled', 'yretret'),
(11, 11, 47, '2026-04-27 10:03:34', 'confirmed', ''),
(12, 11, 45, '2026-04-27 10:03:55', 'confirmed', '');

-- --------------------------------------------------------

--
-- Table structure for table `channelling_centers`
--

CREATE TABLE `channelling_centers` (
  `center_id` int(11) NOT NULL,
  `center_name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `channelling_centers`
--

INSERT INTO `channelling_centers` (`center_id`, `center_name`, `address`, `phone`, `email`, `created_at`) VALUES
(1, 'City Health Center', '123 Main Street, Colombo 01', '+94 11 234 5678', 'cityhealth@channelling.lk', '2026-04-27 04:29:31'),
(2, 'Metro Medical Centre', '456 Galle Road, Colombo 03', '+94 11 345 6789', 'metro@channelling.lk', '2026-04-27 04:29:31'),
(3, 'Lanka Private Hospital', '789 Kandy Road, Kandy', '+94 81 456 7890', 'lanka@channelling.lk', '2026-04-27 04:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `qualification` varchar(200) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT 'default-doctor.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `full_name`, `specialization`, `email`, `phone`, `qualification`, `bio`, `photo`, `created_at`) VALUES
(1, 'Dr. Nimal Perera', 'Cardiologist', 'nimal.perera@hospital.lk', '+94 71 234 5678', 'MBBS, MD (Cardiology), FACC', 'Dr. Nimal Perera is a highly experienced cardiologist with over 15 years of experience in treating heart conditions. He specializes in interventional cardiology and preventive heart care.', 'doctor1.jpg', '2026-04-27 04:29:31'),
(2, 'Dr. Sarath Silva', 'Dermatologist', 'sarath.silva@hospital.lk', '+94 71 345 6789', 'MBBS, MD (Dermatology), FAAD', 'Dr. Sarath Silva is a renowned dermatologist specializing in skin cancer treatment, cosmetic dermatology, and general skin conditions. He has treated thousands of patients with various skin ailments.', 'doctor2.jpg', '2026-04-27 04:29:31'),
(3, 'Dr. Kamal Wickramasinghe', 'Neurologist', 'kamal.wick@hospital.lk', '+94 71 456 7890', 'MBBS, MD (Neurology), PhD', 'Dr. Kamal Wickramasinghe is a leading neurologist in Sri Lanka with expertise in treating brain and spine disorders. He has performed numerous successful neurosurgeries.', 'doctor3.jpg', '2026-04-27 04:29:31'),
(4, 'Dr. Anjali Fernando', 'Pediatrician', 'anjali.f@hospital.lk', '+94 71 567 8902', 'MBBS, MD (Pediatrics), DCH', 'Dr. Anjali Fernando is a compassionate pediatrician dedicated to childrens health. She provides comprehensive care for infants, children, and adolescents.', 'doctor4.jpg', '2026-04-27 04:29:31'),
(5, 'Dr. Roshan Jayawardena', 'General Physician', 'roshan.j@hospital.lk', '+94 71 678 9012', 'MBBS, MD (General Medicine)', 'Dr. Roshan Jayawardena is a trusted general physician with extensive experience in diagnosing and treating common illnesses and chronic conditions.', 'doctor5.jpg', '2026-04-27 04:29:31'),
(6, 'Thilina Gamage', 'Neurologist', 'abc@gmail.com', '12345679', 'MBBS', 'abcfd', 'default-doctor.png', '2026-04-27 09:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_center`
--

CREATE TABLE `doctor_center` (
  `dc_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_center`
--

INSERT INTO `doctor_center` (`dc_id`, `doctor_id`, `center_id`, `created_at`) VALUES
(1, 1, 1, '2026-04-27 04:29:31'),
(2, 1, 2, '2026-04-27 04:29:31'),
(3, 2, 1, '2026-04-27 04:29:31'),
(4, 2, 3, '2026-04-27 04:29:31'),
(5, 3, 2, '2026-04-27 04:29:31'),
(6, 3, 3, '2026-04-27 04:29:31'),
(7, 4, 1, '2026-04-27 04:29:31'),
(8, 4, 2, '2026-04-27 04:29:31'),
(9, 4, 3, '2026-04-27 04:29:31'),
(10, 5, 1, '2026-04-27 04:29:31'),
(11, 5, 2, '2026-04-27 04:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `type` enum('confirmation','reminder','cancellation') DEFAULT 'confirmation',
  `channel` enum('email','sms') DEFAULT 'email',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `appointment_id`, `type`, `channel`, `sent_at`, `status`) VALUES
(1, 1, 'confirmation', 'email', '2026-04-27 04:29:31', 'sent'),
(2, 1, 'reminder', 'sms', '2026-04-27 04:29:31', 'sent'),
(3, 2, 'confirmation', 'email', '2026-04-27 04:29:31', 'sent'),
(4, 3, 'confirmation', 'email', '2026-04-27 04:29:31', 'sent'),
(5, 4, 'confirmation', 'email', '2026-04-27 04:29:31', 'sent');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'male',
  `address` text DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `full_name`, `email`, `password_hash`, `phone`, `date_of_birth`, `gender`, `address`, `registered_at`) VALUES
(1, 'John Doe', 'john@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 123 4567', '1990-05-15', 'male', '12/A, Colombo 07', '2026-04-27 04:29:31'),
(2, 'Mary Perera', 'mary@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 234 5678', '1985-08-22', 'female', '25/B, Kandy', '2026-04-27 04:29:31'),
(3, 'Alex Fernando', 'alex@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 345 6789', '1995-03-10', 'male', '45, Galle', '2026-04-27 04:29:31'),
(4, 'Priya De Silva', 'priya@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 456 7890', '1988-12-05', 'female', '78, Negombo', '2026-04-27 04:29:31'),
(5, 'Kushan Mendis', 'kushan@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 567 8901', '1992-07-20', 'male', '33, Matara', '2026-04-27 04:29:31'),
(6, 'Sasha Nielsen', 'sasha@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 678 9012', '1998-01-30', 'female', '56, Jaffna', '2026-04-27 04:29:31'),
(7, 'Thilan Jayasinghe', 'thilan@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 789 0123', '1982-09-12', 'male', '89, Anuradhapura', '2026-04-27 04:29:31'),
(8, 'Dilini Fernando', 'dilini@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 890 1234', '1993-04-25', 'female', '12, Trincomalee', '2026-04-27 04:29:31'),
(9, 'Imesh Karunaratne', 'imesh@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 901 2345', '1996-11-08', 'male', '67, Batticaloa', '2026-04-27 04:29:31'),
(10, 'Nethmi Dissanayake', 'nethmi@example.com', '$2y$12$qO12dNatnGECg0usp5S64.9HHcwnPSvdP0BO7x/4e37zpUhqZQbXK', '+94 77 012 3456', '2000-06-18', 'female', '90, Polonnaruwa', '2026-04-27 04:29:31'),
(11, 'Thilina Gamage', 'abc1@gmail.com', '$2y$10$2X3i0TDkoN.mz0xzuZEyUu7XJCH4/5QLQoA2upCLXlkfD8dGeOnlu', '123456457', '1998-05-26', 'male', 'rtyrty', '2026-04-27 09:54:54');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 1500.00,
  `method` enum('cash','card','online') DEFAULT 'cash',
  `status` enum('paid','unpaid') DEFAULT 'unpaid',
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `appointment_id`, `amount`, `method`, `status`, `paid_at`) VALUES
(1, 1, 2500.00, 'card', 'paid', '2026-04-27 04:29:31'),
(2, 3, 3000.00, 'cash', 'paid', '2026-04-27 04:29:31'),
(3, 4, 1500.00, 'online', 'paid', '2026-04-27 04:29:31'),
(4, 7, 1500.00, 'card', 'paid', '2026-04-27 09:59:38'),
(5, 8, 1500.00, 'cash', 'unpaid', '2026-04-27 10:00:17'),
(6, 9, 1500.00, 'online', 'unpaid', '2026-04-27 10:01:42'),
(7, 10, 1500.00, 'cash', 'unpaid', NULL),
(8, 11, 1500.00, 'cash', 'unpaid', NULL),
(9, 12, 1500.00, 'card', 'paid', '2026-04-27 10:04:00');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `slot_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('available','booked','cancelled') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`slot_id`, `doctor_id`, `center_id`, `slot_date`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 1, 1, '2026-04-28', '09:00:00', '10:00:00', 'available', '2026-04-27 04:29:31'),
(2, 1, 1, '2026-04-28', '10:00:00', '11:00:00', 'available', '2026-04-27 04:29:31'),
(3, 1, 1, '2026-04-28', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(4, 1, 1, '2026-04-29', '09:00:00', '10:00:00', 'available', '2026-04-27 04:29:31'),
(5, 1, 1, '2026-04-29', '10:00:00', '11:00:00', 'available', '2026-04-27 04:29:31'),
(6, 1, 1, '2026-04-30', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(7, 1, 2, '2026-04-28', '11:00:00', '12:00:00', 'available', '2026-04-27 04:29:31'),
(8, 1, 2, '2026-04-29', '11:00:00', '12:00:00', 'available', '2026-04-27 04:29:31'),
(9, 2, 1, '2026-04-28', '08:00:00', '09:00:00', 'available', '2026-04-27 04:29:31'),
(10, 2, 1, '2026-04-28', '09:00:00', '10:00:00', 'available', '2026-04-27 04:29:31'),
(11, 2, 1, '2026-04-29', '08:00:00', '09:00:00', 'available', '2026-04-27 04:29:31'),
(12, 2, 1, '2026-04-30', '15:00:00', '16:00:00', 'available', '2026-04-27 04:29:31'),
(13, 2, 3, '2026-04-28', '10:00:00', '11:00:00', 'available', '2026-04-27 04:29:31'),
(14, 2, 3, '2026-04-29', '10:00:00', '11:00:00', 'available', '2026-04-27 04:29:31'),
(15, 3, 2, '2026-04-28', '09:00:00', '10:00:00', 'available', '2026-04-27 04:29:31'),
(16, 3, 2, '2026-04-28', '11:00:00', '12:00:00', 'available', '2026-04-27 04:29:31'),
(17, 3, 2, '2026-04-29', '09:00:00', '10:00:00', 'available', '2026-04-27 04:29:31'),
(18, 3, 2, '2026-04-30', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(19, 3, 3, '2026-04-28', '08:00:00', '09:00:00', 'available', '2026-04-27 04:29:31'),
(20, 3, 3, '2026-04-29', '08:00:00', '09:00:00', 'available', '2026-04-27 04:29:31'),
(21, 4, 1, '2026-04-28', '08:00:00', '09:00:00', 'available', '2026-04-27 04:29:31'),
(22, 4, 1, '2026-04-28', '09:00:00', '10:00:00', 'available', '2026-04-27 04:29:31'),
(23, 4, 1, '2026-04-28', '10:00:00', '11:00:00', 'available', '2026-04-27 04:29:31'),
(24, 4, 1, '2026-04-29', '08:00:00', '09:00:00', 'booked', '2026-04-27 04:29:31'),
(26, 4, 2, '2026-04-28', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(27, 4, 2, '2026-04-29', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(28, 4, 3, '2026-04-28', '11:00:00', '12:00:00', 'available', '2026-04-27 04:29:31'),
(29, 4, 3, '2026-04-29', '11:00:00', '12:00:00', 'available', '2026-04-27 04:29:31'),
(30, 5, 1, '2026-04-28', '07:00:00', '08:00:00', 'available', '2026-04-27 04:29:31'),
(31, 5, 1, '2026-04-28', '08:00:00', '09:00:00', 'available', '2026-04-27 04:29:31'),
(32, 5, 1, '2026-04-28', '09:00:00', '10:00:00', 'available', '2026-04-27 04:29:31'),
(33, 5, 1, '2026-04-28', '10:00:00', '11:00:00', 'available', '2026-04-27 04:29:31'),
(34, 5, 1, '2026-04-28', '11:00:00', '12:00:00', 'available', '2026-04-27 04:29:31'),
(35, 5, 1, '2026-04-29', '07:00:00', '08:00:00', 'available', '2026-04-27 04:29:31'),
(36, 5, 1, '2026-04-29', '08:00:00', '09:00:00', 'available', '2026-04-27 04:29:31'),
(37, 5, 1, '2026-04-30', '07:00:00', '08:00:00', 'available', '2026-04-27 04:29:31'),
(38, 5, 2, '2026-04-28', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(39, 5, 2, '2026-04-29', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(40, 5, 2, '2026-04-30', '14:00:00', '15:00:00', 'available', '2026-04-27 04:29:31'),
(41, 4, 1, '2026-04-27', '10:01:00', '22:01:00', 'available', '2026-04-27 04:31:12'),
(42, 4, 1, '2026-04-27', '14:57:00', '16:59:00', 'available', '2026-04-27 09:27:26'),
(43, 4, 1, '2026-04-29', '15:58:00', '04:59:00', 'available', '2026-04-27 09:27:26'),
(44, 4, 1, '2026-04-27', '14:57:00', '16:59:00', 'available', '2026-04-27 09:28:39'),
(45, 4, 1, '2026-04-29', '15:58:00', '04:59:00', 'booked', '2026-04-27 09:28:39'),
(46, 4, 1, '2026-04-27', '14:57:00', '16:59:00', 'available', '2026-04-27 09:32:04'),
(47, 4, 1, '2026-04-29', '15:58:00', '04:59:00', 'booked', '2026-04-27 09:32:04'),
(48, 4, 1, '2026-04-27', '15:02:00', '04:03:00', 'available', '2026-04-27 09:33:02'),
(49, 4, 1, '2026-04-28', '15:02:00', '16:03:00', 'available', '2026-04-27 09:33:02'),
(50, 4, 1, '2026-04-29', '15:02:00', '16:03:00', 'available', '2026-04-27 09:33:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `slot_id` (`slot_id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_booked_at` (`booked_at`);

--
-- Indexes for table `channelling_centers`
--
ALTER TABLE `channelling_centers`
  ADD PRIMARY KEY (`center_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_specialization` (`specialization`);

--
-- Indexes for table `doctor_center`
--
ALTER TABLE `doctor_center`
  ADD PRIMARY KEY (`dc_id`),
  ADD UNIQUE KEY `unique_doctor_center` (`doctor_id`,`center_id`),
  ADD KEY `center_id` (`center_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`slot_id`),
  ADD KEY `center_id` (`center_id`),
  ADD KEY `idx_doctor_date` (`doctor_id`,`slot_date`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `channelling_centers`
--
ALTER TABLE `channelling_centers`
  MODIFY `center_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `doctor_center`
--
ALTER TABLE `doctor_center`
  MODIFY `dc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`slot_id`) REFERENCES `time_slots` (`slot_id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_center`
--
ALTER TABLE `doctor_center`
  ADD CONSTRAINT `doctor_center_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_center_ibfk_2` FOREIGN KEY (`center_id`) REFERENCES `channelling_centers` (`center_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE;

--
-- Constraints for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD CONSTRAINT `time_slots_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `time_slots_ibfk_2` FOREIGN KEY (`center_id`) REFERENCES `channelling_centers` (`center_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
