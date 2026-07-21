-- ==========================================================================
-- Digital Notice Board System — Sample Seed Data
-- ==========================================================================
-- Run: psql -d digital_notice_board -f sql/seed.sql
-- ==========================================================================

-- Clear existing data and reset auto-increment sequences
TRUNCATE users, categories, notices, attachments, activity_logs RESTART IDENTITY CASCADE;

-- ─── Users ─────────────────────────────────────────────────────────────────
-- Passwords are hashed with password_hash('password123', PASSWORD_DEFAULT).
-- The hashes below correspond to "password123" for all test accounts.
INSERT INTO users (name, email, password_hash, role) VALUES
    ('Super Admin', 'admin@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'super_admin'),
    ('Department Admin', 'deptadmin@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'admin'),
    ('Admin User Two', 'admin2@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'admin'),
    ('John Viewer', 'viewer@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'viewer'),
    ('Jane Viewer', 'jane@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'viewer'),
    ('Bob Viewer', 'bob@example.com',
     '$2y$10$wqT.BKaJ.kN9Buls6OMKS.jUkg2KRLdsa7K3qx6wJmkEAvnGU6wDC',
     'viewer');

-- ─── Categories ────────────────────────────────────────────────────────────
INSERT INTO categories (name, description) VALUES
    ('Academic', 'Academic announcements including results, schedules, and academic calendar updates'),
    ('Administrative', 'Administrative notices from the management and administrative office'),
    ('Events', 'Upcoming events, seminars, workshops, and social gatherings'),
    ('Exams', 'Examination timetables, guidelines, and results release information'),
    ('General', 'General announcements and miscellaneous information');

-- ─── Notices ───────────────────────────────────────────────────────────────
-- 8 sample notices with varying statuses and priorities for testing.
INSERT INTO notices (title, body, category_id, posted_by, priority, status, publish_at, expires_at, created_at) VALUES
    ('Semester Examination Timetable Released',
     'The examination timetable for the current semester has been published. All students are advised to check their examination schedules and report any clashes to the Academic Office before the deadline. The examination period will run from the 15th to the 30th of next month.',
     4, 1, 'urgent', 'published',
     NOW() - INTERVAL '1 day', NOW() + INTERVAL '30 days',
     NOW() - INTERVAL '5 days'),

    ('Maintenance Notice: Building B Closure',
     'Building B will be closed for maintenance from Friday 6:00 PM to Monday 6:00 AM. All offices and classrooms in Building B will be inaccessible during this period. Staff and students are advised to make alternative arrangements.',
     2, 2, 'urgent', 'published',
     NOW() - INTERVAL '2 days', NOW() + INTERVAL '7 days',
     NOW() - INTERVAL '6 days'),

    ('Annual Sports Day Registration',
     'Registration for the Annual Sports Day is now open. Interested participants should sign up at the Sports Office or register online via the student portal. Events include athletics, football, basketball, and table tennis.',
     3, 3, 'normal', 'published',
     NOW() - INTERVAL '3 days', NOW() + INTERVAL '60 days',
     NOW() - INTERVAL '7 days'),

    ('New Library Operating Hours',
     'The library will be extending its operating hours during the examination period. New hours will be 7:00 AM to 10:00 PM, Monday through Saturday, and 12:00 PM to 8:00 PM on Sundays.',
     1, 1, 'normal', 'published',
     NOW() - INTERVAL '4 days', NULL,
     NOW() - INTERVAL '8 days'),

    ('ICT Workshop: Introduction to Web Development',
     'The ICT department is organizing a 3-day workshop on Introduction to Web Development using HTML, CSS, and JavaScript. The workshop is open to all students and will be held in the ICT Lab, Room 204. No prior programming experience required.',
     3, 2, 'normal', 'published',
     NOW() - INTERVAL '1 day', NOW() + INTERVAL '14 days',
     NOW() - INTERVAL '3 days'),

    ('Staff Vacancy Announcement',
     'Applications are invited for the position of Senior Lecturer in the Department of Computer Science. Candidates must possess a PhD in Computer Science or a related field with at least 5 years of teaching experience. Application deadline is 30 days from today.',
     2, 1, 'normal', 'published',
     NOW(), NOW() + INTERVAL '30 days',
     NOW() - INTERVAL '2 days'),

    ('Draft Notice: Proposed Academic Calendar 2025',
     'The proposed academic calendar for the 2025 session is under review. Stakeholders are invited to submit feedback to the Academic Office before the final approval.',
     1, 1, 'normal', 'draft',
     NULL, NULL,
     NOW() - INTERVAL '1 day'),

    ('Archived Notice: Previous Semester Results',
     'This notice contained the results for the previous semester and has been archived. Current semester results will be published soon.',
     4, 1, 'normal', 'archived',
     NOW() - INTERVAL '90 days', NOW() - INTERVAL '30 days',
     NOW() - INTERVAL '95 days');

-- ─── Activity Log ──────────────────────────────────────────────────────────
INSERT INTO activity_logs (admin_id, action, notice_id, details, timestamp) VALUES
    (1, 'published', 1, 'Published examination timetable', NOW() - INTERVAL '1 day'),
    (2, 'published', 2, 'Published maintenance notice for Building B', NOW() - INTERVAL '2 days'),
    (3, 'published', 3, 'Published sports day registration', NOW() - INTERVAL '3 days'),
    (1, 'published', 4, 'Published library new operating hours', NOW() - INTERVAL '4 days'),
    (2, 'published', 5, 'Published ICT workshop notice', NOW() - INTERVAL '1 day'),
    (1, 'published', 6, 'Published staff vacancy announcement', NOW()),
    (1, 'created', 7, 'Created draft academic calendar notice', NOW() - INTERVAL '1 day'),
    (1, 'archived', 8, 'Archived previous semester results', NOW() - INTERVAL '30 days');
