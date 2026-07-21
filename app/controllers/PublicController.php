<?php
/**
 * PublicController — Handles public-facing pages (home, notice detail, kiosk).
 */

namespace App\Controllers;

use App\Models\Notice;
use App\Models\Category;
use App\Models\Attachment;

class PublicController
{
    private Notice $noticeModel;
    private Category $categoryModel;
    private Attachment $attachmentModel;

    public function __construct()
    {
        $this->noticeModel     = new Notice();
        $this->categoryModel   = new Category();
        $this->attachmentModel = new Attachment();
    }

    /**
     * Public home page — shows active notices in a responsive grid.
     * Initial data rendered server-side; AJAX polling updates the grid.
     * GET /
     */
    public function home(array $params = []): void
    {
        $notices    = $this->noticeModel->getActive();
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/public/home.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Notice detail page — full content with attachment download links.
     * GET /notice/{id}
     */
    public function noticeDetail(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $notice = $this->noticeModel->findById($id);
        if (!$notice) {
            http_response_code(404);
            require __DIR__ . '/../views/layouts/header.php';
            echo '<div class="container"><div class="card"><div class="card-body"><h1>Notice Not Found</h1><p>The requested notice does not exist or has been removed.</p><a href="/" class="btn btn-primary mt-2">Back to Home</a></div></div></div>';
            require __DIR__ . '/../views/layouts/footer.php';
            return;
        }

        $attachments = $this->attachmentModel->findByNoticeId($id);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/public/notice-detail.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    /**
     * Kiosk display mode — full-screen auto-cycling of active notices.
     * No navigation, just large typography and auto-rotation via JS.
     * GET /kiosk
     */
    public function kiosk(array $params = []): void
    {
        // Kiosk has its own full-screen layout (no header/footer)
        require __DIR__ . '/../views/public/kiosk.php';
    }
}
