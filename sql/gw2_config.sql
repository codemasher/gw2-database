CREATE TABLE IF NOT EXISTS `gw2_config` (
  `variable` varchar(63) COLLATE utf8mb4_bin NOT NULL,
  `value` text COLLATE utf8mb4_bin NOT NULL,
  UNIQUE KEY `variable` (`variable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `gw2_config` (`variable`, `value`) VALUES
('cookie_duration', '48'),
('cookie_prefix', 'gw2db'),
('copyright_info', '&copy; Smiley&trade; 2014'),
('default_lang', 'en'),
('google_analytics', 'false'),
('google_analytics_code', ''),
('google_webmaster_tools', 'true'),
('google_webmaster_tools_code', ''),
('languages', 'de,en,es,fr,zh'),
('minify_html', 'false'),
('output_gzip', 'false');
