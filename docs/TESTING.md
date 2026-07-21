# Testing Documentation

## Test Cases and Results

| # | Test Case | Steps | Expected Result | Actual Result | Pass/Fail |
|---|-----------|-------|-----------------|---------------|-----------|
| 1 | Admin creates category | 1. Login as super_admin<br>2. Navigate to Admin > Categories<br>3. Enter category name "Test Category"<br>4. Click Save | New category appears in the categories list | | |
| 2 | Admin creates notice (draft) | 1. Login as admin<br>2. Navigate to Admin > Notices<br>3. Click "New Notice"<br>4. Fill title, body, select category<br>5. Set status to "Draft"<br>6. Click "Create Notice" | Notice appears in notices list with "draft" status badge. Activity log records "created" action. | | |
| 3 | Admin publishes notice | 1. From Test 2, edit the draft notice<br>2. Set status to "Published"<br>3. Set publish_at to current time<br>4. Click "Update Notice" | Notice status changes to "published". Activity log records "edited" action. | | |
| 4 | Public home page shows notice | 1. Logout or open incognito window<br>2. Navigate to `/` | Published notice appears in the notice grid within 30 seconds via AJAX polling. | | |
| 5 | Search functionality | 1. On public home page<br>2. Type keyword from notice title<br>3. Click Search | Notices matching the keyword are displayed. | | |
| 6 | Category filter | 1. On public home page<br>2. Select a category from the dropdown | Page reloads showing only notices from the selected category. | | |
| 7 | Notice detail page | 1. Click on a notice title | Full notice content is displayed with category badge, priority badge, author name, and date. | | |
| 8 | Admin edits notice | 1. Login as admin<br>2. Navigate to Admin > Notices<br>3. Click "Edit" on a notice<br>4. Modify the title<br>5. Click "Update Notice" | Updated title reflects on public page. Activity log records "edited" action. | | |
| 9 | Admin deletes notice | 1. Login as admin<br>2. Navigate to Admin > Notices<br>3. Click "Delete" on a notice<br>4. Confirm deletion | Notice disappears from admin list and public view. Activity log records "deleted" action. | | |
| 10 | File upload validation (type) | 1. Create/edit a notice<br>2. Attempt to upload a .txt file | Rejected with error: "Invalid file type. Only PDF, JPG, PNG allowed." | | |
| 11 | File upload validation (size) | 1. Create/edit a notice<br>2. Attempt to upload a file > 5MB | Rejected with error: "File too large. Maximum size is 5MB." | | |
| 12 | Viewer access restriction | 1. Login as viewer<br>2. Navigate to `/admin/dashboard` | Redirected to login page or access denied. | | |
| 13 | Admin cannot access user management | 1. Login as admin (not super_admin)<br>2. Navigate to `/admin/users` | Redirected with "unauthorized" error. | | |
| 14 | CSRF protection | 1. Submit any POST form without a CSRF token | Form rejected with "Invalid security token" message. | | |
| 15 | Responsiveness (iPhone SE) | 1. Open public home page in 375x667 viewport | Single column layout, hamburger menu visible, stacked notice cards. | | |
| 16 | Responsiveness (iPhone 14 Pro) | 1. Open public home page in 390x844 viewport | Single column layout, all buttons have 44px min-height touch targets. | | |
| 17 | Responsiveness (iPad) | 1. Open in 820x1180 viewport | 2-column notice grid, expanded navigation. | | |
| 18 | Responsiveness (1920x1080) | 1. Open in 1920x1080 viewport | 3-column notice grid, sidebar + main layout for admin. | | |
| 19 | Kiosk mode display | 1. Navigate to `/kiosk` | Full-screen display with auto-cycling notices (9-second rotation), clock in corner, fade transitions. | | |
| 20 | AJAX polling | 1. Open public home page<br>2. Admin publishes a new notice in another browser<br>3. Wait up to 30 seconds | New notice appears in the grid without page refresh. | | |
| 21 | Email notification | 1. Configure MAIL settings in .env<br>2. Publish a new notice as admin | Viewer users receive email notification with notice title and link. | | |
| 22 | Unread badge | 1. View a notice on public page<br>2. Admin publishes a new notice<br>3. Check the Home nav link | Unread badge displays count of new notices since last visit. | | |
| 23 | Activity log tracking | 1. Perform create, edit, delete actions<br>2. Navigate to Admin > Activity Logs | All actions are recorded with timestamp, admin name, action type, and details. | | |
| 24 | Seed data verification | 1. Run schema.sql and seed.sql<br>2. Login as admin@example.com / password123 | Dashboard shows 8 notices, 5 categories, 6 users. | | |

## Responsiveness Test Matrix

| Device | Resolution | Layout | Nav | Notice Grid | Touch Targets |
|--------|-----------|--------|-----|-------------|---------------|
| iPhone SE | 375x667 | Single column | Hamburger | 1 column | ≥44px |
| iPhone 14 Pro | 390x844 | Single column | Hamburger | 1 column | ≥44px |
| iPad | 820x1180 | Tablet | Expanded | 2 columns | ≥44px |
| Desktop HD | 1920x1080 | Desktop | Expanded | 3 columns | — |
| Desktop 4K | 2560x1440 | Large Desktop | Expanded | 3 columns | — |
| Kiosk | 1920x1080 | Full-screen kiosk | Hidden | 1-at-a-time rotation | — |

## Browser Compatibility

Tested on:
- Google Chrome (latest)
- Mozilla Firefox (latest)
- Apple Safari (latest)
- Microsoft Edge (latest)
