# User Manual

## How to Log In

1. Open the Digital Notice Board website in your browser.
2. Click the **Login** link in the top navigation bar.
3. Enter your email address and password.
4. Click the **Login** button.
5. Depending on your account role:
   - **Admin / Super Admin**: You will be redirected to the Admin Dashboard.
   - **Viewer**: You will be redirected to the public home page.

> If you forget your password, contact your system administrator to reset it.

## Public User Guide (Viewer)

### Browsing Notices

1. The **Home** page displays all active notices in a grid layout.
2. Notices marked with a red **URGENT** badge are high-priority announcements.
3. Each notice card shows:
   - Title (click to view full notice)
   - Short preview of the content
   - Category badge
   - Priority badge
   - Date posted

### Searching for Notices

1. On the Home page, type a keyword in the **Search** box.
2. Click the **Search** button or press **Enter**.
3. Matching notices will be displayed.
4. To clear the search, leave the search box empty and click Search again.

### Filtering by Category

1. On the Home page, click the **All Categories** dropdown.
2. Select a category (e.g., "Academic", "Events").
3. The page will reload showing only notices from that category.

### Viewing Notice Details

1. Click on a notice title to open the full notice detail page.
2. The detail page shows:
   - Full notice content
   - Category and priority badges
   - Author name
   - Date posted
   - Expiry date (if set)
   - Download links for any attached files
3. Click **Back to All Notices** to return to the home page.

### Downloading Attachments

1. Open a notice detail page that has attachments.
2. Click the **Download PDF/JPG/PNG** button next to the file.
3. The file will open or download depending on your browser settings.

### Kiosk Display Mode

The kiosk mode is designed for display on mounted screens or projectors:

1. Click **Kiosk** in the top navigation bar.
2. The page switches to full-screen mode with:
   - Large, readable text
   - Auto-cycling through all active notices every 9 seconds
   - A live clock display in the top-right corner
   - Fade transitions between notices
3. **Keyboard navigation**: Press the arrow keys (← ↑ → ↓) to manually move between notices.
4. To exit kiosk mode, press **Esc** or navigate away in your browser.

### Real-Time Updates

The public home page automatically checks for new notices every 30 seconds. When an admin publishes a new notice, it will appear on your screen without needing to refresh the page.

## Admin Guide

### Accessing the Admin Panel

1. Log in with an admin or super admin account.
2. Click **Admin** in the top navigation bar.
3. You will see the **Dashboard** with summary statistics.

### Dashboard

The dashboard displays:
- **Total Notices**: Count of all notices in the system
- **Active**: Number of currently published and active notices
- **Expired**: Number of expired notices
- **Drafts**: Number of saved drafts
- **Categories**: Total number of categories
- **Users**: Total number of registered users
- **Recent Activity**: A feed showing the latest actions performed by admins

### Creating a Notice

1. In the Admin Panel, click **Notices** in the sidebar.
2. Click the **+ New Notice** button.
3. Fill in the form:
   - **Title** (required): Enter a clear, descriptive title.
   - **Body** (required): Enter the full notice content.
   - **Category**: Select an appropriate category from the dropdown.
   - **Priority**: Choose "Normal" or "Urgent" (urgent notices are highlighted in red).
   - **Status**: Choose "Draft" to save without publishing, "Published" to make it visible immediately, or "Archived" to hide it.
   - **Publish At**: Set a future date/time for scheduled publishing (leave empty to publish immediately).
   - **Expires At**: Set a date/time when the notice should automatically expire.
   - **Attachment**: Upload a PDF, JPG, or PNG file (max 5MB).
4. Click **Create Notice**.

### Editing a Notice

1. In the Admin Panel, click **Notices** in the sidebar.
2. Find the notice you want to edit in the table.
3. Click the **Edit** button.
4. Modify any fields as needed.
5. Click **Update Notice**.

### Deleting a Notice

1. In the Admin Panel, click **Notices** in the sidebar.
2. Find the notice you want to delete.
3. Click the **Delete** button.
4. Confirm the deletion in the popup dialog.

### Managing Categories

1. In the Admin Panel, click **Categories** in the sidebar.
2. **Create**: Click **+ New Category**, enter a name and description, then click **Save Category**.
3. **Rename**: Type a new name in the inline text field next to a category, then click **Rename**.
4. **Delete**: Click the **Delete** button next to a category.

### Managing Users (Super Admin only)

1. In the Admin Panel, click **Users** in the sidebar.
2. The table shows all registered users with their names, emails, roles, and join dates.
3. **Change Role**: Select a new role from the dropdown and click **Change**.
4. **Delete User**: Click the **Delete** button (super admin accounts cannot be deleted).

### Viewing Activity Logs

1. In the Admin Panel, click **Activity Logs** in the sidebar.
2. The log displays a chronological list of all admin actions, including:
   - Timestamp of the action
   - Action type (created, edited, deleted, published, archived)
   - Brief description of what was done
   - Name of the admin who performed the action

## [SCREENSHOT: Admin Dashboard showing statistics cards and recent activity feed]

## [SCREENSHOT: Notice creation form with all fields]

## [SCREENSHOT: Public home page showing notice card grid]

## [SCREENSHOT: Kiosk display mode showing a single notice in full-screen]

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Cannot log in | Check that Caps Lock is off. Ensure you are using the correct email and password. Contact an admin if you've forgotten your password. |
| Notice not appearing on public page | Check that the notice status is "Published" and that the publish date has passed. Check if the notice has expired. |
| File upload fails | Ensure the file is a PDF, JPG, or PNG and is under 5MB in size. |
| Cannot access admin pages | You must be logged in with an admin or super admin account. Viewer accounts cannot access the admin panel. |
| Kiosk not rotating | Ensure JavaScript is enabled in your browser. Check the browser console for errors. |
