-- ==========================================================================
-- Digital Notice Board System — PostgreSQL Full Schema
-- ==========================================================================
-- Run: psql -d digital_notice_board -f sql/schema.sql
-- ==========================================================================

-- Drop all existing tables to ensure a clean slate (order matters for FK deps)
DROP TABLE IF EXISTS
    password_resets,
    notifications,
    bookmarks,
    notice_views,
    notice_attachments,
    activity_logs,
    notices,
    categories,
    users,
    levels,
    programmes,
    departments,
    faculties,
    roles
CASCADE;

-- ─── Roles Table ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- ─── Faculties Table ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS faculties (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20) UNIQUE,
    description TEXT
);

-- ─── Departments Table ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20) UNIQUE,
    faculty_id INTEGER REFERENCES faculties(id) ON DELETE SET NULL,
    description TEXT
);

-- ─── Programmes Table ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS programmes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20) UNIQUE,
    department_id INTEGER REFERENCES departments(id) ON DELETE SET NULL,
    duration_years INTEGER DEFAULT 4
);

-- ─── Levels Table ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS levels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    sort_order INTEGER DEFAULT 0
);

-- ─── Users Table ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'student'
        CHECK (role IN ('admin', 'staff', 'student')),
    staff_id VARCHAR(50),
    student_id VARCHAR(50),
    department_id INTEGER REFERENCES departments(id) ON DELETE SET NULL,
    programme_id INTEGER REFERENCES programmes(id) ON DELETE SET NULL,
    level_id INTEGER REFERENCES levels(id) ON DELETE SET NULL,
    is_active BOOLEAN DEFAULT TRUE,
    avatar_url VARCHAR(255),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- ─── Password Resets Table ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS password_resets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- ─── Categories Table ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- ─── Notices Table ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notices (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    posted_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    priority VARCHAR(10) DEFAULT 'medium'
        CHECK (priority IN ('high', 'medium', 'low')),
    status VARCHAR(20) DEFAULT 'draft'
        CHECK (status IN ('draft', 'pending', 'approved', 'rejected', 'published', 'archived')),
    approval_status VARCHAR(20) DEFAULT 'none'
        CHECK (approval_status IN ('none', 'pending', 'approved', 'rejected')),
    approved_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    rejection_reason TEXT,
    is_pinned BOOLEAN DEFAULT FALSE,
    target_audience_type VARCHAR(30) DEFAULT 'everyone'
        CHECK (target_audience_type IN ('everyone', 'faculty', 'department', 'programme', 'level', 'staff', 'students')),
    target_ids INTEGER[] DEFAULT '{}',
    publish_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- ─── Notice Attachments Table ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notice_attachments (
    id SERIAL PRIMARY KEY,
    notice_id INTEGER REFERENCES notices(id) ON DELETE CASCADE,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INTEGER DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT NOW()
);

-- ─── Notice Views Table ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notice_views (
    id SERIAL PRIMARY KEY,
    notice_id INTEGER REFERENCES notices(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    visitor_ip VARCHAR(45),
    viewed_at TIMESTAMP DEFAULT NOW()
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_notice_views_logged_in 
    ON notice_views(notice_id, user_id) WHERE user_id IS NOT NULL;
CREATE UNIQUE INDEX IF NOT EXISTS idx_notice_views_guest 
    ON notice_views(notice_id, visitor_ip) WHERE visitor_ip IS NOT NULL;

-- ─── Bookmarks Table ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookmarks (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    notice_id INTEGER REFERENCES notices(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(user_id, notice_id)
);

-- ─── Notifications Table ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- ─── Activity Logs Table ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INTEGER,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT NOW()
);

-- ─── Indexes ──────────────────────────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_notices_status ON notices(status);
CREATE INDEX IF NOT EXISTS idx_notices_publish_at ON notices(publish_at);
CREATE INDEX IF NOT EXISTS idx_notices_expires_at ON notices(expires_at);
CREATE INDEX IF NOT EXISTS idx_notices_category ON notices(category_id);
CREATE INDEX IF NOT EXISTS idx_notices_posted_by ON notices(posted_by);
CREATE INDEX IF NOT EXISTS idx_notices_priority ON notices(priority);
CREATE INDEX IF NOT EXISTS idx_notices_is_pinned ON notices(is_pinned);
CREATE INDEX IF NOT EXISTS idx_notices_target_audience ON notices(target_audience_type);
CREATE INDEX IF NOT EXISTS idx_notice_views_notice ON notice_views(notice_id);
CREATE INDEX IF NOT EXISTS idx_notice_views_user ON notice_views(user_id);
CREATE INDEX IF NOT EXISTS idx_bookmarks_user ON bookmarks(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_unread ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_activity_logs_timestamp ON activity_logs(timestamp);
CREATE INDEX IF NOT EXISTS idx_activity_logs_user ON activity_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_logs_entity ON activity_logs(entity_type, entity_id);
