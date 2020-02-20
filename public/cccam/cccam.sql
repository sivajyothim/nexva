-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 02 ott, 2010 at 10:24 PM
-- Versione MySQL: 5.1.41
-- Versione PHP: 5.3.2-1ubuntu4.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cccam`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_accrenew`
--

CREATE TABLE IF NOT EXISTS `cccam_accrenew` (
  `ren_id` int(11) NOT NULL AUTO_INCREMENT,
  `ren_date` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `quote_value` varchar(50) NOT NULL,
  `quote_to` varchar(50) NOT NULL,
  PRIMARY KEY (`ren_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `cccam_accrenew`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_channelinfo`
--

CREATE TABLE IF NOT EXISTS `cccam_channelinfo` (
  `chan_id` int(11) NOT NULL AUTO_INCREMENT,
  `chan_caid` varchar(10) DEFAULT NULL,
  `chan_ident` varchar(10) DEFAULT NULL,
  `chan_chaid` varchar(10) DEFAULT NULL,
  `chan_provider` varchar(20) DEFAULT NULL,
  `chan_category` varchar(30) DEFAULT NULL,
  `chan_channel_name` varchar(50) DEFAULT NULL,
  `chan_encription` varchar(50) DEFAULT NULL,
  `chan_sat` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`chan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1253 ;

--
-- Dump dei dati per la tabella `cccam_channelinfo`
--

INSERT INTO `cccam_channelinfo` (`chan_id`, `chan_caid`, `chan_ident`, `chan_chaid`, `chan_provider`, `chan_category`, `chan_channel_name`, `chan_encription`, `chan_sat`) VALUES
(2, '0919', '000000', '2DCA', 'Sky Italia', 'Cinema', 'Sky Cinema +1', 'NDS', '(13E)'),
(3, '0919', '000000', '2CBC', 'Sky Italia', 'Cinema', 'Sky Cinema +24', 'NDS', '(13E)'),
(4, '0919', '000000', '106A', 'Sky Italia', 'Cinema', 'Sky Cinema 1 HD', 'NDS', '(13E)'),
(5, '0919', '000000', '2B6B', 'Sky Italia', 'Cinema', 'Sky Cinema Max', 'NDS', '(13E)'),
(6, '0919', '000000', '2DC3', 'Sky Italia', 'Cinema', 'Sky Cinema Max +1', 'NDS', '(13E)'),
(7, '0919', '000000', '106e', 'Sky Italia', 'Cinema', 'Sky Cinema Max HD', 'NDS', '(13E)'),
(8, '0919', '000000', '2D52', 'Sky Italia', 'Cinema', 'Sky Cinema Classics', 'NDS', '(13E)'),
(9, '0919', '000000', '2B5F', 'Sky Italia', 'Cinema', 'Sky Cinema Mania', 'NDS', '(13E)'),
(10, '0919', '000000', '2B61', 'Sky Italia', 'Cinema', 'Sky Cinema Family', 'NDS', '(13E)'),
(11, '0919', '000000', '2DCC', 'Sky Italia', 'Cinema', 'Sky Cinema Hits', 'NDS', '(13E)'),
(12, '0919', '000000', '106C', 'Sky Italia', 'Cinema', 'Sky Cinema Hits HD', 'NDS', '(13E)'),
(13, '0919', '000000', '058E', 'Sky Italia', 'Cinema', 'Sky Cinema Italia', 'NDS', '(13E)'),
(14, '0919', '000000', '1FC9', 'Sky Italia', 'Cinema', 'Cult', 'NDS', '(13E)'),
(15, '0919', '000000', '2505', 'Sky Italia', 'Cinema', 'MGM', 'NDS', '(13E)'),
(16, '0919', '000000', '2DC6', 'Sky Italia', 'Intrattenimento', 'Sky Uno', 'NDS', '(13E)'),
(17, '0919', '000000', '2B70', 'Sky Italia', 'Intrattenimento', 'Sky Uno +1', 'NDS', '(13E)'),
(18, '0919', '000000', '1FBA', 'Sky Italia', 'Intrattenimento', 'Fox', 'NDS', '(13E)'),
(19, '0919', '000000', '0E33', 'Sky Italia', 'Intrattenimento', 'Fox +1', 'NDS', '(13E)'),
(20, '0919', '000000', '379D', 'Sky Italia', 'Intrattenimento', 'Fox HD', 'NDS', '(13E)'),
(21, '0919', '000000', '1FC5', 'Sky Italia', 'Intrattenimento', 'Fox Life', 'NDS', '(13E)'),
(22, '0919', '000000', '38C1', 'Sky Italia', 'Intrattenimento', 'Fox Life +1', 'NDS', '(13E)'),
(23, '0919', '000000', '1FCB', 'Sky Italia', 'Intrattenimento', 'Fox Crime', 'NDS', '(13E)'),
(24, '0919', '000000', '0E04', 'Sky Italia', 'Intrattenimento', 'Fox Crime +1', 'NDS', '(13E)'),
(25, '0919', '000000', '379E', 'Sky Italia', 'Intrattenimento', 'Fox Crime HD Italia', 'NDS', '(13E)'),
(26, '0919', '000000', '1fc8', 'Sky Italia', 'Intrattenimento', 'Fox Retro', 'NDS', '(13E)'),
(27, '0919', '000000', '1128', 'Sky Italia', 'Intrattenimento', 'FX', 'NDS', '(13E)'),
(28, '0919', '000000', '05A3', 'Sky Italia', 'Intrattenimento', 'AXN', 'NDS', '(13E)'),
(29, '0919', '000000', '3902', 'Sky Italia', 'Intrattenimento', 'Hallmark', 'NDS', '(13E)'),
(30, '0919', '000000', '1FBC', 'Sky Italia', 'Intrattenimento', 'Fantasy', 'NDS', '(13E)'),
(31, '0919', '000000', '2C36', 'Sky Italia', 'Intrattenimento', 'Comedy Central', 'NDS', '(13E)'),
(32, '0919', '000000', '05ca', 'Sky Italia', 'Intrattenimento', 'Comedy Central +1', 'NDS', '(13E)'),
(33, '0919', '000000', '0DCA', 'Sky Italia', 'Intrattenimento', 'Jimmy', 'NDS', '(13E)'),
(34, '0919', '000000', '2B67', 'Sky Italia', 'Intrattenimento', 'E! Entertainment', 'NDS', '(13E)'),
(35, '0919', '000000', '24CF', 'Sky Italia', 'Intrattenimento', 'Lei', 'NDS', '(13E)'),
(36, '0919', '000000', '3C0B', 'Sky Italia', 'Intrattenimento', 'Mediaset Plus', 'NDS', '(13E)'),
(37, '0919', '000000', '2b66', 'Sky Italia', 'Intrattenimento', 'Cielo', 'NDS', '(13E)'),
(38, '0919', '000000', '2cfa', 'Sky Italia', 'Intrattenimento', 'GXT', 'NDS', '(13E)'),
(39, '0919', '000000', '2502', 'Sky Italia', 'Intrattenimento', 'GXT +1', 'NDS', '(13E)'),
(40, '0919', '000000', '05a2', 'Sky Italia', 'Intrattenimento', 'BBC Entertainment', 'NDS', '(13E)'),
(41, '0919', '000000', '2C37', 'Sky Italia', 'Intrattenimento', 'La 7', 'NDS', '(13E)'),
(42, '0919', '000000', '059F', 'Sky Italia', 'Intrattenimento', 'Lady Channel', 'NDS', '(13E)'),
(43, '0919', '000000', '2B0F', 'Sky Italia', 'Sport', 'Sky Sport 1 HD', 'NDS', '(13E)'),
(44, '0919', '000000', '2b10', 'Sky Italia', 'Sport', 'Sky Sport 2 HD', 'NDS', '(13E)'),
(45, '0919', '000000', '2b11', 'Sky Italia', 'Sport', 'Sky Sport 3 HD', 'NDS', '(13E)'),
(46, '0919', '000000', '2AA8', 'Sky Italia', 'Sport', 'Sky Sport 1', 'NDS', '(13E)'),
(47, '0919', '000000', '2AA9', 'Sky Italia', 'Sport', 'Sky Sport 2', 'NDS', '(13E)'),
(48, '0919', '000000', '2DD9', 'Sky Italia', 'Sport', 'Sky Sport 3', 'NDS', '(13E)'),
(49, '0919', '000000', '2DCD', 'Sky Italia', 'Sport', 'Sky Sport Extra', 'NDS', '(13E)'),
(50, '0919', '000000', '2AB1', 'Sky Italia', 'Sport', 'Sky Sport 24', 'NDS', '(13E)'),
(51, '0919', '000000', '1c53', 'Sky Italia', 'Sport', 'Sky Olimpia 1 HD', 'NDS', '(13E)'),
(52, '0919', '000000', '1c54', 'Sky Italia', 'Sport', 'Sky Olimpia 2 HD', 'NDS', '(13E)'),
(53, '0919', '000000', '1c55', 'Sky Italia', 'Cinema', 'Sky Cinema +1 HD', 'NDS', '(13E)'),
(54, '0919', '000000', '1c56', 'Sky Italia', 'Sport', 'Sky Olimpia 4 HD', 'NDS', '(13E)'),
(55, '0919', '000000', '1c57', 'Sky Italia', 'Cinema', 'Sky Cinema Family HD', 'NDS', '(13E)'),
(56, '0919', '000000', '2dc9', 'Sky Italia', 'Sport', 'Sky Olimpia 1', 'NDS', '(13E)'),
(57, '0919', '000000', '2d59', 'Sky Italia', 'Sport', 'Sky Olimpia 2', 'NDS', '(13E)'),
(58, '0919', '000000', '2d5a', 'Sky Italia', 'Sport', 'Sky Olimpia 3', 'NDS', '(13E)'),
(59, '0919', '000000', '2d5b', 'Sky Italia', 'Sport', 'Sky Olimpia 4', 'NDS', '(13E)'),
(60, '0919', '000000', '2dd0', 'Sky Italia', 'Sport', 'Sky Olimpia 5', 'NDS', '(13E)'),
(61, '0919', '000000', '2C3A', 'Sky Italia', 'Sport', 'Milan Channel', 'NDS', '(13E)'),
(62, '0919', '000000', '2CC0', 'Sky Italia', 'Sport', 'Inter Channel', 'NDS', '(13E)'),
(63, '0919', '000000', '2CBF', 'Sky Italia', 'Sport', 'Juventus Channel', 'NDS', '(13E)'),
(64, '0919', '000000', '2506', 'Sky Italia', 'Sport', 'Roma Channel', 'NDS', '(13E)'),
(65, '0919', '000000', '2C27', 'Sky Italia', 'Sport', 'Sport Italia 1', 'NDS', '(13E)'),
(66, '0919', '000000', '2c2a', 'Sky Italia', 'Sport', 'Sport Italia 2', 'NDS', '(13E)'),
(67, '0919', '000000', '3311', 'Sky Italia', 'Sport', 'ESP HD Italy', 'NDS', '(13E)'),
(68, '0919', '000000', '3420', 'Sky Italia', 'Sport', 'Eurosport 1', 'NDS', '(13E)'),
(69, '0919', '000000', '3439', 'Sky Italia', 'Sport', 'Eurosport 2', 'NDS', '(13E)'),
(70, '0919', '000000', '3437', 'Sky Italia', 'Sport', 'Eurosport News', 'NDS', '(13E)'),
(71, '0919', '000000', '2CF7', 'Sky Italia', 'Sport', 'ESPN Classic Sport Italia', 'NDS', '(13E)'),
(72, '0919', '000000', '2b7a', 'Sky Italia', 'Sport', 'ESPN America', 'NDS', '(13E)'),
(73, '0919', '000000', '0e03', 'Sky Italia', 'Sport', 'Moto Tv', 'NDS', '(13E)'),
(74, '0919', '000000', '0e03', 'Sky Italia', 'Sport', 'Nuvolari', 'NDS', '(13E)'),
(75, '0919', '000000', '10e3', 'Sky Italia', 'Sport', 'Snai Sat', 'NDS', '(13E)'),
(76, '0919', '000000', '2AAA', 'Sky Italia', 'Sport', 'Sky Super Calcio', 'NDS', '(13E)'),
(77, '0919', '000000', '2B0E', 'Sky Italia', 'Sport', 'SKY Super Calcio HD', 'NDS', '(13E)'),
(78, '0919', '000000', '379f', 'Sky Italia', 'Sport', 'Sky Calcio 1 HD', 'NDS', '(13E)'),
(79, '0919', '000000', '37a1', 'Sky Italia', 'Sport', 'Sky Calcio 2 HD', 'NDS', '(13E)'),
(80, '0919', '000000', '0f72', 'Sky Italia', 'Sport', 'Sky Calcio 3 HD', 'NDS', '(13E)'),
(81, '0919', '000000', '0f73', 'Sky Italia', 'Sport', 'Sky Calcio 4 HD', 'NDS', '(13E)'),
(82, '0919', '000000', '0ff3', 'Sky Italia', 'Sport', 'Sky Calcio 5 HD', 'NDS', '(13E)'),
(83, '0919', '000000', '0ffb', 'Sky Italia', 'Sport', 'Sky Calcio 6 HD', 'NDS', '(13E)'),
(84, '0919', '000000', '1087', 'Sky Italia', 'Sport', 'Sky Calcio 7 HD', 'NDS', '(13E)'),
(85, '0919', '000000', '2b12', 'Sky Italia', 'Sport', 'Sky Calcio 8 HD', 'NDS', '(13E)'),
(86, '0919', '000000', '2DC7', 'Sky Italia', 'Sport', 'Sky Calcio 1', 'NDS', '(13E)'),
(87, '0919', '000000', '2BCF', 'Sky Italia', 'Sport', 'Sky Calcio 2', 'NDS', '(13E)'),
(88, '0919', '000000', '2BD1', 'Sky Italia', 'Sport', 'Sky Calcio 3', 'NDS', '(13E)'),
(89, '0919', '000000', '2BD3', 'Sky Italia', 'Sport', 'Sky Calcio 4', 'NDS', '(13E)'),
(90, '0919', '000000', '2BD5', 'Sky Italia', 'Sport', 'Sky Calcio 5', 'NDS', '(13E)'),
(91, '0919', '000000', '2BD7', 'Sky Italia', 'Sport', 'Sky Calcio 6', 'NDS', '(13E)'),
(92, '0919', '000000', '2BD9', 'Sky Italia', 'Sport', 'Sky Calcio 7', 'NDS', '(13E)'),
(93, '0919', '000000', '2D56', 'Sky Italia', 'Sport', 'Sky Calcio 8', 'NDS', '(13E)'),
(94, '0919', '000000', '2D5D', 'Sky Italia', 'Sport', 'Sky Calcio 9', 'NDS', '(13E)'),
(95, '0919', '000000', '2D5F', 'Sky Italia', 'Sport', 'Sky Calcio 10', 'NDS', '(13E)'),
(96, '0919', '000000', '2D61', 'Sky Italia', 'Sport', 'Sky Calcio 11', 'NDS', '(13E)'),
(97, '0919', '000000', '2D62', 'Sky Italia', 'Sport', 'Sky Calcio 12', 'NDS', '(13E)'),
(98, '0919', '000000', '2D63', 'Sky Italia', 'Sport', 'Sky Calcio 13', 'NDS', '(13E)'),
(99, '0919', '000000', '2D58', 'Sky Italia', 'Sport', 'Sky Calcio 14', 'NDS', '(13E)'),
(100, '0919', '000000', '2DC8', 'Sky Italia', 'Sport', 'Sky Calcio 15', 'NDS', '(13E)'),
(101, '0919', '000000', '2AAB', 'Sky Italia', 'Sport', 'Sport Active 1', 'NDS', '(13E)'),
(102, '0919', '000000', '2AAC', 'Sky Italia', 'Sport', 'Sport Active 2', 'NDS', '(13E)'),
(103, '0919', '000000', '2AB7', 'Sky Italia', 'Sport', 'Sport Active 3', 'NDS', '(13E)'),
(104, '0919', '000000', '0E39', 'Sky Italia', 'Cultura', 'National Geographic', 'NDS', '(13E)'),
(105, '0919', '000000', '1FBD', 'Sky Italia', 'Cultura', 'National Geographic +1', 'NDS', '(13E)'),
(106, '0919', '000000', '379c', 'Sky Italia', 'Cultura', 'National Geographic HD', 'NDS', '(13E)'),
(107, '0919', '000000', '1FB9', 'Sky Italia', 'Cultura', 'National Geographic Adventure', 'NDS', '(13E)'),
(108, '0919', '000000', '1FBE', 'Sky Italia', 'Cultura', 'NatGeo Wild', 'NDS', '(13E)'),
(109, '0919', '000000', '1109', 'Sky Italia', 'Cultura', 'NatGeo Music', 'NDS', '(13E)'),
(110, '0919', '000000', '1069', 'Sky Italia', 'Cultura', 'Discovery Channel HD', 'NDS', '(13E)'),
(111, '0919', '000000', '2AB3', 'Sky Italia', 'Cultura', 'Discovery Channel', 'NDS', '(13E)'),
(112, '0919', '000000', '3BE3', 'Sky Italia', 'Cultura', 'Discovery Channel +1', 'NDS', '(13E)'),
(113, '0919', '000000', '2D02', 'Sky Italia', 'Cultura', 'Discovery Real Time', 'NDS', '(13E)'),
(114, '0919', '000000', '05BB', 'Sky Italia', 'Cultura', 'Discovery Science Channel', 'NDS', '(13E)'),
(115, '0919', '000000', '05B9', 'Sky Italia', 'Cultura', 'Discovery Travel & Living Europe', 'NDS', '(13E)'),
(116, '0919', '000000', '2CFF', 'Sky Italia', 'Cultura', 'Animal Planet', 'NDS', '(13E)'),
(117, '0919', '000000', '0DC0', 'Sky Italia', 'Cultura', 'Caccia e Pesca', 'NDS', '(13E)'),
(118, '0919', '000000', '0DC7', 'Sky Italia', 'Cultura', 'History Channel', 'NDS', '(13E)'),
(119, '0919', '000000', '1FBB', 'Sky Italia', 'Cultura', 'History Channel +1', 'NDS', '(13E)'),
(120, '0919', '000000', '1fcc', 'Sky Italia', 'Cultura', 'Gambero Rosso', 'NDS', '(13E)'),
(121, '0919', '000000', '0E2E', 'Sky Italia', 'Cultura', 'Alice', 'NDS', '(13E)'),
(122, '0919', '000000', '0E31', 'Sky Italia', 'Cultura', 'Marco Polo', 'NDS', '(13E)'),
(123, '0919', '000000', '0E2F', 'Sky Italia', 'Cultura', 'Leonardo', 'NDS', '(13E)'),
(124, '0919', '000000', '0DFA', 'Sky Italia', 'Cultura', 'Yacht & Sail', 'NDS', '(13E)'),
(125, '0919', '000000', '110B', 'Sky Italia', 'Cultura', 'Current', 'NDS', '(13E)'),
(126, '0919', '000000', '2CFE', 'Sky Italia', 'Musica', 'MTV Italia', 'NDS', '(13E)'),
(127, '0919', '000000', '2D17', 'Sky Italia', 'Musica', 'MTV Hits', 'NDS', '(13E)'),
(128, '0919', '000000', '2D13', 'Sky Italia', 'Musica', 'MTV Pulse Italy', 'NDS', '(13E)'),
(129, '0919', '000000', '2D16', 'Sky Italia', 'Musica', 'MTV Brand New', 'NDS', '(13E)'),
(130, '0919', '000000', '2D03', 'Sky Italia', 'Musica', 'MTV Gold', 'NDS', '(13E)'),
(131, '0919', '000000', '2D08', 'Sky Italia', 'Musica', 'VH-1', 'NDS', '(13E)'),
(132, '0919', '000000', '2c28', 'Sky Italia', 'Musica', 'myDeejay', 'NDS', '(13E)'),
(133, '0919', '000000', '38F3', 'Sky Italia', 'Musica', 'Music Box Italy', 'NDS', '(13E)'),
(134, '0919', '000000', '3C1C', 'Sky Italia', 'Musica', 'Match Music TV', 'NDS', '(13E)'),
(135, '0919', '000000', '3BCD', 'Sky Italia', 'Musica', 'Hip Hop TV Italia', 'NDS', '(13E)'),
(136, '0919', '000000', '38D0', 'Sky Italia', 'Musica', 'Live', 'NDS', '(13E)'),
(137, '0919', '000000', '3BE5', 'Sky Italia', 'Musica', 'Rock TV', 'NDS', '(13E)'),
(138, '0919', '000000', '059d', 'Sky Italia', 'Musica', 'Onda Latina', 'NDS', '(13E)'),
(139, '0919', '000000', '2C33', 'Sky Italia', 'Musica', 'Video Italia', 'NDS', '(13E)'),
(140, '0919', '000000', '38E3', 'Sky Italia', 'Musica', 'Voce', 'NDS', '(13E)'),
(141, '0919', '000000', '2CC8', 'Sky Italia', 'Musica', 'Classica', 'NDS', '(13E)'),
(142, '0919', '000000', '2B6E', 'Sky Italia', 'Bambini', 'Disney Channel Italia', 'NDS', '(13E)'),
(143, '0919', '000000', '2509', 'Sky Italia', 'Bambini', 'Disney Channel Italia +1', 'NDS', '(13E)'),
(144, '0919', '000000', '3BCB', 'Sky Italia', 'Bambini', 'Disney XD Italia', 'NDS', '(13E)'),
(145, '0919', '000000', '2CBD', 'Sky Italia', 'Bambini', 'Disney XD Italia +1', 'NDS', '(13E)'),
(146, '0919', '000000', '2CBE', 'Sky Italia', 'Bambini', 'Disney Channel U.K.', 'NDS', '(13E)'),
(147, '0919', '000000', '2AB4', 'Sky Italia', 'Bambini', 'Toon Disney Italia', 'NDS', '(13E)'),
(148, '0919', '000000', '2C9C', 'Sky Italia', 'Bambini', 'Toon Disney +1 Italia', 'NDS', '(13E)'),
(149, '0919', '000000', '2B7C', 'Sky Italia', 'Bambini', 'Playhouse Disney Italia', 'NDS', '(13E)'),
(150, '0919', '000000', '3BC5', 'Sky Italia', 'Bambini', 'Playhouse Disney Italia +1', 'NDS', '(13E)'),
(151, '0919', '000000', '3BCC', 'Sky Italia', 'Bambini', 'Cartoon Network', 'NDS', '(13E)'),
(152, '0919', '000000', '2504', 'Sky Italia', 'Bambini', 'Cartoon Network +1', 'NDS', '(13E)'),
(153, '0919', '000000', '2CF9', 'Sky Italia', 'Bambini', 'Boomerang', 'NDS', '(13E)'),
(154, '0919', '000000', '2507', 'Sky Italia', 'Bambini', 'Boomerang +1', 'NDS', '(13E)'),
(155, '0919', '000000', '2CC4', 'Sky Italia', 'Bambini', 'DeA Kids', 'NDS', '(13E)'),
(156, '0919', '000000', '3C04', 'Sky Italia', 'Bambini', 'DeA Kids +1', 'NDS', '(13E)'),
(157, '0919', '000000', '2C38', 'Sky Italia', 'Bambini', 'Nickelodeon Italia', 'NDS', '(13E)'),
(158, '0919', '000000', '2C31', 'Sky Italia', 'Bambini', 'Nickelodeon +1', 'NDS', '(13E)'),
(159, '0919', '000000', '0590', 'Sky Italia', 'Bambini', 'Nickelodeon Jr', 'NDS', '(13E)'),
(160, '0919', '000000', '0DC1', 'Sky Italia', 'Bambini', 'JimJam', 'NDS', '(13E)'),
(161, '0919', '000000', '2C2E', 'Sky Italia', 'Bambini', 'Baby TV', 'NDS', '(13E)'),
(162, '0919', '000000', '24E7', 'Sky Italia', 'Bambini', 'Cooltoon', 'NDS', '(13E)'),
(163, '0919', '000000', '110A', 'Sky Italia', 'Notizie', 'Sky TG 24', 'NDS', '(13E)'),
(164, '0919', '000000', '10EC', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(165, '0919', '000000', '10EA', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(166, '0919', '000000', '10E9', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(167, '0919', '000000', '10E8', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(168, '0919', '000000', '10E7', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(169, '0919', '000000', '10E1', 'Sky Italia', 'Notizie', 'Sky Meteo24', 'NDS', '(13E)'),
(170, '0919', '000000', '05a1', 'Sky Italia', 'Notizie', 'CNN Intl', 'NDS', '(13E)'),
(171, '0919', '000000', '24E4', 'Sky Italia', 'Notizie', 'CNBC Europe', 'NDS', '(13E)'),
(172, '0919', '000000', '0599', 'Sky Italia', 'Notizie', 'Class CNBC', 'NDS', '(13E)'),
(173, '0919', '000000', '24E5', 'Sky Italia', 'Notizie', 'Sky News International', 'NDS', '(13E)'),
(174, '0919', '000000', '24E6', 'Sky Italia', 'Notizie', 'Fox News', 'NDS', '(13E)'),
(175, '0919', '000000', '2B0D', 'Sky Italia', 'Altri', 'Sky Focus HD', 'NDS', '(13E)'),
(176, '0919', '000000', '38DF', 'Sky Italia', 'Altri', 'Sky Assist', 'NDS', '(13E)'),
(177, '0919', '000000', '0dcc', 'Sky Italia', 'Altri', 'Sky Service', 'NDS', '(13E)'),
(178, '0919', '000000', '3BE6', 'Sky Italia', 'Altri', 'Music on SKY', 'NDS', '(13E)'),
(179, '0919', '000000', '3bed', 'Sky Italia', 'Altri', 'Music On SKY', 'NDS', '(13E)'),
(180, '093B', '000000', '2B5D', 'Sky Italia', 'Cinema', 'Sky Cinema 1', 'NDS', '(13E)'),
(181, '093B', '000000', '2DCA', 'Sky Italia', 'Cinema', 'Sky Cinema +1', 'NDS', '(13E)'),
(182, '093B', '000000', '2CBC', 'Sky Italia', 'Cinema', 'Sky Cinema +24', 'NDS', '(13E)'),
(183, '093B', '000000', '106A', 'Sky Italia', 'Cinema', 'Sky Cinema 1 HD', 'NDS', '(13E)'),
(184, '093B', '000000', '2B6B', 'Sky Italia', 'Cinema', 'Sky Cinema Max', 'NDS', '(13E)'),
(185, '093B', '000000', '2CBB', 'Sky Italia', 'Cinema', 'Sky Cinema Max +1', 'NDS', '(13E)'),
(186, '093B', '000000', '106e', 'Sky Italia', 'Cinema', 'Sky Cinema Max HD', 'NDS', '(13E)'),
(187, '093B', '000000', '2D52', 'Sky Italia', 'Cinema', 'Sky Cinema Classics', 'NDS', '(13E)'),
(188, '093B', '000000', '2B5F', 'Sky Italia', 'Cinema', 'Sky Cinema Mania', 'NDS', '(13E)'),
(189, '093B', '000000', '2B61', 'Sky Italia', 'Cinema', 'Sky Cinema Family', 'NDS', '(13E)'),
(190, '093B', '000000', '2DCC', 'Sky Italia', 'Cinema', 'Sky Cinema Hits', 'NDS', '(13E)'),
(191, '093B', '000000', '106C', 'Sky Italia', 'Cinema', 'Sky Cinema Hits HD', 'NDS', '(13E)'),
(192, '093B', '000000', '058E', 'Sky Italia', 'Cinema', 'Sky Cinema Italia', 'NDS', '(13E)'),
(193, '093B', '000000', '1FC9', 'Sky Italia', 'Cinema', 'Cult', 'NDS', '(13E)'),
(194, '093B', '000000', '2505', 'Sky Italia', 'Cinema', 'MGM', 'NDS', '(13E)'),
(195, '093B', '000000', '2DC6', 'Sky Italia', 'Intrattenimento', 'Sky Uno', 'NDS', '(13E)'),
(196, '093B', '000000', '2B70', 'Sky Italia', 'Intrattenimento', 'Sky Uno +1', 'NDS', '(13E)'),
(197, '093B', '000000', '1FBA', 'Sky Italia', 'Intrattenimento', 'Fox', 'NDS', '(13E)'),
(198, '093B', '000000', '0E33', 'Sky Italia', 'Intrattenimento', 'Fox +1', 'NDS', '(13E)'),
(199, '093B', '000000', '379D', 'Sky Italia', 'Intrattenimento', 'Fox HD', 'NDS', '(13E)'),
(200, '093B', '000000', '1FC5', 'Sky Italia', 'Intrattenimento', 'Fox Life', 'NDS', '(13E)'),
(201, '093B', '000000', '38C1', 'Sky Italia', 'Intrattenimento', 'Fox Life +1', 'NDS', '(13E)'),
(202, '093B', '000000', '1FCB', 'Sky Italia', 'Intrattenimento', 'Fox Crime', 'NDS', '(13E)'),
(203, '093B', '000000', '0E04', 'Sky Italia', 'Intrattenimento', 'Fox Crime +1', 'NDS', '(13E)'),
(204, '093B', '000000', '379E', 'Sky Italia', 'Intrattenimento', 'Fox Crime HD Italia', 'NDS', '(13E)'),
(205, '093B', '000000', '1fc8', 'Sky Italia', 'Intrattenimento', 'Fox Retro', 'NDS', '(13E)'),
(206, '093B', '000000', '1128', 'Sky Italia', 'Intrattenimento', 'FX', 'NDS', '(13E)'),
(207, '093B', '000000', '05A3', 'Sky Italia', 'Intrattenimento', 'AXN', 'NDS', '(13E)'),
(208, '093B', '000000', '3902', 'Sky Italia', 'Intrattenimento', 'Hallmark', 'NDS', '(13E)'),
(209, '093B', '000000', '1FBC', 'Sky Italia', 'Intrattenimento', 'Fantasy', 'NDS', '(13E)'),
(210, '093B', '000000', '2C36', 'Sky Italia', 'Intrattenimento', 'Comedy Central', 'NDS', '(13E)'),
(211, '093B', '000000', '05ca', 'Sky Italia', 'Intrattenimento', 'Comedy Central +1', 'NDS', '(13E)'),
(212, '093B', '000000', '0DCA', 'Sky Italia', 'Intrattenimento', 'Jimmy', 'NDS', '(13E)'),
(213, '093B', '000000', '2B67', 'Sky Italia', 'Intrattenimento', 'E! Entertainment', 'NDS', '(13E)'),
(214, '093B', '000000', '24CF', 'Sky Italia', 'Intrattenimento', 'Lei', 'NDS', '(13E)'),
(215, '093B', '000000', '3C0B', 'Sky Italia', 'Intrattenimento', 'Mediaset Plus', 'NDS', '(13E)'),
(216, '093B', '000000', '2b66', 'Sky Italia', 'Intrattenimento', 'Cielo', 'NDS', '(13E)'),
(217, '093B', '000000', '2cfa', 'Sky Italia', 'Intrattenimento', 'GXT', 'NDS', '(13E)'),
(218, '093B', '000000', '2502', 'Sky Italia', 'Intrattenimento', 'GXT +1', 'NDS', '(13E)'),
(219, '093B', '000000', '05a2', 'Sky Italia', 'Intrattenimento', 'BBC Entertainment', 'NDS', '(13E)'),
(220, '093B', '000000', '2C37', 'Sky Italia', 'Intrattenimento', 'La 7', 'NDS', '(13E)'),
(221, '093B', '000000', '059F', 'Sky Italia', 'Intrattenimento', 'Lady Channel', 'NDS', '(13E)'),
(222, '093B', '000000', '2B0F', 'Sky Italia', 'Sport', 'Sky Sport 1 HD', 'NDS', '(13E)'),
(223, '093B', '000000', '2b10', 'Sky Italia', 'Sport', 'Sky Sport 2 HD', 'NDS', '(13E)'),
(224, '093B', '000000', '2b11', 'Sky Italia', 'Sport', 'Sky Sport 3 HD', 'NDS', '(13E)'),
(225, '093B', '000000', '2AA8', 'Sky Italia', 'Sport', 'Sky Sport 1', 'NDS', '(13E)'),
(226, '093B', '000000', '2AA9', 'Sky Italia', 'Sport', 'Sky Sport 2', 'NDS', '(13E)'),
(227, '093B', '000000', '2DD9', 'Sky Italia', 'Sport', 'Sky Sport 3', 'NDS', '(13E)'),
(228, '093B', '000000', '2DCD', 'Sky Italia', 'Sport', 'Sky Sport Extra', 'NDS', '(13E)'),
(229, '093B', '000000', '2AB1', 'Sky Italia', 'Sport', 'Sky Sport 24', 'NDS', '(13E)'),
(230, '093B', '000000', '1c53', 'Sky Italia', 'Sport', 'Sky Olimpia 1 HD', 'NDS', '(13E)'),
(231, '093B', '000000', '1c54', 'Sky Italia', 'Sport', 'Sky Olimpia 2 HD', 'NDS', '(13E)'),
(232, '093B', '000000', '1c55', 'Sky Italia', 'Sport', 'Sky Olimpia 3 HD', 'NDS', '(13E)'),
(233, '093B', '000000', '1c56', 'Sky Italia', 'Sport', 'Sky Olimpia 4 HD', 'NDS', '(13E)'),
(234, '093B', '000000', '1c57', 'Sky Italia', 'Sport', 'Sky Olimpia 5 HD', 'NDS', '(13E)'),
(235, '093B', '000000', '2dc9', 'Sky Italia', 'Sport', 'Sky Olimpia 1', 'NDS', '(13E)'),
(236, '093B', '000000', '2d59', 'Sky Italia', 'Sport', 'Sky Olimpia 2', 'NDS', '(13E)'),
(237, '093B', '000000', '2d5a', 'Sky Italia', 'Sport', 'Sky Olimpia 3', 'NDS', '(13E)'),
(238, '093B', '000000', '2d5b', 'Sky Italia', 'Sport', 'Sky Olimpia 4', 'NDS', '(13E)'),
(239, '093B', '000000', '2dd0', 'Sky Italia', 'Sport', 'Sky Olimpia 5', 'NDS', '(13E)'),
(240, '093B', '000000', '2C3A', 'Sky Italia', 'Sport', 'Milan Channel', 'NDS', '(13E)'),
(241, '093B', '000000', '2CC0', 'Sky Italia', 'Sport', 'Inter Channel', 'NDS', '(13E)'),
(242, '093B', '000000', '2CBF', 'Sky Italia', 'Sport', 'Juventus Channel', 'NDS', '(13E)'),
(243, '093B', '000000', '2506', 'Sky Italia', 'Sport', 'Roma Channel', 'NDS', '(13E)'),
(244, '093B', '000000', '2C27', 'Sky Italia', 'Sport', 'Sport Italia 1', 'NDS', '(13E)'),
(245, '093B', '000000', '2c2a', 'Sky Italia', 'Sport', 'Sport Italia 2', 'NDS', '(13E)'),
(246, '093B', '000000', '3311', 'Sky Italia', 'Sport', 'ESP HD Italy', 'NDS', '(13E)'),
(247, '093B', '000000', '3420', 'Sky Italia', 'Sport', 'Eurosport 1', 'NDS', '(13E)'),
(248, '093B', '000000', '3439', 'Sky Italia', 'Sport', 'Eurosport 2', 'NDS', '(13E)'),
(249, '093B', '000000', '3437', 'Sky Italia', 'Sport', 'Eurosport News', 'NDS', '(13E)'),
(250, '093B', '000000', '2CF7', 'Sky Italia', 'Sport', 'ESPN Classic Sport Italia', 'NDS', '(13E)'),
(251, '093B', '000000', '2b7a', 'Sky Italia', 'Sport', 'ESPN America', 'NDS', '(13E)'),
(252, '093B', '000000', '0e03', 'Sky Italia', 'Sport', 'Moto Tv', 'NDS', '(13E)'),
(253, '093B', '000000', '0e03', 'Sky Italia', 'Sport', 'Nuvolari', 'NDS', '(13E)'),
(254, '093B', '000000', '10e3', 'Sky Italia', 'Sport', 'Snai Sat', 'NDS', '(13E)'),
(255, '093B', '000000', '2AAA', 'Sky Italia', 'Sport', 'Sky Super Calcio', 'NDS', '(13E)'),
(256, '093B', '000000', '2B0E', 'Sky Italia', 'Sport', 'SKY Super Calcio HD', 'NDS', '(13E)'),
(257, '093B', '000000', '379f', 'Sky Italia', 'Sport', 'Sky Calcio 1 HD', 'NDS', '(13E)'),
(258, '093B', '000000', '37a1', 'Sky Italia', 'Sport', 'Sky Calcio 2 HD', 'NDS', '(13E)'),
(259, '093B', '000000', '0f72', 'Sky Italia', 'Sport', 'Sky Calcio 3 HD', 'NDS', '(13E)'),
(260, '093B', '000000', '0f73', 'Sky Italia', 'Sport', 'Sky Calcio 4 HD', 'NDS', '(13E)'),
(261, '093B', '000000', '0ff3', 'Sky Italia', 'Sport', 'Sky Calcio 5 HD', 'NDS', '(13E)'),
(262, '093B', '000000', '0ffb', 'Sky Italia', 'Sport', 'Sky Calcio 6 HD', 'NDS', '(13E)'),
(263, '093B', '000000', '1087', 'Sky Italia', 'Sport', 'Sky Calcio 7 HD', 'NDS', '(13E)'),
(264, '093B', '000000', '2b12', 'Sky Italia', 'Sport', 'Sky Calcio 8 HD', 'NDS', '(13E)'),
(265, '093B', '000000', '2DC7', 'Sky Italia', 'Sport', 'Sky Calcio 1', 'NDS', '(13E)'),
(266, '093B', '000000', '2BCF', 'Sky Italia', 'Sport', 'Sky Calcio 2', 'NDS', '(13E)'),
(267, '093B', '000000', '2BD1', 'Sky Italia', 'Sport', 'Sky Calcio 3', 'NDS', '(13E)'),
(268, '093B', '000000', '2BD3', 'Sky Italia', 'Sport', 'Sky Calcio 4', 'NDS', '(13E)'),
(269, '093B', '000000', '2BD5', 'Sky Italia', 'Sport', 'Sky Calcio 5', 'NDS', '(13E)'),
(270, '093B', '000000', '2BD7', 'Sky Italia', 'Sport', 'Sky Calcio 6', 'NDS', '(13E)'),
(271, '093B', '000000', '2BD9', 'Sky Italia', 'Sport', 'Sky Calcio 7', 'NDS', '(13E)'),
(272, '093B', '000000', '2D56', 'Sky Italia', 'Sport', 'Sky Calcio 8', 'NDS', '(13E)'),
(273, '093B', '000000', '2D5D', 'Sky Italia', 'Sport', 'Sky Calcio 9', 'NDS', '(13E)'),
(274, '093B', '000000', '2D5F', 'Sky Italia', 'Sport', 'Sky Calcio 10', 'NDS', '(13E)'),
(275, '093B', '000000', '2D61', 'Sky Italia', 'Sport', 'Sky Calcio 11', 'NDS', '(13E)'),
(276, '093B', '000000', '2D62', 'Sky Italia', 'Sport', 'Sky Calcio 12', 'NDS', '(13E)'),
(277, '093B', '000000', '2D63', 'Sky Italia', 'Sport', 'Sky Calcio 13', 'NDS', '(13E)'),
(278, '093B', '000000', '2D58', 'Sky Italia', 'Sport', 'Sky Calcio 14', 'NDS', '(13E)'),
(279, '093B', '000000', '2DC8', 'Sky Italia', 'Sport', 'Sky Calcio 15', 'NDS', '(13E)'),
(280, '093B', '000000', '2AAB', 'Sky Italia', 'Sport', 'Sport Active 1', 'NDS', '(13E)'),
(281, '093B', '000000', '2AAC', 'Sky Italia', 'Sport', 'Sport Active 2', 'NDS', '(13E)'),
(282, '093B', '000000', '2AB7', 'Sky Italia', 'Sport', 'Sport Active 3', 'NDS', '(13E)'),
(283, '093B', '000000', '0E39', 'Sky Italia', 'Cultura', 'National Geographic', 'NDS', '(13E)'),
(284, '093B', '000000', '1FBD', 'Sky Italia', 'Cultura', 'National Geographic +1', 'NDS', '(13E)'),
(285, '093B', '000000', '379c', 'Sky Italia', 'Cultura', 'National Geographic HD', 'NDS', '(13E)'),
(286, '093B', '000000', '1FB9', 'Sky Italia', 'Cultura', 'National Geographic Adventure', 'NDS', '(13E)'),
(287, '093B', '000000', '1FBE', 'Sky Italia', 'Cultura', 'NatGeo Wild', 'NDS', '(13E)'),
(288, '093B', '000000', '1109', 'Sky Italia', 'Cultura', 'NatGeo Music', 'NDS', '(13E)'),
(289, '093B', '000000', '1069', 'Sky Italia', 'Cultura', 'Discovery Channel HD', 'NDS', '(13E)'),
(290, '093B', '000000', '2AB3', 'Sky Italia', 'Cultura', 'Discovery Channel', 'NDS', '(13E)'),
(291, '093B', '000000', '3BE3', 'Sky Italia', 'Cultura', 'Discovery Channel +1', 'NDS', '(13E)'),
(292, '093B', '000000', '2D02', 'Sky Italia', 'Cultura', 'Discovery Real Time', 'NDS', '(13E)'),
(293, '093B', '000000', '05BB', 'Sky Italia', 'Cultura', 'Discovery Science Channel', 'NDS', '(13E)'),
(294, '093B', '000000', '05B9', 'Sky Italia', 'Cultura', 'Discovery Travel & Living Europe', 'NDS', '(13E)'),
(295, '093B', '000000', '2CFF', 'Sky Italia', 'Cultura', 'Animal Planet', 'NDS', '(13E)'),
(296, '093B', '000000', '0DC0', 'Sky Italia', 'Cultura', 'Caccia e Pesca', 'NDS', '(13E)'),
(297, '093B', '000000', '0DC7', 'Sky Italia', 'Cultura', 'History Channel', 'NDS', '(13E)'),
(298, '093B', '000000', '1FBB', 'Sky Italia', 'Cultura', 'History Channel +1', 'NDS', '(13E)'),
(299, '093B', '000000', '1fcc', 'Sky Italia', 'Cultura', 'Gambero Rosso', 'NDS', '(13E)'),
(300, '093B', '000000', '0E2E', 'Sky Italia', 'Cultura', 'Alice', 'NDS', '(13E)'),
(301, '093B', '000000', '0E31', 'Sky Italia', 'Cultura', 'Marco Polo', 'NDS', '(13E)'),
(302, '093B', '000000', '0E2F', 'Sky Italia', 'Cultura', 'Leonardo', 'NDS', '(13E)'),
(303, '093B', '000000', '0DFA', 'Sky Italia', 'Cultura', 'Yacht & Sail', 'NDS', '(13E)'),
(304, '093B', '000000', '110B', 'Sky Italia', 'Cultura', 'Current', 'NDS', '(13E)'),
(305, '093B', '000000', '2CFE', 'Sky Italia', 'Musica', 'MTV Italia', 'NDS', '(13E)'),
(306, '093B', '000000', '2D17', 'Sky Italia', 'Musica', 'MTV Hits', 'NDS', '(13E)'),
(307, '093B', '000000', '2D13', 'Sky Italia', 'Musica', 'MTV Pulse Italy', 'NDS', '(13E)'),
(308, '093B', '000000', '2D16', 'Sky Italia', 'Musica', 'MTV Brand New', 'NDS', '(13E)'),
(309, '093B', '000000', '2D03', 'Sky Italia', 'Musica', 'MTV Gold', 'NDS', '(13E)'),
(310, '093B', '000000', '2D08', 'Sky Italia', 'Musica', 'VH-1', 'NDS', '(13E)'),
(311, '093B', '000000', '2c28', 'Sky Italia', 'Musica', 'myDeejay', 'NDS', '(13E)'),
(312, '093B', '000000', '38F3', 'Sky Italia', 'Musica', 'Music Box Italy', 'NDS', '(13E)'),
(313, '093B', '000000', '3C1C', 'Sky Italia', 'Musica', 'Match Music TV', 'NDS', '(13E)'),
(314, '093B', '000000', '3BCD', 'Sky Italia', 'Musica', 'Hip Hop TV Italia', 'NDS', '(13E)'),
(315, '093B', '000000', '38D0', 'Sky Italia', 'Musica', 'Live', 'NDS', '(13E)'),
(316, '093B', '000000', '3BE5', 'Sky Italia', 'Musica', 'Rock TV', 'NDS', '(13E)'),
(317, '093B', '000000', '059d', 'Sky Italia', 'Musica', 'Onda Latina', 'NDS', '(13E)'),
(318, '093B', '000000', '2C33', 'Sky Italia', 'Musica', 'Video Italia', 'NDS', '(13E)'),
(319, '093B', '000000', '38E3', 'Sky Italia', 'Musica', 'Voce', 'NDS', '(13E)'),
(320, '093B', '000000', '2CC8', 'Sky Italia', 'Musica', 'Classica', 'NDS', '(13E)'),
(321, '093B', '000000', '2B6E', 'Sky Italia', 'Bambini', 'Disney Channel Italia', 'NDS', '(13E)'),
(322, '093B', '000000', '2509', 'Sky Italia', 'Bambini', 'Disney Channel Italia +1', 'NDS', '(13E)'),
(323, '093B', '000000', '3BCB', 'Sky Italia', 'Bambini', 'Disney XD Italia', 'NDS', '(13E)'),
(324, '093B', '000000', '2CBD', 'Sky Italia', 'Bambini', 'Disney XD Italia +1', 'NDS', '(13E)'),
(325, '093B', '000000', '2CBE', 'Sky Italia', 'Bambini', 'Disney Channel U.K.', 'NDS', '(13E)'),
(326, '093B', '000000', '2AB4', 'Sky Italia', 'Bambini', 'Toon Disney Italia', 'NDS', '(13E)'),
(327, '093B', '000000', '2C9C', 'Sky Italia', 'Bambini', 'Toon Disney +1 Italia', 'NDS', '(13E)'),
(328, '093B', '000000', '2B7C', 'Sky Italia', 'Bambini', 'Playhouse Disney Italia', 'NDS', '(13E)'),
(329, '093B', '000000', '3BC5', 'Sky Italia', 'Bambini', 'Playhouse Disney Italia +1', 'NDS', '(13E)'),
(330, '093B', '000000', '3BCC', 'Sky Italia', 'Bambini', 'Cartoon Network', 'NDS', '(13E)'),
(331, '093B', '000000', '2504', 'Sky Italia', 'Bambini', 'Cartoon Network +1', 'NDS', '(13E)'),
(332, '093B', '000000', '2CF9', 'Sky Italia', 'Bambini', 'Boomerang', 'NDS', '(13E)'),
(333, '093B', '000000', '2507', 'Sky Italia', 'Bambini', 'Boomerang +1', 'NDS', '(13E)'),
(334, '093B', '000000', '2CC4', 'Sky Italia', 'Bambini', 'DeA Kids', 'NDS', '(13E)'),
(335, '093B', '000000', '3C04', 'Sky Italia', 'Bambini', 'DeA Kids +1', 'NDS', '(13E)'),
(336, '093B', '000000', '2C38', 'Sky Italia', 'Bambini', 'Nickelodeon Italia', 'NDS', '(13E)'),
(337, '093B', '000000', '2C31', 'Sky Italia', 'Bambini', 'Nickelodeon +1', 'NDS', '(13E)'),
(338, '093B', '000000', '0590', 'Sky Italia', 'Bambini', 'Nickelodeon Jr', 'NDS', '(13E)'),
(339, '093B', '000000', '0DC1', 'Sky Italia', 'Bambini', 'JimJam', 'NDS', '(13E)'),
(340, '093B', '000000', '2C2E', 'Sky Italia', 'Bambini', 'Baby TV', 'NDS', '(13E)'),
(341, '093B', '000000', '24E7', 'Sky Italia', 'Bambini', 'Cooltoon', 'NDS', '(13E)'),
(342, '093B', '000000', '110A', 'Sky Italia', 'Notizie', 'Sky TG 24', 'NDS', '(13E)'),
(343, '093B', '000000', '10EC', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(344, '093B', '000000', '10EA', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(345, '093B', '000000', '10E9', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(346, '093B', '000000', '10E8', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(347, '093B', '000000', '10E7', 'Sky Italia', 'Notizie', 'Sky TG24 Active', 'NDS', '(13E)'),
(348, '093B', '000000', '10E1', 'Sky Italia', 'Notizie', 'Sky Meteo24', 'NDS', '(13E)'),
(349, '093B', '000000', '05a1', 'Sky Italia', 'Notizie', 'CNN Intl', 'NDS', '(13E)'),
(350, '093B', '000000', '24E4', 'Sky Italia', 'Notizie', 'CNBC Europe', 'NDS', '(13E)'),
(351, '093B', '000000', '0599', 'Sky Italia', 'Notizie', 'Class CNBC', 'NDS', '(13E)'),
(352, '093B', '000000', '24E5', 'Sky Italia', 'Notizie', 'Sky News International', 'NDS', '(13E)'),
(353, '093B', '000000', '24E6', 'Sky Italia', 'Notizie', 'Fox News', 'NDS', '(13E)'),
(354, '093B', '000000', '2B0D', 'Sky Italia', 'Altri', 'Sky Focus HD', 'NDS', '(13E)'),
(355, '093B', '000000', '38DF', 'Sky Italia', 'Altri', 'Sky Assist', 'NDS', '(13E)'),
(356, '093B', '000000', '0dcc', 'Sky Italia', 'Altri', 'Sky Service', 'NDS', '(13E)'),
(357, '093B', '000000', '3BE6', 'Sky Italia', 'Altri', 'Music on SKY', 'NDS', '(13E)'),
(358, '093B', '000000', '3bed', 'Sky Italia', 'Altri', 'Music On SKY', 'NDS', '(13E)'),
(359, '0500', '023800', '36B2', 'SSR/SRG', 'Generale', 'TSR 1', 'Via Access 2', '(13E)'),
(360, '0500', '023800', '36B3', 'SSR/SRG', 'Generale', 'TSI 1', 'Via Access 2', '(13E)'),
(361, '0500', '023800', '36B8', 'SSR/SRG', 'Generale', 'TSR 2', 'Via Access 2', '(13E)'),
(362, '0500', '023800', '36B9', 'SSR/SRG', 'Generale', 'TSI 2', 'Via Access 2', '(13E)'),
(363, '0500', '023800', '0385', 'SSR/SRG', 'Generale', 'SF 1', 'Via Access 2', '(13E)'),
(364, '0500', '023800', '038B', 'SSR/SRG', 'Generale', 'SF 2', 'Via Access 2', '(13E)'),
(365, '0500', '023800', '038F', 'SSR/SRG', 'Generale', 'SFi', 'Via Access 2', '(13E)'),
(366, '0500', '023800', '03DE', 'SSR/SRG', 'Generale', 'HD Suisse', 'Via Access 2', '(13E)'),
(367, '183D', '005411', '213F', 'TivuSat', 'n/a', 'RaiUno', 'NAGRA3', '(13E)'),
(368, '183D', '005411', '2140', 'TivuSat', 'n/a', 'RaiDue', 'NAGRA3', '(13E)'),
(369, '183D', '005411', '2141', 'TivuSat', 'n/a', 'RaiTre', 'NAGRA3', '(13E)'),
(370, '183D', '005411', '0003', 'TivuSat', 'n/a', 'Retequattro', 'NAGRA3', '(13E)'),
(371, '183D', '005411', '0002', 'TivuSat', 'n/a', 'Canale 5', 'NAGRA3', '(13E)'),
(372, '183D', '005411', '0001', 'TivuSat', 'n/a', 'Italia 1', 'NAGRA3', '(13E)'),
(373, '183D', '005411', '0E1E', 'TivuSat', 'n/a', 'LA7', 'NAGRA3', '(13E)'),
(374, '183D', '005411', '2136', 'TivuSat', 'n/a', 'RaiSat Cinema', 'NAGRA3', '(13E)'),
(375, '183D', '005411', '0004', 'TivuSat', 'n/a', 'Iris', 'NAGRA3', '(13E)'),
(376, '183D', '005411', '0CEA', 'TivuSat', 'n/a', 'RaiSat Premium', 'NAGRA3', '(13E)'),
(377, '183D', '005411', '0D52', 'TivuSat', 'n/a', 'RaiSat Extra', 'NAGRA3', '(13E)'),
(378, '183D', '005411', '2142', 'TivuSat', 'n/a', 'Rai 4', 'NAGRA3', '(13E)'),
(379, '183D', '005411', '0CED', 'TivuSat', 'n/a', 'TV2000', 'NAGRA3', '(13E)'),
(380, '183D', '005411', '0CE9', 'TivuSat', 'n/a', 'Rai Sport+', 'NAGRA3', '(13E)'),
(381, '183D', '005411', '0CEB', 'TivuSat', 'n/a', 'Rai Storia', 'NAGRA3', '(13E)'),
(382, '183D', '005411', '0D4E', 'TivuSat', 'n/a', 'Rai Scuola', 'NAGRA3', '(13E)'),
(383, '183D', '005411', '0D66', 'TivuSat', 'n/a', 'RaiSat YoYo', 'NAGRA3', '(13E)'),
(384, '183D', '005411', '0CEE', 'TivuSat', 'n/a', 'Rai Gulp', 'NAGRA3', '(13E)'),
(385, '183D', '005411', '0006', 'TivuSat', 'n/a', 'Boing', 'NAGRA3', '(13E)'),
(386, '183D', '005411', '3A0B', 'TivuSat', 'n/a', 'K2', 'NAGRA3', '(13E)'),
(387, '183D', '005411', '0CE5', 'TivuSat', 'n/a', 'RaiNotizie24', 'NAGRA3', '(13E)'),
(388, '183D', '005411', '2013', 'TivuSat', 'n/a', 'Euronews', 'NAGRA3', '(13E)'),
(389, '183D', '005411', '4205', 'TivuSat', 'n/a', 'AB Channel', 'NAGRA3', '(13E)'),
(390, '183D', '005411', '0E23', 'TivuSat', 'n/a', 'Nessuno TV', 'NAGRA3', '(13E)'),
(391, '183D', '005411', '127A', 'TivuSat', 'n/a', 'PLAY TV ITALIA', 'NAGRA3', '(13E)'),
(392, '183D', '005411', '01EF', 'TivuSat', 'n/a', 'Mediashopping', 'NAGRA3', '(13E)'),
(393, '183D', '005411', '200C', 'TivuSat', 'n/a', 'BBC World News', 'NAGRA3', '(13E)'),
(394, '183D', '005411', '0003', 'TivuSat', 'n/a', 'Bloomberg European TV', 'NAGRA3', '(13E)'),
(395, '183D', '005411', '3619', 'TivuSat', 'n/a', 'France 24', 'NAGRA3', '(13E)'),
(396, '183D', '005411', '358E', 'TivuSat', 'n/a', 'TVE International', 'NAGRA3', '(13E)'),
(397, '183D', '005411', '000E', 'TivuSat', 'n/a', 'Russia Today', 'NAGRA3', '(13E)'),
(398, '0B00', '000000', '044E', 'DigitAlb', 'n/a', 'A1', 'Conax', '(16E)'),
(399, '0B00', '000000', '044F', 'DigitAlb', 'n/a', 'BBF', 'Conax', '(16E)'),
(400, '0B00', '000000', '0450', 'DigitAlb', 'n/a', 'Supersonic TV', 'Conax', '(16E)'),
(401, '0B00', '000000', '0451', 'DigitAlb', 'n/a', 'Tirana TV', 'Conax', '(16E)'),
(402, '0B00', '000000', '0452', 'DigitAlb', 'n/a', 'National Geographic Romania', 'Conax', '(16E)'),
(403, '0B00', '000000', '0453', 'DigitAlb', 'n/a', 'NatGeo Wild Romania', 'Conax', '(16E)'),
(404, '0B00', '000000', '0454', 'DigitAlb', 'n/a', 'Travel Channel', 'Conax', '(16E)'),
(405, '0B00', '000000', '0455', 'DigitAlb', 'n/a', 'MGM Eastern Europe', 'Conax', '(16E)'),
(406, '0B00', '000000', '0456', 'DigitAlb', 'n/a', 'Fox Life', 'Conax', '(16E)'),
(407, '0B00', '000000', '0457', 'DigitAlb', 'n/a', 'Fox Crime', 'Conax', '(16E)'),
(408, '0B00', '000000', '0458', 'DigitAlb', 'n/a', 'Cartoon Network', 'Conax', '(16E)'),
(409, '0B00', '000000', '0459', 'DigitAlb', 'n/a', 'X3', 'Conax', '(16E)'),
(410, '0B00', '000000', '045A', 'DigitAlb', 'n/a', 'X4', 'Conax', '(16E)'),
(411, '0B00', '000000', '0964', 'DigitAlb', 'n/a', 'National Geographic UK', 'Conax', '(16E)'),
(412, '0B00', '000000', '0385', 'DigitAlb', 'n/a', 'Top Channel', 'Conax', '(16E)'),
(413, '0B00', '000000', '0386', 'DigitAlb', 'n/a', 'Top News', 'Conax', '(16E)'),
(414, '0B00', '000000', '0387', 'DigitAlb', 'n/a', 'My Music', 'Conax', '(16E)'),
(415, '0B00', '000000', '0388', 'DigitAlb', 'n/a', 'Bang Bang', 'Conax', '(16E)'),
(416, '0B00', '000000', '0389', 'DigitAlb', 'n/a', 'Cufo TV', 'Conax', '(16E)'),
(417, '0B00', '000000', '038A', 'DigitAlb', 'n/a', 'Junior TV Albania', 'Conax', '(16E)'),
(418, '0B00', '000000', '038B', 'DigitAlb', 'n/a', 'Explorer shkence', 'Conax', '(16E)'),
(419, '0B00', '000000', '038C', 'DigitAlb', 'n/a', 'Explorer histori', 'Conax', '(16E)'),
(420, '0B00', '000000', '038D', 'DigitAlb', 'n/a', 'Explorer natyra', 'Conax', '(16E)'),
(421, '0B00', '000000', '038E', 'DigitAlb', 'n/a', 'Digi +', 'Conax', '(16E)'),
(422, '0B00', '000000', '038F', 'DigitAlb', 'n/a', 'Film Autor', 'Conax', '(16E)'),
(423, '0B00', '000000', '0390', 'DigitAlb', 'n/a', 'Film Hits', 'Conax', '(16E)'),
(424, '0B00', '000000', '0391', 'DigitAlb', 'n/a', 'Film Thriller', 'Conax', '(16E)'),
(425, '0B00', '000000', '03E9', 'DigitAlb', 'n/a', 'Film Drame', 'Conax', '(16E)'),
(426, '0B00', '000000', '03EA', 'DigitAlb', 'n/a', 'Film Aksion', 'Conax', '(16E)'),
(427, '0B00', '000000', '03EB', 'DigitAlb', 'n/a', 'Film Komedi', 'Conax', '(16E)'),
(428, '0B00', '000000', '03EE', 'DigitAlb', 'n/a', 'X1', 'Conax', '(16E)'),
(429, '0B00', '000000', '03EF', 'DigitAlb', 'n/a', 'X2', 'Conax', '(16E)'),
(430, '0B00', '000000', '03F3', 'DigitAlb', 'n/a', 'T', 'Conax', '(16E)'),
(431, '0B00', '000000', '08AC', 'DigitAlb', 'n/a', 'TV Klan', 'Conax', '(16E)'),
(432, '0B00', '000000', '08C0', 'DigitAlb', 'n/a', 'News 24 Albania', 'Conax', '(16E)'),
(433, '0B00', '000000', '08D4', 'DigitAlb', 'n/a', 'Digi Gold', 'Conax', '(16E)'),
(434, '0B00', '000000', '08E8', 'DigitAlb', 'n/a', 'MusicAL', 'Conax', '(16E)'),
(435, '0B00', '000000', '08FC', 'DigitAlb', 'n/a', 'Koha TV Albania', 'Conax', '(16E)'),
(436, '0B00', '000000', '0910', 'DigitAlb', 'n/a', 'TV Shqiptar', 'Conax', '(16E)'),
(437, '0B00', '000000', '0924', 'DigitAlb', 'n/a', 'Ora News', 'Conax', '(16E)'),
(438, '0B00', '000000', '0938', 'DigitAlb', 'n/a', 'Kohavision', 'Conax', '(16E)'),
(439, '0B00', '000000', '094C', 'DigitAlb', 'n/a', 'RTV 21 Sat', 'Conax', '(16E)'),
(440, '0B00', '000000', '1824', 'DigitAlb', 'n/a', 'TeleNorba', 'Conax', '(16E)'),
(441, '0B00', '000000', '0848', 'DigitAlb', 'n/a', 'SuperSport 1 (Albania)', 'Conax', '(16E)'),
(442, '0B00', '000000', '085C', 'DigitAlb', 'n/a', 'SuperSport 2 (Albania)', 'Conax', '(16E)'),
(443, '0B00', '000000', '0870', 'DigitAlb', 'n/a', 'SuperSport 3 (Albania)', 'Conax', '(16E)'),
(444, '0B00', '000000', '0884', 'DigitAlb', 'n/a', 'SuperSport 4 (Albania)', 'Conax', '(16E)'),
(445, '0B00', '000000', '0898', 'DigitAlb', 'n/a', 'SuperSport 5 (Albania)', 'Conax', '(16E)'),
(446, '0500', '024400', '378F', 'XXX-Xtreme', 'Porno', 'FREE-X TV', 'Via Access', '(13E)'),
(447, '0500', '024400', '378D', 'XXX-Xtreme', 'Porno', 'FREE-X TV2', 'Via Access', '(13E)'),
(448, '0500', '024400', '3788', 'XXX-Xtreme', 'Porno', 'FRENCHLOVER TV', 'Via Access', '(13E)'),
(449, '0500', '024400', '378C', 'XXX-Xtreme', 'Porno', 'X-DREAM TV', 'Via Access', '(13E)'),
(450, '0500', '024400', '378A', 'XXX-Xtreme', 'Porno', 'Redlight Premium', 'Via Access', '(13E)'),
(451, '0100', '000068', '1132', 'Cyfra', 'n/a', 'Movies 24 Eastern Europe', 'S2', '(13E)'),
(452, '0100', '000068', '1135', 'Cyfra', 'n/a', 'Orange Sport TV Polska', 'S2', '(13E)'),
(453, '0100', '000068', '1136', 'Cyfra', 'n/a', 'PlanÃ¨te', 'S2', '(13E)'),
(454, '0100', '000068', '1137', 'Cyfra', 'n/a', 'Mini Mini', 'S2', '(13E)'),
(455, '0100', '000068', '113D', 'Cyfra', 'n/a', 'Canal+ Sport 2 Polska', 'S2', '(13E)'),
(456, '0100', '000068', '113D', 'Cyfra', 'n/a', 'Cyfra+ Info', 'S2', '(13E)'),
(457, '0100', '000068', '1147', 'Cyfra', 'n/a', 'unnamed channel', 'S2', '(13E)'),
(458, '0100', '000068', '114C', 'Cyfra', 'n/a', 'MTV2 Europe', 'S2', '(13E)'),
(459, '0100', '000068', '114D', 'Cyfra', 'n/a', 'HBO Polska (Home Box Office)', 'S2', '(13E)'),
(460, '0100', '000068', '114E', 'Cyfra', 'n/a', 'Hallmark Polska', 'S2', '(13E)'),
(461, '0100', '000068', '114F', 'Cyfra', 'n/a', 'HBO 2 Polska', 'S2', '(13E)'),
(462, '0100', '000068', '1150', 'Cyfra', 'n/a', 'HBO Comedy', 'S2', '(13E)'),
(463, '0100', '000068', '1151', 'Cyfra', 'n/a', 'unnamed channel', 'S2', '(13E)'),
(464, '0100', '000068', '117C', 'Cyfra', 'n/a', 'DTV CABLE PL', 'S2', '(13E)'),
(465, '0100', '000068', '117D', 'Cyfra', 'n/a', 'DTV CABLE MI', 'S2', '(13E)'),
(466, '0100', '000068', '12C1', 'Cyfra', 'n/a', 'Canal+ Polska', 'S2', '(13E)'),
(467, '0100', '000068', '12C2', 'Cyfra', 'n/a', 'Canal+ Film', 'S2', '(13E)'),
(468, '0100', '000068', '12C4', 'Cyfra', 'n/a', 'Kuchnia.tv', 'S2', '(13E)'),
(469, '0100', '000068', '12C5', 'Cyfra', 'n/a', 'Ale Kino!', 'S2', '(13E)'),
(470, '0100', '000068', '12C6', 'Cyfra', 'n/a', 'Hyper', 'S2', '(13E)'),
(471, '0100', '000068', '12C6', 'Cyfra', 'n/a', 'ZigZap', 'S2', '(13E)'),
(472, '0100', '000068', '12C7', 'Cyfra', 'n/a', 'TVP 1', 'S2', '(13E)'),
(473, '0100', '000068', '12C8', 'Cyfra', 'n/a', 'TVP 2', 'S2', '(13E)'),
(474, '0100', '000068', '12C9', 'Cyfra', 'n/a', 'Canal+ Sport Polska', 'S2', '(13E)'),
(475, '0100', '000068', '12CB', 'Cyfra', 'n/a', 'Domo TV', 'S2', '(13E)'),
(476, '0100', '000068', '12CF', 'Cyfra', 'n/a', '119 test 1 E', 'S2', '(13E)'),
(477, '0100', '000068', '12D0', 'Cyfra', 'n/a', '119 test 2 E', 'S2', '(13E)'),
(478, '0100', '000068', '12D1', 'Cyfra', 'n/a', '119 test 3 E', 'S2', '(13E)'),
(479, '0100', '000068', '12D2', 'Cyfra', 'n/a', '119 test 1. E', 'S2', '(13E)'),
(480, '0100', '000068', '12D3', 'Cyfra', 'n/a', '119 test 2. E', 'S2', '(13E)'),
(481, '0100', '000068', '12D4', 'Cyfra', 'n/a', '119 test 3. E', 'S2', '(13E)'),
(482, '0100', '000068', '12DE', 'Cyfra', 'n/a', 'H test 1', 'S2', '(13E)'),
(483, '0100', '000068', '12DF', 'Cyfra', 'n/a', 'H test 2', 'S2', '(13E)'),
(484, '0100', '000068', '12E0', 'Cyfra', 'n/a', 'H test 3', 'S2', '(13E)'),
(485, '0100', '000068', '12E3', 'Cyfra', 'n/a', 'DTV CABLE', 'S2', '(13E)'),
(486, '0100', '000068', '12E4', 'Cyfra', 'n/a', 'DTV CABLE 2', 'S2', '(13E)'),
(487, '0100', '000068', '12E5', 'Cyfra', 'n/a', 'DTV CABLE 3', 'S2', '(13E)'),
(488, '0100', '000068', '130A', 'Cyfra', 'n/a', 'DTV CABLE KU', 'S2', '(13E)'),
(489, '0100', '000068', '130B', 'Cyfra', 'n/a', 'DTV CABLE AL', 'S2', '(13E)'),
(490, '0100', '000068', '130C', 'Cyfra', 'n/a', 'DTV CABLE ZI', 'S2', '(13E)'),
(491, '0100', '000068', '12E8', 'Cyfra', 'n/a', 'CYFRA+ RADIO', 'S2', '(13E)'),
(492, '0100', '000068', '12EA', 'Cyfra', 'n/a', 'CYFRA+ RMF', 'S2', '(13E)'),
(493, '0100', '000068', '12FE', 'Cyfra', 'n/a', 'tech', 'S2', '(13E)'),
(494, '0100', '000068', '12FF', 'Cyfra', 'n/a', 'tech 2', 'S2', '(13E)'),
(495, '0100', '000068', '32DD', 'Cyfra', 'n/a', 'Canal+ Film HD Polska', 'S2', '(13E)'),
(496, '0100', '000068', '32DE', 'Cyfra', 'n/a', 'Canal+ Sport HD Polska', 'S2', '(13E)'),
(497, '0100', '000068', '32DF', 'Cyfra', 'n/a', 'National Geographic Polska', 'S2', '(13E)'),
(498, '0100', '000068', '32E0', 'Cyfra', 'n/a', 'HBO HD Polska', 'S2', '(13E)'),
(499, '0100', '000068', '3305', 'Cyfra', 'n/a', 'Eurosport HD', 'S2', '(13E)'),
(500, '0100', '000068', '3307', 'Cyfra', 'n/a', 'ESP HD Turk', 'S2', '(13E)'),
(501, '0100', '000068', '3308', 'Cyfra', 'n/a', 'ESP HD Russian', 'S2', '(13E)'),
(502, '0100', '000068', '3309', 'Cyfra', 'n/a', 'ESP HD Dutch', 'S2', '(13E)'),
(503, '0100', '000068', '330A', 'Cyfra', 'n/a', 'ESP HD Czech', 'S2', '(13E)'),
(504, '0100', '000068', '330B', 'Cyfra', 'n/a', 'ESP HD German', 'S2', '(13E)'),
(505, '0100', '000068', '330D', 'Cyfra', 'n/a', 'ESP HD Portuguese', 'S2', '(13E)'),
(506, '0100', '000068', '330E', 'Cyfra', 'n/a', 'ESP HD Polish', 'S2', '(13E)'),
(507, '0100', '000068', '330F', 'Cyfra', 'n/a', 'ESP HD Hungarian', 'S2', '(13E)'),
(508, '0100', '000068', '3310', 'Cyfra', 'n/a', 'ESP HD Bulgaria', 'S2', '(13E)'),
(509, '0100', '000068', '3311', 'Cyfra', 'n/a', 'ESP HD Italy', 'S2', '(13E)'),
(510, '0100', '000068', '3C43', 'Cyfra', 'n/a', 'MusicC. Klasyka', 'S2', '(13E)'),
(511, '0100', '000068', '3C44', 'Cyfra', 'n/a', 'Music Choice Pop', 'S2', '(13E)'),
(512, '0100', '000068', '3C45', 'Cyfra', 'n/a', 'Music Ch. Dance', 'S2', '(13E)'),
(513, '0100', '000068', '3C46', 'Cyfra', 'n/a', 'Music Ch. Urban', 'S2', '(13E)'),
(514, '0100', '000068', '3C47', 'Cyfra', 'n/a', 'Music Ch. Swiat', 'S2', '(13E)'),
(515, '0100', '000068', '3C48', 'Cyfra', 'n/a', 'Music Ch. Rock', 'S2', '(13E)'),
(516, '0100', '000068', '3C49', 'Cyfra', 'n/a', 'Music Ch. Oldies', 'S2', '(13E)'),
(517, '0100', '000068', '3C51', 'Cyfra', 'n/a', 'CYFRA+ GRY', 'S2', '(13E)'),
(518, '0100', '000068', '3C52', 'Cyfra', 'n/a', 'CYFRA+ GRY2', 'S2', '(13E)'),
(519, '0100', '000068', '3C53', 'Cyfra', 'n/a', 'INFO+', 'S2', '(13E)'),
(520, '0100', '000068', '13F0', 'Cyfra', 'n/a', 'Kino Polska', 'S2', '(13E)'),
(521, '0100', '000068', '13F1', 'Cyfra', 'n/a', 'Trace TV', 'S2', '(13E)'),
(522, '0100', '000068', '13F6', 'Cyfra', 'n/a', 'TVP Info', 'S2', '(13E)'),
(523, '0100', '000068', '13F7', 'Cyfra', 'n/a', 'Cinemax East Europe', 'S2', '(13E)'),
(524, '0100', '000068', '1402', 'Cyfra', 'n/a', 'Animal Planet Eastern Europe', 'S2', '(13E)'),
(525, '0100', '000068', '1405', 'Cyfra', 'n/a', 'CNBC Europe', 'S2', '(13E)'),
(526, '0100', '000068', '1406', 'Cyfra', 'n/a', 'Zone Club', 'S2', '(13E)'),
(527, '0100', '000068', '1407', 'Cyfra', 'n/a', 'ZoneReality', 'S2', '(13E)'),
(528, '0100', '000068', '0E20', 'Cyfra', 'n/a', 'National Geographic Polska', 'S2', '(13E)'),
(529, '0100', '000068', '00CA', 'Cyfra', 'n/a', 'AB Moteurs', 'S2', '(13E)'),
(530, '0100', '000068', '0069', 'Cyfra', 'n/a', 'Mezzo', 'S2', '(13E)'),
(531, '0100', '000068', '3C43', 'Cyfra', 'n/a', 'MusicC. Klasyka', 'S2', '(13E)'),
(532, '0100', '000068', '3C44', 'Cyfra', 'n/a', 'Music Choice Pop', 'S2', '(13E)'),
(533, '0100', '000068', '3C45', 'Cyfra', 'n/a', 'Music Ch. Dance', 'S2', '(13E)'),
(534, '0100', '000068', '3C46', 'Cyfra', 'n/a', 'Music Ch. Urban', 'S2', '(13E)'),
(535, '0100', '000068', '3C47', 'Cyfra', 'n/a', 'Music Ch. Swiat', 'S2', '(13E)'),
(536, '0100', '000068', '3C48', 'Cyfra', 'n/a', 'Music Ch. Rock', 'S2', '(13E)'),
(537, '0100', '000068', '3C49', 'Cyfra', 'n/a', 'Music Ch. Oldies', 'S2', '(13E)'),
(538, '0100', '000068', '3C51', 'Cyfra', 'n/a', 'CYFRA+ GRY', 'S2', '(13E)'),
(539, '0100', '000068', '3C52', 'Cyfra', 'n/a', 'CYFRA+ GRY2', 'S2', '(13E)'),
(540, '0100', '000068', '3C53', 'Cyfra', 'n/a', 'INFO+', 'S2', '(13E)'),
(541, '0100', '000068', '39F0', 'Cyfra', 'n/a', 'National Geographic Polska', 'S2', '(13E)'),
(542, '0500', '023B00', '35C0', 'Harmonic', 'Adulti', 'S-1 Satisfaction Italy', 'Via Access', '(16E)'),
(543, '0500', '023B00', '35C1', 'Harmonic', 'Adulti', 'S-2 Satisfaction Italy', 'Via Access', '(16E)'),
(544, '0500', '023B00', '35C2', 'Harmonic', 'Adulti', 'S-3 Satisfaction Italy', 'Via Access', '(16E)'),
(545, '0500', '023B00', '35C4', 'Harmonic', 'Adulti', 'S-4 Satisfaction Italy', 'Via Access', '(16E)'),
(546, '0500', '023B00', '35C3', 'Harmonic', 'Adulti', 'S-5 Satisfaction Italy', 'Via Access', '(16E)'),
(547, '0500', '023B00', '35C6', 'Harmonic', 'Adulti', 'S-6 Satisfaction Prive Italy', 'Via Access', '(16E)'),
(548, '0500', '023B00', '35C7', 'Harmonic', 'Adulti', 'S-7 Sexpoker Tv', 'Via Access', '(16E)'),
(549, '0500', '023B00', '35C8', 'Harmonic', 'Adulti', 'SCT', 'Via Access', '(16E)'),
(550, '1702', '000000', '0305', 'Sky Germany', 'n/a', 'Champions TV', '[B sat]', '(19.2E)'),
(551, '1702', '000000', '000A', 'Sky Germany', 'n/a', 'Sky Cinema', '[B sat]', '(19.2E)'),
(552, '1702', '000000', '000B', 'Sky Germany', 'n/a', 'Sky Cinema +1', '[B sat]', '(19.2E)'),
(553, '1702', '000000', '002B', 'Sky Germany', 'n/a', 'Sky Cinema +24', '[B sat]', '(19.2E)'),
(554, '1702', '000000', '0009', 'Sky Germany', 'n/a', 'Sky Action', '[B sat]', '(19.2E)'),
(555, '1702', '000000', '0008', 'Sky Germany', 'n/a', 'Sky Comedy', '[B sat]', '(19.2E)'),
(556, '1702', '000000', '0014', 'Sky Germany', 'n/a', 'Sky Emotion', '[B sat]', '(19.2E)'),
(557, '1702', '000000', '0029', 'Sky Germany', 'n/a', 'Sky Cinema Hits', '[B sat]', '(19.2E)'),
(558, '1702', '000000', '0204', 'Sky Germany', 'n/a', 'Sky Nostalgie', '[B sat]', '(19.2E)'),
(559, '1702', '000000', '0019', 'Sky Germany', 'n/a', 'Disney Cinemagic', '[B sat]', '(19.2E)'),
(560, '1702', '000000', '0010', 'Sky Germany', 'n/a', 'Fox Serie', '[B sat]', '(19.2E)'),
(561, '1702', '000000', '0017', 'Sky Germany', 'n/a', 'Sky Krimi', '[B sat]', '(19.2E)'),
(562, '1702', '000000', '002A', 'Sky Germany', 'n/a', '13th Street', '[B sat]', '(19.2E)'),
(563, '1702', '000000', '0203', 'Sky Germany', 'n/a', 'MGM Germany', '[B sat]', '(19.2E)'),
(564, '1702', '000000', '0024', 'Sky Germany', 'n/a', 'Sci-Fi Deutschland', '[B sat]', '(19.2E)'),
(565, '1702', '000000', '003E', 'Sky Germany', 'n/a', 'AXN Action', '[B sat]', '(19.2E)'),
(566, '1702', '000000', '003D', 'Sky Germany', 'n/a', 'TNT Film', '[B sat]', '(19.2E)'),
(567, '1702', '000000', '0032', 'Sky Germany', 'n/a', 'TNT Serie', '[B sat]', '(19.2E)'),
(568, '1702', '000000', '001B', 'Sky Germany', 'n/a', 'RTL Crime', '[B sat]', '(19.2E)'),
(569, '1702', '000000', '001D', 'Sky Germany', 'n/a', 'RTL Passion', '[B sat]', '(19.2E)'),
(570, '1702', '000000', '3397', 'Sky Germany', 'n/a', 'RTL Living', '[B sat]', '(19.2E)'),
(571, '1702', '000000', '003F', 'Sky Germany', 'n/a', 'Romance TV', '[B sat]', '(19.2E)'),
(572, '1702', '000000', '4461', 'Sky Germany', 'n/a', 'Sat 1 Comedy', '[B sat]', '(19.2E)'),
(573, '1702', '000000', '4462', 'Sky Germany', 'n/a', 'Kabel Eins Classics', '[B sat]', '(19.2E)'),
(574, '1702', '000000', '003C', 'Sky Germany', 'n/a', 'Kinowelt TV', '[B sat]', '(19.2E)');
INSERT INTO `cccam_channelinfo` (`chan_id`, `chan_caid`, `chan_ident`, `chan_chaid`, `chan_provider`, `chan_category`, `chan_channel_name`, `chan_encription`, `chan_sat`) VALUES
(575, '1702', '000000', '000E', 'Sky Germany', 'n/a', 'Discovery Channel Deutschland', '[B sat]', '(19.2E)'),
(576, '1702', '000000', '000D', 'Sky Germany', 'n/a', 'National Geographic Germany', '[B sat]', '(19.2E)'),
(577, '1702', '000000', '000C', 'Sky Germany', 'n/a', 'Nat Geo Wild Germany', '[B sat]', '(19.2E)'),
(578, '1702', '000000', '0034', 'Sky Germany', 'n/a', 'Spiegel Geschichte', '[B sat]', '(19.2E)'),
(579, '1702', '000000', '0044', 'Sky Germany', 'n/a', 'History channel', '[B sat]', '(19.2E)'),
(580, '1702', '000000', '3398', 'Sky Germany', 'n/a', 'Biography Channel', '[B sat]', '(19.2E)'),
(581, '1702', '000000', '00A8', 'Sky Germany', 'n/a', 'Motorvision TV', '[B sat]', '(19.2E)'),
(582, '1702', '000000', '000F', 'Sky Germany', 'n/a', 'Focus Gesundheit', '[B sat]', '(19.2E)'),
(583, '1702', '000000', '3331', 'Sky Germany', 'n/a', 'ESPN America (P)', '[B sat]', '(19.2E)'),
(584, '1702', '000000', '3395', 'Sky Germany', 'n/a', 'Eurosport 2 Germany', '[B sat]', '(19.2E)'),
(585, '1702', '000000', '0045', 'Sky Germany', 'n/a', 'Sportdigital.tv', '[B sat]', '(19.2E)'),
(586, '1702', '000000', '0022', 'Sky Germany', 'n/a', 'Disney Channel Germany', '[B sat]', '(19.2E)'),
(587, '1702', '000000', '001A', 'Sky Germany', 'n/a', 'Playhouse Disney Germany', '[B sat]', '(19.2E)'),
(588, '1702', '000000', '3394', 'Sky Germany', 'n/a', 'Cartoon Network Germany', '[B sat]', '(19.2E)'),
(589, '1702', '000000', '0042', 'Sky Germany', 'n/a', 'Boomerang Germany', '[B sat]', '(19.2E)'),
(590, '1702', '000000', '001C', 'Sky Germany', 'n/a', 'Jetix Deutschland', '[B sat]', '(19.2E)'),
(591, '1702', '000000', '0013', 'Sky Germany', 'n/a', 'Junior', '[B sat]', '(19.2E)'),
(592, '1702', '000000', '700A', 'Sky Germany', 'n/a', 'Nick Premium', '[B sat]', '(19.2E)'),
(593, '1702', '000000', '3393', 'Sky Germany', 'n/a', 'Animax Deutschland', '[B sat]', '(19.2E)'),
(594, '1702', '000000', '0043', 'Sky Germany', 'n/a', 'e.clips', '[B sat]', '(19.2E)'),
(595, '1702', '000000', '7009', 'Sky Germany', 'n/a', 'MTV Entertainment', '[B sat]', '(19.2E)'),
(596, '1702', '000000', '6FFF', 'Sky Germany', 'n/a', 'MTV Music', '[B sat]', '(19.2E)'),
(597, '1702', '000000', '6FF1', 'Sky Germany', 'n/a', 'VH1 classics', '[B sat]', '(19.2E)'),
(598, '1702', '000000', '0018', 'Sky Germany', 'n/a', 'Classica', '[B sat]', '(19.2E)'),
(599, '1702', '000000', '0099', 'Sky Germany', 'n/a', '60er/70er', '[B sat]', '(19.2E)'),
(600, '1702', '000000', '009A', 'Sky Germany', 'n/a', '80er/90er', '[B sat]', '(19.2E)'),
(601, '1702', '000000', '009C', 'Sky Germany', 'n/a', 'Country', '[B sat]', '(19.2E)'),
(602, '1702', '000000', '0096', 'Sky Germany', 'n/a', 'Deutsche Charts', '[B sat]', '(19.2E)'),
(603, '1702', '000000', '0098', 'Sky Germany', 'n/a', 'Love Songs', '[B sat]', '(19.2E)'),
(604, '1702', '000000', '0097', 'Sky Germany', 'n/a', 'Rock Hymnen', '[B sat]', '(19.2E)'),
(605, '1702', '000000', '009B', 'Sky Germany', 'n/a', 'RnB/Hip Hop', '[B sat]', '(19.2E)'),
(606, '1702', '000000', '0206', 'Sky Germany', 'n/a', 'Goldstar TV', '[B sat]', '(19.2E)'),
(607, '1702', '000000', '0016', 'Sky Germany', 'n/a', 'Heimatkanal', '[B sat]', '(19.2E)'),
(608, '1702', '000000', '0015', 'Sky Germany', 'n/a', 'Beate-Uhse.TV', '[B sat]', '(19.2E)'),
(609, '1702', '000000', '0046', 'Sky Germany', 'n/a', 'Alpengl?hen TVX', '[B sat]', '(19.2E)'),
(610, '1702', '000000', '0201', 'Sky Germany', 'n/a', 'Blue Movie Portal', '[B sat]', '(19.2E)'),
(611, '1702', '000000', '0159', 'Sky Germany', 'n/a', 'Blue Movie 1', '[B sat]', '(19.2E)'),
(612, '1702', '000000', '0163', 'Sky Germany', 'n/a', 'Blue Movie 2', '[B sat]', '(19.2E)'),
(613, '1702', '000000', '016d', 'Sky Germany', 'n/a', 'Blue Movie 3', '[B sat]', '(19.2E)'),
(614, '1702', '000000', '0298', 'Sky Germany', 'n/a', 'Big Brother Deutschland', '[B sat]', '(19.2E)'),
(615, '1702', '000000', '0011', 'Sky Germany', 'Sport', 'Sky Sport Info (Portal)', '[B sat]', '(19.2E)'),
(616, '1702', '000000', '00DD', 'Sky Germany', 'Sport', 'Sky Sport 1', '[B sat]', '(19.2E)'),
(617, '1702', '000000', '00DE', 'Sky Germany', 'Sport', 'Sky Sport 2', '[B sat]', '(19.2E)'),
(618, '1702', '000000', '00FD', 'Sky Germany', 'Sport', 'Sky Sport 3 (Feed)', '[B sat]', '(19.2E)'),
(619, '1702', '000000', '014D', 'Sky Germany', 'Sport', 'Sky Sport 4 (Feed)', '[B sat]', '(19.2E)'),
(620, '1702', '000000', '0143', 'Sky Germany', 'Sport', 'Sky Sport 5 (Feed)', '[B sat]', '(19.2E)'),
(621, '1702', '000000', '0139', 'Sky Germany', 'Sport', 'Sky Sport 6 (Feed)', '[B sat]', '(19.2E)'),
(622, '1702', '000000', '012F', 'Sky Germany', 'Sport', 'Sky Sport 7 (Feed)', '[B sat]', '(19.2E)'),
(623, '1702', '000000', '0125', 'SKY Germany', 'Sport', 'Sky Sport 8 (Feed)', '[B sat]', '(19.2E)'),
(624, '1702', '000000', '011b', 'SKY Germany', 'Sport', 'Sky Sport 9 (Feed)', '[B sat]', '(19.2E)'),
(625, '1702', '000000', '0107', 'SKY Germany', 'Sport', 'Sky Sport 10 (Feed)', '[B sat]', '(19.2E)'),
(626, '1702', '000000', '0175', 'SKY Germany', 'Sport', 'Sky Sport 11 (Feed)', '[B sat]', '(19.2E)'),
(627, '1702', '000000', '016B', 'SKY Germany', 'Sport', 'Sky Sport 12 (Feed)', '[B sat]', '(19.2E)'),
(628, '1702', '000000', '0035', 'Sky Germany', 'Sport', 'Sky Sport Austria', '[B sat]', '(19.2E)'),
(629, '1702', '000000', '00DF', 'Sky Germany', 'Sport', 'Sky Bundesliga', '[B sat]', '(19.2E)'),
(630, '1702', '000000', '0106', 'SKY Germany', 'Sport', 'Sky Bundesliga 1 (Feed)', '[B sat]', '(19.2E)'),
(631, '1702', '000000', '0110', 'SKY Germany', 'Sport', 'Sky Bundesliga 2 (Feed)', '[B sat]', '(19.2E)'),
(632, '1702', '000000', '011A', 'SKY Germany', 'Sport', 'Sky Bundesliga 3 (Feed)', '[B sat]', '(19.2E)'),
(633, '1702', '000000', '0124', 'SKY Germany', 'Sport', 'Sky Bundesliga 4 (Feed)', '[B sat]', '(19.2E)'),
(634, '1702', '000000', '012E', 'SKY Germany', 'Sport', 'Sky Bundesliga 5 (Feed)', '[B sat]', '(19.2E)'),
(635, '1702', '000000', '0138', 'SKY Germany', 'Sport', 'Sky Bundesliga 6 (Feed)', '[B sat]', '(19.2E)'),
(636, '1702', '000000', '0142', 'SKY Germany', 'Sport', 'Sky Bundesliga 7 (Feed)', '[B sat]', '(19.2E)'),
(637, '1702', '000000', '014C', 'SKY Germany', 'Sport', 'Sky Bundesliga 8 (Feed)', '[B sat]', '(19.2E)'),
(638, '1702', '000000', '0156', 'SKY Germany', 'Sport', 'Sky Bundesliga 9 (Feed)', '[B sat]', '(19.2E)'),
(639, '1702', '000000', '0160', 'SKY Germany', 'Sport', 'Sky Bundesliga 10 (Feed)', '[B sat]', '(19.2E)'),
(640, '1702', '000000', '00FC', 'SKY Germany', 'Sport', 'Sky Bundesliga 11 (Feed)', '[B sat]', '(19.2E)'),
(641, '1702', '000000', '00FB', 'Sky Germany', 'n/a', 'Sky Select 1', '[B sat]', '(19.2E)'),
(642, '1702', '000000', '0105', 'Sky Germany', 'n/a', 'Sky Select 2', '[B sat]', '(19.2E)'),
(643, '1702', '000000', '010F', 'Sky Germany', 'n/a', 'Sky Select 3', '[B sat]', '(19.2E)'),
(644, '1702', '000000', '0119', 'Sky Germany', 'n/a', 'Sky Select 4', '[B sat]', '(19.2E)'),
(645, '1702', '000000', '0123', 'Sky Germany', 'n/a', 'Sky Select 5', '[B sat]', '(19.2E)'),
(646, '1702', '000000', '012D', 'Sky Germany', 'n/a', 'Sky Select 6', '[B sat]', '(19.2E)'),
(647, '1702', '000000', '0137', 'Sky Germany', 'n/a', 'Sky Select 7', '[B sat]', '(19.2E)'),
(648, '1702', '000000', '0141', 'Sky Germany', 'n/a', 'Sky Select 8', '[B sat]', '(19.2E)'),
(649, '1702', '000000', '014B', 'Sky Germany', 'n/a', 'Sky Select 9', '[B sat]', '(19.2E)'),
(650, '0100', '000081', '00C8', 'CanalSat', 'n/a', 'RTL 9', '[S2 81]', '(19.2E)'),
(651, '0100', '000081', '00C9', 'CanalSat', 'n/a', 'AB 1', '[S2 81]', '(19.2E)'),
(652, '0100', '000081', '00CA', 'CanalSat', 'n/a', 'AB Moteurs', '[S2 81]', '(19.2E)'),
(653, '0100', '000081', '00CB', 'CanalSat', 'n/a', 'Animaux', '[S2 81]', '(19.2E)'),
(654, '0100', '000081', '00CC', 'CanalSat', 'n/a', 'Chasse et P?che', '[S2 81]', '(19.2E)'),
(655, '0100', '000081', '00CD', 'CanalSat', 'n/a', 'XXL', '[S2 81]', '(19.2E)'),
(656, '0100', '000081', '00CF', 'CanalSat', 'n/a', 'Escales', '[S2 81]', '(19.2E)'),
(657, '0100', '000081', '00D0', 'CanalSat', 'n/a', 'Toute l&acute;Histoire', '[S2 81]', '(19.2E)'),
(658, '0100', '000081', '00D1', 'CanalSat', 'n/a', 'NT 1', '[S2 81]', '(19.2E)'),
(659, '0100', '000081', '00D2', 'CanalSat', 'n/a', 'Action', '[S2 81]', '(19.2E)'),
(660, '0100', '000081', '00D3', 'CanalSat', 'n/a', 'Mangas', '[S2 81]', '(19.2E)'),
(661, '0100', '000081', '00D4', 'CanalSat', 'n/a', 'Encyclopedia', '[S2 81]', '(19.2E)'),
(662, '0100', '000081', '00D7', 'CanalSat', 'n/a', 'XXL PL', '[S2 81]', '(19.2E)'),
(663, '0100', '000081', '00DB', 'CanalSat', 'n/a', 'LCP', 'La Cha?ne Parlementaire', '[S2 81]'),
(664, '0100', '000081', '01FB', 'CanalSat', 'n/a', 'Cin&eacute; FX', '[S2 81]', '(19.2E)'),
(665, '0100', '000081', '6FEC', 'CanalSat', 'n/a', 'MTV France', '[S2 81]', '(19.2E)'),
(666, '0100', '000081', '6FEE', 'CanalSat', 'n/a', 'MTV Hits', '[S2 81]', '(19.2E)'),
(667, '0100', '000081', '6FF1', 'CanalSat', 'n/a', 'VH-1 Classic', '[S2 81]', '(19.2E)'),
(668, '0100', '000081', '6FF3', 'CanalSat', 'n/a', 'MTV2 Europe', '[S2 81]', '(19.2E)'),
(669, '0100', '000081', '6FF5', 'CanalSat', 'n/a', 'MTV Base France', '[S2 81]', '(19.2E)'),
(670, '0100', '000081', '6FF7', 'CanalSat', 'n/a', 'Game One', '[S2 81]', '(19.2E)'),
(671, '0100', '000081', '6FFC', 'CanalSat', 'n/a', 'Nickelodeon France', '[S2 81]', '(19.2E)'),
(672, '0100', '000081', '6F69', 'CanalSat', 'n/a', 'Cartoon Network', '[S2 81]', '(19.2E)'),
(673, '0100', '000081', '6F6D', 'CanalSat', 'n/a', 'TCM France', '[S2 81]', '(19.2E)'),
(674, '0100', '000081', '6F6F', 'CanalSat', 'n/a', 'Boomerang France', '[S2 81]', '(19.2E)'),
(675, '0100', '000081', '6FB8', 'CanalSat', 'n/a', 'MTVNHD', '[S2 81]', '(19.2E)'),
(676, '0100', '000081', '1F47', 'CanalSat', 'n/a', 'Mezzo', '[S2 81]', '(19.2E)'),
(677, '0100', '000081', '1F4A', 'CanalSat', 'n/a', 'i-T?l?', '[S2 81]', '(19.2E)'),
(678, '0100', '000081', '1F4C', 'CanalSat', 'n/a', 'MEZZO.', '[S2 81]', '(19.2E)'),
(679, '0100', '000081', '1F55', 'CanalSat', 'n/a', 'I>TELE', '[S2 81]', '(19.2E)'),
(680, '0100', '000081', '7002', 'CanalSat', 'n/a', 'MTV Pulse', '[S2 81]', '(19.2E)'),
(681, '0100', '000081', '7003', 'CanalSat', 'n/a', 'MTV Idol', '[S2 81]', '(19.2E)'),
(682, '0100', '000081', '2261', 'CanalSat', 'n/a', 'France 2', '[S2 81]', '(19.2E)'),
(683, '0100', '000081', '2262', 'CanalSat', 'n/a', 'France 3 Sat', '[S2 81]', '(19.2E)'),
(684, '0100', '000081', '2267', 'CanalSat', 'n/a', 'Trace TV', '[S2 81]', '(19.2E)'),
(685, '0100', '000081', '2270', 'CanalSat', 'n/a', 'Motors TV', '[S2 81]', '(19.2E)'),
(686, '0100', '000081', '2275', 'CanalSat', 'n/a', 'FRANCE 2', '[S2 81]', '(19.2E)'),
(687, '0100', '000081', '2276', 'CanalSat', 'n/a', 'FRANCE 3', '[S2 81]', '(19.2E)'),
(688, '0100', '000081', '2525', 'CanalSat', 'n/a', 'France 4', '[S2 81]', '(19.2E)'),
(689, '0100', '000081', '2539', 'CanalSat', 'n/a', 'FRANCE 4', '[S2 81]', '(19.2E)'),
(690, '0100', '000081', '2135', 'CanalSat', 'n/a', 'France 5', '[S2 81]', '(19.2E)'),
(691, '0100', '000081', '218F', 'CanalSat', 'n/a', 'FRANCE 5', '[S2 81]', '(19.2E)'),
(692, '0100', '000081', '427E', 'CanalSat', 'n/a', 'Animaux', '[S2 81]', '(19.2E)'),
(693, '0100', '000081', '4284', 'CanalSat', 'n/a', 'NT 1', '[S2 81]', '(19.2E)'),
(694, '0100', '000081', '4290', 'CanalSat', 'n/a', 'NT1', '[S2 81]', '(19.2E)'),
(695, '0100', '000081', '219F', 'CanalSat', 'n/a', 'Yacht & Sail France', '[S2 81]', '(19.2E)'),
(696, '0100', '000081', '3335', 'CanalSat', 'n/a', 'ESPN America', '[S2 81]', '(19.2E)'),
(697, '0100', '003311', '00C8', 'CanalSat', 'n/a', 'RTL 9', '[S2 3311]', '(19.2E)'),
(698, '0100', '003311', '00C9', 'CanalSat', 'n/a', 'AB 1', '[S2 3311]', '(19.2E)'),
(699, '0100', '003311', '00CA', 'CanalSat', 'n/a', 'AB Moteurs', '[S2 3311]', '(19.2E)'),
(700, '0100', '003311', '00CB', 'CanalSat', 'n/a', 'Animaux', '[S2 3311]', '(19.2E)'),
(701, '0100', '003311', '00CC', 'CanalSat', 'n/a', 'Chasse et P?che', '[S2 3311]', '(19.2E)'),
(702, '0100', '003311', '00CD', 'CanalSat', 'n/a', 'XXL', '[S2 3311]', '(19.2E)'),
(703, '0100', '003311', '00CF', 'CanalSat', 'n/a', 'Escales', '[S2 3311]', '(19.2E)'),
(704, '0100', '003311', '00D0', 'CanalSat', 'n/a', 'Toute l&acute;Histoire', '[S2 3311]', '(19.2E)'),
(705, '0100', '003311', '00D1', 'CanalSat', 'n/a', 'NT 1', '[S2 3311]', '(19.2E)'),
(706, '0100', '003311', '00D2', 'CanalSat', 'n/a', 'Action', '[S2 3311]', '(19.2E)'),
(707, '0100', '003311', '00D3', 'CanalSat', 'n/a', 'Mangas', '[S2 3311]', '(19.2E)'),
(708, '0100', '003311', '00D4', 'CanalSat', 'n/a', 'Encyclopedia', '[S2 3311]', '(19.2E)'),
(709, '0100', '003311', '00D7', 'CanalSat', 'n/a', 'XXL PL', '[S2 3311]', '(19.2E)'),
(710, '0100', '003311', '00DB', 'CanalSat', 'n/a', 'LCP', '[S2 3311]', '[S2 3311]'),
(711, '0100', '003311', '01FB', 'CanalSat', 'n/a', 'Cin&eacute; FX', '[S2 3311]', '(19.2E)'),
(712, '0100', '003311', '1901', 'CanalSat', 'n/a', 'CANAL EVENEMENT', '[S2 3311]', '(19.2E)'),
(713, '0100', '003311', '1903', 'CanalSat', 'n/a', 'TELEREALITE', '[S2 3311]', '(19.2E)'),
(714, '0100', '003311', '1904', 'CanalSat', 'n/a', 'BabyFirst', '[S2 3311]', '(19.2E)'),
(715, '0100', '003311', '1906', 'CanalSat', 'n/a', 'NRJ Hits', '[S2 3311]', '(19.2E)'),
(716, '0100', '003311', '1908', 'CanalSat', 'n/a', 'Girondins TV', '[S2 3311]', '(19.2E)'),
(717, '0100', '003311', '6FEC', 'CanalSat', 'n/a', 'MTV France', '[S2 3311]', '(19.2E)'),
(718, '0100', '003311', '6FEE', 'CanalSat', 'n/a', 'MTV Hits', '[S2 3311]', '(19.2E)'),
(719, '0100', '003311', '6FF3', 'CanalSat', 'n/a', 'MTV2 Europe', '[S2 3311]', '(19.2E)'),
(720, '0100', '003311', '6FF5', 'CanalSat', 'n/a', 'MTV Base France', '[S2 3311]', '(19.2E)'),
(721, '0100', '003311', '6FF7', 'CanalSat', 'n/a', 'Game One', '[S2 3311]', '(19.2E)'),
(722, '0100', '003311', '6FF8', 'CanalSat', 'n/a', 'MTV Hits', '[S2 3311]', '(19.2E)'),
(723, '0100', '003311', '6FFA', 'CanalSat', 'n/a', 'VH1', '[S2 3311]', '(19.2E)'),
(724, '0100', '003311', '6FFB', 'CanalSat', 'n/a', 'VH1 Classic', '[S2 3311]', '(19.2E)'),
(725, '0100', '003311', '6FFC', 'CanalSat', 'n/a', 'Nickelodeon France', '[S2 3311]', '(19.2E)'),
(726, '0100', '003311', '6FFD', 'CanalSat', 'n/a', 'MTV TWO', '[S2 3311]', '(19.2E)'),
(727, '0100', '003311', '6F69', 'CanalSat', 'n/a', 'Cartoon Network', '[S2 3311]', '(19.2E)'),
(728, '0100', '003311', '6F6D', 'CanalSat', 'n/a', 'TCM France', '[S2 3311]', '(19.2E)'),
(729, '0100', '003311', '6F6F', 'CanalSat', 'n/a', 'Boomerang France', '[S2 3311]', '(19.2E)'),
(730, '0100', '003311', '6FB8', 'CanalSat', 'n/a', 'MTVNHD', '[S2 3311]', '(19.2E)'),
(731, '0100', '003311', '6FB9', 'CanalSat', 'n/a', 'MTVNHD', '[S2 3311]', '(19.2E)'),
(732, '0100', '003311', '1F41', 'CanalSat', 'n/a', 'Seasons', '[S2 3311]', '(19.2E)'),
(733, '0100', '003311', '1F42', 'CanalSat', 'n/a', 'Cinecinema &Eacute;motion', '[S2 3311]', '(19.2E)'),
(734, '0100', '003311', '1F43', 'CanalSat', 'n/a', 'Cinecinema Frisson', '[S2 3311]', '(19.2E)'),
(735, '0100', '003311', '1F45', 'CanalSat', 'n/a', 'Paris Premi?re', '[S2 3311]', '(19.2E)'),
(736, '0100', '003311', '1F46', 'CanalSat', 'n/a', 'Jimmy', '[S2 3311]', '(19.2E)'),
(737, '0100', '003311', '1F47', 'CanalSat', 'n/a', 'Mezzo', '[S2 3311]', '(19.2E)'),
(738, '0100', '003311', '1F48', 'CanalSat', 'n/a', 'La Cha?ne M?t?o', '[S2 3311]', '(19.2E)'),
(739, '0100', '003311', '1F49', 'CanalSat', 'n/a', 'Sport +', '[S2 3311]', '(19.2E)'),
(740, '0100', '003311', '1F4A', 'CanalSat', 'n/a', 'i-T?l?', '[S2 3311]', '(19.2E)'),
(741, '0100', '003311', '1F4B', 'CanalSat', 'n/a', 'Histoire', '[S2 3311]', '(19.2E)'),
(742, '0100', '003311', '1F4C', 'CanalSat', 'n/a', 'MEZZO.', '[S2 3311]', '(19.2E)'),
(743, '0100', '003311', '1F55', 'CanalSat', 'n/a', 'I>TELE', '[S2 3311]', '(19.2E)'),
(744, '0100', '003311', '2009', 'CanalSat', 'n/a', 'Canal+', '[S2 3311]', '(19.2E)'),
(745, '0100', '003311', '200A', 'CanalSat', 'n/a', 'Canal+ D?cal?', '[S2 3311]', '(19.2E)'),
(746, '0100', '003311', '200B', 'CanalSat', 'n/a', 'Canal+ Cinema', '[S2 3311]', '(19.2E)'),
(747, '0100', '003311', '200C', 'CanalSat', 'n/a', 'CANAL+', '[S2 3311]', '(19.2E)'),
(748, '0100', '003311', '200D', 'CanalSat', 'n/a', 'Canal+ Family', '[S2 3311]', '(19.2E)'),
(749, '0100', '003311', '200E', 'CanalSat', 'n/a', 'Cinecinema Premier', '[S2 3311]', '(19.2E)'),
(750, '0100', '003311', '200F', 'CanalSat', 'n/a', 'Disney Channel France', '[S2 3311]', '(19.2E)'),
(751, '0100', '003311', '2010', 'CanalSat', 'n/a', 'Canal+ Sport', '[S2 3311]', '(19.2E)'),
(752, '0100', '003311', '2011', 'CanalSat', 'n/a', 'Equidia', '[S2 3311]', '(19.2E)'),
(753, '0100', '003311', '2012', 'CanalSat', 'n/a', 'PMU sur Canal+', '[S2 3311]', '(19.2E)'),
(754, '0100', '003311', '201D', 'CanalSat', 'n/a', 'CANAL+', '[S2 3311]', '(19.2E)'),
(755, '0100', '003311', '209F', 'CanalSat', 'n/a', 'Cinecinema Club', '[S2 3311]', '(19.2E)'),
(756, '0100', '003311', '20A0', 'CanalSat', 'n/a', 'MCM', '[S2 3311]', '(19.2E)'),
(757, '0100', '003311', '20A2', 'CanalSat', 'n/a', 'MCM Pop', '[S2 3311]', '(19.2E)'),
(758, '0100', '003311', '20A3', 'CanalSat', 'n/a', 'T?l? Maison', '[S2 3311]', '(19.2E)'),
(759, '0100', '003311', '20A6', 'CanalSat', 'n/a', 'Virgin 17', '[S2 3311]', '(19.2E)'),
(760, '0100', '003311', '20AD', 'CanalSat', 'n/a', 'MCM Top', '[S2 3311]', '(19.2E)'),
(761, '0100', '003311', '20AE', 'CanalSat', 'n/a', 'TF 1', '[S2 3311]', '(19.2E)'),
(762, '0100', '003311', '20AF', 'CanalSat', 'n/a', 'M6', '[S2 3311]', '(19.2E)'),
(763, '0100', '003311', '20B3', 'CanalSat', 'n/a', 'TF1', '[S2 3311]', '(19.2E)'),
(764, '0100', '003311', '20B4', 'CanalSat', 'n/a', 'M6', '[S2 3311]', '(19.2E)'),
(765, '0100', '003311', '20B6', 'CanalSat', 'n/a', 'VIRGIN 17', '[S2 3311]', '(19.2E)'),
(766, '0100', '003311', '1FD7', 'CanalSat', 'n/a', 'Eurosport France', '[S2 3311]', '(19.2E)'),
(767, '0100', '003311', '1FD8', 'CanalSat', 'n/a', 'TMC (T?l? Monte Carlo)', '[S2 3311]', '(19.2E)'),
(768, '0100', '003311', '1FD9', 'CanalSat', 'n/a', 'Plan?te', '[S2 3311]', '(19.2E)'),
(769, '0100', '003311', '1FDB', 'CanalSat', 'n/a', 'Voyage', '[S2 3311]', '(19.2E)'),
(770, '0100', '003311', '1FDC', 'CanalSat', 'n/a', 'LCI La Cha?ne Info', '[S2 3311]', '(19.2E)'),
(771, '0100', '003311', '1FDD', 'CanalSat', 'n/a', 'Canal J', '[S2 3311]', '(19.2E)'),
(772, '0100', '003311', '1FDE', 'CanalSat', 'n/a', 'Sci-Fi Channel France', '[S2 3311]', '(19.2E)'),
(773, '0100', '003311', '1FE0', 'CanalSat', 'n/a', 'Cuisine.TV', '[S2 3311]', '(19.2E)'),
(774, '0100', '003311', '1FE1', 'CanalSat', 'n/a', 'Filles TV', '[S2 3311]', '(19.2E)'),
(775, '0100', '003311', '1FE2', 'CanalSat', 'n/a', 'TiJi', '[S2 3311]', '(19.2E)'),
(776, '0100', '003311', '1FEB', 'CanalSat', 'n/a', 'TMC', '[S2 3311]', '(19.2E)'),
(777, '0100', '003311', '7002', 'CanalSat', 'n/a', 'MTV Pulse', '[S2 3311]', '(19.2E)'),
(778, '0100', '003311', '7003', 'CanalSat', 'n/a', 'MTV Idol', '[S2 3311]', '(19.2E)'),
(779, '0100', '003311', '2261', 'CanalSat', 'n/a', 'France 2', '[S2 3311]', '(19.2E)'),
(780, '0100', '003311', '2262', 'CanalSat', 'n/a', 'France 3 Sat', '[S2 3311]', '(19.2E)'),
(781, '0100', '003311', '2264', 'CanalSat', 'n/a', 'Cine+ 11', '[S2 3311]', '(19.2E)'),
(782, '0100', '003311', '2267', 'CanalSat', 'n/a', 'Trace TV', '[S2 3311]', '(19.2E)'),
(783, '0100', '003311', '2268', 'CanalSat', 'n/a', 'T?l? M?lody', '[S2 3311]', '(19.2E)'),
(784, '0100', '003311', '226B', 'CanalSat', 'n/a', 'Cine+ 10', '[S2 3311]', '(19.2E)'),
(785, '0100', '003311', '226D', 'CanalSat', 'n/a', 'ESPN Classic Sport France', '[S2 3311]', '(19.2E)'),
(786, '0100', '003311', '2270', 'CanalSat', 'n/a', 'Motors TV', '[S2 3311]', '(19.2E)'),
(787, '0100', '003311', '2275', 'CanalSat', 'n/a', 'FRANCE 2', '[S2 3311]', '(19.2E)'),
(788, '0100', '003311', '2276', 'CanalSat', 'n/a', 'FRANCE 3', '[S2 3311]', '(19.2E)'),
(789, '0100', '003311', '24B9', 'CanalSat', 'n/a', 'TPS Star', '[S2 3311]', '(19.2E)'),
(790, '0100', '003311', '24BA', 'CanalSat', 'n/a', 'S?rie Club', '[S2 3311]', '(19.2E)'),
(791, '0100', '003311', '24BB', 'CanalSat', 'n/a', 'W9', '[S2 3311]', '(19.2E)'),
(792, '0100', '003311', '24BC', 'CanalSat', 'n/a', 'InfoSport', '[S2 3311]', '(19.2E)'),
(793, '0100', '003311', '24BD', 'CanalSat', 'n/a', 'Cinecinema Star', '[S2 3311]', '(19.2E)'),
(794, '0100', '003311', '24BE', 'CanalSat', 'n/a', 'T?l?toon', '[S2 3311]', '(19.2E)'),
(795, '0100', '003311', '24BF', 'CanalSat', 'n/a', 'M6 Music Hits', '[S2 3311]', '(19.2E)'),
(796, '0100', '003311', '24C0', 'CanalSat', 'n/a', 'Discovery Channel France', '[S2 3311]', '(19.2E)'),
(797, '0100', '003311', '24C1', 'CanalSat', 'n/a', 'Pink TV', '[S2 3311]', '(19.2E)'),
(798, '0100', '003311', '24C2', 'CanalSat', 'n/a', 'T?l?toon +1', '[S2 3311]', '(19.2E)'),
(799, '0100', '003311', '24CF', 'CanalSat', 'n/a', 'W9', '[S2 3311]', '(19.2E)'),
(800, '0100', '003311', '20D2', 'CanalSat', 'n/a', 'Cine+ 1', '[S2 3311]', '(19.2E)'),
(801, '0100', '003311', '20D3', 'CanalSat', 'n/a', 'Cine+ 2', '[S2 3311]', '(19.2E)'),
(802, '0100', '003311', '20D4', 'CanalSat', 'n/a', 'Cine+ 3', '[S2 3311]', '(19.2E)'),
(803, '0100', '003311', '20D5', 'CanalSat', 'n/a', 'Cine+ 4', '[S2 3311]', '(19.2E)'),
(804, '0100', '003311', '20D6', 'CanalSat', 'n/a', 'Cine+ 5', '[S2 3311]', '(19.2E)'),
(805, '0100', '003311', '20D7', 'CanalSat', 'n/a', 'Cine+ 6', '[S2 3311]', '(19.2E)'),
(806, '0100', '003311', '20D8', 'CanalSat', 'n/a', 'Cine+ 7', '[S2 3311]', '(19.2E)'),
(807, '0100', '003311', '20D9', 'CanalSat', 'n/a', 'Cine+ 8', '[S2 3311]', '(19.2E)'),
(808, '0100', '003311', '20DA', 'CanalSat', 'n/a', 'Cine+ 9', '[S2 3311]', '(19.2E)'),
(809, '0100', '003311', '20DB', 'CanalSat', 'n/a', 'Cine+ 13', '[S2 3311]', '(19.2E)'),
(810, '0100', '003311', '20DC', 'CanalSat', 'n/a', 'Cine+ 14', '[S2 3311]', '(19.2E)'),
(811, '0100', '003311', '251D', 'CanalSat', 'n/a', 'TF 6', '[S2 3311]', '(19.2E)'),
(812, '0100', '003311', '251E', 'CanalSat', 'n/a', 'Ushuaia TV', '[S2 3311]', '(19.2E)'),
(813, '0100', '003311', '251F', 'CanalSat', 'n/a', 'Eurosport 2 France', '[S2 3311]', '(19.2E)'),
(814, '0100', '003311', '2521', 'CanalSat', 'n/a', 'Cinecinema Premier 16/9', '[S2 3311]', '(19.2E)'),
(815, '0100', '003311', '2522', 'CanalSat', 'n/a', 'Nat Geo Wild France', '[S2 3311]', '(19.2E)'),
(816, '0100', '003311', '2523', 'CanalSat', 'n/a', 'Piwi', '[S2 3311]', '(19.2E)'),
(817, '0100', '003311', '2524', 'CanalSat', 'n/a', 'Extreme Sports', '[S2 3311]', '(19.2E)'),
(818, '0100', '003311', '2525', 'CanalSat', 'n/a', 'France 4', '[S2 3311]', '(19.2E)'),
(819, '0100', '003311', '2526', 'CanalSat', 'n/a', 'Teletoon Africa', '[S2 3311]', '(19.2E)'),
(820, '0100', '003311', '2539', 'CanalSat', 'n/a', 'FRANCE 4', '[S2 3311]', '(19.2E)'),
(821, '0100', '003311', '2135', 'CanalSat', 'n/a', 'France 5', '[S2 3311]', '(19.2E)'),
(822, '0100', '003311', '2136', 'CanalSat', 'n/a', 'TV Breizh', '[S2 3311]', '(19.2E)'),
(823, '0100', '003311', '2138', 'CanalSat', 'n/a', 'Plan&egrave;te Thalassa', '[S2 3311]', '(19.2E)'),
(824, '0100', '003311', '213A', 'CanalSat', 'n/a', 'Playboy TV France', '[S2 3311]', '(19.2E)'),
(825, '0100', '003311', '213C', 'CanalSat', 'n/a', 'France ?', '[S2 3311]', '(19.2E)'),
(826, '0100', '003311', '2143', 'CanalSat', 'n/a', 'Cine+ 12', '[S2 3311]', '(19.2E)'),
(827, '0100', '003311', '2144', 'CanalSat', 'n/a', 'OMTV (Olympique Marseille)', '[S2 3311]', '(19.2E)'),
(828, '0100', '003311', '2145', 'CanalSat', 'n/a', 'OLTV (Olympique Lyonnais)', '[S2 3311]', '(19.2E)'),
(829, '0100', '003311', '218F', 'CanalSat', 'n/a', 'FRANCE 5', '[S2 3311]', '(19.2E)'),
(830, '0100', '003311', '427C', 'CanalSat', 'n/a', 'AB Moteurs', '[S2 3311]', '(19.2E)'),
(831, '0100', '003311', '427D', 'CanalSat', 'n/a', 'AB 1', '[S2 3311]', '(19.2E)'),
(832, '0100', '003311', '427E', 'CanalSat', 'n/a', 'Animaux', '[S2 3311]', '(19.2E)'),
(833, '0100', '003311', '427F', 'CanalSat', 'n/a', 'Encyclopedia', '[S2 3311]', '(19.2E)'),
(834, '0100', '003311', '4280', 'CanalSat', 'n/a', 'XXL', '[S2 3311]', '(19.2E)'),
(835, '0100', '003311', '4281', 'CanalSat', 'n/a', 'Escales', '[S2 3311]', '(19.2E)'),
(836, '0100', '003311', '4282', 'CanalSat', 'n/a', 'Toute l&acute;Histoire', '[S2 3311]', '(19.2E)'),
(837, '0100', '003311', '4283', 'CanalSat', 'n/a', 'TMC (T?l? Monte Carlo)', '[S2 3311]', '(19.2E)'),
(838, '0100', '003311', '4284', 'CanalSat', 'n/a', 'NT 1', '[S2 3311]', '(19.2E)'),
(839, '0100', '003311', '4285', 'CanalSat', 'n/a', 'Dorcel TV', '[S2 3311]', '(19.2E)'),
(840, '0100', '003311', '4286', 'CanalSat', 'n/a', 'Action', '[S2 3311]', '(19.2E)'),
(841, '0100', '003311', '4287', 'CanalSat', 'n/a', 'Mangas', '[S2 3311]', '(19.2E)'),
(842, '0100', '003311', '4289', 'CanalSat', 'n/a', 'Cin? Polar', '[S2 3311]', '(19.2E)'),
(843, '0100', '003311', '428A', 'CanalSat', 'n/a', 'Cin&eacute; FX', '[S2 3311]', '(19.2E)'),
(844, '0100', '003311', '428B', 'CanalSat', 'n/a', 'RTL 9', '[S2 3311]', '(19.2E)'),
(845, '0100', '003311', '4290', 'CanalSat', 'n/a', 'NT1', '[S2 3311]', '(19.2E)'),
(846, '0100', '003311', '219A', 'CanalSat', 'n/a', 'Onzeo', '[S2 3311]', '(19.2E)'),
(847, '0100', '003311', '219F', 'CanalSat', 'n/a', 'Yacht & Sail France', '[S2 3311]', '(19.2E)'),
(848, '0100', '003311', '21A5', 'CanalSat', 'n/a', 'Private Spice', '[S2 3311]', '(19.2E)'),
(849, '0100', '003311', '21AC', 'CanalSat', 'n/a', 'MOSAIQUE C+', '[S2 3311]', '(19.2E)'),
(850, '0100', '003311', '21AD', 'CanalSat', 'n/a', 'MOSAIQUE C+', '[S2 3311]', '(19.2E)'),
(851, '0100', '003311', '21AF', 'CanalSat', 'n/a', 'MOSAIQUE C+', '[S2 3311]', '(19.2E)'),
(852, '0100', '003311', '21FD', 'CanalSat', 'n/a', 'EQUIDIA INFO', '[S2 3311]', '(19.2E)'),
(853, '0100', '003311', '21FE', 'CanalSat', 'n/a', 'Com?die !', '[S2 3311]', '(19.2E)'),
(854, '0100', '003311', '21FF', 'CanalSat', 'n/a', '13?me Rue', '[S2 3311]', '(19.2E)'),
(855, '0100', '003311', '2200', 'CanalSat', 'n/a', 'Discovery Real Time France', '[S2 3311]', '(19.2E)'),
(856, '0100', '003311', '2201', 'CanalSat', 'n/a', 'Disney XD France', '[S2 3311]', '(19.2E)'),
(857, '0100', '003311', '2202', 'CanalSat', 'n/a', 'L&acute;Equipe TV', '[S2 3311]', '(19.2E)'),
(858, '0100', '003311', '2203', 'CanalSat', 'n/a', 'Plan?te No Limit', '[S2 3311]', '(19.2E)'),
(859, '0100', '003311', '2204', 'CanalSat', 'n/a', 'National Geographic France', '[S2 3311]', '(19.2E)'),
(860, '0100', '003311', '2205', 'CanalSat', 'n/a', 'Cinecinema Classic', '[S2 3311]', '(19.2E)'),
(861, '0100', '003311', '2206', 'CanalSat', 'n/a', 'NRJ 12', '[S2 3311]', '(19.2E)'),
(862, '0100', '003311', '2207', 'CanalSat', 'n/a', 'Cinecinema Famiz', '[S2 3311]', '(19.2E)'),
(863, '0100', '003311', '2211', 'CanalSat', 'n/a', 'NRJ 12', '[S2 3311]', '(19.2E)'),
(864, '0100', '003311', '23F1', 'CanalSat', 'n/a', 'Canal+', '[S2 3311]', '(19.2E)'),
(865, '0100', '003311', '23F2', 'CanalSat', 'n/a', 'National Geographic HD France', '[S2 3311]', '(19.2E)'),
(866, '0100', '003311', '23F3', 'CanalSat', 'n/a', 'France 2 HD', '[S2 3311]', '(19.2E)'),
(867, '0100', '003311', '23F4', 'CanalSat', 'n/a', 'TF 1', '[S2 3311]', '(19.2E)'),
(868, '0100', '003311', '2404', 'CanalSat', 'n/a', 'CANAL HD TEST 3', '[S2 3311]', '(19.2E)'),
(869, '0100', '003311', '2405', 'CanalSat', 'n/a', 'CANAL+ HD', '[S2 3311]', '(19.2E)'),
(870, '0100', '003311', '2406', 'CanalSat', 'n/a', 'NATIONAL GEO HD', '[S2 3311]', '(19.2E)'),
(871, '0100', '003311', '2407', 'CanalSat', 'n/a', 'FRANCE 2 HD', '[S2 3311]', '(19.2E)'),
(872, '0100', '003311', '2408', 'CanalSat', 'n/a', 'TF1 HD', '[S2 3311]', '(19.2E)'),
(873, '0100', '003311', '240F', 'CanalSat', 'n/a', 'CANAL+ HD', '[S2 3311]', '(19.2E)'),
(874, '0100', '003311', '2455', 'CanalSat', 'n/a', 'Cinecinema Premier HD', '[S2 3311]', '(19.2E)'),
(875, '0100', '003311', '2456', 'CanalSat', 'n/a', '13?me Rue HD', '[S2 3311]', '(19.2E)'),
(876, '0100', '003311', '2457', 'CanalSat', 'n/a', 'Disney Cinemagic HD', '[S2 3311]', '(19.2E)'),
(877, '0100', '003311', '245E', 'CanalSat', 'n/a', 'M6 HD', '[S2 3311]', '(19.2E)'),
(878, '0100', '003311', '2469', 'CanalSat', 'n/a', 'CINE PREMIER HD', '[S2 3311]', '(19.2E)'),
(879, '0100', '003311', '246A', 'CanalSat', 'n/a', '13EME RUE HD', '[S2 3311]', '(19.2E)'),
(880, '0100', '003311', '246B', 'CanalSat', 'n/a', 'DISNEY MAGIC HD', '[S2 3311]', '(19.2E)'),
(881, '0100', '003311', '2472', 'CanalSat', 'n/a', 'M6 HD', '[S2 3311]', '(19.2E)'),
(882, '0100', '003311', '2583', 'CanalSat', 'n/a', 'Ma Cha?ne Sport', '[S2 3311]', '(19.2E)'),
(883, '0100', '003311', '2584', 'CanalSat', 'n/a', 'M6 Music Black', '[S2 3311]', '(19.2E)'),
(884, '0100', '003311', '2585', 'CanalSat', 'n/a', 'M6 Music Club', '[S2 3311]', '(19.2E)'),
(885, '0100', '003311', '2586', 'CanalSat', 'n/a', 'Vivolta', '[S2 3311]', '(19.2E)'),
(886, '0100', '003311', '2587', 'CanalSat', 'n/a', 'Odyss?e', '[S2 3311]', '(19.2E)'),
(887, '0100', '003311', '2588', 'CanalSat', 'n/a', 'Plan?te Justice', '[S2 3311]', '(19.2E)'),
(888, '0100', '003311', '22C7', 'CanalSat', 'n/a', 'Gulli', '[S2 3311]', '(19.2E)'),
(889, '0100', '003311', '22C8', 'CanalSat', 'n/a', 'unnamed channel', '[S2 3311]', '(19.2E)'),
(890, '0100', '003311', '22C9', 'CanalSat', 'n/a', 'T?va', '[S2 3311]', '(19.2E)'),
(891, '0100', '003311', '22CA', 'CanalSat', 'n/a', 'Disney Channel France +1', '[S2 3311]', '(19.2E)'),
(892, '0100', '003311', '22CB', 'CanalSat', 'n/a', 'Playhouse Disney France', '[S2 3311]', '(19.2E)'),
(893, '0100', '003311', '22CC', 'CanalSat', 'n/a', 'Disney Cinemagic France', '[S2 3311]', '(19.2E)'),
(894, '0100', '003311', '22CD', 'CanalSat', 'n/a', 'Disney Cinemagic +1 France', '[S2 3311]', '(19.2E)'),
(895, '0100', '003311', '22CF', 'CanalSat', 'n/a', 'E! Entertainment TV', '[S2 3311]', '(19.2E)'),
(896, '0100', '003311', '22D9', 'CanalSat', 'n/a', 'GULLI', '[S2 3311]', '(19.2E)'),
(897, '0100', '003311', '3335', 'CanalSat', 'n/a', 'ESPN America', '[S2 3311]', '(19.2E)'),
(898, '0100', '003311', '26AD', 'CanalSat', 'n/a', 'Ushua?a TV HD', '[S2 3311]', '(19.2E)'),
(899, '0100', '003311', '26AE', 'CanalSat', 'n/a', 'Sci-Fi HD France', '[S2 3311]', '(19.2E)'),
(900, '0100', '003311', '26AF', 'CanalSat', 'n/a', 'Eurosport HD', '[S2 3311]', '(19.2E)'),
(901, '0100', '003311', '26B0', 'CanalSat', 'n/a', 'Arte HD', '[S2 3311]', '(19.2E)'),
(902, '0100', '003311', '26C1', 'CanalSat', 'n/a', 'USHUAIA TV HD', '[S2 3311]', '(19.2E)'),
(903, '0100', '003311', '26C2', 'CanalSat', 'n/a', 'SCI FI HD', '[S2 3311]', '(19.2E)'),
(904, '0100', '003311', '26C3', 'CanalSat', 'n/a', 'EUROSPORT HD', '[S2 3311]', '(19.2E)'),
(905, '0100', '003311', '26C4', 'CanalSat', 'n/a', 'ARTE HD', '[S2 3311]', '(19.2E)'),
(906, '0100', '003311', '25E5', 'CanalSat', 'n/a', 'France 3 Amiens', '[S2 3311]', '(19.2E)'),
(907, '0100', '003311', '25E6', 'CanalSat', 'n/a', 'France 3 Besan?on', '[S2 3311]', '(19.2E)'),
(908, '0100', '003311', '25E7', 'CanalSat', 'n/a', 'France 3 Bordeaux', '[S2 3311]', '(19.2E)'),
(909, '0100', '003311', '25E8', 'CanalSat', 'n/a', 'France 3 Nancy', '[S2 3311]', '(19.2E)'),
(910, '0100', '003311', '25E9', 'CanalSat', 'n/a', 'France 3 Clermont Ferrand', '[S2 3311]', '(19.2E)'),
(911, '0100', '003311', '25EA', 'CanalSat', 'n/a', 'France 3 Paris Ile-de-France', '[S2 3311]', '(19.2E)'),
(912, '0100', '003311', '25EB', 'CanalSat', 'n/a', 'France 3 Rennes', '[S2 3311]', '(19.2E)'),
(913, '0100', '003311', '25EC', 'CanalSat', 'n/a', 'France 3 Rouen', '[S2 3311]', '(19.2E)'),
(914, '0100', '003311', '25ED', 'CanalSat', 'n/a', 'France 3 Limoges', '[S2 3311]', '(19.2E)'),
(915, '0100', '003311', '25EE', 'CanalSat', 'n/a', 'France 3 Lyon', '[S2 3311]', '(19.2E)'),
(916, '0100', '003311', '25EF', 'CanalSat', 'n/a', 'France 3 Marseille', '[S2 3311]', '(19.2E)'),
(917, '0100', '003311', '25F0', 'CanalSat', 'n/a', 'France 3 Toulouse', '[S2 3311]', '(19.2E)'),
(918, '0100', '003311', '2649', 'CanalSat', 'n/a', 'France 3 Caen', '[S2 3311]', '(19.2E)'),
(919, '0100', '003311', '264A', 'CanalSat', 'n/a', 'France 3 Nantes', '[S2 3311]', '(19.2E)'),
(920, '0100', '003311', '264B', 'CanalSat', 'n/a', 'France 3 Nice', '[S2 3311]', '(19.2E)'),
(921, '0100', '003311', '264C', 'CanalSat', 'n/a', 'France 3 Orl?ans', '[S2 3311]', '(19.2E)'),
(922, '0100', '003311', '264D', 'CanalSat', 'n/a', 'France 3 Dijon', '[S2 3311]', '(19.2E)'),
(923, '0100', '003311', '264E', 'CanalSat', 'n/a', 'France 3 Poitiers', '[S2 3311]', '(19.2E)'),
(924, '0100', '003311', '264F', 'CanalSat', 'n/a', 'France 3 Reims', '[S2 3311]', '(19.2E)'),
(925, '0100', '003311', '2650', 'CanalSat', 'n/a', 'France 3 Grenoble', '[S2 3311]', '(19.2E)'),
(926, '0100', '003311', '2651', 'CanalSat', 'n/a', 'France 3 Lille', '[S2 3311]', '(19.2E)'),
(927, '0100', '003311', '2652', 'CanalSat', 'n/a', 'France 3 Strasbourg', '[S2 3311]', '(19.2E)'),
(928, '0100', '003311', '2653', 'CanalSat', 'n/a', 'France 3 Montpellier', '[S2 3311]', '(19.2E)'),
(929, '0100', '003311', '2654', 'CanalSat', 'n/a', 'Via Stella', '[S2 3311]', '(19.2E)'),
(930, '0500', '020810', '3786', 'Bis', 'Adulti', 'Dorcel TV', '[V2 20810]', '(19.2E)'),
(931, '0500', '020810', '00C8', 'Bis', 'n/a', 'RTL 9', '[V2 20810]', '(19.2E)'),
(932, '0500', '020810', '00C9', 'Bis', 'n/a', 'AB 1', '[V2 20810]', '(19.2E)'),
(933, '0500', '020810', '00CA', 'Bis', 'n/a', 'AB Moteurs', '[V2 20810]', '(19.2E)'),
(934, '0500', '020810', '00CB', 'Bis', 'n/a', 'Animaux', '[V2 20810]', '(19.2E)'),
(935, '0500', '020810', '00CC', 'Bis', 'n/a', 'Chasse et P?che', '[V2 20810]', '(19.2E)'),
(936, '0500', '020810', '00CD', 'Bis', 'n/a', 'XXL', '[V2 20810]', '(19.2E)'),
(937, '0500', '020810', '00CE', 'Bis', 'n/a', 'TF 1', '[V2 20810]', '(19.2E)'),
(938, '0500', '020810', '00CF', 'Bis', 'n/a', 'Escales', '[V2 20810]', '(19.2E)'),
(939, '0500', '020810', '00D0', 'Bis', 'n/a', 'Toute lÂ´Histoire', '[V2 20810]', '(19.2E)'),
(940, '0500', '020810', '00D1', 'Bis', 'n/a', 'NT 1', '[V2 20810]', '(19.2E)'),
(941, '0500', '020810', '00D2', 'Bis', 'n/a', 'Action', '[V2 20810]', '(19.2E)'),
(942, '0500', '020810', '00D3', 'Bis', 'n/a', 'Mangas', '[V2 20810]', '(19.2E)'),
(943, '0500', '020810', '00D4', 'Bis', 'n/a', 'Encyclopedia', '[V2 20810]', '(19.2E)'),
(944, '0500', '020810', '00D7', 'Bis', 'n/a', 'XXL PL', '[V2 20810]', '(19.2E)'),
(945, '0500', '020810', '00D9', 'Bis', 'n/a', 'France 5', '[V2 20810]', '(19.2E)'),
(946, '0500', '020810', '00DA', 'Bis', 'n/a', 'France ?', '[V2 20810]', '(19.2E)'),
(947, '0500', '020810', '00DB', 'Bis', 'n/a', 'LCP', 'La Cha?ne Parlementaire', '[V2 20810]'),
(948, '0500', '020810', '019A', 'Bis', 'n/a', 'Prime Sports', '[V2 20810]', '(19.2E)'),
(949, '0500', '020810', '01AE', 'Bis', 'n/a', 'ART Aflam 1', '[V2 20810]', '(19.2E)'),
(950, '0500', '020810', '01E7', 'Bis', 'n/a', 'ART Hekayat Zaman', '[V2 20810]', '(19.2E)'),
(951, '0500', '020810', '1C39', 'Bis', 'n/a', 'Equidia', '[V2 20810]', '(19.2E)'),
(952, '0500', '020810', '01F5', 'Bis', 'n/a', 'M6', '[V2 20810]', '(19.2E)'),
(953, '0500', '020810', '01F6', 'Bis', 'n/a', 'NRJ 12', '[V2 20810]', '(19.2E)'),
(954, '0500', '020810', '01F7', 'Bis', 'n/a', 'France 2', '[V2 20810]', '(19.2E)'),
(955, '0500', '020810', '01F8', 'Bis', 'n/a', 'W9', '[V2 20810]', '(19.2E)'),
(956, '0500', '020810', '01F9', 'Bis', 'n/a', 'TMC (T?l? Monte Carlo)', '[V2 20810]', '(19.2E)'),
(957, '0500', '020810', '01FA', 'Bis', 'n/a', 'CinÃ© Polar', '[V2 20810]', '(19.2E)'),
(958, '0500', '020810', '01FB', 'Bis', 'n/a', 'CinÃ© FX', '[V2 20810]', '(19.2E)'),
(959, '0500', '020810', '01FD', 'Bis', 'n/a', 'Virgin 17', '[V2 20810]', '(19.2E)'),
(960, '0500', '020810', '01FF', 'Bis', 'n/a', 'AB 3', '[V2 20810]', '(19.2E)'),
(961, '0500', '020810', '0200', 'Bis', 'n/a', 'AB 4', '[V2 20810]', '(19.2E)'),
(962, '0500', '020810', '0201', 'Bis', 'n/a', 'Gulli', '[V2 20810]', '(19.2E)'),
(963, '0500', '020810', '0218', 'Bis', 'n/a', 'Orange Sport TV', '[V2 20810]', '(19.2E)'),
(964, '0500', '020810', '021A', 'Bis', 'n/a', 'France 3 Sat', '[V2 20810]', '(19.2E)'),
(965, '0500', '020810', '021B', 'Bis', 'n/a', 'France 4', '[V2 20810]', '(19.2E)'),
(966, '0500', '020810', '021D', 'Bis', 'n/a', 'Cin? First', '[V2 20810]', '(19.2E)'),
(967, '0500', '020810', '427C', 'Bis', 'n/a', 'AB Moteurs', '[V2 20810]', '(19.2E)'),
(968, '0500', '020810', '427D', 'Bis', 'n/a', 'AB 1', '[V2 20810]', '(19.2E)'),
(969, '0500', '020810', '427E', 'Bis', 'n/a', 'Animaux', '[V2 20810]', '(19.2E)'),
(970, '0500', '020810', '427F', 'Bis', 'n/a', 'Encyclopedia', '[V2 20810]', '(19.2E)'),
(971, '0500', '020810', '4280', 'Bis', 'n/a', 'XXL', '[V2 20810]', '(19.2E)'),
(972, '0500', '020810', '4281', 'Bis', 'n/a', 'Escales', '[V2 20810]', '(19.2E)'),
(973, '0500', '020810', '4282', 'Bis', 'n/a', 'Toute lÂ´Histoire', '[V2 20810]', '(19.2E)'),
(974, '0500', '020810', '4283', 'Bis', 'n/a', 'TMC (T?l? Monte Carlo)', '[V2 20810]', '(19.2E)'),
(975, '0500', '020810', '4284', 'Bis', 'n/a', 'NT 1', '[V2 20810]', '(19.2E)'),
(976, '0500', '020810', '4285', 'Bis', 'n/a', 'Dorcel TV', '[V2 20810]', '(19.2E)'),
(977, '0500', '020810', '4286', 'Bis', 'n/a', 'Action', '[V2 20810]', '(19.2E)'),
(978, '0500', '020810', '4287', 'Bis', 'n/a', 'Mangas', '[V2 20810]', '(19.2E)'),
(979, '0500', '020810', '4288', 'Bis', 'n/a', 'TMC', '[V2 20810]', '(19.2E)'),
(980, '0500', '020810', '4289', 'Bis', 'n/a', 'Cin? Polar', '[V2 20810]', '(19.2E)'),
(981, '0500', '020810', '428A', 'Bis', 'n/a', 'CinÃ© FX', '[V2 20810]', '(19.2E)'),
(982, '0500', '020810', '428B', 'Bis', 'n/a', 'RTL 9', '[V2 20810]', '(19.2E)'),
(983, '0500', '020810', '428C', 'Bis', 'n/a', 'Chasse et P?che', '[V2 20810]', '(19.2E)'),
(984, '0500', '020810', '4290', 'Bis', 'n/a', 'NT1', '[V2 20810]', '(19.2E)'),
(985, '0500', '020810', '0196', 'Bis', 'n/a', 'France ?', '[V2 20810]', '(19.2E)'),
(986, '0500', '020810', '00D5', 'Bis', 'n/a', 'Cin? First', '[V2 20810]', '(19.2E)'),
(987, '0500', '020810', '00D6', 'Bis', 'n/a', 'CinÃ© FX', '[V2 20810]', '(19.2E)'),
(988, '0500', '020810', '00D8', 'Bis', 'n/a', 'XXL', '[V2 20810]', '(19.2E)'),
(989, '0500', '020810', '012D', 'Bis', 'n/a', 'TF 1', '[V2 20810]', '(19.2E)'),
(990, '0500', '020810', '0132', 'Bis', 'n/a', 'M6', '[V2 20810]', '(19.2E)'),
(991, '0500', '020810', '0134', 'Bis', 'n/a', 'Direct 8', '[V2 20810]', '(19.2E)'),
(992, '0500', '020810', '0135', 'Bis', 'n/a', 'W9', '[V2 20810]', '(19.2E)'),
(993, '0500', '020810', '0137', 'Bis', 'n/a', 'NT 1', '[V2 20810]', '(19.2E)'),
(994, '0500', '020810', '0138', 'Bis', 'n/a', 'NRJ 12', '[V2 20810]', '(19.2E)'),
(995, '0500', '020810', '013A', 'Bis', 'n/a', 'France 4', '[V2 20810]', '(19.2E)'),
(996, '0500', '020810', '013B', 'Bis', 'n/a', 'BFM TV', '[V2 20810]', '(19.2E)'),
(997, '0500', '020810', '013D', 'Bis', 'n/a', 'Virgin 17', '[V2 20810]', '(19.2E)'),
(998, '0500', '020810', '013E', 'Bis', 'n/a', 'Gulli', '[V2 20810]', '(19.2E)'),
(999, '0500', '020810', '3C46', 'Bis', 'n/a', 'Dorcel TV', '[V2 20810]', '(19.2E)'),
(1000, '0500', '020810', '3C5A', 'Bis', 'n/a', 'Equidia', '[V2 20810]', '(19.2E)'),
(1001, '0500', '020810', '0196', 'Bis', 'n/a', 'France ?', '[V2 20810]', '(19.2E)'),
(1002, '0500', '020820', '3786', 'Bis', 'n/a', 'Dorcel TV', '[V3 20820]', '(19.2E)'),
(1003, '0500', '020820', '00C8', 'Bis', 'n/a', 'RTL 9', '[V3 20820]', '(19.2E)'),
(1004, '0500', '020820', '00C9', 'Bis', 'n/a', 'AB 1', '[V3 20820]', '(19.2E)'),
(1005, '0500', '020820', '00CA', 'Bis', 'n/a', 'AB Moteurs', '[V3 20820]', '(19.2E)'),
(1006, '0500', '020820', '00CB', 'Bis', 'n/a', 'Animaux', '[V3 20820]', '(19.2E)'),
(1007, '0500', '020820', '00CC', 'Bis', 'n/a', 'Chasse et P?che', '[V3 20820]', '(19.2E)'),
(1008, '0500', '020820', '00CD', 'Bis', 'n/a', 'XXL', '[V3 20820]', '(19.2E)'),
(1009, '0500', '020820', '00CE', 'Bis', 'n/a', 'TF 1', '[V3 20820]', '(19.2E)'),
(1010, '0500', '020820', '00CF', 'Bis', 'n/a', 'Escales', '[V3 20820]', '(19.2E)'),
(1011, '0500', '020820', '00D0', 'Bis', 'n/a', 'Toute lÂ´Histoire', '[V3 20820]', '(19.2E)'),
(1012, '0500', '020820', '00D1', 'Bis', 'n/a', 'NT 1', '[V3 20820]', '(19.2E)'),
(1013, '0500', '020820', '00D2', 'Bis', 'n/a', 'Action', '[V3 20820]', '(19.2E)'),
(1014, '0500', '020820', '00D3', 'Bis', 'n/a', 'Mangas', '[V3 20820]', '(19.2E)'),
(1015, '0500', '020820', '00D4', 'Bis', 'n/a', 'Encyclopedia', '[V3 20820]', '(19.2E)'),
(1016, '0500', '020820', '00D7', 'Bis', 'n/a', 'XXL PL', '[V3 20820]', '(19.2E)'),
(1017, '0500', '020820', '00D9', 'Bis', 'n/a', 'France 5', '[V3 20820]', '(19.2E)'),
(1018, '0500', '020820', '00DA', 'Bis', 'n/a', 'France ?', '[V3 20820]', '(19.2E)'),
(1019, '0500', '020820', '00DB', 'Bis', 'n/a', 'LCP La Cha?ne Parlementaire', '[V3 20820]', '(19.2E)'),
(1020, '0500', '020820', '1C39', 'Bis', 'n/a', 'Equidia', '[V3 20820]', '(19.2E)'),
(1021, '0500', '020820', '01F6', 'Bis', 'n/a', 'NRJ 12', '[V3 20820]', '(19.2E)'),
(1022, '0500', '020820', '01F7', 'Bis', 'n/a', 'France 2', '[V3 20820]', '(19.2E)'),
(1023, '0500', '020820', '01F9', 'Bis', 'n/a', 'TMC (T?l? Monte Carlo)', '[V3 20820]', '(19.2E)'),
(1024, '0500', '020820', '01FA', 'Bis', 'n/a', 'CinÃ© Polar', '[V3 20820]', '(19.2E)'),
(1025, '0500', '020820', '01FB', 'Bis', 'n/a', 'CinÃ© FX', '[V3 20820]', '(19.2E)'),
(1026, '0500', '020820', '01FD', 'Bis', 'n/a', 'Virgin 17', '[V3 20820]', '(19.2E)'),
(1027, '0500', '020820', '0201', 'Bis', 'n/a', 'Gulli', '[V3 20820]', '(19.2E)'),
(1028, '0500', '020820', '0218', 'Bis', 'n/a', 'Orange Sport TV', '[V3 20820]', '(19.2E)'),
(1029, '0500', '020820', '021A', 'Bis', 'n/a', 'France 3 Sat', '[V3 20820]', '(19.2E)'),
(1030, '0500', '020820', '021B', 'Bis', 'n/a', 'France 4', '[V3 20820]', '(19.2E)'),
(1031, '0500', '020820', '427C', 'Bis', 'n/a', 'AB Moteurs', '[V3 20820]', '(19.2E)'),
(1032, '0500', '020820', '427D', 'Bis', 'n/a', 'AB 1', '[V3 20820]', '(19.2E)'),
(1033, '0500', '020820', '427E', 'Bis', 'n/a', 'Animaux', '[V3 20820]', '(19.2E)'),
(1034, '0500', '020820', '427F', 'Bis', 'n/a', 'Encyclopedia', '[V3 20820]', '(19.2E)'),
(1035, '0500', '020820', '4280', 'Bis', 'n/a', 'XXL', '[V3 20820]', '(19.2E)'),
(1036, '0500', '020820', '4281', 'Bis', 'n/a', 'Escales', '[V3 20820]', '(19.2E)'),
(1037, '0500', '020820', '4282', 'Bis', 'n/a', 'Toute lÂ´Histoire', '[V3 20820]', '(19.2E)'),
(1038, '0500', '020820', '4283', 'Bis', 'n/a', 'TMC (T?l? Monte Carlo)', '[V3 20820]', '(19.2E)'),
(1039, '0500', '020820', '4284', 'Bis', 'n/a', 'NT 1', '[V3 20820]', '(19.2E)'),
(1040, '0500', '020820', '4285', 'Bis', 'n/a', 'Dorcel TV', '[V3 20820]', '(19.2E)'),
(1041, '0500', '020820', '4286', 'Bis', 'n/a', 'Action', '[V3 20820]', '(19.2E)'),
(1042, '0500', '020820', '4287', 'Bis', 'n/a', 'Mangas', '[V3 20820]', '(19.2E)'),
(1043, '0500', '020820', '4288', 'Bis', 'n/a', 'TMC', '[V3 20820]', '(19.2E)'),
(1044, '0500', '020820', '4289', 'Bis', 'n/a', 'Cin? Polar', '[V3 20820]', '(19.2E)'),
(1045, '0500', '020820', '428A', 'Bis', 'n/a', 'CinÃ© FX', '[V3 20820]', '(19.2E)'),
(1046, '0500', '020820', '428B', 'Bis', 'n/a', 'RTL 9', '[V3 20820]', '(19.2E)'),
(1047, '0500', '020820', '428C', 'Bis', 'n/a', 'Chasse et P?che', '[V3 20820]', '(19.2E)'),
(1048, '0500', '020820', '4290', 'Bis', 'n/a', 'NT1', '[V3 20820]', '(19.2E)'),
(1049, '0500', '020820', '01F5', 'Bis', 'n/a', 'Equidia', '[V3 20820]', '(19.2E)'),
(1050, '0500', '020820', '0196', 'Bis', 'n/a', 'France ?', '[V3 20820]', '(19.2E)'),
(1051, '0500', '020820', '00D6', 'Bis', 'n/a', 'CinÃ© FX', '[V3 20820]', '(19.2E)'),
(1052, '0500', '020820', '00D8', 'Bis', 'n/a', 'XXL', '[V3 20820]', '(19.2E)'),
(1053, '0500', '020820', '012D', 'Bis', 'n/a', 'TF 1', '[V3 20820]', '(19.2E)'),
(1054, '0500', '020820', '0132', 'Bis', 'n/a', 'M6', '[V3 20820]', '(19.2E)'),
(1055, '0500', '020820', '0134', 'Bis', 'n/a', 'Direct 8', '[V3 20820]', '(19.2E)'),
(1056, '0500', '020820', '0135', 'Bis', 'n/a', 'W9', '[V3 20820]', '(19.2E)'),
(1057, '0500', '020820', '0137', 'Bis', 'n/a', 'NT 1', '[V3 20820]', '(19.2E)'),
(1058, '0500', '020820', '0138', 'Bis', 'n/a', 'NRJ 12', '[V3 20820]', '(19.2E)'),
(1059, '0500', '020820', '013A', 'Bis', 'n/a', 'France 4', '[V3 20820]', '(19.2E)'),
(1060, '0500', '020820', '013B', 'Bis', 'n/a', 'BFM TV', '[V3 20820]', '(19.2E)'),
(1061, '0500', '020820', '013D', 'Bis', 'n/a', 'Virgin 17', '[V3 20820]', '(19.2E)'),
(1062, '0500', '020820', '013E', 'Bis', 'n/a', 'Gulli', '[V3 20820]', '(19.2E)'),
(1063, '0500', '020820', '3C46', 'Bis', 'n/a', 'Dorcel TV', '[V3 20820]', '(19.2E)'),
(1064, '0500', '020820', '3C5A', 'Bis', 'n/a', 'Equidia', '[V3 20820]', '(19.2E)'),
(1065, '0100', '000065', '1147', 'Cyfra', 'n/a', 'unnamed channel', '[S2 65]', '(13E)'),
(1066, '0100', '000065', '117C', 'Cyfra', 'n/a', 'DTV CABLE PL', '[S2 65]', '(13E)'),
(1067, '0100', '000065', '117D', 'Cyfra', 'n/a', 'DTV CABLE MI', '[S2 65]', '(13E)'),
(1068, '0100', '000065', '12E8', 'Cyfra', 'n/a', 'CYFRA+ RADIO', '[S2 65]', '(13E)'),
(1069, '0100', '000065', '12FE', 'Cyfra', 'n/a', 'tech', '[S2 65]', '(13E)'),
(1070, '0100', '000065', '12FF', 'Cyfra', 'n/a', 'tech 2', '[S2 65]', '(13E)'),
(1071, '0100', '000065', '3305', 'Cyfra', 'n/a', 'Eurosport HD', '[S2 65]', '(13E)'),
(1072, '0100', '000065', '3307', 'Cyfra', 'n/a', 'ESP HD Turk', '[S2 65]', '(13E)'),
(1073, '0100', '000065', '3308', 'Cyfra', 'n/a', 'ESP HD Russian', '[S2 65]', '(13E)'),
(1074, '0100', '000065', '3309', 'Cyfra', 'n/a', 'ESP HD Dutch', '[S2 65]', '(13E)'),
(1075, '0100', '000065', '330A', 'Cyfra', 'n/a', 'ESP HD Czech', '[S2 65]', '(13E)'),
(1076, '0100', '000065', '330B', 'Cyfra', 'n/a', 'ESP HD German', '[S2 65]', '(13E)'),
(1077, '0100', '000065', '330D', 'Cyfra', 'n/a', 'ESP HD Portuguese', '[S2 65]', '(13E)'),
(1078, '0100', '000065', '330E', 'Cyfra', 'n/a', 'ESP HD Polish', '[S2 65]', '(13E)'),
(1079, '0100', '000065', '330F', 'Cyfra', 'n/a', 'ESP HD Hungarian', '[S2 65]', '(13E)'),
(1080, '0100', '000065', '3310', 'Cyfra', 'n/a', 'ESP HD Bulgaria', '[S2 65]', '(13E)'),
(1081, '0100', '000065', '3311', 'Cyfra', 'n/a', 'ESP HD Italy', '[S2 65]', '(13E)'),
(1082, '0100', '000065', '00CA', 'Cyfra', 'n/a', 'AB Moteurs', '[S2 65]', '(13E)'),
(1083, '0100', '000065', '00CC', 'Cyfra', 'n/a', 'Chasse et P?che', '[S2 65]', '(13E)'),
(1084, '0100', '000065', '00CD', 'Cyfra', 'n/a', 'XXL', '[S2 65]', '(13E)'),
(1085, '0100', '000065', '00D7', 'Cyfra', 'n/a', 'XXL PL', '[S2 65]', '(13E)'),
(1086, '0100', '000065', '00D9', 'Cyfra', 'n/a', 'France 5', '[S2 65]', '(13E)'),
(1087, '0100', '000065', '00DA', 'Cyfra', 'n/a', 'France ?', '[S2 65]', '(13E)'),
(1088, '0100', '000065', '0C24', 'Cyfra', 'n/a', 'unnamed channel', '[S2 65]', '(13E)'),
(1089, '0100', '000065', '01F7', 'Cyfra', 'n/a', 'France 2', '[S2 65]', '(13E)'),
(1090, '0100', '000065', '021A', 'Cyfra', 'n/a', 'France 3 Sat', '[S2 65]', '(13E)'),
(1091, '0100', '000065', '021B', 'Cyfra', 'n/a', 'France 4', '[S2 65]', '(13E)'),
(1106, '0919', '000000', '0F74', 'Sky Italia', 'Cinema', 'Sky Cinema Mania HD', 'NDS', '(13E)'),
(1105, '0919', '000000', '0F71', 'Sky Italia', 'Cinema', 'Sky Cinema Max +1 HD', 'NDS', '(13E)'),
(1, '0919', '000000', '2B5D', 'Sky Italia', 'Cinema', 'Sky Cinema 1', 'NDS', '(13E)'),
(1107, '0919', '000000', '0F75', 'Sky Italia', 'Cinema', 'Sky Cinema Italia HD', 'NDS', '(13E)'),
(1111, '0500', '021700', '34da', 'DMC', 'Porn', 'Private Spice', 'V 21700', '(13E)'),
(1110, '0100', '000030', '2140', 'Rai', 'n/a', 'Rai Due', 'S1', '(13E)'),
(1109, '0100', '000030', '0D4B', 'Rai', 'n/a', 'Rai Tre', 'S1', '(13E)'),
(1248, '0500', '032000', '36BD', 'SOS', 'Porn', 'Premium 1', 'Via 32000', '(13E)'),
(1108, '0100', '000030', '0D49', 'Rai', 'n/a', 'Rai Uno', 'S1', '(13E)'),
(1112, '0604', '000000', '0001', 'Bulsatcom', 'n/a', 'Diema Family', 'I2', '(13E)'),
(1113, '0604', '000000', '0003', 'Bulsatcom', 'n/a', 'TV 1000 Balkan', 'I2', '(13E)'),
(1114, '0604', '000000', '0005', 'Bulsatcom', 'n/a', 'Cinemax East Europe', 'I2', '(13E)'),
(1115, '0604', '000000', '0007', 'Bulsatcom', 'n/a', 'Eurosport', 'I2', '(13E)'),
(1116, '0604', '000000', '000A', 'Bulsatcom', 'n/a', 'Eurosport 2 budensliga', 'I2', '(13E)'),
(1117, '0604', '000000', '000F', 'Bulsatcom', 'n/a', 'PRO BG', 'I2', '(13E)'),
(1118, '0604', '000000', '0011', 'Bulsatcom', 'n/a', 'AXN Sci Fi', 'I2', '(13E)'),
(1119, '0604', '000000', '0015', 'Bulsatcom', 'n/a', 'Film+ HD', 'I2', '(13E)'),
(1120, '0604', '000000', '001C', 'Bulsatcom', 'n/a', 'Fox Life Bulgaria', 'I2', '(13E)'),
(1121, '0604', '000000', '001F', 'Bulsatcom', 'n/a', 'Hallmark Central Europe', 'I2', '(13E)'),
(1122, '0604', '000000', '0023', 'Bulsatcom', 'n/a', 'Discovery Travel & Living UK', 'I2', '(13E)'),
(1123, '0604', '000000', '0025', 'Bulsatcom', 'n/a', 'VH-1 (Video Hits One)', 'I2', '(13E)'),
(1124, '0604', '000000', '0028', 'Bulsatcom', 'n/a', 'Ring TV', 'I2', '(13E)'),
(1125, '0604', '000000', '002C', 'Bulsatcom', 'n/a', 'Pop Core', 'I2', '(13E)'),
(1126, '0604', '000000', '002E', 'Bulsatcom', 'n/a', 'Nova Sport Bulgaria', 'I2', '(13E)'),
(1127, '0604', '000000', '0031', 'Bulsatcom', 'n/a', 'TV Evropa', 'I2', '(13E)'),
(1128, '0604', '000000', '006F', 'Bulsatcom', 'n/a', 'Playboy TV/Viasat Explorer', 'I2', '(13E)'),
(1129, '0604', '000000', '0071', 'Bulsatcom', 'n/a', 'Cartoon Network/TCM', 'I2', '(13E)'),
(1130, '0604', '000000', '0073', 'Bulsatcom', 'n/a', 'Perviy Kanal', 'I2', '(13E)'),
(1131, '0604', '000000', '0075', 'Bulsatcom', 'n/a', 'Hustler TV', 'I2', '(13E)'),
(1132, '0604', '000000', '0077', 'Bulsatcom', 'n/a', 'Folklor TV', 'I2', '(13E)'),
(1133, '0604', '000000', '0079', 'Bulsatcom', 'n/a', 'Fan TV', 'I2', '(13E)'),
(1134, '0604', '000000', '007B', 'Bulsatcom', 'n/a', 'Balkanika', 'I2', '(13E)'),
(1135, '0604', '000000', '007D', 'Bulsatcom', 'n/a', 'CNN', 'I2', '(13E)'),
(1136, '0604', '000000', '0081', 'Bulsatcom', 'n/a', 'City TV', 'I2', '(13E)'),
(1137, '0604', '000000', '0084', 'Bulsatcom', 'n/a', 'RTV International', 'I2', '(13E)'),
(1138, '0604', '000000', '0089', 'Bulsatcom', 'n/a', 'MGM', 'I2', '(13E)'),
(1139, '0604', '000000', '00C9', 'OSN', 'n/a', 'OSN Cinema 1', 'I2', '(13E)'),
(1140, '0604', '000000', '00CB', 'Bulsatcom', 'n/a', 'Hobby TV HD', 'I2', '(13E)'),
(1141, '0604', '000000', '00CC', 'Bulsatcom', 'n/a', 'National Geographic Europe', 'I2', '(13E)'),
(1142, '0604', '000000', '00CD', 'Bulsatcom', 'n/a', 'Discovery HD Europe', 'I2', '(13E)'),
(1143, '0604', '000000', '00CE', 'Bulsatcom', 'n/a', 'Planeta HD', 'I2', '(13E)'),
(1144, '0604', '000000', '00CF', 'OSN', 'n/a', 'OSN Shasha', 'I2', '(13E)'),
(1145, '0604', '000000', '00D0', 'OSN', 'n/a', 'MBC+ Drama', 'I2', '(13E)'),
(1146, '0604', '000000', '00D2', 'OSN', 'n/a', 'OSN Comedy', 'I2', '(13E)'),
(1147, '0604', '000000', '00D4', 'OSN', 'n/a', 'ShowSeries 1', 'I2', '(13E)'),
(1148, '0604', '000000', '012D', 'NOVA', 'n/a', 'R1', 'I2', '(13E)'),
(1149, '0604', '000000', '012E', 'OSN', 'n/a', 'Super Movies +2', 'I2', '(13E)'),
(1150, '0604', '000000', '012F', 'OSN', 'n/a', 'ShowMovies 1', 'I2', '(13E)'),
(1151, '0604', '000000', '0130', 'OSN', 'n/a', 'Super Movies', 'I2', '(13E)'),
(1152, '0604', '000000', '0132', 'NOVA', 'n/a', 'R5', 'I2', '(13E)'),
(1153, '0604', '000000', '0133', 'NOVA', 'n/a', 'R6', 'I2', '(13E)'),
(1154, '0604', '000000', '0134', 'NOVA', 'n/a', 'Nova sport1 Cy', 'I2', '(13E)'),
(1155, '0604', '000000', '0135', 'OSN', 'n/a', 'ShowSports 2', 'I2', '(13E)');
INSERT INTO `cccam_channelinfo` (`chan_id`, `chan_caid`, `chan_ident`, `chan_chaid`, `chan_provider`, `chan_category`, `chan_channel_name`, `chan_encription`, `chan_sat`) VALUES
(1156, '0604', '000000', '0137', 'OSN', 'n/a', 'Cinema City', 'I2', '(13E)'),
(1157, '0604', '000000', '013D', 'NOVA', 'n/a', 'Nova cinema1', 'I2', '(13E)'),
(1158, '0604', '000000', '013F', 'NOVA', 'n/a', 'Mega channel', 'I2', '(13E)'),
(1159, '0604', '000000', '0141', 'NOVA', 'n/a', 'Star channel', 'I2', '(13E)'),
(1160, '0604', '000000', '0144', 'NOVA', 'n/a', 'R13', 'I2', '(13E)'),
(1161, '0604', '000000', '0146', 'NOVA', 'n/a', 'R11', 'I2', '(13E)'),
(1162, '0604', '000000', '015F', 'NOVA', 'n/a', 'Discovery Channel Greece', 'I2', '(13E)'),
(1163, '0604', '000000', '0161', 'NOVA', 'n/a', 'Nova sport2', 'I2', '(13E)'),
(1164, '0604', '000000', '0163', 'NOVA', 'n/a', 'Fox life greece', 'I2', '(13E)'),
(1165, '0604', '000000', '0165', 'NOVA', 'n/a', 'R10', 'I2', '(13E)'),
(1166, '0604', '000000', '0168', 'NOVA', 'n/a', 'Cartoon Network', 'I2', '(13E)'),
(1167, '0604', '000000', '016A', 'NOVA', 'n/a', 'Skai', 'I2', '(13E)'),
(1168, '0604', '000000', '016F', 'NOVA', 'n/a', 'R7', 'I2', '(13E)'),
(1169, '0604', '000000', '0171', 'NOVA', 'n/a', 'Nova sport7 Cy', 'I2', '(13E)'),
(1170, '0604', '000000', '0179', 'NOVA', 'n/a', 'Mad tv', 'I2', '(13E)'),
(1171, '0604', '000000', '017C', 'NOVA', 'n/a', 'Mad tv', 'I2', '(13E)'),
(1172, '0604', '000000', '017F', 'NOVA', 'n/a', 'novasports2 Cy', 'I2', '(13E)'),
(1173, '0604', '000000', '01A6', 'OSN', 'n/a', 'TCM', 'I2', '(13E)'),
(1174, '0604', '000000', '01A7', 'OSN', 'n/a', 'VH-1 (Video Hits One)', 'I2', '(13E)'),
(1175, '0604', '000000', '01A9', 'OSN', 'n/a', 'BBC Lifestyle', 'I2', '(13E)'),
(1176, '0604', '000000', '01AB', 'OSN', 'n/a', 'BET U.K', 'I2', '(13E)'),
(1177, '0604', '000000', '01AD', 'OSN', 'n/a', 'CNBC Europe', 'I2', '(13E)'),
(1178, '0604', '000000', '01B0', 'OSN', 'n/a', 'Boomerang', 'I2', '(13E)'),
(1179, '0604', '000000', '02BE', 'ADD', 'n/a', 'Spare', 'I2', '(13E)'),
(1180, '0604', '000000', '02C0', 'ADD', 'n/a', 'Hekayat Kaman', 'I2', '(13E)'),
(1181, '0604', '000000', '02C2', 'ADD', 'n/a', 'ART Aflam 1', 'I2', '(13E)'),
(1182, '0604', '000000', '02C5', 'ADD', 'n/a', 'ART Cinema', 'I2', '(13E)'),
(1183, '0604', '000000', '02C8', 'ADD', 'n/a', 'ART Tarab', 'I2', '(13E)'),
(1184, '0604', '000000', '03EA', 'ADD', 'n/a', 'Ten Sports Middle East', 'I2', '(13E)'),
(1185, '0604', '000000', '03EB', 'ADD', 'n/a', 'B4U Music', 'I2', '(13E)'),
(1186, '0604', '000000', '03EC', 'ADD', 'n/a', 'B4U Movies', 'I2', '(13E)'),
(1187, '0604', '000000', '03ED', 'Bulsatcom', 'n/a', 'HBO Bulgaria', 'I2', '(13E)'),
(1188, '0604', '000000', '03EE', 'Bulsatcom', 'n/a', 'TV 7 Bulgaria', 'I2', '(13E)'),
(1189, '0604', '000000', '03EF', 'ADD', 'n/a', 'ZoneReality', 'I2', '(13E)'),
(1190, '0604', '000000', '03F0', 'OSN', 'n/a', 'Discovery Science Channel', 'I2', '(13E)'),
(1191, '0604', '000000', '03F2', 'Bulsatcom', 'n/a', 'TV Plus', 'I2', '(13E)'),
(1192, '0604', '000000', '03F4', 'ADD', 'n/a', 'Granada UK TV', 'I2', '(13E)'),
(1193, '0604', '000000', '03F5', 'ADD', 'n/a', 'Star one', 'I2', '(13E)'),
(1194, '0604', '000000', '03F6', 'Bulsatcom', 'n/a', 'Hobby TV', 'I2', '(13E)'),
(1195, '0604', '000000', '03F8', 'ADD', 'n/a', 'Fox news', 'I2', '(13E)'),
(1196, '0604', '000000', '03FO', 'ADD', 'n/a', 'Discovery Science Channel', 'I2', '(13E)'),
(1251, '0500', '025100', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a'),
(1250, '0B00', '000000', '0967', 'Canal Digital Nordic', 'n/a', 'Boomerang', 'C', '(0,8W)'),
(1249, '0500', '032000', '36BE', 'SOS', 'Porn', 'Premium 2', 'Via 32000', '(13E)'),
(1205, '0604', '000000', '07D3', 'OSN', 'n/a', 'Fox Sports Middle East', 'I2', '(13E)'),
(1206, '0604', '000000', '07DB', 'OSN', 'n/a', 'Sky News International', 'I2', '(13E)'),
(1207, '0604', '000000', '08FD', 'ADD', 'n/a', 'Baby tv', 'I2', '(13E)'),
(1208, '0604', '000000', '08FF', 'ADD', 'n/a', 'Kairali TV', 'I2', '(13E)'),
(1209, '0604', '000000', '091F', 'ADD', 'n/a', 'Prime Sports', 'I2', '(13E)'),
(1210, '0604', '000000', '0929', 'ADD', 'n/a', 'Dawn News', 'I2', '(13E)'),
(1211, '0604', '000000', '0DB4', 'ADD', 'n/a', 'Aaj news', 'I2', '(13E)'),
(1212, '0604', '000000', '0DB8', 'ADD', 'n/a', 'Spare', 'I2', '(13E)'),
(1213, '0604', '000000', '1196', 'OSN', 'n/a', 'Al Safwa', 'I2', '(13E)'),
(1214, '0604', '000000', '1198', 'OSN', 'n/a', 'Cinema2', 'I2', '(13E)'),
(1215, '0604', '000000', '119A', 'OSN', 'n/a', 'Fann', 'I2', '(13E)'),
(1216, '0604', '000000', '119C', 'OSN', 'n/a', 'Orbit news', 'I2', '(13E)'),
(1217, '0604', '000000', '119E', 'OSN', 'n/a', 'Pinoy Extreme', 'I2', '(13E)'),
(1218, '0604', '000000', '11A0', 'OSN', 'n/a', 'GMA Life TV', 'I2', '(13E)'),
(1219, '0604', '000000', '11FA', 'OSN', 'n/a', 'Trace TV', 'I2', '(13E)'),
(1220, '0604', '000000', '11FC', 'OSN', 'n/a', 'OSN Variety', 'I2', '(13E)'),
(1221, '0604', '000000', '11FE', 'OSN', 'n/a', 'OSN Comedy +2', 'I2', '(13E)'),
(1222, '0604', '000000', '1200', 'OSN', 'n/a', 'America Plus +2', 'I2', '(13E)'),
(1223, '0604', '000000', '1202', 'OSN', 'n/a', 'Style Network EMEA', 'I2', '(13E)'),
(1224, '0604', '000000', '1BBE', 'NOVA', 'n/a', 'E!', 'I2', '(13E)'),
(1225, '0604', '000000', '1BC0', 'NOVA', 'n/a', 'Private spice', 'I2', '(13E)'),
(1226, '0604', '000000', '1BC3', 'NOVA', 'n/a', 'Nova sport4', 'I2', '(13E)'),
(1227, '0604', '000000', '1BC5', 'NOVA', 'n/a', 'Nova sport6', 'I2', '(13E)'),
(1228, '0604', '000000', '1BC8', 'NOVA', 'n/a', 'Playboy tv', 'I2', '(13E)'),
(1229, '0604', '000000', '1BCA', 'NOVA', 'n/a', 'Nova cinema3 Cy', 'I2', '(13E)'),
(1230, '0604', '000000', '1BCC', 'NOVA', 'n/a', 'Nova sport6 Cy', 'I2', '(13E)'),
(1231, '0604', '000000', '1BD0', 'NOVA', 'n/a', 'Alpha TV Cyprus', 'I2', '(13E)'),
(1232, '0604', '000000', '1BD2', 'NOVA', 'n/a', 'Boomerand', 'I2', '(13E)'),
(1233, '0604', '000000', '1D4D', 'NOVA', 'n/a', 'Nova sport3', 'I2', '(13E)'),
(1234, '0604', '000000', '1D4F', 'NOVA', 'n/a', 'MTV grecce', 'I2', '(13E)'),
(1235, '0604', '000000', '1D52', 'NOVA', 'n/a', 'Animal planet', 'I2', '(13E)'),
(1236, '0604', '000000', '1D54', 'NOVA', 'n/a', 'National geographic greece', 'I2', '(13E)'),
(1237, '0604', '000000', '1D57', 'NOVA', 'n/a', 'summer cinema', 'I2', '(13E)'),
(1238, '0604', '000000', '1D88', 'NOVA', 'n/a', 'Nova sport3 Cy', 'I2', '(13E)'),
(1239, '0604', '000000', '1D97', 'NOVA', 'n/a', 'Travel channel', 'I2', '(13E)'),
(1240, '0604', '000000', '2011', 'NOVA', 'n/a', 'CNN', 'I2', '(13E)'),
(1241, '0604', '000000', '35C1', 'SCT', 'n/a', 'D-XTV2', 'I2', '(13E)'),
(1242, '0604', '000000', '35C3', 'SCT', 'n/a', 'SCT 5', 'I2', '(13E)'),
(1243, '0604', '000000', '35C6', 'SCT', 'n/a', 'Satisfaction Channel Television', 'I2', '(13E)'),
(1244, '0604', '000000', '35C8', 'SCT', 'n/a', 'Satisfaction Channel Television', 'I2', '(13E)'),
(1245, '0604', '000000', '3607', 'NOVA', 'n/a', 'Eurosport', 'I2', '(13E)'),
(1246, '0604', '000000', '3C8E', 'NOVA', 'n/a', 'Fox life greece', 'I2', '(13E)'),
(1247, '0604', '000000', '3C91', 'NOVA', 'n/a', 'FX greece', 'I2', '(13E)'),
(1252, '0500', '032500', '-', '-', '-', '-', '-', '-');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_cline`
--

CREATE TABLE IF NOT EXISTS `cccam_cline` (
  `cline_id` int(11) NOT NULL AUTO_INCREMENT,
  `cline_hostname` varchar(50) NOT NULL,
  `cline_port` int(5) NOT NULL,
  `cline_username` varchar(20) NOT NULL,
  `cline_password` varchar(20) NOT NULL,
  `cline_wantemus` varchar(3) NOT NULL,
  `cline_caid_id_hops` longtext NOT NULL,
  `cline_cardlimit` longtext NOT NULL,
  `user_id` varchar(30) NOT NULL,
  `cline_active` varchar(2) NOT NULL,
  PRIMARY KEY (`cline_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `cccam_cline`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_config`
--

CREATE TABLE IF NOT EXISTS `cccam_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_value_name` varchar(200) NOT NULL,
  `config_value` longtext NOT NULL,
  `config_active` varchar(2) NOT NULL,
  PRIMARY KEY (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47 ;

--
-- Dump dei dati per la tabella `cccam_config`
--

INSERT INTO `cccam_config` (`config_id`, `config_value_name`, `config_value`, `config_active`) VALUES
(1, 'SERVER LISTEN PORT :', '12000', '1'),
(2, 'ALLOW TELNETINFO :', 'yes', '1'),
(3, 'ALLOW WEBINFO :', 'yes', '1'),
(4, 'SHOW EXTENEDED CLIENT INFO :', 'no', '0'),
(5, 'WEBINFO USERNAME :', 'user', '1'),
(6, 'WEBINFO PASSWORD :', 'pass', '1'),
(7, 'TELNETINFO USERNAME :', 'CHANGE_ME', '0'),
(8, 'TELNETINFO PASSWORD :', '<password>', '0'),
(9, 'TELNETINFO LISTEN PORT :', '16000', '1'),
(10, 'WEBINFO LISTEN PORT :', '16001', '1'),
(11, 'ZAP OSD TIME :', '3', '0'),
(12, 'OSD USERNAME :', 'root', '0'),
(13, 'OSD PASSWORD :', 'dreambox', '0'),
(14, 'OSD PORT :', '80', '0'),
(15, 'DISABLE EMM :', 'no', '1'),
(16, 'EXTRA EMM LEVEL :', 'yes', '0'),
(17, 'EMM THREADS :', '1', '0'),
(18, 'BOXKEY :', '/dev/sci0 00 11 22 33', '0'),
(19, 'PIN :', '/dev/sci0 1234', '0'),
(20, 'CAMKEY :', '/dev/sci0 11 22 33 44 55 66 77 88', '0'),
(21, 'CAMDATA :', '/dev/sci0 11 22 33 44 55 66 77 88 99 aa bb cc dd ee ff', '0'),
(22, 'BEEF ID :', '4101 0 0 0 0 0 0 0 /dev/sci0', '0'),
(23, 'SOFTKEY FILE :', '/var/keys/SoftCam.Key', '0'),
(24, 'AUTOROLL FILE', '/var/keys/AutoRoll.Key', '0'),
(25, 'STATIC CW FILE :', '/var/keys/constant.cw', '0'),
(26, 'CAID PRIO FILE :', '/var/etc/CCcam.prio', '0'),
(27, 'PROVIDERINFO FILE :', '/var/etc/CCcam.providers', '0'),
(28, 'CHANNELINFO FILE :', '/var/etc/CCcam.channelinfo', '0'),
(29, 'LOG WARNINGS :', '/tmp/warnings.txt', '0'),
(30, 'NEWCAMD STEALTH :', '0', '0'),
(31, 'LOADBALANCE :', '/dev/ttyUSB0 /dev/ttyUSB1 /dev/ttyUSB2', '0'),
(32, 'MINIMUM CLIENT VERSION :', '2.0.0', '1'),
(33, 'TRY ALL CHIDS :', '/dev/ttyUSB0', '0'),
(34, 'POSTINIT :', '/dev/sci0 /tmp/postinit', '0'),
(35, 'DVB API :', '-1', '0'),
(36, 'GLOBAL LIMITS :', '{ 0100:000080, 0622:000000:1, 0500:000000:2 }', '0'),
(37, 'MINIMUM DOWNHOPS', '1', '0'),
(38, 'IGNORE NODE :', '<nodeid>', '0'),
(39, 'SECA HANDLER', '4', '0'),
(40, 'SMARTCARD SID ASSIGN :', '/dev/ttyUSB0 0 { 0df3,0df4,0df5 }', '0'),
(41, 'SHOW TIMING :', 'yes', '1'),
(42, 'MINI OSD :', 'yes', '0'),
(43, 'DEBUG :', 'yes', '1'),
(44, 'NEWCAMD CONF :', 'yes', '0');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_emm_blocker`
--

CREATE TABLE IF NOT EXISTS `cccam_emm_blocker` (
  `emm_blocker_id` int(11) NOT NULL AUTO_INCREMENT,
  `emm_blocker_name_value` varchar(5) NOT NULL,
  `emm_blocker_reader` varchar(50) NOT NULL,
  `emm_blocker_value` varchar(2) NOT NULL,
  PRIMARY KEY (`emm_blocker_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dump dei dati per la tabella `cccam_emm_blocker`
--

INSERT INTO `cccam_emm_blocker` (`emm_blocker_id`, `emm_blocker_name_value`, `emm_blocker_reader`, `emm_blocker_value`) VALUES
(1, 'B:', '/dev/ttyUSB0', '00'),
(2, 'B:', '/dev/ttyUSB1', '00'),
(3, 'B:', '/dev/ttyUSB2', '00'),
(4, 'B:', '/dev/ttyUSB3', '00'),
(5, 'B:', '/dev/ttyUSB4', '00'),
(6, 'B:', '/dev/ttyUSB5', '00'),
(7, 'B:', '/dev/ttyUSB6', '00'),
(8, 'B:', '/dev/ttyUSB7', '00'),
(9, 'B:', '/dev/ttyUSB8', '00');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_fline`
--

CREATE TABLE IF NOT EXISTS `cccam_fline` (
  `fline_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `fline_active` varchar(2) NOT NULL,
  `fline_username` varchar(20) NOT NULL,
  `fline_password` varchar(20) NOT NULL,
  `fline_uphops` varchar(2) NOT NULL,
  `fline_shareemus` varchar(2) NOT NULL,
  `fline_allowemm` varchar(2) NOT NULL,
  `fline_reshare` varchar(10) NOT NULL,
  `fline_cardlimit` longtext,
  `fline_chanlimit` longtext,
  `fline_timelimit` varchar(255) DEFAULT NULL,
  `fline_hostlimit` varchar(255) DEFAULT NULL,
  `ftp_active` varchar(2) NOT NULL,
  `ftp_ip` varchar(50) NOT NULL,
  `ftp_port` varchar(5) NOT NULL,
  `ftp_user` varchar(20) NOT NULL,
  `ftp_pass` varchar(20) NOT NULL,
  `ftp_local` varchar(255) NOT NULL,
  `ftp_remote` varchar(255) NOT NULL,
  PRIMARY KEY (`fline_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `cccam_fline`
--

INSERT INTO `cccam_fline` (`fline_id`, `user_id`, `fline_active`, `fline_username`, `fline_password`, `fline_uphops`, `fline_shareemus`, `fline_allowemm`, `fline_reshare`, `fline_cardlimit`, `fline_chanlimit`, `fline_timelimit`, `fline_hostlimit`, `ftp_active`, `ftp_ip`, `ftp_port`, `ftp_user`, `ftp_pass`, `ftp_local`, `ftp_remote`) VALUES
(1, 1, '1', 'admin', 'admin', '1', '0', '1', '0:0:1', NULL, NULL, NULL, NULL, '0', '-', '-', '-', '-', '-', '-');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_lline`
--

CREATE TABLE IF NOT EXISTS `cccam_lline` (
  `lline_id` int(11) NOT NULL AUTO_INCREMENT,
  `lline_hostname` varchar(30) NOT NULL,
  `lline_port` varchar(5) NOT NULL,
  `lline_username` varchar(20) NOT NULL,
  `lline_password` varchar(20) NOT NULL,
  `lline_caid` varchar(10) NOT NULL,
  `lline_ident` varchar(10) NOT NULL,
  `lline_hops` varchar(3) DEFAULT NULL,
  `lline_active` varchar(2) NOT NULL,
  PRIMARY KEY (`lline_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `cccam_lline`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_logevent`
--

CREATE TABLE IF NOT EXISTS `cccam_logevent` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_user` varchar(30) NOT NULL,
  `log_action` varchar(255) NOT NULL,
  `log_ip` varchar(50) NOT NULL,
  `log_agent` varchar(255) NOT NULL,
  `log_date` datetime NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `cccam_logevent`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_nline`
--

CREATE TABLE IF NOT EXISTS `cccam_nline` (
  `nline_id` int(11) NOT NULL AUTO_INCREMENT,
  `nline_hostname` varchar(30) NOT NULL,
  `nline_port` varchar(5) NOT NULL,
  `nline_username` varchar(20) NOT NULL,
  `nline_password` varchar(20) NOT NULL,
  `nline_des` varchar(100) NOT NULL,
  `nline_hops` varchar(5) NOT NULL,
  `nline_stealth` varchar(5) DEFAULT NULL,
  `nline_active` varchar(2) NOT NULL,
  PRIMARY KEY (`nline_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `cccam_nline`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_option`
--

CREATE TABLE IF NOT EXISTS `cccam_option` (
  `opt_id` int(11) NOT NULL AUTO_INCREMENT,
  `opt_enable_auto_cccamcfg` varchar(50) NOT NULL,
  PRIMARY KEY (`opt_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `cccam_option`
--

INSERT INTO `cccam_option` (`opt_id`, `opt_enable_auto_cccamcfg`) VALUES
(1, '0');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_paypal`
--

CREATE TABLE IF NOT EXISTS `cccam_paypal` (
  `paypal_id` int(11) NOT NULL AUTO_INCREMENT,
  `paypal_email` varchar(255) NOT NULL,
  `paypal_cur_1` varchar(255) NOT NULL,
  `paypal_cur_3` varchar(255) NOT NULL,
  `paypal_cur_6` varchar(255) NOT NULL,
  `paypal_cur_12` varchar(255) NOT NULL,
  `paypal_cur_code` varchar(255) NOT NULL,
  PRIMARY KEY (`paypal_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `cccam_paypal`
--

INSERT INTO `cccam_paypal` (`paypal_id`, `paypal_email`, `paypal_cur_1`, `paypal_cur_3`, `paypal_cur_6`, `paypal_cur_12`, `paypal_cur_code`) VALUES
(1, 'your_email', '25', '75', '150', '300', 'CHF');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_quote`
--

CREATE TABLE IF NOT EXISTS `cccam_quote` (
  `quote_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `quote_value` varchar(50) NOT NULL,
  `quote_from` varchar(20) NOT NULL,
  `quote_to` varchar(20) NOT NULL,
  PRIMARY KEY (`quote_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `cccam_quote`
--

INSERT INTO `cccam_quote` (`quote_id`, `user_id`, `quote_value`, `quote_from`, `quote_to`) VALUES
(1, '1', '-1', '-1', '-1');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_reader`
--

CREATE TABLE IF NOT EXISTS `cccam_reader` (
  `reader_id` int(11) NOT NULL AUTO_INCREMENT,
  `reader_value_name` varchar(200) NOT NULL,
  `reader_value` varchar(200) NOT NULL,
  `reader_misc` varchar(50) NOT NULL,
  `reader_active` varchar(2) NOT NULL,
  PRIMARY KEY (`reader_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

--
-- Dump dei dati per la tabella `cccam_reader`
--

INSERT INTO `cccam_reader` (`reader_id`, `reader_value_name`, `reader_value`, `reader_misc`, `reader_active`) VALUES
(1, 'SERIAL READER :', '/dev/ttyUSB0', '', '0'),
(2, 'SERIAL READER :', '/dev/ttyUSB1', '', '0'),
(3, 'SERIAL READER :', '/dev/ttyUSB2', '', '0'),
(4, 'SERIAL READER :', '/dev/ttyUSB3', '', '0'),
(5, 'SERIAL READER :', '/dev/ttyUSB4', '', '0'),
(6, 'SERIAL READER :', '/dev/ttyUSB5', '', '0'),
(7, 'SERIAL READER :', '/dev/ttyUSB6', '', '0'),
(8, 'SERIAL READER :', '/dev/ttyUSB7', '', '0'),
(9, 'SERIAL READER :', '/dev/ttyUSB8', '', '0'),
(12, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB0', '10000', '0'),
(13, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB1', '10000', '0'),
(14, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB2', '10000', '0'),
(15, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB3', '10000', '0'),
(16, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB4', '10000', '0'),
(17, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB5', '10000', '0'),
(18, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB6', '10000', '0'),
(19, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB7', '10000', '0'),
(20, 'SMARTCARD WRITE DELAY :', '/dev/ttyUSB8', '10000', '0'),
(21, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB0', '3670000', '0'),
(22, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB1', '3670000', '0'),
(23, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB2', '3670000', '0'),
(24, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB3', '3670000', '0'),
(25, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB4', '3670000', '0'),
(26, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB5', '3670000', '0'),
(27, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB6', '3670000', '0'),
(28, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB7', '3670000', '0'),
(29, 'SMARTCARD CLOCK FREQUENCY :', '/dev/ttyUSB8', '3670000', '0');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_rline`
--

CREATE TABLE IF NOT EXISTS `cccam_rline` (
  `rline_id` int(11) NOT NULL AUTO_INCREMENT,
  `rline_hostname` varchar(30) DEFAULT NULL,
  `rline_port` varchar(5) DEFAULT NULL,
  `rline_caid` varchar(10) DEFAULT NULL,
  `rline_ident` varchar(10) DEFAULT NULL,
  `rline_hops` varchar(3) DEFAULT NULL,
  `rline_active` varchar(2) NOT NULL,
  PRIMARY KEY (`rline_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `cccam_rline`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_servers`
--

CREATE TABLE IF NOT EXISTS `cccam_servers` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `fline_id` varchar(11) NOT NULL,
  `server_list_id` varchar(11) NOT NULL,
  `server_host` varchar(50) NOT NULL,
  `server_port` varchar(10) NOT NULL,
  `server_wantemu` varchar(10) NOT NULL,
  `server_uphops` varchar(10) NOT NULL,
  PRIMARY KEY (`server_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `cccam_servers`
--

INSERT INTO `cccam_servers` (`server_id`, `fline_id`, `server_list_id`, `server_host`, `server_port`, `server_wantemu`, `server_uphops`) VALUES
(1, '1', '9', 'example.no-ip.info', '12000', 'no', '{ 0:0:2 }');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_server_list`
--

CREATE TABLE IF NOT EXISTS `cccam_server_list` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_host` varchar(100) NOT NULL,
  `server_port` varchar(10) NOT NULL,
  `server_wantemu` varchar(20) NOT NULL,
  `server_uphops` varchar(20) NOT NULL,
  PRIMARY KEY (`server_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dump dei dati per la tabella `cccam_server_list`
--

INSERT INTO `cccam_server_list` (`server_id`, `server_host`, `server_port`, `server_wantemu`, `server_uphops`) VALUES
(9, 'example.no-ip.info', '12000', 'no', '{ 0:0:2 }');

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_stealth`
--

CREATE TABLE IF NOT EXISTS `cccam_stealth` (
  `stealth_id` int(11) NOT NULL AUTO_INCREMENT,
  `stealth_name` varchar(50) NOT NULL,
  `stealth_value` int(20) NOT NULL,
  PRIMARY KEY (`stealth_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dump dei dati per la tabella `cccam_stealth`
--

INSERT INTO `cccam_stealth` (`stealth_id`, `stealth_name`, `stealth_value`) VALUES
(1, 'Disable', 0),
(2, 'mgcamd new', 1),
(3, 'mgcamd old', 2),
(4, 'evocamd', 3),
(5, 'generic', 4);

-- --------------------------------------------------------

--
-- Struttura della tabella `cccam_user`
--

CREATE TABLE IF NOT EXISTS `cccam_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(20) NOT NULL,
  `user_surname` varchar(20) DEFAULT NULL,
  `user_street` varchar(40) DEFAULT NULL,
  `user_number` varchar(10) DEFAULT NULL,
  `user_zip_code` varchar(10) DEFAULT NULL,
  `user_city` varchar(50) DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `user_email` varchar(50) DEFAULT NULL,
  `user_level` varchar(5) DEFAULT NULL,
  `user_username` varchar(10) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `user_note` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `cccam_username` (`user_username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `cccam_user`
--

INSERT INTO `cccam_user` (`user_id`, `user_name`, `user_surname`, `user_street`, `user_number`, `user_zip_code`, `user_city`, `user_phone`, `user_email`, `user_level`, `user_username`, `user_password`, `user_note`) VALUES
(1, 'Administrator', '-', '-', '-', '-', '-', '-', '-', 'admin', 'admin', 'admin', 'Administrator Account');

-- --------------------------------------------------------

--
-- Struttura della tabella `list_emusemm`
--

CREATE TABLE IF NOT EXISTS `list_emusemm` (
  `muem_id` int(11) NOT NULL AUTO_INCREMENT,
  `muem_value` varchar(2) NOT NULL,
  PRIMARY KEY (`muem_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `list_emusemm`
--

INSERT INTO `list_emusemm` (`muem_id`, `muem_value`) VALUES
(1, '0'),
(2, '1');

-- --------------------------------------------------------

--
-- Struttura della tabella `list_reshare`
--

CREATE TABLE IF NOT EXISTS `list_reshare` (
  `reshare_id` int(11) NOT NULL AUTO_INCREMENT,
  `reshare_value` varchar(5) NOT NULL,
  PRIMARY KEY (`reshare_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dump dei dati per la tabella `list_reshare`
--

INSERT INTO `list_reshare` (`reshare_id`, `reshare_value`) VALUES
(1, '0:0:0'),
(2, '0:0:1'),
(3, '0:0:2'),
(4, '0:0:3'),
(5, '0:0:4'),
(6, '0:0:5');

-- --------------------------------------------------------

--
-- Struttura della tabella `list_time`
--

CREATE TABLE IF NOT EXISTS `list_time` (
  `time_id` int(11) NOT NULL AUTO_INCREMENT,
  `time_value` varchar(5) NOT NULL,
  PRIMARY KEY (`time_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Dump dei dati per la tabella `list_time`
--

INSERT INTO `list_time` (`time_id`, `time_value`) VALUES
(1, '01:00'),
(2, '02:00'),
(3, '03:00'),
(4, '04:00'),
(5, '05:00'),
(6, '06:00'),
(7, '07:00'),
(8, '08:00'),
(9, '09:00'),
(10, '10:00'),
(11, '11:00'),
(12, '12:00'),
(13, '13:00'),
(14, '14:00'),
(15, '15:00'),
(16, '16:00'),
(17, '17:00'),
(18, '18:00'),
(19, '19:00'),
(20, '20:00'),
(21, '21:00'),
(22, '22:00'),
(23, '23:00'),
(24, '24:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `list_ulevel`
--

CREATE TABLE IF NOT EXISTS `list_ulevel` (
  `ulev_id` int(11) NOT NULL AUTO_INCREMENT,
  `ulev_value` varchar(10) NOT NULL,
  PRIMARY KEY (`ulev_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `list_ulevel`
--

INSERT INTO `list_ulevel` (`ulev_id`, `ulev_value`) VALUES
(1, 'user'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Struttura della tabella `list_uphops`
--

CREATE TABLE IF NOT EXISTS `list_uphops` (
  `uphops_id` int(11) NOT NULL AUTO_INCREMENT,
  `uphops_value` varchar(2) NOT NULL,
  PRIMARY KEY (`uphops_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dump dei dati per la tabella `list_uphops`
--

INSERT INTO `list_uphops` (`uphops_id`, `uphops_value`) VALUES
(1, '0'),
(2, '1'),
(3, '2'),
(4, '3'),
(5, '4'),
(6, '5');

-- --------------------------------------------------------

--
-- Struttura della tabella `list_wantemus`
--

CREATE TABLE IF NOT EXISTS `list_wantemus` (
  `wantemus_id` int(11) NOT NULL AUTO_INCREMENT,
  `wantemus_value` varchar(3) NOT NULL,
  PRIMARY KEY (`wantemus_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `list_wantemus`
--

INSERT INTO `list_wantemus` (`wantemus_id`, `wantemus_value`) VALUES
(1, 'no'),
(2, 'yes');
