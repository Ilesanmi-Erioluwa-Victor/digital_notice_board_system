-- ==========================================================================
-- Digital Notice Board System — Sample Seed Data
-- ==========================================================================
-- Run: psql -d digital_notice_board -f sql/seed.sql
-- ==========================================================================

TRUNCATE users, roles, faculties, departments, programmes, levels, categories, notices, notice_attachments, notice_views, bookmarks, notifications, activity_logs, password_resets RESTART IDENTITY CASCADE;

-- ─── Roles ─────────────────────────────────────────────────────────────────
INSERT INTO roles (name, description) VALUES
    ('admin', 'Full system access: manage users, approve notices, view analytics'),
    ('staff', 'Can create and submit notices for approval'),
    ('student', 'Can view notices, bookmark, and track reading');

-- ─── Faculties ─────────────────────────────────────────────────────────────
INSERT INTO faculties (name, code, description) VALUES
    ('Faculty of Science', 'SCI', 'Natural and applied sciences'),
    ('Faculty of Engineering', 'ENG', 'Engineering and technology'),
    ('Faculty of Arts', 'ART', 'Humanities and liberal arts');

-- ─── Departments ───────────────────────────────────────────────────────────
INSERT INTO departments (name, code, faculty_id, description) VALUES
    ('Computer Science', 'CSC', 1, 'Computer science and informatics'),
    ('Mathematics', 'MTH', 1, 'Pure and applied mathematics'),
    ('Electrical Engineering', 'ELE', 2, 'Electrical and electronics engineering'),
    ('Mechanical Engineering', 'MEC', 2, 'Mechanical and industrial engineering'),
    ('English', 'ENG', 3, 'English language and literature');

-- ─── Programmes ────────────────────────────────────────────────────────────
INSERT INTO programmes (name, code, department_id, duration_years) VALUES
    ('B.Sc. Computer Science', 'BSC-CSC', 1, 4),
    ('B.Sc. Mathematics', 'BSC-MTH', 2, 4),
    ('B.Eng. Electrical Engineering', 'BENG-ELE', 3, 5),
    ('B.Eng. Mechanical Engineering', 'BENG-MEC', 4, 5),
    ('B.A. English', 'BA-ENG', 5, 4);

-- ─── Levels ────────────────────────────────────────────────────────────────
INSERT INTO levels (name, sort_order) VALUES
    ('100 Level', 1),
    ('200 Level', 2),
    ('300 Level', 3),
    ('400 Level', 4),
    ('500 Level', 5);

-- ─── Users ─────────────────────────────────────────────────────────────────
INSERT INTO users (name, email, password_hash, role, staff_id, department_id, programme_id, level_id, is_active) VALUES
    ('System Admin', 'admin@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'admin', 'ADM001', 1, NULL, NULL, TRUE),
    ('Dr. Jane Staff', 'staff@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'staff', 'STF001', 1, NULL, NULL, TRUE),
    ('Mr. Bob Staff', 'staff2@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'staff', 'STF002', 3, NULL, NULL, TRUE),
    ('Alice Student', 'student@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'student', NULL, 1, 1, 3, TRUE),
    ('Bob Student', 'student2@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'student', NULL, 3, 3, 3, TRUE);

-- ─── Categories ────────────────────────────────────────────────────────────
INSERT INTO categories (name, description) VALUES
    ('Academic', 'Academic announcements including results, schedules, and calendar updates'),
    ('Administrative', 'Administrative notices from the management'),
    ('Events', 'Upcoming events, seminars, workshops, and social gatherings'),
    ('Exams', 'Examination timetables, guidelines, and results'),
    ('General', 'General announcements and miscellaneous information');

-- ─── Notices ───────────────────────────────────────────────────────────────
INSERT INTO notices (title, body, category_id, posted_by, priority, status, approval_status, approved_by, is_pinned, target_audience_type, publish_at, expires_at, created_at) VALUES
    ('Semester Exam Timetable',
     'The examination timetable has been published. Check your schedules and report any clashes.',
     4, 1, 'high', 'published', 'approved', 1, TRUE, 'everyone',
     NOW() - INTERVAL '1 day', NOW() + INTERVAL '30 days', NOW() - INTERVAL '5 days'),

    ('Building B Closure',
     'Building B will be closed for maintenance from Friday 6 PM to Monday 6 AM.',
     2, 2, 'high', 'published', 'approved', 1, FALSE, 'everyone',
     NOW() - INTERVAL '2 days', NOW() + INTERVAL '7 days', NOW() - INTERVAL '6 days'),

    ('Annual Sports Day',
     'Registration is open for the Annual Sports Day. Sign up at the Sports Office.',
     3, 2, 'medium', 'published', 'approved', 1, FALSE, 'everyone',
     NOW() - INTERVAL '3 days', NOW() + INTERVAL '60 days', NOW() - INTERVAL '7 days'),

    ('New Library Hours',
     'Library hours extended during exams: 7 AM to 10 PM weekdays.',
     1, 1, 'low', 'published', 'approved', 1, FALSE, 'everyone',
     NOW() - INTERVAL '4 days', NULL, NOW() - INTERVAL '8 days'),

    ('ICT Workshop',
     'Three-day workshop on Web Development. Open to all students. No experience needed.',
     3, 2, 'medium', 'published', 'approved', 1, FALSE, 'students',
     NOW() - INTERVAL '1 day', NOW() + INTERVAL '14 days', NOW() - INTERVAL '3 days'),

    ('Staff Vacancy',
     'Applications invited for Senior Lecturer position in Computer Science.',
     2, 1, 'high', 'published', 'approved', 1, FALSE, 'staff',
     NOW(), NOW() + INTERVAL '30 days', NOW() - INTERVAL '2 days'),

    ('Draft: Proposed Calendar',
     'The proposed academic calendar for next session is under review.',
     1, 2, 'medium', 'draft', 'none', NULL, FALSE, 'everyone',
     NULL, NULL, NOW() - INTERVAL '1 day'),

    ('Pending: Lab Maintenance',
     'Requesting approval for lab equipment maintenance schedule.',
     2, 3, 'medium', 'pending', 'pending', NULL, FALSE, 'department',
     NULL, NULL, NOW());

-- ─── Activity Logs ─────────────────────────────────────────────────────────
INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, timestamp) VALUES
    (1, 'approve', 'notice', 1, 'Approved notice: Semester Exam Timetable', NOW() - INTERVAL '1 day'),
    (1, 'approve', 'notice', 2, 'Approved notice: Building B Closure', NOW() - INTERVAL '2 days'),
    (2, 'create', 'notice', 5, 'Created notice: ICT Workshop', NOW() - INTERVAL '3 days'),
    (1, 'approve', 'notice', 5, 'Approved notice: ICT Workshop', NOW() - INTERVAL '2 days'),
    (3, 'create', 'notice', 8, 'Created notice: Lab Maintenance', NOW());
