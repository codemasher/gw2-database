CREATE TABLE IF NOT EXISTS `gw2_map_floors` (
  `id`           INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
  `continent_id` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0',
  `floor_id`     SMALLINT(3)         NOT NULL DEFAULT '0',
  `regions`      TEXT                NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;
