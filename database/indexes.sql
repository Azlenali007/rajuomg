-- ============================================================================
-- Database Indexes for OTP Platform Optimization
-- ============================================================================

-- Users indexing
CREATE INDEX `idx_users_role_status` ON `users` (`role`, `status`);
CREATE INDEX `idx_users_created_at` ON `users` (`created_at`);

-- Wallets & Transactions indexing
CREATE INDEX `idx_wallets_balance` ON `wallets` (`balance`);
CREATE INDEX `idx_wallet_tx_user_created` ON `wallet_transactions` (`user_id`, `created_at` DESC);
CREATE INDEX `idx_wallet_tx_ref` ON `wallet_transactions` (`reference_type`, `reference_id`);

-- Orders indexing for high-throughput status polling and expiration
CREATE INDEX `idx_orders_status_expires` ON `orders` (`status`, `expires_at`);
CREATE INDEX `idx_orders_user_created` ON `orders` (`user_id`, `created_at` DESC);
CREATE INDEX `idx_orders_activation_prov` ON `orders` (`provider_activation_id`, `provider_id`);
CREATE INDEX `idx_orders_phone` ON `orders` (`phone_number`);

-- Services and Provider mapping indexing
CREATE INDEX `idx_services_active_cat` ON `services` (`is_active`, `category`);
CREATE INDEX `idx_prov_srv_stock` ON `provider_services` (`stock_status`, `provider_cost`);

-- Payments indexing
CREATE INDEX `idx_payments_user_status` ON `payments` (`user_id`, `status`);
CREATE INDEX `idx_payments_ref` ON `payments` (`payment_reference`);
CREATE INDEX `idx_manual_pay_status` ON `manual_payment_requests` (`status`, `created_at` DESC);

-- Audit and Logging
CREATE INDEX `idx_audit_admin_action` ON `admin_audit_logs` (`admin_id`, `action`, `created_at` DESC);
CREATE INDEX `idx_prov_logs_prov_time` ON `provider_logs` (`provider_id`, `created_at` DESC);
