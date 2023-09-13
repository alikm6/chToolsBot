-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 10, 2023 at 06:08 PM
-- Server version: 8.0.34-0ubuntu0.20.04.1
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farsbot_chtools_source`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admins`
--

CREATE TABLE `tbl_admins` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `cmd` tinyint NOT NULL DEFAULT '1',
  `notify_new_member` tinyint NOT NULL DEFAULT '1',
  `notify_contact` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attachments`
--

CREATE TABLE `tbl_attachments` (
  `id` int NOT NULL,
  `attachment_id` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` enum('photo','document','animation','video','video_note','voice','audio','sticker') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tg_file_unique_id` varchar(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `channel_id` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message_id` int NOT NULL,
  `date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attachment_relations`
--

CREATE TABLE `tbl_attachment_relations` (
  `id` int NOT NULL,
  `attachment_id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `target_type` enum('process','attach','hyper','inlinekey','contact') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `target_id` int DEFAULT NULL,
  `date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_callback_data`
--

CREATE TABLE `tbl_callback_data` (
  `id` int NOT NULL,
  `action` varchar(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `target_id` int DEFAULT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_channels`
--

CREATE TABLE `tbl_channels` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `channel_id` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_commands`
--

CREATE TABLE `tbl_commands` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `name` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `col1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col4` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col5` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col6` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col7` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col8` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col9` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `col10` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_contact`
--

CREATE TABLE `tbl_contact` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `main_contact_id` int DEFAULT NULL,
  `messages_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_file_and_user_relation`
--

CREATE TABLE `tbl_file_and_user_relation` (
  `id` int NOT NULL,
  `tg_file_unique_id` varchar(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `insert_date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_forward`
--

CREATE TABLE `tbl_forward` (
  `id` int NOT NULL,
  `pending_chats_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `successful_chats_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `unsuccessful_chats_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `submitter_chat_id` bigint NOT NULL,
  `message_id` bigint NOT NULL,
  `log_message_id` bigint NOT NULL,
  `method` enum('copy','forward') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'forward',
  `try_status` tinyint NOT NULL DEFAULT '0',
  `try_count` int NOT NULL DEFAULT '0',
  `last_try_date` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inlinekey`
--

CREATE TABLE `tbl_inlinekey` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `inline_id` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `language_code` enum('en_US','fa_IR') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_US',
  `type` enum('text','photo','document','animation','video','video_note','voice','audio','sticker','location','venue','contact') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_unique_id` varchar(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `parse_mode` enum('html','markdown','markdownv2') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attach_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `web_page_preview` tinyint DEFAULT NULL,
  `keyboard` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `counter_type` enum('percent','count') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'count',
  `show_alert` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inlinekey_chosen`
--

CREATE TABLE `tbl_inlinekey_chosen` (
  `id` int NOT NULL,
  `keyboard_id` int NOT NULL,
  `from_id` bigint NOT NULL,
  `chat_id` bigint DEFAULT NULL,
  `message_id` bigint DEFAULT NULL,
  `inline_message_id` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `chosen_date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inlinekey_counter`
--

CREATE TABLE `tbl_inlinekey_counter` (
  `id` int NOT NULL,
  `keyboard_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inlinekey_counter_stats`
--

CREATE TABLE `tbl_inlinekey_counter_stats` (
  `id` int NOT NULL,
  `keyboard_id` int NOT NULL,
  `counter_id` int NOT NULL,
  `user_id` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inlinekey_update_keyboard_log`
--

CREATE TABLE `tbl_inlinekey_update_keyboard_log` (
  `id` int NOT NULL,
  `keyboard_id` int NOT NULL,
  `date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

CREATE TABLE `tbl_settings` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `language_code` enum('en_US','fa_IR') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_US',
  `sendto_web_page_preview` tinyint NOT NULL DEFAULT '0',
  `sendto_notification` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_stats`
--

CREATE TABLE `tbl_stats` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `name` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stat_date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tg_Chat`
--

CREATE TABLE `tbl_tg_Chat` (
  `id` int NOT NULL,
  `tg_id` bigint NOT NULL,
  `type` enum('private','group','supergroup','channel') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `username` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `first_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `last_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `update_date` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tg_file_id_relation`
--

CREATE TABLE `tbl_tg_file_id_relation` (
  `id` int NOT NULL,
  `file_unique_id` varchar(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_id` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `insert_date` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tg_User`
--

CREATE TABLE `tbl_tg_User` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `is_bot` tinyint NOT NULL DEFAULT '0',
  `first_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `last_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `language_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `update_date` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int NOT NULL,
  `user_id` bigint NOT NULL,
  `referral_user_id` bigint DEFAULT NULL,
  `start_m_id` bigint NOT NULL,
  `start_date` int UNSIGNED DEFAULT NULL,
  `last_m_date` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admins`
--
ALTER TABLE `tbl_admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cmd` (`cmd`),
  ADD KEY `notify_new_member` (`notify_new_member`),
  ADD KEY `notify_contact` (`notify_contact`);

--
-- Indexes for table `tbl_attachments`
--
ALTER TABLE `tbl_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tg_file_unique_id` (`tg_file_unique_id`),
  ADD KEY `attachment_id` (`attachment_id`),
  ADD KEY `channel_id` (`channel_id`);

--
-- Indexes for table `tbl_attachment_relations`
--
ALTER TABLE `tbl_attachment_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachment_id` (`attachment_id`),
  ADD KEY `target_type` (`target_type`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `target_id` (`target_id`);

--
-- Indexes for table `tbl_callback_data`
--
ALTER TABLE `tbl_callback_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `action` (`action`),
  ADD KEY `target_id` (`target_id`);

--
-- Indexes for table `tbl_channels`
--
ALTER TABLE `tbl_channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `channel_id` (`channel_id`);

--
-- Indexes for table `tbl_commands`
--
ALTER TABLE `tbl_commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_contact`
--
ALTER TABLE `tbl_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `main_contact_id` (`main_contact_id`);

--
-- Indexes for table `tbl_file_and_user_relation`
--
ALTER TABLE `tbl_file_and_user_relation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tg_file_unique_id` (`tg_file_unique_id`);

--
-- Indexes for table `tbl_forward`
--
ALTER TABLE `tbl_forward`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_inlinekey`
--
ALTER TABLE `tbl_inlinekey`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `inline_id` (`inline_id`);

--
-- Indexes for table `tbl_inlinekey_chosen`
--
ALTER TABLE `tbl_inlinekey_chosen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `keyboard_id` (`keyboard_id`),
  ADD KEY `chosen_date` (`chosen_date`);

--
-- Indexes for table `tbl_inlinekey_counter`
--
ALTER TABLE `tbl_inlinekey_counter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `keyboard_id` (`keyboard_id`);

--
-- Indexes for table `tbl_inlinekey_counter_stats`
--
ALTER TABLE `tbl_inlinekey_counter_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `keyboard_id` (`keyboard_id`),
  ADD KEY `counter_id` (`counter_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_inlinekey_update_keyboard_log`
--
ALTER TABLE `tbl_inlinekey_update_keyboard_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `keyboard_id` (`keyboard_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_stats`
--
ALTER TABLE `tbl_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_tg_Chat`
--
ALTER TABLE `tbl_tg_Chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tg_id` (`tg_id`);

--
-- Indexes for table `tbl_tg_file_id_relation`
--
ALTER TABLE `tbl_tg_file_id_relation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_unique_id` (`file_unique_id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `tbl_tg_User`
--
ALTER TABLE `tbl_tg_User`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admins`
--
ALTER TABLE `tbl_admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_attachments`
--
ALTER TABLE `tbl_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_attachment_relations`
--
ALTER TABLE `tbl_attachment_relations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_callback_data`
--
ALTER TABLE `tbl_callback_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_channels`
--
ALTER TABLE `tbl_channels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_commands`
--
ALTER TABLE `tbl_commands`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_contact`
--
ALTER TABLE `tbl_contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_file_and_user_relation`
--
ALTER TABLE `tbl_file_and_user_relation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_forward`
--
ALTER TABLE `tbl_forward`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_inlinekey`
--
ALTER TABLE `tbl_inlinekey`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_inlinekey_chosen`
--
ALTER TABLE `tbl_inlinekey_chosen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_inlinekey_counter`
--
ALTER TABLE `tbl_inlinekey_counter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_inlinekey_counter_stats`
--
ALTER TABLE `tbl_inlinekey_counter_stats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_inlinekey_update_keyboard_log`
--
ALTER TABLE `tbl_inlinekey_update_keyboard_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_stats`
--
ALTER TABLE `tbl_stats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tg_Chat`
--
ALTER TABLE `tbl_tg_Chat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tg_file_id_relation`
--
ALTER TABLE `tbl_tg_file_id_relation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tg_User`
--
ALTER TABLE `tbl_tg_User`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_attachment_relations`
--
ALTER TABLE `tbl_attachment_relations`
  ADD CONSTRAINT `tbl_attachment_relations_ibfk_1` FOREIGN KEY (`attachment_id`) REFERENCES `tbl_attachments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_contact`
--
ALTER TABLE `tbl_contact`
  ADD CONSTRAINT `tbl_contact_ibfk_1` FOREIGN KEY (`main_contact_id`) REFERENCES `tbl_contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_inlinekey_chosen`
--
ALTER TABLE `tbl_inlinekey_chosen`
  ADD CONSTRAINT `tbl_inlinekey_chosen_ibfk_1` FOREIGN KEY (`keyboard_id`) REFERENCES `tbl_inlinekey` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_inlinekey_counter`
--
ALTER TABLE `tbl_inlinekey_counter`
  ADD CONSTRAINT `tbl_inlinekey_counter_ibfk_1` FOREIGN KEY (`keyboard_id`) REFERENCES `tbl_inlinekey` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_inlinekey_counter_stats`
--
ALTER TABLE `tbl_inlinekey_counter_stats`
  ADD CONSTRAINT `tbl_inlinekey_counter_stats_ibfk_1` FOREIGN KEY (`keyboard_id`) REFERENCES `tbl_inlinekey` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_inlinekey_counter_stats_ibfk_2` FOREIGN KEY (`counter_id`) REFERENCES `tbl_inlinekey_counter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_inlinekey_update_keyboard_log`
--
ALTER TABLE `tbl_inlinekey_update_keyboard_log`
  ADD CONSTRAINT `tbl_inlinekey_update_keyboard_log_ibfk_1` FOREIGN KEY (`keyboard_id`) REFERENCES `tbl_inlinekey` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
