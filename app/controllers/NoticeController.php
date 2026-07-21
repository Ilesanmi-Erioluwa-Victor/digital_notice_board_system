<?php
/**
 * NoticeController — Handles all notice CRUD operations and API endpoints.
 *
 * Admin routes manage the full lifecycle; API routes return JSON for AJAX polling.
 * Every state-changing action logs to activity_logs and validates CSRF.
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Models\Notice;
use App\Models\Attachment;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\User;

class NoticeController
{
    private Notice $noticeModel;
    private Attachment $attachmentModel;
    private ActivityLog $logModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->noticeModel     = new Notice();
        $this->attachmentModel = new Attachment();
        $this->logModel        = new ActivityLog();
        $this->categoryModel   = new Category();
    }

    /**
     * List all notices (admin) with optional status/category filters.
     * GET /admin/notices
     */
    public function index(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);

        $page    = (int) ($_GET['page'] ?? 1);
        $status  = $_GET['status'] ?? '';
        $catId   = isset($_GET['category']) ? (int) $_GET['category'] : null;
        $result  = $this->noticeModel->getPaginated($page, 15, $status, $catId);
        $categories = $this->categoryModel->all();

        extract($result);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/admin/notices-list.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Show the notice creation form.
     * GET /admin/notices/create
     */
    public function createForm(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/admin/notice-form.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Process notice creation with file upload handling.
     * POST /admin/notices/create
     */
    public function create(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices/create');
            exit;
        }

        $user = Auth::currentUser();
        $noticeId = $this->noticeModel->create([
            'title'       => $_POST['title'] ?? '',
            'body'        => $_POST['body'] ?? '',
            'category_id' => $_POST['category_id'] ? (int) $_POST['category_id'] : null,
            'posted_by'   => $user['id'],
            'priority'    => $_POST['priority'] ?? 'normal',
            'status'      => $_POST['status'] ?? 'draft',
            'publish_at'  => $_POST['publish_at'] ?: null,
            'expires_at'  => $_POST['expires_at'] ?: null,
        ]);

        // Handle file upload
        if (!empty($_FILES['attachment']['name'])) {
            $this->handleFileUpload($noticeId);
        }

        $this->logModel->log($user['id'], 'created', $noticeId, 'Created notice: ' . $_POST['title']);
        $_SESSION['success'] = 'Notice created successfully.';
        header('Location: /admin/notices');
        exit;
    }

    /**
     * Show the notice edit form pre-filled.
     * GET /admin/notices/edit/{id}
     */
    public function editForm(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);
        $id = (int) ($params['id'] ?? 0);
        $notice = $this->noticeModel->findById($id);
        if (!$notice) {
            http_response_code(404);
            echo 'Notice not found.';
            return;
        }
        $categories = $this->categoryModel->all();
        $attachments = $this->attachmentModel->findByNoticeId($id);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/admin/notice-form.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Update an existing notice.
     * POST /admin/notices/edit/{id}
     */
    public function update(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $this->noticeModel->update($id, [
            'title'       => $_POST['title'] ?? '',
            'body'        => $_POST['body'] ?? '',
            'category_id' => $_POST['category_id'] ? (int) $_POST['category_id'] : null,
            'priority'    => $_POST['priority'] ?? 'normal',
            'status'      => $_POST['status'] ?? 'draft',
            'publish_at'  => $_POST['publish_at'] ?: null,
            'expires_at'  => $_POST['expires_at'] ?: null,
        ]);

        // Handle new file upload if provided
        if (!empty($_FILES['attachment']['name'])) {
            $this->handleFileUpload($id);
        }

        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'created', $noticeId, 'Created notice: ' . $_POST['title']);

        // Send email notifications if the notice is published immediately
        if (($_POST['status'] ?? '') === 'published') {
            $this->sendEmailNotifications($noticeId, $_POST['title']);
        }

        $_SESSION['success'] = 'Notice created successfully.';
        header('Location: /admin/notices');
        exit;
    }

    /**
     * Delete a notice and its attachment file.
     * POST /admin/notices/delete/{id}
     */
    public function delete(array $params = []): void
    {
        Auth::requireAuth(['super_admin', 'admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);

        // Remove attachment files from disk
        $attachments = $this->attachmentModel->findByNoticeId($id);
        foreach ($attachments as $att) {
            $filePath = __DIR__ . '/../../' . $att['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->attachmentModel->delete($att['id']);
        }

        $this->noticeModel->delete($id);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'edited', $id, 'Edited notice ID ' . $id);

        // Send email notifications if status changed to published
        if (($_POST['status'] ?? '') === 'published') {
            $this->sendEmailNotifications($id, $_POST['title'] ?? '');
        }

        $_SESSION['success'] = 'Notice updated successfully.';
        header('Location: /admin/notices');
        exit;
    }

    /**
     * JSON endpoint returning active published notices.
     * Used by AJAX polling on public pages and kiosk display.
     * GET /api/notices/active
     */
    public function apiActive(array $params = []): void
    {
        header('Content-Type: application/json');
        $notices = $this->noticeModel->getActive();
        echo json_encode($notices);
    }

    /**
     * JSON endpoint for keyword search across notices.
     * GET /api/notices/search?q=
     */
    public function apiSearch(array $params = []): void
    {
        header('Content-Type: application/json');
        $keyword = $_GET['q'] ?? '';
        if (empty(trim($keyword))) {
            echo json_encode([]);
            return;
        }
        $results = $this->noticeModel->search($keyword);
        echo json_encode($results);
    }

    /**
     * Send email notifications to all viewer users about a new notice.
     * Runs synchronously within the request for project scope simplicity.
     *
     * @param int    $noticeId
     * @param string $noticeTitle
     */
    private function sendEmailNotifications(int $noticeId, string $noticeTitle): void
    {
        $mailer = new Mailer();
        $userModel = new User();
        $viewers = $userModel->all();

        $noticeUrl = APP_URL . '/notice/' . $noticeId;

        foreach ($viewers as $viewer) {
            if ($viewer['role'] === 'viewer') {
                $mailer->sendNoticeNotification(
                    $viewer['email'],
                    $viewer['name'],
                    $noticeTitle,
                    $noticeUrl
                );
            }
        }
    }

    /**
     * Handle validated file upload for notice attachments.
     * Validates type (pdf, jpg, png) and size (max 5MB).
     *
     * @param int $noticeId
     */
    private function handleFileUpload(int $noticeId): void
    {
        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        // Validate file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'png'];
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = 'Invalid file type. Only PDF, JPG, PNG allowed.';
            return;
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'File too large. Maximum size is 5MB.';
            return;
        }

        // Generate unique filename and move to uploads directory
        $uploadDir = __DIR__ . '/../../assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('notice_') . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->attachmentModel->create(
                $noticeId,
                'assets/uploads/' . $filename,
                $ext
            );
        }
    }
}
