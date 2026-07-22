<?php
require_once __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/config.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(404);
    exit;
}

$attachment = (new \App\Models\NoticeAttachment())->findById($id);
if (!$attachment) {
    http_response_code(404);
    exit;
}

if (!empty($attachment['file_data'])) {
    header('Content-Type: ' . ($attachment['file_mime'] ?: 'application/octet-stream'));
    header('Content-Disposition: inline; filename="' . $attachment['original_name'] . '"');
    header('Content-Length: ' . strlen(base64_decode($attachment['file_data'])));
    echo base64_decode($attachment['file_data']);
    exit;
}

$localPath = __DIR__ . '/' . $attachment['file_path'];
if (file_exists($localPath)) {
    header('Content-Type: ' . mime_content_type($localPath));
    header('Content-Length: ' . filesize($localPath));
    readfile($localPath);
    exit;
}

http_response_code(404);
