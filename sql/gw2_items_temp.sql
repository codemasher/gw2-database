CREATE TABLE IF NOT EXISTS `gw2_items_temp` (
  `id`            INT(10) UNSIGNED         NOT NULL,
  `de`            TEXT COLLATE utf8mb4_bin NOT NULL,
  `en`            TEXT COLLATE utf8mb4_bin NOT NULL,
  `es`            TEXT COLLATE utf8mb4_bin NOT NULL,
  `fr`            TEXT COLLATE utf8mb4_bin NOT NULL,
  `zh`            TEXT COLLATE utf8mb4_bin NOT NULL,
  `updated`       TINYINT(1) UNSIGNED      NOT NULL DEFAULT '0',
  `response_time` TIMESTAMP                NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;
