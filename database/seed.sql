-- Users
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin', 'admin@example.com', '$2y$10$dummyhashdummyhashdummyhashdummyhashdummyhashdummyhash', 'admin'),
('Customer', 'customer@example.com', '$2y$10$dummyhashdummyhashdummyhashdummyhashdummyhashdummyhash', 'customer');

-- Products: buy_now
INSERT INTO products (title, description, type, price, is_active) VALUES
('Gaming Mouse', 'High DPI mouse', 'buy_now', 49.99, 1),
('Mechanical Keyboard', 'Blue switches', 'buy_now', 89.99, 1);

-- Products: auction
INSERT INTO products (title, description, type, starting_bid, auction_end_at, is_active) VALUES
('Vintage Camera', 'Old camera in good condition', 'auction', 25.00, DATE_ADD(NOW(), INTERVAL 3 DAY), 1),
('Collector Watch', 'Limited edition', 'auction', 100.00, DATE_ADD(NOW(), INTERVAL 5 DAY), 1);
