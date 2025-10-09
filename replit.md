# EU Projects in MNE - Dashboard

## Overview
A PHP-based user management dashboard with role-based access control (RBAC). The system supports two user roles: Administrator and Editor.

## Recent Changes
- Initial project setup (October 9, 2025)
- Created user management system with PostgreSQL database
- Implemented authentication and authorization
- Two-role system: Administrator and Editor
- Editors can only manage their own account, Administrators can manage all users

## Project Architecture

### Technology Stack
- **Backend**: PHP 8.2
- **Database**: PostgreSQL (Replit built-in)
- **Frontend**: Plain HTML/CSS
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
│   └── profile.php     # Profile redirect
└── router.php          # PHP server router (unused currently)
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

### User Roles & Permissions

#### Administrator
- Can view, create, edit, and delete all users
- Can change user roles and active status
- Full access to user management interface

#### Editor
- Can only view and edit their own profile
- Cannot access user management interface
- Cannot change their own role or active status

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

3. **Access Control**
   - Role-based access control (RBAC)
   - Editors restricted to their own profile
   - Administrators have full access

4. **Security Features**
   - Password hashing with PHP's password_hash()
   - CSRF protection ready (can be enhanced)
   - Input sanitization with htmlspecialchars()
   - Prepared statements to prevent SQL injection

## Development

### Running the Application
The PHP built-in server runs on port 5000:
```bash
php -S 0.0.0.0:5000 -t public
```

### Database Access
PostgreSQL database is automatically configured via environment variables:
- DATABASE_URL
- PGHOST, PGPORT, PGUSER, PGPASSWORD, PGDATABASE

### First Time Setup
The application automatically:
1. Creates the users table on first run
2. Creates a default administrator account if none exists

## User Preferences
None specified yet.
