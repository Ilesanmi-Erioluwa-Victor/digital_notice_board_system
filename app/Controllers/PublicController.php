<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Notice;
use App\Models\Category;
use App\Models\NoticeAttachment;
use App\Models\NoticeView;
use App\Models\Bookmark;
use App\Models\ArchivedNotice;

class PublicController
{
    private Notice $noticeModel;
    private Category $categoryModel;
    private NoticeAttachment $attachmentModel;

    public function __construct()
    {
        $this->noticeModel     = new Notice();
        $this->categoryModel   = new Category();
        $this->attachmentModel = new NoticeAttachment();
    }

    public function index(array $params = []): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;
        $categoryId = !empty($_GET['category']) ? (int) $_GET['category'] : null;
        $search = trim($_GET['search'] ?? '');
        $user = Auth::isLoggedIn() ? Auth::currentUser() : null;

        if ($search) {
            $notices = $this->noticeModel->search($search);
            $allNotices = $notices;
            $pinnedNotices = [];
            $regularNotices = $notices;
            $bookmarkedIds = [];
            if ($user) {
                $archivedModel = new ArchivedNotice();
                $archivedIds = $archivedModel->getArchivedIds((int) $user['id']);
                $regularNotices = array_filter($regularNotices, function ($n) use ($archivedIds) {
                    return !in_array((int) $n['id'], $archivedIds, true);
                });
                $bookmarkModel = new Bookmark();
                $userBookmarks = $bookmarkModel->getByUser((int) $user['id']);
                $bookmarkedIds = array_map(function ($b) { return (int) $b['notice_id']; }, $userBookmarks);
            }
            $categories = $this->categoryModel->all();
            $result = ['total' => count($regularNotices), 'pages' => 1, 'page' => 1];
            require __DIR__ . '/../Views/layouts/header.php';
            require __DIR__ . '/../Views/public/home.php';
            require __DIR__ . '/../Views/layouts/footer.php';
            return;
        }

        if ($user) {
            $result = $this->noticeModel->getByAudiencePaginated($user['role'], [], $page, $perPage, $categoryId);
        } else {
            $result = $this->noticeModel->getActivePaginated($page, $perPage, $categoryId);
        }

        $allNotices = $result['notices'];

        if ($user) {
            $archivedModel = new ArchivedNotice();
            $archivedIds = $archivedModel->getArchivedIds((int) $user['id']);
            $allNotices = array_filter($allNotices, function ($n) use ($archivedIds) {
                return !in_array((int) $n['id'], $archivedIds, true);
            });
        }

        $pinnedNotices = array_filter($allNotices, function ($n) {
            return !empty($n['is_pinned']);
        });
        $regularNotices = array_filter($allNotices, function ($n) {
            return empty($n['is_pinned']);
        });

        $bookmarkedIds = [];
        if ($user) {
            $bookmarkModel = new Bookmark();
            $userBookmarks = $bookmarkModel->getByUser((int) $user['id']);
            $bookmarkedIds = array_map(function ($b) { return (int) $b['notice_id']; }, $userBookmarks);
        }

        $categories = $this->categoryModel->all();

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/public/home.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function noticeDetail(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $notice = $this->noticeModel->findById($id);
        if (!$notice) {
            http_response_code(404);
            require __DIR__ . '/../Views/layouts/header.php';
            echo '<div class="container"><div class="card"><div class="card-body"><h1>Notice Not Found</h1><p>The requested notice does not exist or has been removed.</p><a href="/" class="btn btn-primary mt-2">Back to Home</a></div></div></div>';
            require __DIR__ . '/../Views/layouts/footer.php';
            return;
        }

        $isBookmarked = false;
        $user = Auth::currentUser();
        $viewModel = new NoticeView();
        if ($user) {
            $viewModel->trackView($id, (int) $user['id']);
            $bookmarkModel = new Bookmark();
            $isBookmarked = $bookmarkModel->isBookmarked($user['id'], $id);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            try {
                $viewModel->trackView($id, null, $ip);
            } catch (\Throwable $e) {
            }
        }

        $attachments = $this->attachmentModel->findByNoticeId($id);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/public/notice-detail.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function kiosk(array $params = []): void
    {
        require __DIR__ . '/../Views/public/kiosk.php';
    }

    public function bookmarks(array $params = []): void
    {
        Auth::requireAuth();
        $user = Auth::currentUser();
        $bookmarkModel = new Bookmark();
        $bookmarks = $bookmarkModel->getByUser((int) $user['id']);

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/public/bookmarks.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function archived(array $params = []): void
    {
        Auth::requireAuth();
        $user = Auth::currentUser();
        $archivedModel = new ArchivedNotice();
        $archived = $archivedModel->getByUser((int) $user['id']);

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/public/archived.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
