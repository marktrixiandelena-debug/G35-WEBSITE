-- Flood and Drainage Incident Reporting and Management System
-- Complete Database Schema

CREATE DATABASE IF NOT EXISTS `barangay_system`;
USE `barangay_system`;

-- 1. Users Table (Base table used by others)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','resident') NOT NULL,
  `require_password_change` tinyint(1) DEFAULT 0,
  `contact_number` varchar(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','disabled','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Response Teams (Used by Assignments | Deactivation preferred over deletion to preserve history)
CREATE TABLE IF NOT EXISTS `response_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_name` varchar(100) NOT NULL,
  `team_leader` varchar(100) NOT NULL,
  `contact_number` varchar(11) NOT NULL,
  `status` enum('Active','Inactive','Deployed') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Reports Table (Refers to Users)
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(150) DEFAULT NULL,
  `guest_contact` varchar(11) DEFAULT NULL,
  `type` enum('flood','drainage') NOT NULL,
  `location` varchar(255) NOT NULL,
  `location_details` text DEFAULT NULL,
  `description` text NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','resolved','dismissed') DEFAULT 'pending',
  `dismissal_reason` text,
  `report_source` varchar(50) NOT NULL DEFAULT 'Walk-In',
  `encoded_by` int(11) DEFAULT NULL,
  `severity` enum('Low','Medium','High','Critical') DEFAULT 'Low',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `encoded_by` (`encoded_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`encoded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Announcements Table (Refers to Users)
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('System Update','Advisory','General') DEFAULT 'General',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Audit Logs Table (Refers to Users)
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Case Timeline (Refers to Reports and Users)
CREATE TABLE IF NOT EXISTS `case_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `status_from` varchar(50) DEFAULT NULL,
  `status_to` varchar(50) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `case_timeline_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `case_timeline_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Report Assignments (Refers to Reports and Response Teams)
CREATE TABLE IF NOT EXISTS `report_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Assigned','Completed') DEFAULT 'Assigned',
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `report_assignments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_assignments_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `response_teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default Admin Account (Temporary Password: admin123 | Must be changed on first login)
INSERT IGNORE INTO `users` (`full_name`, `username`, `password`, `role`, `status`, `require_password_change`) 
VALUES ('Mark Trixian Deleña', 'mark.td', '$2a$12$fw1oGmUTfM6SGCFDSvDI5O8Kc7n7pB88.dCE/Y0wW4cHHFb0kKX3a', 'admin', 'active', 1);