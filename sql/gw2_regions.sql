CREATE TABLE IF NOT EXISTS `gw2_regions` (
  `id`           INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
  `region_id`    SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `continent_id` TINYINT(2) UNSIGNED  NOT NULL DEFAULT '0',
  `floor_id`     SMALLINT(3)          NOT NULL DEFAULT '0',
  `label_coord`  TINYTEXT             NOT NULL,
  `maps`         TEXT                 NOT NULL,
  `name_de`      TINYTEXT             NOT NULL,
  `name_en`      TINYTEXT             NOT NULL,
  `name_es`      TINYTEXT             NOT NULL,
  `name_fr`      TINYTEXT             NOT NULL,
  `name_zh`      TINYTEXT             NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;
