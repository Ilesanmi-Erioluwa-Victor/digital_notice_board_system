-- ==========================================================================
-- Digital Notice Board System — PostgreSQL Schema
-- ==========================================================================
-- Run: psql -d digital_notice_board -f sql/schema.sql
-- ==========================================================================

-- ─── Users Table ──────────────────────────────────────────────────────────
-- Stores all system users with role-based access control.
-- Roles: super_admin (full access), admin (department admin), viewer (read-only)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'viewer'
        CHECK (role IN ('super_admin', 'admin', 'viewer')),
    created_at TIMESTAMP DEFAULT NOW()
);

-- ─── Categories Table ─────────────────────────────────────────────────────
-- Notice categories for organizing announcements (e.g., Academic, Events).
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- ─── Notices Table ────────────────────────────────────────────────────────
-- Core table for all notice board postings.
-- Supports: scheduling (publish_at, expires_at), priority levels, and status
-- lifecycle (draft → published → archived).
CREATE TABLE IF NOT EXISTS notices (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    posted_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    priority VARCHAR(20) DEFAULT 'normal'
        CHECK (priority IN ('normal', 'urgent')),
    status VARCHAR(20) DEFAULT 'draft'
        CHECK (status IN ('draft', 'published', 'archived')),
    publish_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- ─── Attachments Table ────────────────────────────────────────────────────
-- File attachments linked to notices (PDF, JPG, PNG — max 5MB per config).
CREATE TABLE IF NOT EXISTS attachments (
    id SERIAL PRIMARY KEY,
    notice_id INTEGER REFERENCES notices(id) ON DELETE CASCADE,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT NOW()
);

-- ─── Activity Logs Table ──────────────────────────────────────────────────
-- Audit trail tracking all admin actions on notices (create, edit, delete,
-- publish, archive) for accountability and reporting.
CREATE TABLE IF NOT EXISTS activity_logs (
    id SERIAL PRIMARY KEY,
    admin_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    notice_id INTEGER REFERENCES notices(id) ON DELETE SET NULL,
    details TEXT,
    timestamp TIMESTAMP DEFAULT NOW()
);

-- ─── Indexes ──────────────────────────────────────────────────────────────
-- Performance indexes for frequently queried columns.
CREATE INDEX IF NOT EXISTS idx_notices_status ON notices(status);
CREATE INDEX IF NOT EXISTS idx_notices_publish_at ON notices(publish_at);
CREATE INDEX IF NOT EXISTS idx_notices_expires_at ON notices(expires_at);
CREATE INDEX IF NOT EXISTS idx_notices_category ON notices(category_id);
CREATE INDEX IF NOT EXISTS idx_activity_logs_timestamp ON activity_logs(timestamp);
