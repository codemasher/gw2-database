CREATE TABLE `gw2_colors` (
  `id`          INT(10) UNSIGNED    NOT NULL,
  `item_id`     INT(10) UNSIGNED    NOT NULL,
  `hue`         ENUM ('Blue', 'Brown', 'Gray', 'Green', 'Orange', 'Purple', 'Red', 'Yellow') DEFAULT NULL,
  `material`    ENUM ('Leather', 'Metal', 'Vibrant')                                         DEFAULT NULL,
  `rarity`      ENUM ('Common', 'Rare', 'Starter', 'Uncommon')                               DEFAULT NULL,
  `name_de`     TINYTEXT            NOT NULL,
  `name_en`     TINYTEXT            NOT NULL,
  `name_es`     TINYTEXT            NOT NULL,
  `name_fr`     TINYTEXT            NOT NULL,
  `name_zh`     TEXT                NOT NULL,
  `data_de`     TEXT                NOT NULL,
  `data_en`     TEXT                NOT NULL,
  `data_es`     TEXT                NOT NULL,
  `data_fr`     TEXT                NOT NULL,
  `data_zh`     TEXT                NOT NULL,
  `updated`     TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `update_time` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_added`  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;

