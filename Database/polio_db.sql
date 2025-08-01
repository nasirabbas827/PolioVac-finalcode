-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 06:31 AM
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
-- Database: `polio_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `child_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `special_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`child_id`, `parent_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `address`, `city`, `province`, `postal_code`, `special_notes`, `created_at`, `updated_at`) VALUES
(2, 2, 'Ali', 'Khan', '2020-03-15', 'Male', 'Street 5, Block A', 'Karachi', 'Sindh', '74000', 'None', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(3, 2, 'Fatima', 'Zafar', '2019-07-22', 'Female', 'House 10, Gulberg', 'Lahore', 'Punjab', '54000', 'Peanut allergy', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(4, 2, 'Ahmed', 'Iqbal', '2021-01-05', 'Male', 'House 7, G-10', 'Islamabad', 'ICT', '44000', 'None', '2025-05-29 05:17:44', '2025-05-29 05:19:49'),
(5, 2, 'Noor', 'Awan', '2022-02-11', 'Female', 'Flat 11, Bahria', 'Rawalpindi', 'Punjab', '46000', 'Asthma', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(6, 2, 'Zain', 'Ali', '2018-11-30', 'Male', 'House 2, Saddar', 'Peshawar', 'KP', '25000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(7, 2, 'Hira', 'Saleem', '2020-09-01', 'Female', 'Street 8, DHA', 'Quetta', 'Balochistan', '87300', 'Lactose Intolerance', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(8, 2, 'Usman', 'Hafeez', '2019-04-20', 'Male', 'Building 2, City Center', 'Faisalabad', 'Punjab', '38000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(9, 2, 'Maryam', 'Javed', '2021-10-09', 'Female', 'Near Canal Road', 'Multan', 'Punjab', '60000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(10, 2, 'Hassan', 'Sohail', '2020-06-30', 'Male', 'House 18, Askari', 'Hyderabad', 'Sindh', '71000', 'Eczema', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(11, 2, 'Sara', 'Rafiq', '2022-03-18', 'Female', 'Flat 3, Clifton', 'Karachi', 'Sindh', '75000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44');

-- --------------------------------------------------------

--
-- Table structure for table `missed_vaccinations`
--

CREATE TABLE `missed_vaccinations` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `preferred_date` date NOT NULL,
  `status` enum('pending','rescheduled','resolved') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `missed_vaccinations`
--

INSERT INTO `missed_vaccinations` (`id`, `child_id`, `reported_by`, `reason`, `preferred_date`, `status`, `created_at`) VALUES
(1, 10, 2, 'ds', '2025-05-31', 'rescheduled', '2025-05-29 10:32:07');

-- --------------------------------------------------------

--
-- Table structure for table `unvaccinated_reports`
--

CREATE TABLE `unvaccinated_reports` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `report_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unvaccinated_reports`
--

INSERT INTO `unvaccinated_reports` (`id`, `child_id`, `worker_id`, `reason`, `report_date`) VALUES
(1, 6, 1, 'fdas', '2025-05-30'),
(2, 3, 1, 'dss', '2025-05-30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `cnic` varchar(13) NOT NULL,
  `bio` text DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `age`, `cnic`, `bio`, `user_type`, `profile_picture`, `status`) VALUES
(1, 'worker12', '$2y$10$gps/vTvTY1t1FGWgealP8.zmrT0NUXQCBKVorMsz7X0Mrjo5sO.km', 'worker@gmail.com', '3176526827', 23, '3240284436399', 'ds', 'Worker', 'images/1754022562_504-1.jpg', 'approved'),
(2, 'parent1', '$2y$10$OnwDThbrX/FOiMGixb5QHOFPLRf89npIHBpwRvxF1tJHxRY8PNAMq', 'parent@gmail.com', '03120558064', 23, '3240254008217', 'noe', 'Parent', 'images/1754022511_3.png', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `vaccinations`
--

CREATE TABLE `vaccinations` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `vaccine_date` date NOT NULL,
  `batch_number` varchar(100) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `method` enum('center','home') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccinations`
--

INSERT INTO `vaccinations` (`id`, `child_id`, `worker_id`, `vaccine_date`, `batch_number`, `schedule_id`, `method`, `created_at`) VALUES
(1, 8, 1, '2025-05-31', 'ds', 13, 'center', '2025-05-29 10:48:06'),
(2, 2, 1, '2025-05-31', 'ds', 15, 'center', '2025-05-29 10:50:25');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_centers`
--

CREATE TABLE `vaccination_centers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccination_centers`
--

INSERT INTO `vaccination_centers` (`id`, `name`, `location`, `contact_number`) VALUES
(2, 'Jinnah Hospital Center', 'Lahore, Punjab', '042-99231400'),
(3, 'Civil Hospital Unit', 'Karachi, Sindh', '021-99215740'),
(4, 'PIMS Polio Unit', 'Islamabad Capital Territory', '051-9261170'),
(5, 'Lady Reading Hospital Center', 'Peshawar, Khyber Pakhtunkhwa', '091-9211430'),
(6, 'Children Hospital Polio Cell', 'Multan, Punjab', '061-9200937'),
(7, 'DHQ Hospital Center', 'Faisalabad, Punjab', '041-9200120'),
(8, 'Nishtar Hospital Polio Desk', 'Multan, Punjab', '061-9200231'),
(9, 'Liaquat University Hospital', 'Hyderabad, Sindh', '022-9210305'),
(10, 'Sheikh Zayed Hospital Center', 'Rahim Yar Khan, Punjab', '068-9230160'),
(11, 'Quetta Civil Hospital Unit', 'Quetta, Balochistan', '081-9201614');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_schedules`
--

CREATE TABLE `vaccination_schedules` (
  `id` int(11) NOT NULL,
  `center_id` int(11) NOT NULL,
  `vaccine_date` date NOT NULL,
  `campaign_name` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccination_schedules`
--

INSERT INTO `vaccination_schedules` (`id`, `center_id`, `vaccine_date`, `campaign_name`, `notes`) VALUES
(11, 11, '2025-06-01', 'National Immunization Day', 'Start at 9AM. Refreshments for staff.'),
(12, 2, '2025-06-05', 'City-Wide Polio Drive', 'Mobile teams assigned.'),
(13, 3, '2025-06-10', 'Routine EPI Campaign', 'Include door-to-door initiative.'),
(14, 4, '2025-06-12', 'June Vaccination Campaign', 'High-risk area, extra doses.'),
(15, 5, '2025-06-15', 'Rural Outreach Program', 'Vaccination in remote villages.'),
(16, 6, '2025-06-18', 'School Entry Campaign', 'Coordinate with school admins.'),
(17, 7, '2025-06-20', 'Booster Campaign', 'Focus on children under 2 years.'),
(18, 8, '2025-06-22', 'Weekend Special Round', 'Evening session planned.'),
(19, 9, '2025-06-25', 'Pre-Monsoon Vaccination', 'Raincoats for staff issued.'),
(20, 10, '2025-06-28', 'Back-to-Back Campaign', 'Follow-up for missed cases.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`child_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `missed_vaccinations`
--
ALTER TABLE `missed_vaccinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `reported_by` (`reported_by`);

--
-- Indexes for table `unvaccinated_reports`
--
ALTER TABLE `unvaccinated_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `worker_id` (`worker_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `vaccination_centers`
--
ALTER TABLE `vaccination_centers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vaccination_schedules`
--
ALTER TABLE `vaccination_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `center_id` (`center_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `child_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `missed_vaccinations`
--
ALTER TABLE `missed_vaccinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `unvaccinated_reports`
--
ALTER TABLE `unvaccinated_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vaccinations`
--
ALTER TABLE `vaccinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vaccination_centers`
--
ALTER TABLE `vaccination_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vaccination_schedules`
--
ALTER TABLE `vaccination_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `missed_vaccinations`
--
ALTER TABLE `missed_vaccinations`
  ADD CONSTRAINT `missed_vaccinations_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `missed_vaccinations_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unvaccinated_reports`
--
ALTER TABLE `unvaccinated_reports`
  ADD CONSTRAINT `unvaccinated_reports_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unvaccinated_reports_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD CONSTRAINT `vaccinations_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vaccinations_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vaccinations_ibfk_3` FOREIGN KEY (`schedule_id`) REFERENCES `vaccination_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaccination_schedules`
--
ALTER TABLE `vaccination_schedules`
  ADD CONSTRAINT `vaccination_schedules_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `vaccination_centers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
