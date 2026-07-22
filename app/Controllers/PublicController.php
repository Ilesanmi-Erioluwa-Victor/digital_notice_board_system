<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Notice;
use App\Models\Category;
use App\Models\NoticeAttachment;
use App\Models\NoticeView;
use App\Models\Bookmark;

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
        $user = Auth::isLoggedIn() ? Auth::currentUser() : null;

        if ($user) {
            $notices = $this->noticeModel->getByAudience($user['role'], []);
        } else {
            $notices = $this->noticeModel->getActive();
        }

        $pinnedNotices = array_filter($notices, function ($n) {
            return !empty($n['is_pinned']);
        });
        $regularNotices = array_filter($notices, function ($n) {
            return empty($n['is_pinned']);
        });

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
        if ($user) {
            $viewModel = new NoticeView();
            $viewModel->trackView($id, $user['id']);
            $bookmarkModel = new Bookmark();
            $isBookmarked = $bookmarkModel->isBookmarked($user['id'], $id);
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
}
