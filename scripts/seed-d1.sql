-- 1. ROLES
INSERT OR IGNORE INTO roles (id, name, display_name, description, created_at) VALUES
(1, 'super_admin', 'Super Administrator', 'Complete system control and administration', unixepoch()),
(2, 'admin', 'Administrator', 'Operations and management control', unixepoch()),
(3, 'manager', 'Sales Manager', 'Team management and analytics', unixepoch()),
(4, 'member', 'Associate Member', 'Independent associate / distributor', unixepoch());

-- 2. INITIAL ADMIN USER (password: 'password')
INSERT OR IGNORE INTO users (id, name, email, phone, designation, password, status, created_at, updated_at) VALUES
(1, 'Admin', 'admin@sbl.test', '+8801700000000', 'Chief Operating Officer', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', unixepoch(), unixepoch());

-- 3. ASSIGN ADMIN ROLE
INSERT OR IGNORE INTO user_roles (user_id, role_id) VALUES
(1, 1);

-- 4. LEAD SOURCES
INSERT OR IGNORE INTO lead_sources (id, name, description, is_active, sort_order, created_at) VALUES
(1, 'Personal Contact / Warm Market', 'Friends, family, acquaintances', 1, 1, unixepoch()),
(2, 'Facebook Campaign', 'Paid social ads and sponsored leads', 1, 2, unixepoch()),
(3, 'WhatsApp Referral', 'Direct referral via WhatsApp broadcast/group', 1, 3, unixepoch()),
(4, 'Phone Call / Direct Outreach', 'Direct outreach or phone inquiry', 1, 4, unixepoch()),
(5, 'Physical Seminar / Presentation', 'In-person presentation or event attendee', 1, 5, unixepoch()),
(6, 'Online Zoom Meeting', 'Webinar or virtual meeting participant', 1, 6, unixepoch()),
(7, 'Website Inquiry', 'Organic website contact or form submission', 1, 7, unixepoch()),
(8, 'Existing Associate Referral', 'Referred by active network associate', 1, 8, unixepoch());

-- 5. INVESTMENT PACKAGES
INSERT OR IGNORE INTO investment_plans (id, name, min_amount, max_amount, return_rate, duration_days, description, is_active, created_at) VALUES
(1, 'Basic Starter', 10000, 25000, 8.50, 180, 'Entry tier package with steady baseline yields', 1, unixepoch()),
(2, 'Growth Builder', 25000, 50000, 10.00, 270, 'Mid-tier package for active team builders', 1, unixepoch()),
(3, 'Executive Pro', 50000, 100000, 12.00, 365, 'High yield package for serious associates', 1, unixepoch()),
(4, 'Leadership Premium', 100000, 500000, 15.00, 365, 'Elite package with maximum leadership pool bonuses', 1, unixepoch());

-- 6. ABBREVIATIONS & GLOSSARY
INSERT OR IGNORE INTO abbreviations (id, abbreviation, term, definition, category, sort_order, created_at) VALUES
(1, 'BDT', 'Bangladeshi Taka', 'Official currency of Bangladesh (standard calculation 1 USD = 120 BDT)', 'Financial', 1, unixepoch()),
(2, 'USD', 'United States Dollar', 'International base currency for global valuation', 'Financial', 2, unixepoch()),
(3, 'TPIN', 'Transaction PIN', 'Confidential security code for transactions and withdrawals', 'Security', 3, unixepoch()),
(4, 'PV', 'Point Volume', 'Measurement points associated with product and investment sales', 'Network', 4, unixepoch()),
(5, 'BV', 'Business Volume', 'Commissionable volume calculation metric', 'Network', 5, unixepoch()),
(6, 'KYC', 'Know Your Customer', 'Identity and address verification required for associates', 'Compliance', 6, unixepoch());

-- 7. ECOSYSTEM LINKS
INSERT OR IGNORE INTO ecosystem_links (id, title, url, category, description, sort_order, is_active, created_at) VALUES
(1, 'SBL Official Portal', 'https://sbl.com.bd', 'Official', 'Corporate website and official portal', 1, 1, unixepoch()),
(2, 'Associate Dashboard', 'https://sbltool.creationtech.info', 'Portal', 'Field associate tools and growth dashboard', 2, 1, unixepoch()),
(3, 'Customer Support Desk', 'https://wa.me/8801700000000', 'Support', 'Direct WhatsApp support channel', 3, 1, unixepoch());

-- 8. SBL CONTACTS
INSERT OR IGNORE INTO sbl_contacts (id, name, designation, department, phone, whatsapp, email, sort_order, created_at) VALUES
(1, 'Central Helpdesk', 'Customer Support', 'Operations', '+8801700000000', '8801700000000', 'support@sbl.test', 1, unixepoch()),
(2, 'Accounts & Finance', 'Finance Officer', 'Finance', '+8801700000001', '8801700000001', 'accounts@sbl.test', 2, unixepoch()),
(3, 'Leadership Support', 'Network Coordinator', 'Field Network', '+8801700000002', '8801700000002', 'network@sbl.test', 3, unixepoch());
