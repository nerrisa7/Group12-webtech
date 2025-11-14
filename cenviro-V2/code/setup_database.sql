-- Database Setup for Cenviro ESG Reports
-- Database: sustainedge

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS sustainedge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sustainedge;

-- Table: organizations
CREATE TABLE IF NOT EXISTS organizations (
    org_id INT AUTO_INCREMENT PRIMARY KEY,
    org_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    org_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: esg_kpis (ESG KPI definitions)
CREATE TABLE IF NOT EXISTS esg_kpis (
    kpi_id INT AUTO_INCREMENT PRIMARY KEY,
    kpi_name VARCHAR(255) NOT NULL,
    category ENUM('environmental', 'social', 'governance') NOT NULL,
    description TEXT,
    unit VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: esg_data (ESG data entries)
CREATE TABLE IF NOT EXISTS esg_data (
    data_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kpi_id INT NOT NULL,
    category ENUM('environmental', 'social', 'governance') NOT NULL,
    value DECIMAL(15, 2),
    period_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (kpi_id) REFERENCES esg_kpis(kpi_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_kpi_period (user_id, kpi_id, period_date),
    INDEX idx_user_category (user_id, category),
    INDEX idx_period (period_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reports (Generated reports)
CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    esg_score DECIMAL(5, 2),
    generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'complete', 'failed') DEFAULT 'complete',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, generated_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample organization
INSERT INTO organizations (org_name) VALUES 
('Micnerris Ltd'),
('GreenTech Solutions');

-- Insert sample user
INSERT INTO users (name, email, password, org_id) VALUES 
('Nerrisa Abunu', 'nerrisa@micnerris.com', MD5('password123'), 1),
('Demo User', 'demo@cenviro.com', MD5('Demo@123'), 2);

-- Insert sample ESG KPIs
INSERT INTO esg_kpis (kpi_name, category, description, unit) VALUES
-- Environmental KPIs
('Energy Consumption', 'environmental', 'Total energy consumption', 'kWh'),
('Carbon Emissions', 'environmental', 'Total CO2 emissions', 'tons CO2'),
('Water Usage', 'environmental', 'Total water consumption', 'liters'),
('Waste Generated', 'environmental', 'Total waste generated', 'kg'),
('Recycling Rate', 'environmental', 'Percentage of waste recycled', '%'),
('Renewable Energy', 'environmental', 'Percentage of renewable energy used', '%'),
('Paper Usage', 'environmental', 'Total paper consumption', 'kg'),
-- Social KPIs
('Employee Satisfaction', 'social', 'Employee satisfaction score', 'score'),
('Training Hours', 'social', 'Total training hours per employee', 'hours'),
('Diversity Index', 'social', 'Workforce diversity index', 'index'),
('Safety Incidents', 'social', 'Number of safety incidents', 'count'),
('Community Engagement', 'social', 'Community engagement activities', 'count'),
-- Governance KPIs
('Board Diversity', 'governance', 'Percentage of diverse board members', '%'),
('Ethics Training', 'governance', 'Employees trained on ethics', 'count'),
('Compliance Rate', 'governance', 'Regulatory compliance rate', '%'),
('Transparency Score', 'governance', 'Corporate transparency score', 'score');

-- Insert sample ESG data
INSERT INTO esg_data (user_id, kpi_id, category, value, period_date) VALUES
-- Environmental data for user 1
(1, 1, 'environmental', 50000, '2025-01-15'),
(1, 1, 'environmental', 48000, '2025-01-01'),
(1, 2, 'environmental', 125.5, '2025-01-15'),
(1, 2, 'environmental', 120.3, '2025-01-01'),
(1, 3, 'environmental', 25000, '2025-01-15'),
(1, 3, 'environmental', 24500, '2025-01-01'),
(1, 4, 'environmental', 1500, '2025-01-15'),
(1, 4, 'environmental', 1480, '2025-01-01'),
(1, 5, 'environmental', 75.5, '2025-01-15'),
(1, 5, 'environmental', 74.2, '2025-01-01'),
(1, 6, 'environmental', 45.0, '2025-01-15'),
(1, 6, 'environmental', 43.5, '2025-01-01'),
(1, 7, 'environmental', 850, '2025-01-15'),
(1, 7, 'environmental', 820, '2025-01-01'),
-- Social data for user 1
(1, 8, 'social', 4.2, '2025-01-15'),
(1, 8, 'social', 4.1, '2025-01-01'),
(1, 9, 'social', 40, '2025-01-15'),
(1, 9, 'social', 38, '2025-01-01'),
(1, 10, 'social', 0.65, '2025-01-15'),
(1, 10, 'social', 0.63, '2025-01-01'),
(1, 11, 'social', 2, '2025-01-15'),
(1, 11, 'social', 3, '2025-01-01'),
(1, 12, 'social', 5, '2025-01-15'),
(1, 12, 'social', 4, '2025-01-01'),
-- Governance data for user 1
(1, 13, 'governance', 55.0, '2025-01-15'),
(1, 13, 'governance', 53.0, '2025-01-01'),
(1, 14, 'governance', 95, '2025-01-15'),
(1, 14, 'governance', 92, '2025-01-01'),
(1, 15, 'governance', 98.5, '2025-01-15'),
(1, 15, 'governance', 97.8, '2025-01-01'),
(1, 16, 'governance', 85.5, '2025-01-15'),
(1, 16, 'governance', 84.2, '2025-01-01');

-- Insert sample reports
INSERT INTO reports (user_id, period_start, period_end, esg_score, generated_date, status) VALUES
(1, '2025-01-01', '2025-01-31', 72.5, '2025-01-31 14:30:00', 'complete'),
(1, '2024-12-01', '2024-12-31', 68.3, '2024-12-31 16:15:00', 'complete');

