CREATE TABLE IF NOT EXISTS `gw2_maps` (
  `id`             INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `map_id`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `continent_id`   TINYINT(2) UNSIGNED NOT NULL DEFAULT '0',
  `floor_id`       SMALLINT(3)         NOT NULL DEFAULT '0',
  `region_id`      SMALLINT(3)         NOT NULL DEFAULT '0',
  `default_floor`  SMALLINT(3)         NOT NULL DEFAULT '0',
  `map_rect`       TINYTEXT            NOT NULL,
  `continent_rect` TINYTEXT            NOT NULL,
  `min_level`      TINYINT(2) UNSIGNED NOT NULL DEFAULT '0',
  `max_level`      TINYINT(2) UNSIGNED NOT NULL DEFAULT '0',
  `name_de`        TINYTEXT            NOT NULL,
  `name_en`        TINYTEXT            NOT NULL,
  `name_es`        TINYTEXT            NOT NULL,
  `name_fr`        TINYTEXT            NOT NULL,
  `name_zh`        TINYTEXT            NOT NULL,
  `data_de`        TEXT                NOT NULL,
  `data_en`        TEXT                NOT NULL,
  `data_es`        TEXT                NOT NULL,
  `data_fr`        TEXT                NOT NULL,
  `data_zh`        TEXT                NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;

