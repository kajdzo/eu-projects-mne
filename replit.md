# EU Projects in MNE - Dashboard

## Overview
A PHP-based dashboard for managing EU Projects in Montenegro with user management and role-based access control (RBAC). The system supports two user roles: Administrator and Editor.

## Recent Changes
- **October 18, 2025**: Added EU Disclaimer to Public Pages
  - Added disclaimer footer to all public pages (home.php, public.php, public-project.php)
  - Disclaimer text: "This website was created and maintained with the financial support of the European Union. Its contents are the sole responsibility of the Europe House and do not necessarily reflect the views of the European Union."
  - Footer styling consistent across all public pages
- **October 18, 2025**: Hidden Sensitive Fields from Public View
  - Removed Management Mode, Contract Number, and Decision Number from public project details page
  - These fields remain visible and editable in admin area (project-view.php, project-edit.php, project-add.php)
  - Cleaner public-facing project information while maintaining full admin control
- **October 18, 2025**: Implemented Dynamic Cascading Filters on Public Dashboard
  - Filter dropdowns now update dynamically to show only relevant options based on current selections
  - AJAX-powered real-time filter option updates without page refresh
  - Users can only select filter combinations that have matching data
  - Improved user experience by preventing empty result sets
  - Created getFilterOptions() function for dynamic filter queries
- **October 18, 2025**: Added Comprehensive Dashboard Statistics and Charts
  - Added 4 overview statistics cards: Total Projects, Total EU Funding, Ongoing Projects, Completed Projects
  - Integrated Chart.js library for data visualization
  - Created 4 interactive charts: Top 10 Sectors (horizontal bar), Top 10 Municipalities (doughnut), Top 10 Programs (bar), Status Distribution (pie)
  - Responsive grid layouts for statistics and charts
  - Hover animations on stat cards for better UX
  - Color-coded charts using EU brand colors and complementary palette
- **October 18, 2025**: Enhanced Dashboard Header with User Avatar Dropdown
  - Replaced plain text "Welcome User" with circular avatar showing user's initial
  - Added dropdown menu with "My Profile" and "Logout" options
  - Removed profile/logout from main navigation for cleaner header
  - Smooth slide-down animation with icons for better UX
  - Click outside to close functionality
- **October 18, 2025**: Standardized Button and Link Styles Platform-Wide
  - Applied `line-height: 1` and `text-align: center` to all buttons and links
  - Added `white-space: nowrap` to prevent button text wrapping
  - Consistent styling across home page, dashboard, public pages, and admin sections
- **October 16, 2025**: Added Public Project Details Page
  - Created public-project.php for displaying full project details to public visitors
  - Made project titles on public dashboard clickable links to detail page
  - Breadcrumb navigation: Home → Projects → Project Details
  - Displays all project information (sectors, financials, location, description, links)
  - Status badges (Ongoing/Completed) based on end date
  - Links work in both initial page load and AJAX "Load More" responses
  - Consistent header styling matching public dashboard (logo box + Home button)
- **October 16, 2025**: Optimized Filter Options with Case-Insensitive Deduplication
  - Updated filter queries to use case-insensitive DISTINCT (MIN + UPPER grouping)
  - Merged duplicate options like "Podgorica", "PODGORICA", "podgorica" into single option
  - Updated WHERE clauses to use case-insensitive matching (UPPER comparison)
  - Cleaner filter dropdowns with significantly fewer duplicate options
  - Filtering works correctly regardless of text casing in database
- **October 16, 2025**: Added "Load More" Pagination to Public Dashboard
  - Implemented pagination with LIMIT 20 projects per load for faster page load times
  - Added "Load More" button with AJAX functionality to dynamically load more projects
  - Statistics remain accurate across all pages (not just visible projects)
  - Filter parameters are preserved when loading additional projects
  - Spinner animation on Load More button for visual feedback
  - Button automatically disappears when all projects are loaded
  - Improved performance and user experience for large datasets
- **October 16, 2025**: Added Public Home Page
  - Created new landing page (home.php) as the main entry point for public visitors
  - Hero section with EU flag colors, call-to-action buttons (Explore Projects, Admin Login)
  - Statistics section showing EU investment, active projects, sectors, and municipalities
  - Features grid explaining dashboard capabilities (search/filter, project details, map, statistics)
  - About section with information about EU support to Montenegro
  - Updated index.php to redirect to home page (instead of login) for public visitors
  - Added navigation on public dashboard header (Home and Admin Login links)
  - Fully responsive design with mobile support
- **October 16, 2025**: Added Spinner Animation to Action Buttons
  - Implemented CSS spinner animation for visual feedback on button clicks
  - Added JavaScript handlers to all form submit buttons (login, import, filters, user/project forms)
  - Buttons show loading spinner and become disabled during form submission
  - Improves user experience by providing clear feedback for async operations
- **October 14, 2025**: Added Public Dashboard
  - Created public-facing page with 3-column layout (filters, map, projects)
  - Single-select dropdown filters: Sector, Municipality, Program, Start Year, End Year, Beneficiary, Status
  - Filters combine with AND logic and populate from distinct database values
  - Interactive map placeholder ready for geodata integration
  - Real-time statistics: total projects, funding, ongoing/completed counts
  - Project cards with status badges (Ongoing/Completed)
  - Responsive design with mobile support
- **October 15, 2025**: Added Data Trimming to Excel Import
  - All text values are now automatically trimmed during import (removes leading/trailing spaces)
  - Filter queries use TRIM() to eliminate duplicate filter options
  - Prevents issues like "Danilovgrad" and "Danilovgrad " appearing as separate values
  - Improved empty row detection to skip rows with only formatting (borders, colors) but no data
- **October 15, 2025**: Fixed Excel Import Parser (v2)
  - Fixed PHP 8.2 deprecation error when processing null/empty Excel header cells
  - Added proper null checks before str_replace() operations to prevent errors
  - Parser now safely skips empty columns without breaking the import
- **October 14, 2025**: Fixed Excel Import Parser
  - Fixed "untitled projects" issue caused by column name mismatches
  - Added header normalization: removes non-breaking spaces (U+00A0) and trailing whitespace
  - Added support for "Assistance framework" column (in addition to "Financial framework")
  - Fixed Excel serial date parsing (numeric dates like 39730 now convert correctly)
  - Parser now handles special characters and whitespace variations in Excel headers
- **October 13, 2025**: Added Projects CRUD functionality
  - Created Projects database table with 26 fields
  - Implemented Excel import feature for bulk data import (50MB max file size)
  - Added Projects list with pagination (20 items per page)
  - Added Projects view, add, edit pages
  - Role-based delete (Admin only, Editor cannot delete)
  - Installed PhpSpreadsheet library for Excel processing
  - Updated UI with EU flag colors (#003399 blue, #FFCC00 yellow)
  - Configured PHP server with increased resources (50MB upload, 1GB memory) via -d flags
- **October 9, 2025**: Initial project setup
  - Created user management system with PostgreSQL database
  - Implemented authentication and authorization
  - Two-role system: Administrator and Editor
  - Editors can only manage their own account, Administrators can manage all users

## Project Architecture

### Technology Stack
- **Backend**: PHP 8.2
- **Database**: PostgreSQL (Replit built-in)
- **Frontend**: Plain HTML/CSS
- **Libraries**: PhpSpreadsheet 5.1.0 (Excel processing)
- **Server**: PHP built-in development server

### Directory Structure
```
/
├── config/
│   ├── database.php    # Database connection configuration
│   └── init.php        # Session, auth helpers, database initialization
├── includes/
│   └── header.php      # Shared header component
├── public/
│   ├── css/
│   │   └── style.css   # Application styles
│   ├── index.php       # Entry point (redirects to home/dashboard)
│   ├── home.php        # Public home/landing page
│   ├── public.php      # Public dashboard with filters
│   ├── login.php       # Login page
│   ├── logout.php      # Logout handler
│   ├── dashboard.php   # Main dashboard
│   ├── users.php       # User management (Admin only)
│   ├── user-add.php    # Add new user (Admin only)
│   ├── user-edit.php   # Edit user
│   ├── profile.php     # Profile redirect
│   ├── projects.php    # Projects list
│   ├── projects-import.php  # Excel import (Admin only)
│   ├── project-add.php      # Add new project
│   ├── project-edit.php     # Edit project
│   ├── project-view.php     # View project details
│   └── project-delete.php   # Delete project (Admin only)
├── vendor/             # Composer dependencies (PhpSpreadsheet)
└── composer.json       # PHP dependencies
```

### Database Schema

#### Users Table
- `id` (SERIAL PRIMARY KEY)
- `full_name` (VARCHAR 255)
- `email` (VARCHAR 255, UNIQUE)
- `password` (VARCHAR 255, hashed)
- `role` (VARCHAR 50) - 'Administrator' or 'Editor'
- `is_active` (BOOLEAN)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

#### Projects Table
- `id` (SERIAL PRIMARY KEY)
- `financial_framework` (VARCHAR 255)
- `programme` (VARCHAR 255)
- `type_of_programme` (VARCHAR 255)
- `management_mode` (VARCHAR 255)
- `sector_1` (TEXT)
- `sector_2` (TEXT)
- `contract_title` (TEXT)
- `contract_type` (VARCHAR 100)
- `commitment_year` (VARCHAR 10)
- `contract_year` (VARCHAR 10)
- `start_date` (DATE)
- `end_date` (DATE)
- `contract_number` (VARCHAR 100)
- `contracting_party` (TEXT)
- `decision_number` (VARCHAR 100)
- `contracted_eu_contribution` (DECIMAL 15,2)
- `eu_contribution_mne` (DECIMAL 15,2)
- `eu_contribution_overall` (DECIMAL 15,2)
- `total_euro_value` (DECIMAL 15,2)
- `municipality` (VARCHAR 255)
- `short_description` (TEXT)
- `keywords` (TEXT)
- `project_link` (TEXT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### User Roles & Permissions

#### Administrator
- Can view, create, edit, and delete all users
- Can change user roles and active status
- Full access to user management interface

#### Editor
- Can only view and edit their own profile
- Cannot access user management interface
- Cannot change their own role or active status
- Can create, view, and edit projects
- **Cannot delete projects**

### Default Credentials
- **Email**: admin@euprojects.me
- **Password**: admin123

### Key Features
1. **Authentication System**
   - Secure login with password hashing (bcrypt)
   - Session-based authentication
   - Login/logout functionality

2. **User Management**
   - CRUD operations for users (Admin only)
   - Role assignment (Administrator/Editor)
   - Active/inactive user status toggle
   - Email validation (unique constraint)

3. **Projects Management**
   - Full CRUD operations for EU projects
   - Excel import for bulk data upload (Admin only)
   - Multi-sheet Excel file support
   - Automatic date and decimal parsing
   - Detailed project view with all 26 fields
   - Role-based delete (Admin only)

4. **Excel Import**
   - Upload .xlsx or .xls files
   - Process multiple sheets automatically
   - Map Excel columns to database fields
   - Display import statistics per sheet
   - Error handling for invalid data

5. **Access Control**
   - Role-based access control (RBAC)
   - Editors restricted to their own profile
   - Editors cannot delete projects
   - Administrators have full access

6. **Security Features**
   - Password hashing with PHP's password_hash()
   - CSRF protection ready (can be enhanced)
   - Input sanitization with htmlspecialchars()
   - Prepared statements to prevent SQL injection

## Development

### Running the Application
The PHP built-in server runs on port 5000 with increased upload limits:
```bash
php -d upload_max_filesize=50M -d post_max_size=50M -d memory_limit=1G -d max_execution_time=600 -S 0.0.0.0:5000 -t public
```

**Upload Configuration:**
- Maximum file upload size: 50 MB
- Maximum POST size: 50 MB
- Memory limit: 1 GB (1024 MB)
- Execution timeout: 600 seconds (10 minutes)

These settings enable importing very large Excel files with multiple sheets and thousands of rows.

### Database Access
PostgreSQL database is automatically configured via environment variables:
- DATABASE_URL
- PGHOST, PGPORT, PGUSER, PGPASSWORD, PGDATABASE

### First Time Setup
The application automatically:
1. Creates the users and projects tables on first run
2. Creates a default administrator account if none exists

### Dependencies
Install Composer dependencies:
```bash
composer install
```

This will install:
- PhpSpreadsheet 5.1.0 for Excel file processing
- All required PSR packages and dependencies

## User Preferences
None specified yet.
