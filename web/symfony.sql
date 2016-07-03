-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 03, 2016 at 12:38 PM
-- Server version: 5.6.30
-- PHP Version: 7.0.3-3+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `symfony`
--

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`) VALUES
(1, 'New product name!', '9.99', 'good mouse'),
(2, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(3, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(4, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(5, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(6, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(7, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(8, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(9, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(10, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(11, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(12, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(13, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(14, 'Keyboard', '19.99', 'Ergonomic and stylish!'),
(15, 'good api product', '45.50', 'good api testing description'),
(16, 'good api product', '45.50', 'good api testing description'),
(17, 'another api product', '50.50', 'good api another testing description'),
(18, 'another api product', '50.50', 'good api another testing description'),
(19, 'another api product', '50.50', 'good api another testing description'),
(20, 'another api product', '50.50', 'good api another testing description');

-- --------------------------------------------------------

--
-- Table structure for table `programmers`
--

CREATE TABLE `programmers` (
  `id` int(4) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `programmers`
--
ALTER TABLE `programmers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `programmers`
--
ALTER TABLE `programmers`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
