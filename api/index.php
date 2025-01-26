<?php
require_once "config/Database.php";
require_once "controllers/AuthController.php";
require_once "controllers/LoanController.php";
require_once "middlewares/AuthMiddleware.php";

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

// Define Routes
$routes = [
    "POST" => [
        "login" => [$authController, 'login'],
        "loans" => [$loanController, 'createLoan'], // Create
    ],
    "GET" => [
        "loans" => [$loanController, 'getAllLoans'], // List all loans
        "loans/{id}" => [$loanController, 'getLoanById'], // Get a specific loan
    ],
    "PUT" => [
        "loans/{id}" => [$loanController, 'updateLoan'], // Update
    ],
    "DELETE" => [
        "loans/{id}" => [$loanController, 'deleteLoan'], // Delete
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
