SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `orangesolutions` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `orangesolutions`;

CREATE TABLE IF NOT EXISTS `group` (
  `group_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` varchar(64) NOT NULL DEFAULT 'Unnamed Group',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `group` (`group_id`, `group_name`) VALUES
(1, 'Administrators'),
(2, 'Patients'),
(3, 'Volunteers');

CREATE TABLE IF NOT EXISTS `medical_specialty` (
  `medical_specialty_id` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  `medical_specialty_name` varchar(34) NOT NULL,
  PRIMARY KEY (`medical_specialty_id`),
  UNIQUE KEY `medical_specialty_name` (`medical_specialty_name`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COMMENT='Medical specialties available';

INSERT INTO `medical_specialty` (`medical_specialty_id`, `medical_specialty_name`) VALUES
(1, 'Allergy and immunology'),
(2, 'Anesthesiology'),
(3, 'Cardiology'),
(4, 'Cardiovascular surgery'),
(5, 'Clinical laboratorysciences'),
(6, 'Dermatology'),
(7, 'Dietetics'),
(8, 'Emergency medicine'),
(9, 'Endocrinology'),
(10, 'Family medicine'),
(11, 'Gastroenterology'),
(12, 'General surgery'),
(13, 'Geriatrics'),
(14, 'Gynecology'),
(15, 'Hepatology'),
(16, 'Hospital medicine'),
(17, 'Infectious disease'),
(18, 'Intensive care medicine'),
(19, 'Internal Medicine'),
(20, 'Medical research'),
(21, 'Nephrology'),
(22, 'Neurology'),
(23, 'Neurosurgery'),
(24, 'Obstetrics and gynecology'),
(25, 'Oncology'),
(26, 'Ophthalmology'),
(27, 'Oral and maxillofacial surgery'),
(28, 'Orthopedic surgery'),
(29, 'Otorhinolaryngology, or ENT'),
(30, 'Palliative care'),
(31, 'Pathology'),
(33, 'Pediatric surgery'),
(32, 'Pediatrics'),
(34, 'Physical medicine & Rehabilitation'),
(35, 'Plastic surgery'),
(36, 'Podiatry'),
(37, 'Proctology'),
(38, 'Psychiatry'),
(40, 'Public Health'),
(39, 'Pulmonology'),
(41, 'Radiology'),
(42, 'Rheumatology'),
(43, 'Surgical oncology'),
(44, 'Thoracic surgery'),
(45, 'Transplant surgery'),
(46, 'Urgent Care Medicine'),
(47, 'Urology'),
(48, 'Vascular surgery');

CREATE TABLE IF NOT EXISTS `medical_specialty_mastery` (
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `medical_specialty_id` tinyint(2) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`,`medical_specialty_id`) USING BTREE,
  KEY `user_id` (`user_id`),
  KEY `medical_specialty_id` (`medical_specialty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `membership` (
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `membership_validfrom` datetime DEFAULT NULL,
  `membership_expiration` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `patient_profile` (
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `patient_gender` enum('Male','Female','Intersex','FtM Male','MtF Female') DEFAULT NULL,
  `patient_birth_year` year(4) DEFAULT NULL,
  `patient_conditions` text NOT NULL,
  `patient_allergies` text NOT NULL,
  `patient_medications` text NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'User ID',
  `user_email` varchar(255) DEFAULT NULL COMMENT 'Email',
  `user_firstname` varchar(40) NOT NULL DEFAULT '' COMMENT 'First Name',
  `user_middlename` varchar(40) NOT NULL DEFAULT '' COMMENT 'Middle Name',
  `user_lastname` varchar(40) NOT NULL DEFAULT '' COMMENT 'Last Name',
  `user_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'User Activated',
  `user_password` varchar(256) DEFAULT NULL COMMENT 'Password',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `USER_EMAIL_INDEX` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


ALTER TABLE `medical_specialty_mastery`
  ADD CONSTRAINT `FK_MEDICALSPECIALTYMASTERY_MEDICALSPECIALTY` FOREIGN KEY (`medical_specialty_id`) REFERENCES `medical_specialty` (`medical_specialty_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_MEDICALSPECIALTYMASTERY_USER` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `membership`
  ADD CONSTRAINT `FK_MEMBERSHIP_GROUP` FOREIGN KEY (`group_id`) REFERENCES `group` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_MEMBERSHIP_USER` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `patient_profile`
  ADD CONSTRAINT `FK_PATIENTPROFILE_USER` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
