-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2019 at 12:39 PM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crudsp`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `createproduct` (IN `name` VARCHAR(150), IN `code` VARCHAR(150), IN `details` TEXT, IN `manufac` VARCHAR(150), IN `cat_id` INT, IN `unit_price` DOUBLE, IN `img` VARCHAR(150), IN `img_thumb` VARCHAR(150), IN `weight` DOUBLE, IN `weight_unit` CHAR(5), IN `stock` INT, IN `created_at` DATETIME)  BEGIN

  INSERT INTO tblproducts
  (
    pro_name,pro_code,pro_details, pro_manufac, pro_cat_id, pro_unit_price, pro_img, pro_img_thumb, pro_weight, pro_weight_unit, pro_stock, created_at
  )
  VALUES
  (
    name,code,details,manufac,cat_id,unit_price,img,img_thumb,weight,weight_unit,stock, created_at
  );

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteproduct` (IN `id` INT)  BEGIN
  DELETE FROM tblproducts WHERE pro_id = id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `doesprocodeexists` (IN `pro_code` VARCHAR(150), IN `pro_id` INT)  BEGIN
	declare sql_query varchar(255);
    SET sql_query = concat("select count(*) AS Total from tblproducts WHERE pro_code='", pro_code, "'");
    
    IF (pro_id > 0) THEN
		SET sql_query = concat(sql_query, " AND pro_id<>", pro_id);
    END IF;
    
    SET @t1 = sql_query;
	PREPARE stmt FROM @t1;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dynamicsp` (IN `food` VARCHAR(5), IN `smoke` VARCHAR(5), IN `drink` VARCHAR(5))  BEGIN
  DECLARE cond_food VARCHAR(100);
  DECLARE cond_smoke VARCHAR(100);
  DECLARE cond_drink VARCHAR(100);
  DECLARE flag_food BOOL DEFAULT FALSE;
  DECLARE flag_smoke BOOL DEFAULT FALSE;
  -- DECLARE flag_drink BOOL DEFAULT FALSE;

  DECLARE sql_query VARCHAR(255);

  SET sql_query = "SELECT * FROM tblprofile WHERE ";


  -- If food is present apply condition for food and set flag_food to true

  IF (LENGTH(food) > 0) THEN
    SET cond_food = CONCAT("food_habit='",food,"' ");
    SET flag_food = TRUE;
    SET sql_query = CONCAT(sql_query, cond_food);
  END IF;


  -- If smoke is present apply condition for smoking and set flag_smoke to true

  IF (LENGTH(smoke) > 0)  THEN
    SET flag_smoke = TRUE;
    SET cond_smoke = CONCAT(" smoking_habit = '",smoke,"' ");
    CASE flag_food
      WHEN TRUE THEN
        SET sql_query = CONCAT(sql_query, " AND ", cond_smoke);
      ELSE
        SET sql_query = CONCAT(sql_query, cond_smoke);
    END CASE;
  END IF;

  -- If drink is present apply condition for drinking

  IF (LENGTH(drink) > 0)  THEN
    SET cond_drink = CONCAT(" drinking_habit='",drink,"' ");

    IF ( flag_food IS FALSE AND flag_smoke IS FALSE ) THEN
      SET sql_query = CONCAT(sql_query, cond_drink);
      ELSE
        SET sql_query = CONCAT(sql_query, " AND ", cond_drink);
    END IF;

  END IF;

  SET @t1 = sql_query;

  PREPARE stmt FROM @t1;
  EXECUTE stmt;
  DEALLOCATE PREPARE stmt;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getcategories` (IN `category_status` CHAR(1))  BEGIN
	select * from tblcategories where is_active = category_status order by cat_name asc;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getproduct` (IN `id` INT)  BEGIN
  SELECT pro.*, cat.cat_name FROM tblproducts AS pro JOIN tblcategories AS cat ON pro.pro_cat_id = cat.id WHERE pro.pro_id = id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getproducts` (IN `pro_status` CHAR(1))  BEGIN
  SELECT pro.*, cat.cat_name FROM tblproducts AS pro JOIN tblcategories AS cat ON pro.pro_cat_id = cat.id WHERE pro.is_active = pro_status
  ORDER BY pro.pro_id DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateproduct` (IN `name` VARCHAR(150), IN `code` VARCHAR(150), IN `details` TEXT, IN `manufac` VARCHAR(150), IN `cat_id` INT, IN `unit_price` DOUBLE, IN `img` VARCHAR(150), IN `img_thumb` VARCHAR(150), IN `weight` DOUBLE, IN `weight_unit` CHAR(5), IN `stock` INT, IN `id` INT)  BEGIN


    UPDATE tblproducts SET pro_name = name, pro_code = code, pro_details = details, pro_manufac = manufac, pro_cat_id = cat_id, pro_unit_price = unit_price,
    pro_img = img, pro_img_thumb = img_thumb, pro_weight = weight, pro_weight_unit = weight_unit, pro_stock = stock WHERE pro_id = id;




END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateproductstatus` (IN `pro_status` CHAR(1), IN `id` INT)  BEGIN
  UPDATE tblproducts SET is_active = pro_status WHERE pro_id = id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tblcategories`
--

CREATE TABLE `tblcategories` (
  `id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `is_active` enum('1','0') NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblcategories`
--

INSERT INTO `tblcategories` (`id`, `cat_name`, `is_active`, `created`, `updated`) VALUES
(1, 'Smartphone', '1', '2019-08-17 07:45:00', '2019-08-17 07:47:17'),
(2, 'Laptops', '1', '2019-08-17 00:00:00', '2019-08-17 07:47:17'),
(3, 'Watch', '1', '2019-08-17 00:00:00', '2019-08-17 07:47:46'),
(4, 'Television', '1', '2019-08-17 00:00:00', '2019-08-17 07:47:46'),
(5, 'Others', '1', '2019-08-17 00:00:00', '2019-08-17 07:49:33');

-- --------------------------------------------------------

--
-- Table structure for table `tblproducts`
--

CREATE TABLE `tblproducts` (
  `pro_id` int(11) NOT NULL,
  `pro_name` varchar(150) NOT NULL,
  `pro_code` varchar(150) NOT NULL,
  `pro_details` text NOT NULL,
  `pro_manufac` varchar(150) NOT NULL,
  `pro_cat_id` int(11) NOT NULL,
  `pro_unit_price` decimal(11,2) NOT NULL,
  `pro_img` varchar(150) DEFAULT NULL,
  `pro_img_thumb` varchar(150) DEFAULT NULL,
  `pro_weight` decimal(11,2) NOT NULL,
  `pro_weight_unit` enum('lb','kgs','gms') NOT NULL,
  `pro_stock` int(11) NOT NULL,
  `is_active` enum('1','0') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblproducts`
--

INSERT INTO `tblproducts` (`pro_id`, `pro_name`, `pro_code`, `pro_details`, `pro_manufac`, `pro_cat_id`, `pro_unit_price`, `pro_img`, `pro_img_thumb`, `pro_weight`, `pro_weight_unit`, `pro_stock`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Samsung Galaxy J5', 'SAMJ5', '<p>This is a useful <strong>smartphone</strong>.</p>', 'Samsung', 1, '9200.00', '810823.jpg', '810823_thumb.jpg', '10.50', 'gms', 12, '1', '2019-08-19 09:51:03', '2019-08-19 13:21:03'),
(3, 'Samsung Galaxy J7', 'SAMJ7', '<p>A highend smartphone.</p>', 'Samsung', 1, '11200.00', '261644.jpg', '261644_thumb.jpg', '10.50', 'gms', 5, '1', '2019-08-19 10:24:47', '2019-08-19 13:54:47'),
(4, 'Vivo Smartphone', 'VIV0907', '<p>A smartphone from vivo.</p>', 'Vivo', 1, '11000.00', '644802.jpg', '644802_thumb.jpg', '9.57', 'gms', 6, '1', '2019-08-19 10:29:53', '2019-08-19 13:59:53'),
(5, 'HP Laptop', 'HPL0087', '<p>A laptop for programmers.</p>', 'HP', 2, '32900.00', '', '', '15.56', 'gms', 8, '1', '2019-08-19 10:54:16', '2019-08-19 14:24:16'),
(6, 'HP Laptop', 'HPLAP00312', '<p>An online rich-text editor is the interface for editing rich text within web browsers, which presents the user with a &quot;what-you-see-is-what-you-get&quot; editing area. The aim is to reduce the effort for users trying to express their formatting directly as valid HTML markup.</p>\r\n\r\n<p>An online rich-text editor is the <span style=\"color:#8e44ad\"><strong>interface</strong></span> for editing rich text within web browsers, which presents the user with a &quot;what-you-see-is-what-you-get&quot; editing area. The aim is to reduce the effort for users trying to express their formatting directly as <span style=\"color:#cc3300\">valid</span> HTML markup.</p>\r\n\r\n<p>An online rich-text editor is the interface for editing rich text within web browsers, which presents the user with a &quot;what-you-see-is-what-you-get&quot; editing area. The aim is to reduce the effort for users trying to express their formatting directly as valid HTML markup.</p>', 'HP', 2, '22000.00', '20289.jpg', '20289_thumb.jpg', '10.50', 'gms', 7, '1', '2019-08-19 11:35:11', '2019-08-19 15:05:11'),
(7, 'Micromax Canvas', 'MICA67009', '<p>A micromax smartphone.</p>', 'Micromax', 1, '8500.00', '561708.jpg', '561708_thumb.jpg', '9.50', 'gms', 3, '1', '2019-08-19 12:27:17', '2019-08-19 15:57:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblcategories`
--
ALTER TABLE `tblcategories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblproducts`
--
ALTER TABLE `tblproducts`
  ADD PRIMARY KEY (`pro_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblcategories`
--
ALTER TABLE `tblcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblproducts`
--
ALTER TABLE `tblproducts`
  MODIFY `pro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
