# Architecture Document

## Project Title
**Design and Implementation of a Digital Notice Board System**

## System Purpose
To replace traditional paper-based notice boards with a centralized web platform for posting, managing, and viewing organizational announcements. The system enables administrators to create, categorize, prioritize, and schedule notices while providing public users with a responsive, real-time-updating display for viewing announcements.

## Tech Stack Breakdown
| Layer | Technology | Purpose |
|---|---|---|
| Frontend | HTML5, CSS3, Vanilla JavaScript | Responsive UI, AJAX polling, kiosk display |
| Backend | PHP 8+ (OOP, lightweight MVC) | RESTful API, business logic, authentication |
| Database | PostgreSQL | Relational data storage with full-text search |
| Communication | Fetch API / AJAX Polling | Real-time-style updates from server |

## Folder Structure
```
/
├── public/                     # Web root (entry point)
│   ├── index.php               # Front controller (all requests routed here)
│   ├── .htaccess               # Apache rewrite rules
│   └── assets/                 # Static assets (symlinked or copied)
├── app/
│   ├── controllers/            # Request handlers
│   │   ├── AuthController.php
│   │   ├── NoticeController.php
│   │   ├── CategoryController.php
│   │   ├── UserController.php
│   │   ├── DashboardController.php
│   │   └── PublicController.php
│   ├── models/                 # Database interaction layer
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Notice.php
│   │   ├── Attachment.php
│   │   └── ActivityLog.php
│   ├── core/                   # Framework core
│   │   ├── Database.php        # PDO singleton wrapper
│   │   ├── Router.php          # Front controller router
│   │   └── Auth.php            # Session/auth helper
│   └── views/                  # Presentation templates
│       ├── admin/
│       │   ├── dashboard.php
│       │   ├── notices-list.php
│       │   ├── notice-form.php
│       │   ├── categories.php
│       │   ├── users.php
│       │   └── logs.php
│       ├── public/
│       │   ├── home.php
│       │   ├── notice-detail.php
│       │   └── kiosk.php
│       └── layouts/
│           ├── header.php
│           ├── footer.php
│           └── sidebar.php
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── admin.css
│   ├── js/
│   │   ├── main.js
│   │   ├── admin.js
│   │   ├── kiosk.js
│   │   └── ajax-polling.js
│   ├── uploads/                # File attachments (gitignored)
│   │   └── .gitkeep
│   └── ...
├── config/
│   ├── config.php
│   └── .env.example
├── sql/
│   ├── schema.sql
│   └── seed.sql
├── docs/                       # Academic documentation
│   ├── SYSTEM_DESIGN.md
│   ├── API_DOCUMENTATION.md
│   ├── USER_MANUAL.md
│   └── TESTING.md
├── composer.json
├── .gitignore
├── README.md
└── ARCHITECTURE.md
```

## Data Flow
```
Admin (Browser)
    │
    ├── POST /admin/notices/create ──► PHP NoticeController ──► PostgreSQL (INSERT)
    │                                       │
    │                                       └──► ActivityLog (log action)
    │
    ├── POST /admin/notices/edit/{id} ──► PHP NoticeController ──► PostgreSQL (UPDATE)
    │                                       │
    │                                       └──► ActivityLog (log action)
    │
    └── POST /admin/notices/delete/{id} ──► PHP NoticeController ──► PostgreSQL (DELETE/SOFT)
                                                │
                                                └──► ActivityLog (log action)

Public Display / User Dashboard
    │
    ├── Initial Load (GET /) ──► PHP PublicController ──► PostgreSQL ──► HTML rendered server-side
    │
    └── AJAX Polling (every 30s) ──► GET /api/notices/active ──► PHP NoticeController
                                         ──► JSON Response ──► JavaScript re-renders grid
                                              without full page reload

Kiosk Display
    │
    ├── Initial Load (GET /kiosk) ──► PHP renders full-screen template
    │
    └── AJAX Polling (every 30s) ──► GET /api/notices/active ──► JSON ──► JS rotates notices
                                         with fade transitions every 8-10 seconds

Search/Filter
    │
    └── GET /api/notices/search?q=keyword ──► PHP NoticeController
         ──► JSON filtered results ──► JS renders results
```

## User Roles

| Role | Permissions |
|---|---|
| **Super Admin** | Full system access: manage notices, categories, users, roles, view activity logs, access all admin sections |
| **Department Admin** | Create, edit, delete, publish own notices; manage categories; cannot manage users or change roles |
| **Viewer / User** | Browse active notices via public home page; search/filter notices; view notice details and download attachments; view kiosk display mode |
