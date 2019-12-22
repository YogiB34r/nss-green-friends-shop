CREATE TABLE `nss_devel`.`wp_nss_external_banners_widget` (
  `itemId` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(45) NOT NULL ,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `salePrice` INT UNSIGNED NULL,
  `regularPrice` INT UNSIGNED NULL,
  `categoryUrl` VARCHAR(128) NULL,
  `itemUrl` VARCHAR(128) NOT NULL,
  `imageSrc` VARCHAR(128) NOT NULL,
  `partnerId` INT UNSIGNED NULL,
  PRIMARY KEY (`itemId`),
  UNIQUE INDEX `itemId_UNIQUE` (`itemId` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;
