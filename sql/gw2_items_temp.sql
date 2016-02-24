CREATE TABLE IF NOT EXISTS `gw2_items_temp` (
  `id`            INT(10) UNSIGNED    NOT NULL,
  `blacklist`     TINYINT(1)          NOT NULL DEFAULT '0',
  `name_de`       VARCHAR(255)        NOT NULL,
  `name_en`       VARCHAR(255)        NOT NULL,
  `name_es`       VARCHAR(255)        NOT NULL,
  `name_fr`       VARCHAR(255)        NOT NULL,
  `name_zh`       VARCHAR(255)        NOT NULL,
  `data_de`       TEXT                NOT NULL,
  `data_en`       TEXT                NOT NULL,
  `data_es`       TEXT                NOT NULL,
  `data_fr`       TEXT                NOT NULL,
  `data_zh`       TEXT                NOT NULL,
  `updated`       TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `create_time`   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `response_time` TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;