<?php

namespace App\Core;

use App\Models\Notification;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private ?PHPMailer $mailer = null;

    public function __construct()
    {
        if (empty(MAIL_HOST) || empty(MAIL_USER)) {
            return;
        }

        try {
            $this->mailer = new PHPMailer(true);
            $this->mailer->isSMTP();
            $this->mailer->Host       = MAIL_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = MAIL_USER;
            $this->mailer->Password   = MAIL_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = 587;
            $this->mailer->setFrom(MAIL_USER, 'Digital Notice Board');
            $this->mailer->isHTML(true);
        } catch (Exception $e) {
            error_log('Mailer initialization failed: ' . $e->getMessage());
        }
    }

    public function sendNoticeNotification(
        int $userId,
        string $toEmail,
        string $toName,
        string $noticeTitle,
        string $noticeUrl
    ): bool {
        // Create in-app notification
        try {
            $notification = new Notification();
            $notification->create(
                $userId,
                'notice',
                'New Notice: ' . $noticeTitle,
                'A new notice "' . $noticeTitle . '" has been published.',
                $noticeUrl
            );
        } catch (\Exception $e) {
            error_log('In-app notification creation failed: ' . $e->getMessage());
        }

        // Send email via PHPMailer
        if ($this->mailer === null) {
            return false;
        }

        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);

            $this->mailer->Subject = 'New Notice: ' . $noticeTitle;

            $this->mailer->Body = '
                <h2>New Notice Published</h2>
                <p>A new notice has been published on the Digital Notice Board:</p>
                <h3>' . htmlspecialchars($noticeTitle) . '</h3>
                <p>
                    <a href="' . htmlspecialchars($noticeUrl) . '"
                       style="display:inline-block;padding:0.6rem 1.25rem;background:#1D4ED8;color:#fff;text-decoration:none;border-radius:6px;">
                        View Notice
                    </a>
                </p>
                <hr>
                <p style="color:#666;font-size:0.85rem;">
                    This is an automated notification from the Digital Notice Board System.
                </p>
            ';

            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $this->mailer->Body));

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer send failed: ' . $e->getMessage());
            return false;
        }
    }
}
