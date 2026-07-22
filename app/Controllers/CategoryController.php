<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Category;

class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(array $params = []): void
    {
        Auth::requireAuth(['admin']);
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/admin/categories.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function create(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/categories');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$name) {
            $_SESSION['error'] = 'Category name is required.';
            header('Location: /admin/categories');
            exit;
        }

        $this->categoryModel->create($name, $description);
        $_SESSION['success'] = 'Category created successfully.';
        header('Location: /admin/categories');
        exit;
    }

    public function update(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/categories');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$name) {
            $_SESSION['error'] = 'Category name is required.';
            header('Location: /admin/categories');
            exit;
        }

        $this->categoryModel->update($id, $name, $description);
        $_SESSION['success'] = 'Category updated successfully.';
        header('Location: /admin/categories');
        exit;
    }

    public function delete(array $params = []): void
    {
        Auth::requireAuth(['admin']);

        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /admin/categories');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $this->categoryModel->delete($id);
        $_SESSION['success'] = 'Category deleted successfully.';
        header('Location: /admin/categories');
        exit;
    }
}
