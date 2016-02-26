CREATE TABLE IF NOT EXISTS `gw2_diff` (
  `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `db_id` INT(10) UNSIGNED NOT NULL,
  `type`  TINYTEXT         NOT NULL,
  `lang`  ENUM ('de', 'en', 'es', 'fr', 'zh') DEFAULT NULL,
  `date`  INT(10)          NOT NULL,
  `data`  TEXT             NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;
