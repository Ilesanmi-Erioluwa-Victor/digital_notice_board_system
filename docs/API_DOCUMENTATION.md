# API Documentation

## Base URL
```
http://localhost:8000
```

## Authentication

All admin endpoints require an active session (cookie-based authentication). State-changing endpoints (POST, DELETE) additionally require a CSRF token.

### CSRF Token
Include the CSRF token in all POST/DELETE requests:
```html
<input type="hidden" name="csrf_token" value="<token>">
```

The token is available in the meta tag:
```html
<meta name="csrf-token" content="<token>">
```

## Public Endpoints

### GET / — Public Home Page
Renders the public home page with active notices.

**Response:** HTML page

---

### GET /notice/{id} — Notice Detail
View a single notice with full content and attachments.

**Parameters:**
| Name | Type | Description |
|------|------|-------------|
| id | int | Notice ID |

**Response:** HTML page

---

### GET /kiosk — Kiosk Display
Full-screen auto-cycling display of active notices.

**Response:** HTML page (no navigation)

---

### GET /api/notices/active — Active Notices (JSON)
Returns all currently active published notices. Used by AJAX polling.

**Response:**
```json
[
  {
    "id": 1,
    "title": "Semester Examination Timetable Released",
    "body": "The examination timetable...",
    "category_id": 4,
    "category_name": "Exams",
    "posted_by": 1,
    "author_name": "Super Admin",
    "priority": "urgent",
    "status": "published",
    "publish_at": "2025-01-10 00:00:00",
    "expires_at": "2025-02-10 00:00:00",
    "created_at": "2025-01-05 00:00:00",
    "updated_at": "2025-01-10 00:00:00"
  }
]
```

---

### GET /api/notices/search?q=keyword — Search Notices (JSON)
Search active notices by keyword in title or body.

**Parameters:**
| Name | Type | Description |
|------|------|-------------|
| q | string | Search keyword |

**Response:**
```json
[
  {
    "id": 1,
    "title": "Semester Examination Timetable Released",
    "body": "The examination timetable...",
    "category_name": "Exams",
    "priority": "urgent",
    "created_at": "2025-01-05 00:00:00"
  }
]
```

---

## Authentication Endpoints

### GET /login — Login Form
Renders the login page.

**Response:** HTML page

---

### POST /login — Process Login
Authenticates user credentials and starts a session.

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| email | string | Yes |
| password | string | Yes |
| csrf_token | string | Yes |

**Response:** Redirect to `/admin/dashboard` (admin) or `/` (viewer)

---

### POST /logout — Logout
Destroys the current session.

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |

**Response:** Redirect to `/`

---

### GET /register — Registration Form
Renders the user registration page.

**Response:** HTML page

---

### POST /register — Process Registration
Creates a new viewer account.

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| name | string | Yes |
| email | string | Yes |
| password | string | Yes |
| csrf_token | string | Yes |

**Response:** Redirect to `/` on success

---

## Admin Endpoints (requires auth)

### GET /admin/dashboard — Admin Dashboard
Displays statistics and recent activity.

**Authentication:** super_admin, admin

**Response:** HTML page

---

### GET /admin/notices — List Notices
Paginated list of all notices with filter options.

**Authentication:** super_admin, admin

**Query Parameters:**
| Name | Type | Description |
|------|------|-------------|
| page | int | Page number (default: 1) |
| status | string | Filter by status (draft, published, archived) |
| category | int | Filter by category ID |

**Response:** HTML page

---

### GET /admin/notices/create — Create Notice Form
Renders the notice creation form.

**Authentication:** super_admin, admin

**Response:** HTML page

---

### POST /admin/notices/create — Create Notice
Creates a new notice with optional file attachment.

**Authentication:** super_admin, admin

**Request (multipart/form-data):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |
| title | string | Yes |
| body | string | Yes |
| category_id | int | No |
| priority | string | No (default: normal) |
| status | string | No (default: draft) |
| publish_at | datetime-local | No |
| expires_at | datetime-local | No |
| attachment | file | No (max 5MB, pdf/jpg/png) |

**Response:** Redirect to `/admin/notices`

---

### GET /admin/notices/edit/{id} — Edit Notice Form
Renders the notice edit form pre-filled with existing data.

**Authentication:** super_admin, admin

**Response:** HTML page

---

### POST /admin/notices/edit/{id} — Update Notice
Updates an existing notice.

**Authentication:** super_admin, admin

**Request (multipart/form-data):** Same fields as create

**Response:** Redirect to `/admin/notices`

---

### POST /admin/notices/delete/{id} — Delete Notice
Permanently deletes a notice and its attachments.

**Authentication:** super_admin, admin

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |

**Response:** Redirect to `/admin/notices`

---

### GET /admin/categories — List Categories
Lists all notice categories.

**Authentication:** super_admin, admin

**Response:** HTML page

---

### POST /admin/categories/create — Create Category
Creates a new category.

**Authentication:** super_admin, admin

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |
| name | string | Yes |
| description | string | No |

**Response:** Redirect to `/admin/categories`

---

### POST /admin/categories/edit/{id} — Update Category
Renames a category.

**Authentication:** super_admin, admin

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |
| name | string | Yes |

**Response:** Redirect to `/admin/categories`

---

### POST /admin/categories/delete/{id} — Delete Category
Deletes a category.

**Authentication:** super_admin, admin

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |

**Response:** Redirect to `/admin/categories`

---

### GET /admin/users — List Users
Lists all registered users.

**Authentication:** super_admin only

**Response:** HTML page

---

### POST /admin/users/role/{id} — Update User Role
Changes a user's role.

**Authentication:** super_admin only

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |
| role | string | Yes (viewer, admin, super_admin) |

**Response:** Redirect to `/admin/users`

---

### POST /admin/users/delete/{id} — Delete User
Permanently deletes a user account.

**Authentication:** super_admin only

**Request (form-encoded):**
| Field | Type | Required |
|-------|------|----------|
| csrf_token | string | Yes |

**Response:** Redirect to `/admin/users`

---

### GET /admin/logs — Activity Logs
Displays all activity log entries.

**Authentication:** super_admin, admin

**Response:** HTML page

## Route Summary

| Method | Path | Auth Required | Role Required |
|--------|------|---------------|---------------|
| GET | / | No | — |
| GET | /notice/{id} | No | — |
| GET | /kiosk | No | — |
| GET | /api/notices/active | No | — |
| GET | /api/notices/search | No | — |
| GET | /login | No | — |
| POST | /login | No | — |
| POST | /logout | Yes | — |
| GET | /register | No | — |
| POST | /register | No | — |
| GET | /admin/dashboard | Yes | super_admin, admin |
| GET | /admin/notices | Yes | super_admin, admin |
| GET | /admin/notices/create | Yes | super_admin, admin |
| POST | /admin/notices/create | Yes | super_admin, admin |
| GET | /admin/notices/edit/{id} | Yes | super_admin, admin |
| POST | /admin/notices/edit/{id} | Yes | super_admin, admin |
| POST | /admin/notices/delete/{id} | Yes | super_admin, admin |
| GET | /admin/categories | Yes | super_admin, admin |
| POST | /admin/categories/create | Yes | super_admin, admin |
| POST | /admin/categories/edit/{id} | Yes | super_admin, admin |
| POST | /admin/categories/delete/{id} | Yes | super_admin, admin |
| GET | /admin/users | Yes | super_admin |
| POST | /admin/users/role/{id} | Yes | super_admin |
| POST | /admin/users/delete/{id} | Yes | super_admin |
| GET | /admin/logs | Yes | super_admin, admin |
