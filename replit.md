# EU Projects in MNE - Dashboard

## Overview
A PHP-based dashboard for managing EU Projects in Montenegro with user management and role-based access control (RBAC). The system supports two user roles: Administrator and Editor.

## Recent Changes
- **October 13, 2025**: Added Projects CRUD functionality
  - Created Projects database table with 26 fields
  - Implemented Excel import feature for bulk data import (50MB max file size)
  - Added Projects list, view, add, edit pages
  - Role-based delete (Admin only, Editor cannot delete)
  - Installed PhpSpreadsheet library for Excel processing
  - Updated UI with EU flag colors (#003399 blue, #FFCC00 yellow)
  - Configured PHP server with increased upload limits (50MB) via -d flags
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
│   ├── index.php       # Entry point (redirects to dashboard/login)
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
php -d upload_max_filesize=50M -d post_max_size=50M -d memory_limit=256M -d max_execution_time=300 -S 0.0.0.0:5000 -t public
```

**Upload Configuration:**
- Maximum file upload size: 50 MB
- Maximum POST size: 50 MB
- Memory limit: 256 MB
- Execution timeout: 300 seconds (5 minutes)

These settings enable importing large Excel files with multiple sheets.

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
