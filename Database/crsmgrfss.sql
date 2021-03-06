SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `assignments`;
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(65) NOT NULL,
  `section_id` int(11) UNSIGNED NOT NULL,
  `due_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_assignments_section_id` (`section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

INSERT INTO `assignments` (`id`, `name`, `section_id`, `due_date`) VALUES
(1, 'Assignment 1', 5, '2016-08-31 00:00:00');

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(65) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

INSERT INTO `courses` (`id`, `name`) VALUES
(1, 'COMP353'),
(2, 'COMP345'),
(3, 'super'),
(5, 'Databases'),
(6, 'Engineering'),
(7, 'Mathematics'),
(8, 'Computers'),
(9, 'Electronics');

DROP TABLE IF EXISTS `files`;
CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(65) NOT NULL,
  `size_bytes` int(11) UNSIGNED NOT NULL,
  `checksum` varchar(65) NOT NULL,
  `upload_date` datetime NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(65) NOT NULL,
  `version_number` int(2) UNSIGNED NOT NULL,
  `file_name` varchar(65) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_files_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

INSERT INTO `files` (`id`, `name`, `size_bytes`, `checksum`, `upload_date`, `user_id`, `ip_address`, `version_number`, `file_name`) VALUES
(2, 'test', 0, '0b6a00b6f6ddb36c950a710ec088c69a', '2016-08-04 18:56:32', 2, '192.168.101.100', 1, '1470336992m349v2d2bguam1hnk4c1oh8el0.pdf'),
(5, 'my File', 8519, '0b6a00b6f6ddb36c950a710ec088c69a', '2016-08-04 19:14:06', 2, '::1', 2, '14703380462m349v2d2bguam1hnk4c1oh8el0.pdf'),
(6, 'testnew', 8519, '0b6a00b6f6ddb36c950a710ec088c69a', '2016-08-04 19:50:17', 2, '::1', 4, '14703402172m349v2d2bguam1hnk4c1oh8el0.pdf');

DROP TABLE IF EXISTS `sections`;
CREATE TABLE IF NOT EXISTS `sections` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` int(11) UNSIGNED NOT NULL,
  `semester_id` int(11) UNSIGNED NOT NULL,
  `ta_user_id` int(11) UNSIGNED DEFAULT NULL,
  `instructor_user_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sections_course_id` (`course_id`),
  KEY `fk_sections_semester_id` (`semester_id`),
  KEY `fk_sections_user_id` (`ta_user_id`),
  KEY `instructor_user_id` (`instructor_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

INSERT INTO `sections` (`id`, `course_id`, `semester_id`, `ta_user_id`, `instructor_user_id`) VALUES
(5, 1, 1, NULL, 4),
(6, 1, 1, 1, NULL),
(8, 3, 1, 1, 4),
(9, 7, 1, 6, NULL);

DROP TABLE IF EXISTS `semesters`;
CREATE TABLE IF NOT EXISTS `semesters` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

INSERT INTO `semesters` (`id`, `name`, `start_date`, `end_date`) VALUES
(1, 'Summer 2016', '2016-07-01', '2016-08-18'),
(3, 'fall 2015', '2015-08-01', '2015-12-08'),
(4, 'winter 2016', '2016-01-10', '2016-05-10');

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `section_id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`user_id`,`section_id`),
  KEY `fk_students_team_id` (`team_id`),
  KEY `fk_students_section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `students` (`user_id`, `section_id`, `team_id`) VALUES
(1, 5, NULL),
(2, 8, NULL),
(2, 5, 4),
(1, 8, 5);

DROP TABLE IF EXISTS `submissions`;
CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) UNSIGNED NOT NULL,
  `team_id` int(11) UNSIGNED NOT NULL,
  `file_id` int(11) UNSIGNED NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `deletion_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_submissions_assignment_id` (`assignment_id`),
  KEY `fk_submissions_team_id` (`team_id`),
  KEY `fk_submissions_file_id` (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

INSERT INTO `submissions` (`id`, `assignment_id`, `team_id`, `file_id`, `is_deleted`, `deletion_date`) VALUES
(1, 1, 4, 2, 0, NULL),
(4, 1, 4, 5, 0, NULL),
(5, 1, 4, 6, 0, NULL);

DROP TABLE IF EXISTS `teams`;
CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `leader_user_id` int(11) UNSIGNED DEFAULT NULL,
  `section_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_teams_user_id` (`leader_user_id`),
  KEY `fk_teams_section_id` (`section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

INSERT INTO `teams` (`id`, `leader_user_id`, `section_id`) VALUES
(4, NULL, 5),
(5, NULL, 8),
(6, NULL, 8),
(7, NULL, 8);

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(65) NOT NULL,
  `password` varchar(65) NOT NULL,
  `email` varchar(65) NOT NULL,
  `first_name` varchar(65) DEFAULT NULL,
  `last_name` varchar(65) DEFAULT NULL,
  `crsmgrid` int(11) UNSIGNED NOT NULL,
  `permission_level` int(2) UNSIGNED NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

INSERT INTO `users` (`id`, `username`, `password`, `email`, `first_name`, `last_name`, `crsmgrid`, `permission_level`) VALUES
(1, 'raz', '$2y$10$itcxI9jf3ZzBkdWJEA5HROhI/7KwQ9jm8rMbzqxxVmqblvncSo9Ra', 'raz@email.com', 'raz', 'r', 342, 4),
(2, 'mouad', '$2y$10$trnTlM5diWeDGUU1JFcJme/gYS6GFOav/yXFu5iJywFtkXbAoPYhi', 'mouad@email.com', 'mouad', 'bro', 276, 1),
(5, 'onlystudent', '$2y$10$4.46ZgHmbiRZhBR73amxTeDfcbm1F9nThef5Y8rD/tkkCUQtANSt2', 'onlystudent@mail.com', 'OnlyA', 'Student', 1, 1),
(6, 'onlyta', '$2y$10$NZwR0Ep1BICByDqmP7rrOebRTRTUO79ttOeKOJcg48GC2TuukpxVS', 'onlyta@mail.com', 'OnlyA', 'TA', 2, 1),
(7, 'onlyinstructor', '$2y$10$TCTLIaWRyJpTdX8rlzxCPOLXE/eZjux6.B2b2CkbajuAS/.BfH31e', 'onlyinstructor@mail.com', 'OnlyA', 'Instructor', 3, 1),
(8, 'onlyadmin', '$2y$10$yzdjsjAxxgN1PKc6QKwhseEh0j7NFQZCZtr3DyCwBj2eIJzJk0yoK', 'onlyadmin@mail.com', 'OnlyA', 'Admin', 4, 4);


ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assignments_section_id` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `sections`
  ADD CONSTRAINT `fk_sections_course_id` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `fk_sections_semester_id` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`),
  ADD CONSTRAINT `fk_sections_user_id` FOREIGN KEY (`ta_user_id`) REFERENCES `users` (`id`);

ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_section_id` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  ADD CONSTRAINT `fk_students_team_id` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `fk_students_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `submissions`
  ADD CONSTRAINT `fk_submissions_assignment_id` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`),
  ADD CONSTRAINT `fk_submissions_file_id` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`),
  ADD CONSTRAINT `fk_submissions_team_id` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

ALTER TABLE `teams`
  ADD CONSTRAINT `fk_teams_section_id` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  ADD CONSTRAINT `fk_teams_user_id` FOREIGN KEY (`leader_user_id`) REFERENCES `users` (`id`);
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
