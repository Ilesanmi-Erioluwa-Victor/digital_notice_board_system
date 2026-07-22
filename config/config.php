<?php
/**
 * Configuration Loader
 *
 * Loads environment variables from .env file using vlucas/phpdotenv
 * and exposes them as defined constants and a config array.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file from the project root (one level up from /config)
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'digital_notice_board');
define('DB_USER', $_ENV['DB_USER'] ?? 'postgres');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? 'secret');

// Application configuration
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');
define('SESSION_SECRET', $_ENV['SESSION_SECRET'] ?? 'default-secret-change-me');

// Mail configuration
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? '');
define('MAIL_USER', $_ENV['MAIL_USER'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');

// Application settings
define('UPLOAD_DIR', $_ENV['UPLOAD_DIR'] ?? __DIR__ . '/../public/assets/uploads');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'png']);
define('AJAX_POLL_INTERVAL', 30000); // 30 seconds
define('KIOSK_ROTATION_INTERVAL', 9000); // 9 seconds

// Return config as array for programmatic use
return [
    'db' => [
        'host' => DB_HOST,
        'port' => DB_PORT,
        'name' => DB_NAME,
        'user' => DB_USER,
        'password' => DB_PASSWORD,
    ],
    'app' => [
        'url' => APP_URL,
        'session_secret' => SESSION_SECRET,
    ],
    'mail' => [
        'host' => MAIL_HOST,
        'user' => MAIL_USER,
        'password' => MAIL_PASSWORD,
    ],
    'uploads' => [
        'max_size' => UPLOAD_MAX_SIZE,
        'allowed_types' => ALLOWED_FILE_TYPES,
    ],
    'polling' => [
        'interval' => AJAX_POLL_INTERVAL,
    ],
    'kiosk' => [
        'rotation_interval' => KIOSK_ROTATION_INTERVAL,
    ],
];
