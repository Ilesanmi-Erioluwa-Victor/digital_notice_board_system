# System Design Document

## System Architecture

The Digital Notice Board System follows a lightweight **Model-View-Controller (MVC)** architecture pattern implemented in PHP, with a PostgreSQL database backend and a vanilla JavaScript frontend.

### Architecture Layers

```
┌─────────────────────────────────────────────────────────────┐
│                   CLIENT LAYER (Browser)                     │
│  HTML5 + CSS3 + Vanilla JavaScript                          │
│  - Responsive UI (mobile-first)                             │
│  - AJAX polling via Fetch API                               │
│  - Kiosk auto-cycling display                               │
│  - Client-side form validation                              │
└───────────────────────┬─────────────────────────────────────┘
                        │ HTTP / JSON
                        ▼
┌─────────────────────────────────────────────────────────────┐
│                   SERVER LAYER (PHP 8+)                      │
│                                                             │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │   Router    │→ │ Controllers  │→ │     Models       │   │
│  │ (Front      │  │ (Request     │  │ (Database        │   │
│  │  Controller)│  │  Handlers)   │  │  Interaction)    │   │
│  └─────────────┘  └──────────────┘  └────────┬─────────┘   │
│         │                                     │             │
│         ▼                                     ▼             │
│  ┌─────────────┐                     ┌──────────────────┐   │
│  │  Auth       │                     │   PostgreSQL DB   │   │
│  │ (Session/   │                     │                   │   │
│  │  CSRF)      │                     │   - users         │   │
│  └─────────────┘                     │   - categories    │   │
│                                      │   - notices       │   │
│                                      │   - attachments   │   │
│                                      │   - activity_logs │   │
│                                      └──────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

```
Admin (Browser)                          Public Viewer (Browser)
     │                                         │
     │ POST /admin/notices/create              │ GET /
     │ (form submit)                           │ (initial page load)
     ▼                                         ▼
  PHP NoticeController                    PHP PublicController
     │                                         │
     │ INSERT INTO notices                     │ SELECT active notices
     ▼                                         ▼
  PostgreSQL                              PostgreSQL
     │                                         │
     └── ActivityLog entry                     └── HTML rendered
                                                    │
                                               AJAX Polling
                                               (every 30s)
                                                    │
                                               GET /api/notices/active
                                                    │
                                               JSON response
                                                    │
                                               JS re-renders grid
                                                    │
                                          ┌───────────────────┐
                                          │ Real-time update  │
                                          │ without page      │
                                          │ reload            │
                                          └───────────────────┘
```

## Use Case Diagram

### Actor: Admin (Super Admin / Department Admin)
- Login to the system
- Create, edit, delete, publish, archive notices
- Upload file attachments to notices
- Manage categories (create, rename, delete)
- View dashboard with statistics
- View activity logs

### Actor: Super Admin (extends Admin)
- Manage users (view, change roles, delete)
- Full access to all admin functions

### Actor: Viewer / Public User
- Browse active notices on the public home page
- Search notices by keyword
- Filter notices by category
- View full notice details
- Download attached files
- View kiosk display mode (auto-cycling full-screen)

## Entity-Relationship Diagram

### Table: `users`
| Column | Type | Constraints |
|--------|------|-------------|
| id | SERIAL | PRIMARY KEY |
| name | VARCHAR(150) | NOT NULL |
| email | VARCHAR(150) | UNIQUE, NOT NULL |
| password_hash | VARCHAR(255) | NOT NULL |
| role | VARCHAR(20) | DEFAULT 'viewer', CHECK (super_admin, admin, viewer) |
| created_at | TIMESTAMP | DEFAULT NOW() |

### Table: `categories`
| Column | Type | Constraints |
|--------|------|-------------|
| id | SERIAL | PRIMARY KEY |
| name | VARCHAR(100) | NOT NULL |
| description | TEXT | |

### Table: `notices`
| Column | Type | Constraints |
|--------|------|-------------|
| id | SERIAL | PRIMARY KEY |
| title | VARCHAR(255) | NOT NULL |
| body | TEXT | NOT NULL |
| category_id | INTEGER | FK → categories(id) ON DELETE SET NULL |
| posted_by | INTEGER | FK → users(id) ON DELETE SET NULL |
| priority | VARCHAR(20) | DEFAULT 'normal', CHECK (normal, urgent) |
| status | VARCHAR(20) | DEFAULT 'draft', CHECK (draft, published, archived) |
| publish_at | TIMESTAMP | |
| expires_at | TIMESTAMP | |
| created_at | TIMESTAMP | DEFAULT NOW() |
| updated_at | TIMESTAMP | DEFAULT NOW() |

### Table: `attachments`
| Column | Type | Constraints |
|--------|------|-------------|
| id | SERIAL | PRIMARY KEY |
| notice_id | INTEGER | FK → notices(id) ON DELETE CASCADE |
| file_path | VARCHAR(255) | NOT NULL |
| file_type | VARCHAR(50) | |
| uploaded_at | TIMESTAMP | DEFAULT NOW() |

### Table: `activity_logs`
| Column | Type | Constraints |
|--------|------|-------------|
| id | SERIAL | PRIMARY KEY |
| admin_id | INTEGER | FK → users(id) ON DELETE SET NULL |
| action | VARCHAR(100) | NOT NULL |
| notice_id | INTEGER | FK → notices(id) ON DELETE SET NULL |
| details | TEXT | |
| timestamp | TIMESTAMP | DEFAULT NOW() |

### Relationships
- A **Notice** belongs to one **Category** (optional)
- A **Notice** is posted by one **User** (admin)
- A **Notice** can have multiple **Attachments**
- An **Activity Log** entry references one **User** (admin) and optionally one **Notice**

## Real-Time Update Mechanism: AJAX Polling

### Why AJAX Polling instead of WebSockets?

AJAX polling was chosen over WebSockets for this project for the following reasons:

1. **Project Scope**: This is an academic project (HND/ND level). AJAX polling is simpler to implement, understand, and document within the project's scope and timeline.

2. **Infrastructure Simplicity**: WebSockets require a persistent server connection (e.g., Ratchet for PHP or a Node.js sidecar), adding deployment complexity. AJAX polling works with standard PHP hosting without additional infrastructure.

3. **Sufficient for the Use Case**: Notice board updates are not high-frequency (notices are posted minutes/hours apart, not milliseconds). A 30-second polling interval provides a "real-time enough" experience for this use case.

4. **No Additional Dependencies**: AJAX polling uses the built-in Fetch API, avoiding the need for additional JavaScript libraries or PHP WebSocket packages.

5. **Low Server Load**: With caching headers and efficient SQL queries, a 30-second polling interval (or configurable) places minimal load on the server even with many concurrent viewers.

### How It Works

1. On page load, PHP renders the initial notice grid server-side (no delay).
2. `ajax-polling.js` starts a `setInterval` that calls `GET /api/notices/active` every 30 seconds.
3. The API endpoint returns JSON with all currently active notices.
4. The JavaScript replaces the grid content with the new data (if changed).
5. A kiosk-specific script (`kiosk.js`) uses the same polling mechanism but renders notices one-at-a-time with rotation.
