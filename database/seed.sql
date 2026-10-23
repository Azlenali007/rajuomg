-- ============================================================================
-- OTP Platform Seed Data
-- ============================================================================

-- Default Provider: OTP Doctor API
INSERT INTO `providers` (`id`, `name`, `code`, `api_url`, `api_key`, `is_active`, `balance`, `currency`) VALUES
(1, 'OTP Doctor Global API', 'otpdoctor', 'https://www.otpdoctor.in/stubs/handler_api.php', 'YOUR_OTPDOCTOR_API_KEY_HERE', 1, 250.0000, 'RUB')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `api_url` = VALUES(`api_url`);

-- Default Countries
INSERT INTO `countries` (`id`, `provider_id`, `country_code`, `country_name`, `dial_code`, `is_active`) VALUES
(1, 1, 'in', 'India', '+91', 1),
(2, 1, 'us', 'United States', '+1', 1),
(3, 1, 'gb', 'United Kingdom', '+44', 1),
(4, 1, 'id', 'Indonesia', '+62', 1),
(5, 1, 'ru', 'Russia', '+7', 1),
(6, 1, 'br', 'Brazil', '+55', 1),
(7, 1, 'ph', 'Philippines', '+63', 1),
(8, 1, 'vn', 'Vietnam', '+84', 1)
ON DUPLICATE KEY UPDATE `country_name` = VALUES(`country_name`), `dial_code` = VALUES(`dial_code`);

-- Default Verification Services
INSERT INTO `services` (`id`, `service_code`, `service_name`, `category`, `icon`, `custom_price`, `profit_margin_percent`, `is_active`) VALUES
(1, 'wa', 'WhatsApp', 'messaging', 'MessageSquare', 0.8500, 25.00, 1),
(2, 'tg', 'Telegram', 'messaging', 'Send', 0.6500, 25.00, 1),
(3, 'go', 'Google / Gmail / YouTube', 'technology', 'Mail', 0.9000, 20.00, 1),
(4, 'ig', 'Instagram', 'social', 'Camera', 0.7000, 20.00, 1),
(5, 'ds', 'Discord', 'gaming', 'Gamepad2', 0.5500, 20.00, 1),
(6, 'oa', 'OpenAI / ChatGPT', 'ai', 'Cpu', 1.2000, 30.00, 1),
(7, 'tw', 'Twitter / X', 'social', 'Share2', 0.7500, 20.00, 1),
(8, 'ms', 'Microsoft / Outlook', 'technology', 'Briefcase', 0.8000, 20.00, 1),
(9, 'tt', 'TikTok', 'video', 'Video', 0.6000, 20.00, 1),
(10, 'am', 'Amazon', 'shopping', 'ShoppingBag', 0.9500, 25.00, 1),
(11, 'ub', 'Uber', 'transport', 'Navigation', 0.7000, 20.00, 1),
(12, 'st', 'Steam', 'gaming', 'Flame', 0.5000, 20.00, 1)
ON DUPLICATE KEY UPDATE `service_name` = VALUES(`service_name`), `custom_price` = VALUES(`custom_price`);

-- Default Provider Services Mapping
INSERT INTO `provider_services` (`provider_id`, `service_id`, `country_id`, `provider_service_code`, `provider_cost`, `stock_status`, `stock_count`, `server_id`, `server_name`) VALUES
(1, 1, 1, 'wa', 0.5000, 'AVAILABLE', 840, 'srv-1', 'Server Alpha'),
(1, 2, 1, 'tg', 0.3500, 'AVAILABLE', 1200, 'srv-1', 'Server Alpha'),
(1, 3, 1, 'go', 0.6000, 'AVAILABLE', 450, 'srv-2', 'Server Beta'),
(1, 4, 1, 'ig', 0.4500, 'AVAILABLE', 920, 'srv-1', 'Server Alpha'),
(1, 5, 1, 'ds', 0.3000, 'AVAILABLE', 610, 'srv-2', 'Server Beta'),
(1, 6, 1, 'oa', 0.7500, 'AVAILABLE', 350, 'srv-1', 'Server Alpha'),
(1, 1, 2, 'wa', 0.6000, 'AVAILABLE', 410, 'srv-3', 'US Node 1'),
(1, 2, 2, 'tg', 0.4000, 'AVAILABLE', 750, 'srv-3', 'US Node 1'),
(1, 3, 2, 'go', 0.7000, 'AVAILABLE', 300, 'srv-3', 'US Node 1'),
(1, 6, 2, 'oa', 0.8500, 'AVAILABLE', 220, 'srv-3', 'US Node 1')
ON DUPLICATE KEY UPDATE `provider_cost` = VALUES(`provider_cost`), `stock_status` = VALUES(`stock_status`);

-- Payment Gateways
INSERT INTO `payment_gateways` (`id`, `code`, `name`, `is_enabled`, `config_json`) VALUES
(1, 'razorpay', 'Razorpay Checkout', 1, '{"key_id": "rzp_test_placeholder_key", "key_secret": "rzp_test_placeholder_secret", "webhook_secret": "whsec_placeholder", "min_amount": 5.0, "max_amount": 500.0}'),
(2, 'manual_qr', 'Manual UPI / Crypto QR Payment', 1, '{"upi_id": "pay@otpdoctor", "qr_image_url": "/assets/images/qr_placeholder.png", "instructions": "Scan QR code or pay to UPI ID. Enter UTR/Reference ID below and upload screenshot.", "min_amount": 2.0, "max_amount": 1000.0}')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Core System Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'OTP Doctor Verification'),
('support_email', 'support@otpdoctor.in'),
('support_telegram', '@otpdoctor_support'),
('currency', 'USD'),
('currency_symbol', '$'),
('min_deposit', '5.00'),
('max_deposit', '1000.00'),
('order_expiry_minutes', '15'),
('maintenance_mode', '0'),
('terms_content', 'All phone numbers provided are strictly for legitimate verification purposes only. Unused numbers where no OTP was received within 15 minutes are 100% refunded to your platform wallet balance.'),
('privacy_content', 'We respect user privacy and never retain sensitive personal logs beyond financial transaction ledgers and active session lifespans.')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
