<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\Database;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Models\NoticeView;
use App\Models\Bookmark;
use App\Models\ArchivedNotice;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Notification;
use App\Models\User;

class NoticeController
{
    private Notice $noticeModel;
    private NoticeAttachment $attachmentModel;
    private NoticeView $noticeViewModel;
    private Bookmark $bookmarkModel;
    private ArchivedNotice $archivedNoticeModel;
    private ActivityLog $logModel;
    private Category $categoryModel;
    private Notification $notificationModel;

    public function __construct()
    {
        $this->noticeModel       = new Notice();
        $this->attachmentModel   = new NoticeAttachment();
        $this->noticeViewModel   = new NoticeView();
        $this->bookmarkModel     = new Bookmark();
        $this->archivedNoticeModel = new ArchivedNotice();
        $this->logModel          = new ActivityLog();
        $this->categoryModel     = new Category();
        $this->notificationModel = new Notification();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin', 'staff']);

        $page    = (int) ($_GET['page'] ?? 1);
        $status  = $_GET['status'] ?? '';
        $catId   = isset($_GET['category']) ? (int) $_GET['category'] : null;
        $result  = $this->noticeModel->getPaginated($page, 15, $status, $catId);
        $categories = $this->categoryModel->all();

        extract($result);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/notices-list.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function createForm(array $params = []): void
    {
        Auth::requireAuth(['admin', 'staff']);
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/notice-form.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function create(array $params = []): void
    {
        Auth::requireAuth(['admin', 'staff']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices/create');
            exit;
        }

        $user    = Auth::currentUser();
        $isAdmin = $user['role'] === 'admin';

        $data = [
            'title'                => $_POST['title'] ?? '',
            'body'                 => $_POST['body'] ?? '',
            'category_id'          => $_POST['category_id'] ? (int) $_POST['category_id'] : null,
            'posted_by'            => $user['id'],
            'priority'             => $_POST['priority'] ?? 'medium',
            'publish_at'           => $_POST['publish_at'] ?: null,
            'expires_at'           => $_POST['expires_at'] ?: null,
            'is_pinned'            => isset($_POST['is_pinned']) ? (bool) $_POST['is_pinned'] : false,
            'target_audience_type' => $_POST['target_audience_type'] ?? 'everyone',
            'target_ids'           => isset($_POST['target_ids']) ? (array) $_POST['target_ids'] : [],
        ];

        if ($isAdmin) {
            $data['status']          = $_POST['status'] ?? 'published';
            $data['approval_status'] = $data['status'] === 'published' ? 'approved' : ($_POST['approval_status'] ?? 'none');
        } else {
            $data['status']          = 'pending';
            $data['approval_status'] = 'pending';
        }

        $noticeId = $this->noticeModel->create($data);

        if (!empty($_FILES['attachment']['name'])) {
            $this->handleFileUpload($noticeId);
        }

        $this->logModel->log($user['id'], 'created', 'notice', $noticeId, 'Created notice: ' . $_POST['title']);

        if ($data['status'] === 'published') {
            $this->sendEmailNotifications($noticeId, $_POST['title'] ?? '');
        }

        $_SESSION['success'] = 'Notice created successfully.';
        header('Location: /admin/notices');
        exit;
    }

    public function editForm(array $params = []): void
    {
        Auth::requireAuth(['admin', 'staff']);
        $id = (int) ($params['id'] ?? 0);
        $notice = $this->noticeModel->findById($id);
        if (!$notice) {
            http_response_code(404);
            echo 'Notice not found.';
            return;
        }
        $categories = $this->categoryModel->all();
        $attachments = $this->attachmentModel->findByNoticeId($id);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/notice-form.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function update(array $params = []): void
    {
        Auth::requireAuth(['admin', 'staff']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);

        $data = [
            'title'                => $_POST['title'] ?? '',
            'body'                 => $_POST['body'] ?? '',
            'category_id'          => $_POST['category_id'] ? (int) $_POST['category_id'] : null,
            'priority'             => $_POST['priority'] ?? 'medium',
            'publish_at'           => $_POST['publish_at'] ?: null,
            'expires_at'           => $_POST['expires_at'] ?: null,
            'is_pinned'            => isset($_POST['is_pinned']) ? (bool) $_POST['is_pinned'] : false,
            'target_audience_type' => $_POST['target_audience_type'] ?? 'everyone',
        ];

        if (isset($_POST['target_ids'])) {
            $data['target_ids'] = (array) $_POST['target_ids'];
        }

        $user = Auth::currentUser();
        if ($user['role'] === 'admin' && isset($_POST['status'])) {
            $data['status'] = $_POST['status'];
        }

        $this->noticeModel->update($id, $data);

        if (!empty($_FILES['attachment']['name'])) {
            $this->handleFileUpload($id);
        }

        $this->logModel->log($user['id'], 'edited', 'notice', $id, 'Edited notice ID ' . $id);

        if (($_POST['status'] ?? '') === 'published') {
            $this->sendEmailNotifications($id, $_POST['title'] ?? '');
        }

        $_SESSION['success'] = 'Notice updated successfully.';
        header('Location: /admin/notices');
        exit;
    }

    public function delete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);

        $attachments = $this->attachmentModel->findByNoticeId($id);
        foreach ($attachments as $att) {
            $filePath = __DIR__ . '/../../' . $att['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->attachmentModel->delete($att['id']);
        }

        $db = Database::getInstance();
        $db->execute('DELETE FROM notice_views WHERE notice_id = :id', ['id' => $id]);
        $db->execute('DELETE FROM bookmarks WHERE notice_id = :id', ['id' => $id]);
        $db->execute('DELETE FROM notifications WHERE link LIKE :pattern', ['pattern' => '%/notice/' . $id . '%']);

        $this->noticeModel->delete($id);
        $user = Auth::currentUser();
        $this->logModel->log($user['id'], 'deleted', 'notice', $id, 'Deleted notice ID ' . $id);

        $_SESSION['success'] = 'Notice deleted successfully.';
        header('Location: /admin/notices');
        exit;
    }

    public function apiActive(array $params = []): void
    {
        header('Content-Type: application/json');
        $notices = $this->noticeModel->getActive();
        echo json_encode($notices);
    }

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

    public function approve(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id   = (int) ($params['id'] ?? 0);
        $user = Auth::currentUser();

        $this->noticeModel->approve($id, $user['id']);
        $this->logModel->log($user['id'], 'approved', 'notice', $id, 'Approved notice ID ' . $id);

        $notice = $this->noticeModel->findById($id);
        if ($notice) {
            $this->notificationModel->create(
                (int) $notice['posted_by'],
                'notice_approved',
                'Notice Approved',
                'Your notice "' . $notice['title'] . '" has been approved.',
                '/notice/' . $id
            );
        }

        $_SESSION['success'] = 'Notice approved successfully.';
        header('Location: /admin/notices');
        exit;
    }

    public function reject(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id     = (int) ($params['id'] ?? 0);
        $reason = $_POST['rejection_reason'] ?? '';
        $user   = Auth::currentUser();

        $this->noticeModel->reject($id, $user['id'], $reason);
        $this->logModel->log($user['id'], 'rejected', 'notice', $id, 'Rejected notice ID ' . $id . ' reason: ' . $reason);

        $notice = $this->noticeModel->findById($id);
        if ($notice) {
            $this->notificationModel->create(
                (int) $notice['posted_by'],
                'notice_rejected',
                'Notice Rejected',
                'Your notice "' . $notice['title'] . '" has been rejected.' . ($reason ? ' Reason: ' . $reason : ''),
                '/notice/' . $id
            );
        }

        $_SESSION['success'] = 'Notice rejected.';
        header('Location: /admin/notices');
        exit;
    }

    public function duplicate(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id   = (int) ($params['id'] ?? 0);
        $user = Auth::currentUser();

        $newId = $this->noticeModel->duplicate($id);
        if ($newId) {
            $this->logModel->log($user['id'], 'duplicated', 'notice', $newId, 'Duplicated notice ID ' . $id . ' as notice ID ' . $newId);
            $_SESSION['success'] = 'Notice duplicated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to duplicate notice.';
        }

        header('Location: /admin/notices');
        exit;
    }

    public function pin(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/notices');
            exit;
        }

        $id   = (int) ($params['id'] ?? 0);
        $user = Auth::currentUser();

        $notice = $this->noticeModel->findById($id);
        if (!$notice) {
            http_response_code(404);
            echo 'Notice not found.';
            return;
        }

        $newPinned = !$notice['is_pinned'];
        $this->noticeModel->update($id, ['is_pinned' => $newPinned]);
        $this->logModel->log($user['id'], 'pinned', 'notice', $id, ($newPinned ? 'Pinned' : 'Unpinned') . ' notice ID ' . $id);

        $_SESSION['success'] = $newPinned ? 'Notice pinned successfully.' : 'Notice unpinned successfully.';
        header('Location: /admin/notices');
        exit;
    }

    public function pending(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $notices       = $this->noticeModel->getPending();
        $categories    = $this->categoryModel->all();
        $pendingFilter = 'pending';
        $page          = 1;
        $pages         = 1;

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/notices-list.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function show(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);

        $notice = $this->noticeModel->findById($id);
        if (!$notice) {
            http_response_code(404);
            require __DIR__ . '/../Views/layouts/header.php';
            echo '<p class="text-center mt-5">Notice not found.</p>';
            require __DIR__ . '/../Views/layouts/footer.php';
            return;
        }

        $user = Auth::currentUser();
        if ($user) {
            $this->noticeViewModel->trackView($id, (int) $user['id']);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            try {
                $this->noticeViewModel->trackView($id, null, $ip);
            } catch (\Throwable $e) {
            }
        }

        $attachments  = $this->attachmentModel->findByNoticeId($id);
        $isBookmarked = $user ? $this->bookmarkModel->isBookmarked((int) $user['id'], $id) : false;

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/public/notice-detail.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function bookmark(array $params = []): void
    {
        header('Content-Type: application/json');
        Auth::requireAuth();

        $id   = (int) ($params['id'] ?? 0);
        $user = Auth::currentUser();

        $result = $this->bookmarkModel->toggle((int) $user['id'], $id);
        echo json_encode(['bookmarked' => $result['bookmarked']]);
    }

    public function unbookmark(array $params = []): void
    {
        header('Content-Type: application/json');
        Auth::requireAuth();

        $id   = (int) ($params['id'] ?? 0);
        $user = Auth::currentUser();

        $db = Database::getInstance();
        $db->execute(
            'DELETE FROM bookmarks WHERE user_id = :user_id AND notice_id = :notice_id',
            ['user_id' => $user['id'], 'notice_id' => $id]
        );

        echo json_encode(['bookmarked' => false]);
    }

    public function archive(array $params = []): void
    {
        header('Content-Type: application/json');
        Auth::requireAuth();

        $id   = (int) ($params['id'] ?? 0);
        $user = Auth::currentUser();

        $result = $this->archivedNoticeModel->toggle((int) $user['id'], $id);
        echo json_encode(['archived' => $result['archived']]);
    }

    public function apiBookmarks(array $params = []): void
    {
        header('Content-Type: application/json');
        Auth::requireAuth();

        $user = Auth::currentUser();
        $rows = $this->bookmarkModel->getByUser((int) $user['id']);
        echo json_encode($rows);
    }

    public function apiArchived(array $params = []): void
    {
        header('Content-Type: application/json');
        Auth::requireAuth();

        $user = Auth::currentUser();
        $rows = $this->archivedNoticeModel->getByUser((int) $user['id']);
        echo json_encode($rows);
    }

    public function apiCalendar(array $params = []): void
    {
        header('Content-Type: application/json');

        $notices = $this->noticeModel->getUpcomingEvents(50);

        $events = array_map(function ($notice) {
            $event = [
                'id'    => (int) $notice['id'],
                'title' => $notice['title'],
                'start' => $notice['publish_at'],
            ];
            if (!empty($notice['expires_at'])) {
                $event['end'] = $notice['expires_at'];
            }
            return $event;
        }, $notices);

        echo json_encode($events);
    }

    private function sendEmailNotifications(int $noticeId, string $noticeTitle): void
    {
        $mailer = new Mailer();
        $userModel = new User();
        $users = $userModel->all();

        $noticeUrl = APP_URL . '/notice/' . $noticeId;

        foreach ($users as $user) {
            if ($user['role'] === 'student') {
                $mailer->sendNoticeNotification(
                    (int) $user['id'],
                    $user['email'],
                    $user['name'],
                    $noticeTitle,
                    $noticeUrl
                );
            }
        }
    }

    private function handleFileUpload(int $noticeId): void
    {
        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'docx', 'jpg', 'png', 'gif', 'zip'];
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = 'Invalid file type. Only PDF, DOCX, JPG, PNG, GIF, ZIP allowed.';
            return;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $_SESSION['error'] = 'File too large. Maximum size is 10MB.';
            return;
        }

        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
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
