CREATE TABLE `gw2_recipes` (
  `id`           INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `output_id`    INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `output_count` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `disciplines`  SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `rating`       SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `type`         TINYTEXT             NOT NULL,
  `from_item`    TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0',
  `ing_id_1`     INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `ing_count_1`  SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `ing_id_2`     INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `ing_count_2`  SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `ing_id_3`     INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `ing_count_3`  SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `ing_id_4`     INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `ing_count_4`  SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `data`         TEXT                 NOT NULL,
  `updated`      TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0',
  `update_time`  TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_added`   TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;
