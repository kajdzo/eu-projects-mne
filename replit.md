# EU Projects in MNE - Dashboard

## Overview
This project is a PHP-based dashboard designed to manage EU Projects in Montenegro. It provides a comprehensive system for tracking, visualizing, and sharing information about European Union-funded initiatives. The dashboard features user management with role-based access control (Administrator and Editor roles), allowing for secure and differentiated interaction with project data. Its main purpose is to centralize project information, facilitate data import via Excel, and present project statistics and details to both internal users and the public through a dedicated public-facing interface. The project aims to enhance transparency and accessibility of EU project data in Montenegro.

## User Preferences
None specified yet.

## System Architecture

### Technology Stack
- **Backend**: PHP 8.2
- **Database**: PostgreSQL
- **Frontend**: Plain HTML/CSS, Chart.js for data visualization
- **Libraries**: PhpSpreadsheet 5.1.0 (Excel processing)
- **UI/UX**:
    - Consistent styling with EU flag colors (#003399 blue, #FFCC00 yellow).
    - Responsive design for all public and administrative interfaces.
    - Interactive elements include dynamic cascading filters, "Load More" pagination, and spinner animations for button clicks.
    - Dashboard features overview statistics cards and interactive charts (bar, doughnut, pie) for sectors, municipalities, programs, and project status.
    - Public dashboard displays statistics in a single horizontal row at the top for better visual hierarchy (responsive: 4 cards → 2 cards on tablet → 1 card on mobile).
    - Standardized button and link styles across the platform.
    - Enhanced dashboard header with user avatar dropdown for "My Profile" and "Logout".

### Core Features
- **Authentication & Authorization**: Secure login with password hashing, session-based authentication, and a two-role RBAC system (Administrator, Editor).
- **User Management**: Administrators have full CRUD access over users; Editors can manage only their own profile.
- **Projects Management**: Comprehensive CRUD operations for EU projects.
- **Excel Import**: Bulk data upload via .xlsx/.xls files with automatic parsing, column mapping, date/decimal handling, data trimming, and error management.
- **Public Dashboard**: A public-facing interface featuring project listings, dynamic filters, a map placeholder, real-time statistics, and detailed project view pages. Sensitive project fields are hidden from public view.
- **Data Visualization**: Integration of Chart.js for interactive statistical charts on the dashboard.
- **Export Functionality**: Public dashboard projects can be exported to Excel, respecting applied filters.

### System Design Choices
- **Database Schema**: Dedicated `Users` and `Projects` tables store comprehensive project details and user information, respectively.
- **Folder Structure**: Organizes configuration, shared components, public-facing files, and administrative sections logically.
- **Security**: Implements password hashing, input sanitization, and prepared statements for SQL injection prevention.
- **Performance**: "Load More" pagination for public listings and optimized filter queries with case-insensitive deduplication for improved user experience.

## External Dependencies
- **PostgreSQL**: The primary database for storing all application data.
- **PhpSpreadsheet 5.1.0**: PHP library used for reading and writing spreadsheet files, specifically for Excel import and export functionalities.
- **Chart.js**: JavaScript library used for creating interactive and customizable charts for data visualization on the dashboard.