<?php

require_once "config/Database.php";
require_once "controllers/AuthController.php";
require_once "controllers/LoanController.php";
require_once "controllers/ReferralController.php";
require_once "controllers/AccountController.php";
require_once "controllers/ExpenseCategoryController.php";
require_once "controllers/ExpenseController.php";
require_once "middlewares/AuthMiddleware.php";
require_once "middlewares/CorsMiddleware.php";

CorsMiddleware::handle();

// Parse the URI and Method
$scriptName = $_SERVER['SCRIPT_NAME']; // This includes 'index.php'
$requestUri = $_SERVER['REQUEST_URI']; // Full requested URI

// Remove base directory and clean the request URI
$requestUri = str_replace($scriptName, '', $requestUri); // Remove '/api/index.php'
$requestUri = trim($requestUri, '/'); // Remove leading and trailing slashes
$method = $_SERVER['REQUEST_METHOD'];

// Initialize Controllers
$authController = new AuthController();
$loanController = new LoanController();
$referralController = new ReferralController();
$accountController = new AccountController();
$categoryController = new ExpenseCategoryController();
$expenseController = new ExpenseController();

// Define Routes
$routes = [
    "POST" => [
        "login" => [$authController, 'login'],
        "loans" => [$loanController, 'createLoan'], // Create
        "referrals" => [$referralController, 'create'], // Create Referral
        "accounts" => [$accountController, 'create'],  // Create Account
        "expense-categories" => [$categoryController, 'create'],
        "expenses" => [$expenseController, 'create']
    ],
    "GET" => [
        "loans" => [$loanController, 'getAllLoans'], // List all loans
        "loans/{id}" => [$loanController, 'getLoanById'], // Get a specific loan,
        "referrals" => [$referralController, 'getAll'], // List Referrals
        "accounts" => [$accountController, 'getAll'],  // List Accounts
        "referrals/{id}" => [$referralController, 'getById'], // Get Referral by ID
        "accounts/{id}" => [$accountController, 'getById'],  // Get Account by ID
        "expense-categories" => [$categoryController, 'getAll'],
        "expenses" => [$expenseController, 'getAll']
    ],
    "PUT" => [
        "loans/{id}" => [$loanController, 'updateLoan'], // Update
        "referrals/{id}" => [$referralController, 'update'], // Update Referral
        "accounts/{id}" => [$accountController, 'update'],   // Update Account
    ],
    "DELETE" => [
        "loans/{id}" => [$loanController, 'deleteLoan'], // Delete
        "referrals/{id}" => [$referralController, 'delete'], // Delete Referral
        "accounts/{id}" => [$accountController, 'delete'],   // Delete Account
        "expense-categories/{id}" => [$categoryController, 'delete'],
        "expenses/{id}" => [$expenseController, 'delete']
    ]
];

// Match Route
function matchRoute($routes, $method, $requestUri) {
    foreach ($routes[$method] as $route => $handler) {
        $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $route); // Replace {id} with a regex
        if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
            array_shift($matches); // Remove the full match from $matches
            return [$handler, $matches];
        }
    }
    return [null, []];
}

// Check if route matches
list($handler, $params) = matchRoute($routes, $method, $requestUri);

if ($handler) {
    if ($requestUri !== "login") {
        AuthMiddleware::checkAuth(); // Add authentication for non-login routes
    }
    call_user_func_array($handler, $params); // Call the controller method with parameters
} else {
    echo json_encode(["status" => false, "message" => "Route not found"]);
}
