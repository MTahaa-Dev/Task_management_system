-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 05:16 PM
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
-- Database: `task_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `description`, `created_at`) VALUES
(3, 4, 'Created a new task', 'Task ID: 2', '2026-05-02 19:45:20'),
(4, 3, 'Updated task status', 'Task ID: 1 to In Progress', '2026-05-05 19:45:20'),
(5, 2, 'Updated task status', 'Task ID: 4 to Completed', '2026-05-06 19:45:20'),
(6, 4, 'User logged out', NULL, '2026-05-07 19:47:51'),
(7, 5, 'User registered', NULL, '2026-05-07 19:53:29'),
(8, 5, 'User logged in', NULL, '2026-05-07 19:53:40'),
(9, 5, 'User logged out', NULL, '2026-05-07 19:55:04'),
(10, 5, 'User logged in', NULL, '2026-05-07 19:58:18'),
(11, 5, 'Updated profile information', NULL, '2026-05-07 19:59:42'),
(12, 5, 'User logged in', NULL, '2026-05-08 10:56:53'),
(13, 5, 'User logged out', NULL, '2026-05-08 11:00:11'),
(14, 7, 'User registered', NULL, '2026-05-08 11:01:07'),
(15, 7, 'User logged in', NULL, '2026-05-08 11:01:41'),
(16, 7, 'Updated profile information', NULL, '2026-05-08 11:02:30'),
(17, 7, 'User logged out', NULL, '2026-05-08 11:02:38'),
(18, 7, 'User logged in', NULL, '2026-05-08 11:03:09'),
(19, 7, 'User logged in', NULL, '2026-05-08 11:57:47'),
(20, 7, 'Changed user status to banned', 'User ID: 6', '2026-05-08 12:19:38'),
(21, 7, 'Deleted a user', 'User ID: 6', '2026-05-08 12:19:44'),
(22, 7, 'Created a new task', 'Task ID: 7', '2026-05-08 14:38:21'),
(23, 7, 'User logged out', NULL, '2026-05-08 14:38:32'),
(24, 5, 'User logged in', NULL, '2026-05-08 14:38:54'),
(25, 5, 'Updated profile information', NULL, '2026-05-08 14:40:50'),
(26, 5, 'User logged out', NULL, '2026-05-08 14:41:11'),
(27, 7, 'User logged in', NULL, '2026-05-08 14:41:19'),
(28, 7, 'User logged out', NULL, '2026-05-08 14:51:52'),
(29, 5, 'User logged in', NULL, '2026-05-08 14:53:58'),
(30, 5, 'User logged out', NULL, '2026-05-08 14:54:17'),
(31, 7, 'User logged in', NULL, '2026-05-08 14:54:32'),
(32, 7, 'Created a new task', 'Task ID: 8', '2026-05-08 15:01:33'),
(33, 7, 'Deleted a user', 'User ID: 1', '2026-05-08 15:05:42'),
(34, 7, 'Changed user status to banned', 'User ID: 5', '2026-05-08 15:05:46'),
(35, 7, 'User logged out', NULL, '2026-05-08 15:06:32'),
(36, 7, 'User logged in', NULL, '2026-05-08 15:07:37'),
(37, 7, 'User logged out', NULL, '2026-05-08 15:08:39'),
(38, 7, 'User logged in', NULL, '2026-05-08 15:08:49'),
(39, 7, 'Changed user status to active', 'User ID: 5', '2026-05-08 15:09:00'),
(40, 7, 'User logged out', NULL, '2026-05-08 15:09:07'),
(41, 5, 'User logged in', NULL, '2026-05-08 15:09:16'),
(42, 7, 'User logged in', NULL, '2026-05-08 16:41:21'),
(43, 5, 'User logged in', NULL, '2026-05-08 18:42:09'),
(44, 5, 'User logged out', NULL, '2026-05-08 18:55:09'),
(45, 8, 'User registered', NULL, '2026-05-08 18:57:17'),
(46, 8, 'User logged in', NULL, '2026-05-08 18:57:33'),
(47, 8, 'User logged out', NULL, '2026-05-08 20:59:29'),
(48, 7, 'User logged in', NULL, '2026-05-08 21:00:15'),
(49, 7, 'User logged out', NULL, '2026-05-08 21:00:56'),
(50, 7, 'User logged in', NULL, '2026-05-08 21:01:26'),
(51, 7, 'User logged out', NULL, '2026-05-08 21:01:33'),
(52, 7, 'User logged in', NULL, '2026-05-08 21:02:06'),
(53, 7, 'Created a new task', 'Task ID: 9', '2026-05-08 21:05:09'),
(54, 7, 'User logged out', NULL, '2026-05-08 21:06:22'),
(55, 5, 'User logged in', NULL, '2026-05-08 21:10:59'),
(56, 5, 'User logged out', NULL, '2026-05-08 21:11:05'),
(57, 7, 'User logged in', NULL, '2026-05-08 21:21:07'),
(58, 7, 'User logged out', NULL, '2026-05-08 21:21:25'),
(59, 5, 'User logged in', NULL, '2026-05-08 21:21:41'),
(60, 5, 'User logged out', NULL, '2026-05-08 21:23:04'),
(61, 7, 'User logged in', NULL, '2026-05-08 21:23:19'),
(62, 7, 'User logged out', NULL, '2026-05-08 21:25:47'),
(63, 5, 'User logged in', NULL, '2026-05-08 21:26:16'),
(64, 5, 'User logged out', NULL, '2026-05-08 21:27:57'),
(65, 7, 'User logged in', NULL, '2026-05-08 21:28:12'),
(66, 7, 'User logged out', NULL, '2026-05-08 21:35:37'),
(67, 9, 'User registered', NULL, '2026-05-08 21:36:12'),
(68, 9, 'User logged in', NULL, '2026-05-08 21:36:27'),
(69, 9, 'Updated profile information', NULL, '2026-05-08 21:37:51'),
(70, 9, 'User logged in', NULL, '2026-05-09 11:23:05'),
(71, 9, 'User logged in', NULL, '2026-05-09 11:44:34'),
(72, 9, 'User logged in', NULL, '2026-05-09 11:59:55'),
(73, 9, 'User logged out', NULL, '2026-05-09 12:02:25'),
(74, 7, 'User logged in', NULL, '2026-05-09 12:02:35'),
(75, 9, 'User logged out', NULL, '2026-05-09 13:59:38'),
(76, 7, 'User logged in', NULL, '2026-05-09 13:59:45'),
(77, 7, 'User logged in', NULL, '2026-05-09 14:44:01');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(5, 7, NULL, 'Welcome to the Team Chat!', '2026-05-08 18:47:24'),
(6, 7, NULL, 'Welcome to the Team Chat!', '2026-05-08 18:47:24'),
(7, 5, NULL, 'heelo', '2026-05-08 18:49:27'),
(8, 5, NULL, 'how are you?', '2026-05-08 18:49:44'),
(9, 8, NULL, 'hi', '2026-05-08 19:07:13'),
(10, 7, NULL, 'hey! what going on?', '2026-05-08 21:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(3, 3, 'You have been assigned a new task: Prepare Q3 Marketing Assets', 'assignment', 0, '2026-05-07 19:45:20'),
(4, 2, 'You have been assigned a new task: Implement Payment Gateway', 'assignment', 1, '2026-05-02 19:45:20'),
(5, 5, 'Welcome to TaskMaster Pro!', 'info', 0, '2026-05-07 19:53:29'),
(6, 7, 'Welcome to TaskMaster Pro!', 'info', 0, '2026-05-08 11:01:07'),
(7, 5, 'You have been assigned a new task: fix header issue', 'assignment', 0, '2026-05-08 14:38:21'),
(8, 5, 'You have been assigned a new task: Create backup', 'assignment', 0, '2026-05-08 15:01:33'),
(9, 8, 'Welcome to TaskMaster Pro!', 'info', 0, '2026-05-08 18:57:17'),
(10, 8, 'You have been assigned a new task: redesign landing page', 'assignment', 0, '2026-05-08 21:05:09'),
(11, 9, 'Welcome to TaskMaster Pro!', 'info', 0, '2026-05-08 21:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `status` enum('Pending','In Progress','Review','Completed') NOT NULL DEFAULT 'Pending',
  `progress` int(11) NOT NULL DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `estimated_time` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `created_by`, `priority`, `status`, `progress`, `deadline`, `category`, `attachment`, `estimated_time`, `created_at`) VALUES
(2, 'Implement Payment Gateway', 'Integrate Stripe API for the new subscription model. Ensure webhooks are catching failed payments correctly.', 2, 4, 'Urgent', 'Review', 90, '2026-05-09', 'Development', NULL, '2 Days', '2026-05-02 19:45:20'),
(3, 'Write API Documentation', 'Document all the REST endpoints for the mobile app team using Swagger.', NULL, 4, 'Medium', 'Pending', 0, '2026-05-18', 'Documentation', NULL, '5 Hours', '2026-05-06 19:45:20'),
(7, 'fix header issue', 'urgent work', 5, 7, 'High', 'Pending', 0, '2026-05-15', 'deign', NULL, '', '2026-05-08 14:38:21'),
(8, 'Create backup', 'site@.com', 5, 7, 'Medium', 'Pending', 0, '2026-05-11', 'backup', NULL, '', '2026-05-08 15:01:33'),
(9, 'redesign landing page', 'this is an urgent work', 8, 7, 'Urgent', 'Pending', 0, NULL, 'deign', NULL, '12 hours', '2026-05-08 21:05:09');

-- --------------------------------------------------------

--
-- Table structure for table `task_checklists`
--

CREATE TABLE `task_checklists` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_checklists`
--

INSERT INTO `task_checklists` (`id`, `task_id`, `title`, `is_completed`, `created_at`) VALUES
(5, 2, 'Set up Stripe test account', 1, '2026-05-07 19:45:20'),
(6, 2, 'Implement webhook listener', 1, '2026-05-07 19:45:20'),
(7, 2, 'Test with test credit cards', 0, '2026-05-07 19:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`) VALUES
(3, 2, 2, 'The webhook listener is failing on some edge cases. I need another day to fix it.', '2026-05-07 17:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `role`, `profile_picture`, `bio`, `phone`, `status`, `remember_token`, `last_login`, `created_at`) VALUES
(2, 'Sarah Jenkins', 'sarahj', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Frontend Developer with a passion for UI animations.', NULL, 'active', NULL, NULL, '2026-05-07 19:45:19'),
(3, 'Mike Chen', 'mikec', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Lead UX/UI Designer focusing on SaaS applications.', NULL, 'active', NULL, NULL, '2026-05-07 19:45:19'),
(4, 'Elena Rodriguez', 'elenar', 'elena@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Project Manager and Agile Scrum Master.', NULL, 'active', NULL, NULL, '2026-05-07 19:45:19'),
(5, 'john', 'john', 'john@demo.com', '$2y$10$efamgqyH92TjsIRAAD/LY.uswBbsbWSTSpR8kCAd5arwOMpshl9ce', 'user', 'assets/uploads/profile_pictures/avatar_5_1778183981.jpg', 'Designer', '', 'active', NULL, '2026-05-09 02:26:16', '2026-05-07 19:53:29'),
(7, 'david', 'david', 'david@gmail.com', '$2y$10$EdOiUbRlIXlSOB0Fm8t/2efgWt1nNc0bFK6B0cCvrQuTVH7syCRWW', 'admin', 'assets/uploads/profile_pictures/avatar_7_1778238150.jpg', '', '', 'active', NULL, '2026-05-09 19:44:01', '2026-05-08 11:01:07'),
(8, 'zara', 'zara', 'zara@gmail.com', '$2y$10$vn3EBYnAnQxTTESu.CwN2OckN3QQxiWbfwQqsXGJ.HfAfNQ8DrqLK', 'user', NULL, NULL, NULL, 'active', NULL, '2026-05-08 23:57:33', '2026-05-08 18:57:17'),
(9, 'Taha', 'taha', 'taha@gmail.com', '$2y$10$6Arc4D54I2v/n58B1QfVleoLsv1cOVgB1ruMd97DpTfgTe6GB7QQe', 'user', 'assets/uploads/profile_pictures/avatar_9_1778276271.jpeg', '', '', 'active', NULL, '2026-05-09 16:59:55', '2026-05-08 21:36:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `task_checklists`
--
ALTER TABLE `task_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `task_checklists`
--
ALTER TABLE `task_checklists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
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
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_checklists`
--
ALTER TABLE `task_checklists`
  ADD CONSTRAINT `task_checklists_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
