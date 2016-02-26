CREATE TABLE IF NOT EXISTS `gw2_attributes` (
  `shortname` VARCHAR(32)         NOT NULL,
  `primary`   TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `name_de`   TINYTEXT            NOT NULL,
  `name_en`   TINYTEXT            NOT NULL,
  `name_es`   TINYTEXT            NOT NULL,
  `name_fr`   TINYTEXT            NOT NULL,
  `name_zh`   TINYTEXT            NOT NULL,
  PRIMARY KEY (`shortname`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;

INSERT INTO `gw2_attributes` (`shortname`, `primary`, `name_de`, `name_en`, `name_es`, `name_fr`, `name_zh`) VALUES
  ('BoonDuration', 0, 'Segensdauer', 'Boon Duration', 'Duración de la Bendición', 'Durée d''avantage', ''),
  ('ConditionDamage', 0, 'Zustandsschaden', 'Condition Damage', 'Daño de Condición', 'Dégâts par altération', ''),
  ('ConditionDuration', 0, 'Zustandsdauer', 'Condition Duration', 'Duración de la Condición', 'Durée d''altération', ''),
  ('Ferocity', 0, 'Wildheit', 'Ferocity', 'Ferocidad', 'Férocité', ''),
  ('Healing', 0, 'Heilkraft', 'Healing Power', 'Poder de Curación', 'Guérison', ''),
  ('Power', 1, 'Kraft', 'Power', 'Potencia', 'Puissance', ''),
  ('Precision', 1, 'Präzision', 'Precision', 'Precisión', 'Précision', ''),
  ('Toughness', 1, 'Zähigkeit', 'Toughness', 'Fortaleza', 'Robustesse', ''),
  ('Vitality', 1, 'Vitalität', 'Vitality', 'Vitalidad', 'Vitalité', '');
