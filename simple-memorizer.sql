SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `course` (
  `courseId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseName` varchar(64) NOT NULL,
  `courseQuestionsAmount` int(11) NOT NULL DEFAULT '0',
  `courseAveragePoints` float NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`courseId`),
  KEY `fk_course_user_idx` (`userId`),
  KEY `courseName` (`courseName`),
  KEY `courseQuestionsAmount` (`courseQuestionsAmount`),
  KEY `courseAveragePoints` (`courseAveragePoints`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=84 ;

CREATE TABLE IF NOT EXISTS `question` (
  `questionId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `questionKey` varchar(1024) DEFAULT NULL,
  `questionValue` varchar(1024) DEFAULT NULL,
  `questionPoints` int(11) DEFAULT '5',
  `courseId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`questionId`),
  KEY `fk_question_course1_idx` (`courseId`),
  KEY `questionKey` (`questionKey`(255)),
  KEY `questionValue` (`questionValue`(255)),
  KEY `questionPoints` (`questionPoints`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=726 ;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` varchar(64) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user` (
  `userId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(45) NOT NULL,
  `userEmail` varchar(256) NOT NULL,
  `userOauthId` varchar(512) NOT NULL,
  `userOauthProvider` varchar(32) NOT NULL,
  `userCoursesAmount` int(11) unsigned NOT NULL DEFAULT '0',
  `userPermissions` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;


ALTER TABLE `course`
  ADD CONSTRAINT `fk_course_user` FOREIGN KEY (`userId`) REFERENCES `user` (`userId`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `question`
  ADD CONSTRAINT `fk_question_course1` FOREIGN KEY (`courseId`) REFERENCES `course` (`courseId`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
