<?php
require_once __DIR__ . '/../models/ExpenseCategory.php';
require_once __DIR__ . '/../utils/Response.php';

class ExpenseCategoryController {
    private $categoryModel;

    public function __construct() {
        $database = new Database();
        $this->categoryModel = new ExpenseCategory($database->getConnection());
    }

    public function getAll() {
        $categories = $this->categoryModel->getAll();
        Response::send(true, "Categories retrieved successfully", $categories);
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['name'])) {
            Response::send(false, "Category name is required");
        }

        if ($this->categoryModel->create($data)) {
            Response::send(true, "Category created successfully");
        } else {
            Response::send(false, "Failed to create category");
        }
    }

    public function delete($id) {
        if ($this->categoryModel->delete($id)) {
            Response::send(true, "Category deleted successfully");
        } else {
            Response::send(false, "Failed to delete category");
        }
    }
}
