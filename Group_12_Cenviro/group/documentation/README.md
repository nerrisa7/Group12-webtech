

# ESG Dashboard for Small and Medium Enterprises

A web-based platform that enables Ghanaian SMEs to track, measure, and improve their Environmental, Social, and Governance (ESG) performance through automated KPI tracking and reporting.


## Project Description

This ESG Dashboard helps small and medium enterprises in Ghana monitor their sustainability performance across three key pillars: Environmental, Social, and Governance metrics. The platform allows companies to input ESG data, compare performance against industry benchmarks, and generate automated reports for continuous improvement.

### Key Features
- User registration and authentication for company personnel
- Manual data input for ESG KPIs across Environmental, Social, and Governance categories
- Automated ESG score calculation based on industry benchmarks
- Interactive dashboard with data visualization
- Downloadable performance reports
- Historical tracking of ESG metrics over time



## Technologies Used

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MySQL
- Version Control: Git/GitHub




## Installation & Setup

### Prerequisites
- Web browser (Chrome, Firefox, Safari, etc.)
- Internet connection
- XAMPP for local development

### Accessing the Application

The application is already deployed and accessible online:

1. Visit the live application
   - Open your browser and navigate to: http://169.239.251.102:341/~davis.amponsah/cenviro/index.php
   - Or use localhost
  

2. Register your company
   - Click "Register" on the homepage
   - Fill in your company details
   - Create your account

3. Start tracking ESG metrics
   - Log in with your credentials
   - Begin inputting your ESG data



### For Developers: Local Setup

If you want to run the project locally or contribute:

1. Clone the repository
```bash
   git clone https://github.com/nerrisa7/Group12-webtech.git
   cd Group12-webtech
```

2. Set up local database
   - The project uses a shared database on the school server
   - For local testing, create a MySQL database named `esg_dashboard`
   - Import the SQL file located in `/setup/database.sql`

3. Configure database connection (if running locally)
   - Open `/code/config/db_config.php`
   - Update with your local database credentials
   - Note: Production credentials are not included in the public repository for security

4. Run on local server
   - Place project files in your web server directory (e.g., `htdocs` for XAMPP)
   - Start Apache and MySQL services
   - Navigate to: `http://localhost/Group12-webtech`



### Database Information

- The database is hosted on the school's server
- Database credentials are managed securely and not included in the public repository
- For access to the production database, contact the development team



### Repository

- GitHub: https://github.com/nerrisa7/Group12-webtech
- The repository is public and open for viewing

## Usage

### 1. Registration
- Navigate to the registration page
- Enter company details: company name, industry sector, personnel name, email, and password
- Submit to create an account

### 2. Login
- Use registered email and password to log in
- Access your company's dashboard

### 3. Input ESG Data
- Navigate to the Data Input page
- Enter KPI data for each ESG category:
  - Environmental: Energy consumption, carbon emissions, water usage, waste management
  - Social: Employee turnover, workforce diversity, training hours, health & safety
  - Governance: Anti-corruption measures, board composition, ESG integration, regulatory compliance
  - Click submit data after for the information to be inputed into the database

### 4. View Dashboard
- Review your ESG scores and performance metrics
- Compare actual performance against ideal benchmarks
- View visualizations of your ESG data

### 5. Generate Reports
- Navigate to the Reports page
- View automated ESG performance summaries
- Download reports as needed



## Project Structure

```
activity_04/
├── group/
│   ├── code/               # All source code files
│   ├── documentation/      # Wireframes, database schema, user guides
│   ├── setup/             # Installation guides, database setup
│   └── presentation/      # Slides and demo materials
└── tools_sdk_other/       # Documentation for tools and libraries used
```

## Database Schema

### Main Tables:
1. users – Stores company registration and login information.
2. esg_kpis – Stores all ESG metric entries submitted by users.
3. reports – Contains generated performance reports for each company.
4. esg_data – Holds reference ESG information and baseline data used in calculations.
5. company_setting – Stores each company’s custom settings, preferences, and ESG configuration.
6. organization – Stores a company’s organizational details such as structure, size, and industry.



## ESG Metrics Tracked

### Environmental
- Energy consumption (kWh/employee/month)
- Carbon emissions (tCO₂e/employee/year)
- Water usage (m³/employee/year)
- Waste recycling percentage

### Social
- Employee turnover rate
- Workforce diversity (gender & underrepresented groups)
- Training & development hours
- Health & safety incident rate

### Governance
- Anti-corruption compliance
- Board composition & diversity
- ESG policy integration
- Regulatory compliance


## Contributors

Group 12 - CS321: Web Technologies
- Davis Kweku Amponsah -Team Lead
- Derrick Fiagbedzi - Scribe
- Enyonam Attipoe - Steward
- Nerrisa Abunu - Secretary

Instructor: Kwadwo Osafo-Maafo



## Future Enhancement

- CSV/Excel data import functionality
- Multi-language support
- Mobile responsive design improvements
- API integration for automated data collection
- Advanced analytics and trend forecasting


## License

This project is developed as part of CS321 coursework at Ashesi University.



## Contact

For questions or support, please contact: https://github.com/nerrisa7/Group12-webtech

