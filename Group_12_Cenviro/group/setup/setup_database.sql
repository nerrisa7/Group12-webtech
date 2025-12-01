
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

-- Table: company_settings (Company information - single row with id = 1)
CREATE TABLE IF NOT EXISTS company_settings (
    id INT PRIMARY KEY DEFAULT 1,
    company_name VARCHAR(255) NOT NULL DEFAULT '',
    ceo VARCHAR(255) DEFAULT '',
    company_description TEXT DEFAULT NULL,
    country VARCHAR(100) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    full_address TEXT DEFAULT NULL,
    time_zone VARCHAR(50) DEFAULT '',
    main_email VARCHAR(255) DEFAULT '',
    support_email VARCHAR(255) DEFAULT '',
    phone_number VARCHAR(50) DEFAULT '',
    website_url VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default company_settings row if it doesn't exist
INSERT IGNORE INTO company_settings (id, company_name) VALUES (1, 'My Company');

-- Insert ESG KPIs
INSERT IGNORE INTO esg_kpis (kpi_name, category, description, unit) VALUES
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
('Employee Turnover', 'social', 'Number of employees who left', 'count'),
('Total Employees', 'social', 'Total number of employees', 'count'),
-- Governance KPIs
('Board Diversity', 'governance', 'Percentage of diverse board members', '%'),
('Ethics Training', 'governance', 'Employees trained on ethics', 'count'),
('Compliance Rate', 'governance', 'Regulatory compliance rate', '%'),
('Transparency Score', 'governance', 'Corporate transparency score', 'score');
