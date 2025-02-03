<?php
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../utils/Response.php';

class ExpenseController {
    private $expenseModel;

    public function __construct() {
        $database = new Database();
        $this->expenseModel = new Expense($database->getConnection());
    }

    public function getAll() {
        $expenses = $this->expenseModel->getAll();
        Response::send(true, "Expenses retrieved successfully", $expenses);
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['category_id']) || empty($data['amount']) || empty($data['expense_date'])) {
            Response::send(false, "Category, amount, and date are required");
        }

        if ($this->expenseModel->create($data)) {
            Response::send(true, "Expense recorded successfully");
        } else {
            Response::send(false, "Failed to record expense");
        }
    }

    public function delete($id) {
        if ($this->expenseModel->delete($id)) {
            Response::send(true, "Expense deleted successfully");
        } else {
            Response::send(false, "Failed to delete expense");
        }
    }
}
