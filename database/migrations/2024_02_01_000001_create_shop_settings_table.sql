CREATE TABLE IF NOT EXISTS `shop_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(100) DEFAULT 'My Perfume Shop',
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `tiktok_url` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `currency_symbol` varchar(10) DEFAULT '$',
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings if table is empty
INSERT INTO `shop_settings` (`id`, `site_name`) 
SELECT 1, 'My Perfume Shop'
WHERE NOT EXISTS (SELECT 1 FROM `shop_settings` WHERE `id` = 1);
