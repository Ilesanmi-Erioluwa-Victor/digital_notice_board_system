# Design and Implementation of a Digital Notice Board System

## Abstract
A web-based digital notice board system replacing traditional paper-based notice boards with a centralized platform for posting, managing, and viewing organizational announcements. Built with PHP (OOP/MVC), PostgreSQL, and vanilla JavaScript, featuring responsive design, AJAX polling for real-time updates, role-based access control, and a dedicated kiosk display mode. This system serves as a complete replacement for physical notice boards in educational institutions, corporate offices, and public organizations, providing a modern, accessible, and environmentally friendly solution for information dissemination.

## Tech Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (no framework dependency)
- **Backend:** PHP 8+ (Object-Oriented Programming, lightweight MVC architecture)
- **Database:** PostgreSQL (relational database with full-text search capabilities)
- **Real-time Updates:** AJAX polling via Fetch API (configurable 30-second interval)
- **Email Notifications:** PHPMailer (SMTP-based, optional configuration)
- **Environment Configuration:** vlucas/phpdotenv

## Features
- **Role-Based Access Control**: Super Admin, Department Admin, and Viewer roles with granular permissions
- **Notice Lifecycle Management**: Draft → Published → Archived workflow with scheduled publishing and expiration
- **Real-Time Updates**: AJAX polling provides near-instant notice visibility without page refreshes
- **Responsive Design**: Mobile-first CSS with breakpoints at 640px, 1024px, and 1280px
- **Kiosk Display Mode**: Full-screen auto-cycling notice display for mounted screens
- **File Attachments**: PDF, JPG, and PNG uploads with type/size validation (max 5MB)
- **Search and Filter**: Keyword search and category filtering for easy notice discovery
- **Activity Logging**: Complete audit trail of all admin actions
- **CSRF Protection**: Token-based security on all state-changing operations
- **Email Notifications**: Optional email alerts to viewers when new notices are published
- **Unread Notice Badge**: Visual indicator of new notices since last visit

## Setup Instructions

### Prerequisites
- PHP 8.0 or higher (with PDO and pgsql extensions)
- Composer (Dependency Manager for PHP)
- PostgreSQL 13 or higher
- Apache with mod_rewrite enabled (or PHP built-in server for development)

### Installation

1. Clone the repository:
   ```bash
   git clone <repo-url>
   cd digital_notice_board_system
   ```

2. Install Composer dependencies:
   ```bash
   composer install
   ```

3. Configure environment:
   ```bash
   cp config/.env.example .env
   # Edit .env with your database credentials and app settings
   ```

4. Set up PostgreSQL database:
   ```bash
   createdb digital_notice_board
   psql -d digital_notice_board -f sql/schema.sql
   psql -d digital_notice_board -f sql/seed.sql
   ```

5. Start the development server:
   ```bash
   php -S localhost:8000 -t public
   ```

6. Open your browser at `http://localhost:8000`

### Apache Deployment
Ensure `.htaccess` files are enabled and `mod_rewrite` is loaded. The provided `/public/.htaccess` routes all requests through `index.php`.

### Docker Deployment (Render)
A Dockerfile and docker-compose.yml are included for containerized deployment. Build and run with:
```bash
docker-compose up --build
```

### Default Credentials (from seed data)
| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@example.com | password123 |
| Department Admin | deptadmin@example.com | password123 |
| Admin User | admin2@example.com | password123 |
| Viewer | viewer@example.com | password123 |
| Viewer | jane@example.com | password123 |
| Viewer | bob@example.com | password123 |

## Project Structure
```
/
├── public/                  # Web root (entry point via index.php)
├── app/
│   ├── controllers/         # Request handlers (7 controllers)
│   ├── models/              # Database models (5 models)
│   ├── core/                # Framework: Database, Router, Auth, Mailer
│   └── views/               # Presentation templates (layouts, admin, public)
├── assets/
│   ├── css/                 # style.css, admin.css
│   ├── js/                  # main.js, admin.js, kiosk.js, ajax-polling.js
│   └── uploads/             # File attachments (gitignored)
├── config/                  # config.php, .env.example
├── sql/                     # schema.sql, seed.sql
├── docs/                    # Academic documentation
├── composer.json
├── .gitignore
├── ARCHITECTURE.md
└── README.md
```

## Documentation
See the `/docs` folder for complete academic documentation:

- **[System Design](docs/SYSTEM_DESIGN.md)** — Architecture overview, use case descriptions, ER diagram, real-time update mechanism explanation
- **[API Documentation](docs/API_DOCUMENTATION.md)** — Full endpoint reference with request/response examples, authentication requirements, and role permissions
- **[User Manual](docs/USER_MANUAL.md)** — Non-technical guide for admins and viewers covering all system features
- **[Testing](docs/TESTING.md)** — Comprehensive test cases with steps, expected results, and test matrices

## Academic Context
This project was developed as a **Higher National Diploma / National Diploma final year project** to demonstrate:
- Object-Oriented Programming in PHP (MVC architecture)
- Relational database design and SQL (PostgreSQL)
- Client-side interactivity with vanilla JavaScript (AJAX, DOM manipulation)
- Responsive web design principles (mobile-first CSS)
- Security best practices (prepared statements, CSRF protection, password hashing)
- Software documentation and testing methodology
