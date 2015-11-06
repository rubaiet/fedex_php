-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2015 at 02:47 PM
-- Server version: 5.6.21
-- PHP Version: 5.5.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fedex_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE IF NOT EXISTS `shipments` (
`id` int(11) NOT NULL,
  `length` decimal(10,0) NOT NULL,
  `width` decimal(10,0) NOT NULL,
  `height` decimal(10,0) NOT NULL,
  `weight` decimal(10,0) NOT NULL,
  `receiver` varchar(250) NOT NULL,
  `receiver_company` varchar(250) NOT NULL,
  `receiver_phone` varchar(250) NOT NULL,
  `receiver_address` varchar(250) NOT NULL,
  `service_type` varchar(250) NOT NULL,
  `token` varchar(250) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `length`, `width`, `height`, `weight`, `receiver`, `receiver_company`, `receiver_phone`, `receiver_address`, `service_type`, `token`, `status`, `created`) VALUES
(1, '10', '10', '2', '12', 'Raju', 'ELL', '01656565', '', 'STANDARD_OVERNIGHT', '794661724100', 1, '2015-11-03 12:25:17'),
(2, '12', '8', '2', '12', 'Raju', 'ELL', '01656565', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661726294', 1, '2015-11-03 12:44:45'),
(3, '12', '8', '2', '12', 'Raju', 'ELL', '01656565', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661726754', 1, '2015-11-03 12:47:47'),
(4, '12', '8', '2', '12', 'Raju', 'ELL', '01656565', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661727062', 1, '2015-11-03 12:49:53'),
(5, '5', '55', '5', '5', '5', '5', '5', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661729981', 1, '2015-11-03 13:17:00'),
(6, '5', '55', '5', '5', '5', '5', '5', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661730069', 1, '2015-11-03 13:18:06'),
(7, '2', '2', '2', '22', '2', '2', '5', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661730529', 1, '2015-11-03 13:21:23'),
(8, '2', '2', '2', '22', '2', '2', '5', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661731168', 1, '2015-11-03 13:26:44'),
(9, '2', '2', '2', '22', '2', '2', '5', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661731271', 1, '2015-11-03 13:27:46'),
(10, '10', '10', '10', '10', 'Raju', 'ELL', '016565656565', 'Address Line 1<br>Herndon<br>VA<br>20171<br>US', 'STANDARD_OVERNIGHT', '794661732782', 1, '2015-11-03 13:43:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
