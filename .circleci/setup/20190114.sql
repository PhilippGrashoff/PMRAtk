SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `SecondaryBaseModel`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SecondaryBaseModel` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  `value` TEXT NULL,
  `model_class` VARCHAR(255) NULL,
  `model_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `email` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  `value` TEXT NULL,
  `model_class` VARCHAR(255) NULL,
  `model_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `audit` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  `created_by` INT NULL,
  `created_by_name` VARCHAR(255) NULL,
  `value` TEXT NULL,
  `data`  TEXT NULL,
  `model_class` VARCHAR(255) NULL,
  `model_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `address` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  `value` TEXT NULL,
  `model_class` VARCHAR(255) NULL,
  `model_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `phone` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  `value` TEXT NULL,
  `model_class` VARCHAR(255) NULL,
  `model_id` INT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


-- -----------------------------------------------------
-- Table `BaseModelA`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `BaseModelA` (
  `name` VARCHAR(255) NULL,
  `id` INT NOT NULL AUTO_INCREMENT,
  `created_date` DATETIME NULL,
  `last_updated` DATETIME NULL,
  `time` TIME NULL,
  `date` DATE NULL,
  `dd_test` INT NULL,
  `dd_test_2` VARCHAR(255) NULL,
  `firstname` VARCHAR(255) NULL,
  `lastname` VARCHAR(255) NULL,
  `BaseModelB_id` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


-- -----------------------------------------------------
-- Table `BaseModelB`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `BaseModelB` (
  `name` VARCHAR(255) NULL,
  `id` INT NOT NULL AUTO_INCREMENT,
  `created_date` DATETIME NULL,
  `last_updated` DATETIME NULL,
  `time_test` TIME NULL,
  `date_test` DATE NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


-- -----------------------------------------------------
-- Table `AToB`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AToB` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `BaseModelA_id` INT NOT NULL,
  `BaseModelB_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `APlusB_UNIQUE` (`BaseModelA_id`, `BaseModelB_id`),
  INDEX `fk_AToB_BaseModelA_idx` (`BaseModelA_id` ASC),
  INDEX `fk_AToB_BaseModelB1_idx` (`BaseModelB_id` ASC),
  CONSTRAINT `fk_AToB_BaseModelA`
    FOREIGN KEY (`BaseModelA_id`)
    REFERENCES `BaseModelA` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_AToB_BaseModelB1`
    FOREIGN KEY (`BaseModelB_id`)
    REFERENCES `BaseModelB` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


-- -----------------------------------------------------
-- Table `Token`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `token` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  `value` TEXT NULL,
  `model_class` VARCHAR(255) NULL,
  `model_id` INT NULL,
  `expires` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `User` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  `name` VARCHAR(255) NULL,
  `username` VARCHAR(255) NULL,
  `password` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `base_email` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `subject` TEXT NULL,
  `message` TEXT NULL,
  `attachments` JSON NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `email_recipient` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `base_email_id` INT NULL,
  `model_id` INT NULL,
  `model_class` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `firstname` VARCHAR(255) NULL,
  `lastname` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;


CREATE TABLE IF NOT EXISTS `file` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `value` TEXT NULL,
  `model_id` INT NULL,
  `model_class` VARCHAR(255) NULL,
  `path` VARCHAR(255) NULL,
  `type` VARCHAR(255) NULL,
  `filetype` VARCHAR(255) NULL,
  `auto_generated` TINYINT NULL,
  `last_updated` DATETIME NULL,
  `created_date` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_german1_ci;

