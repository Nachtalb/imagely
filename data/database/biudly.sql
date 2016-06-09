--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` INT NOT NULL  AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(100) NOT NULL,
  `isAdmin` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id` ASC)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

--
-- Table structure for table `gallery`
--

CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `author` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(500) NOT NULL,
  `creationDate` VARCHAR(100) NOT NULL,
  `modifiedDate` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id` ASC)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

--
-- Table structure for table `image`
--

CREATE TABLE IF NOT EXISTS `image` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `galleryId` INT(11) NOT NULL,
  `imagePath` VARCHAR(100) NOT NULL,
  `thumbnailPath` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id` ASC)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `imageId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id` ASC)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`,`name`,`password`,`isAdmin`) VALUES
(1,'admin@buendig.net','$2y$10$qIq5qBz8Q/pCO9tgGy.I.ex6cS/.Gu0EH1XDlHx5mY11jRDJYHcAy',1),
(2,'user@buendig.net','$2y$10$qIq5qBz8Q/pCO9tgGy.I.ex6cS/.Gu0EH1XDlHx5mY11jRDJYHcAy',0);

--
-- Daten für Tabelle `gallery`
--

INSERT INTO `gallery` (`id`, `author`, `status`, `name`, `description`) VALUES
(1, 2, 1, 'Landscapes', 'They look verry good'),
(2, 2, 0, 'Selfies', 'Of course they are looking good');

--
-- Dumping data for table `image`
--

INSERT INTO `image` (`id`, `galleryId`, `thumbnailPath1`, `imagePath`) VALUES
(1, 1, '/data/media/gallery/image.jpg', '/data/media/gallery/image.jpg');

--
-- Daten für Tabelle `tag`
--

INSERT INTO `tag` (`id`, `imageId`, `name`) VALUES
(1, 1, 'food'),
(2, 1, 'cats');
