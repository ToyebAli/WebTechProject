<?php

define('BASE_PATH', dirname(__DIR__));
define('APP_ENTRY_PATH', str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php'));

require_once BASE_PATH . '/config/helpers.php';
require_once BASE_PATH . '/Controller/AuthController.php';
require_once BASE_PATH . '/Controller/ProfileController.php';
require_once BASE_PATH . '/Controller/ProductController.php';
require_once BASE_PATH . '/Controller/CartController.php';
require_once BASE_PATH . '/Controller/CheckoutController.php';
// TASK 4 PART START: Order and review controllers
require_once BASE_PATH . '/Controller/4_OrderController.php';
require_once BASE_PATH . '/Controller/4_ReviewController.php';
// TASK 4 PART END

AuthController::restoreFromCookie();

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$entryPath   = APP_ENTRY_PATH;
$entryDir    = rtrim(str_replace('\\', '/', dirname($entryPath)), '/');

if ($entryPath !== '' && str_starts_with($requestPath, $entryPath)) {
    $requestPath = substr($requestPath, strlen($entryPath));
} elseif ($entryDir !== '' && $entryDir !== '/' && str_starts_with($requestPath, $entryDir)) {
    $requestPath = substr($requestPath, strlen($entryDir));
}

$uri = '/' . trim($requestPath, '/');
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/') {
    start_session_if_needed();

    if (!empty($_SESSION['user_id'])) {
        redirect($_SESSION['role'] === 'admin' ? '/admin/dashboard' : '/products');
    }

    redirect('/login');
} elseif ($uri === '/register') {
    $c = new AuthController();
    $method === 'POST' ? $c->register() : $c->registerForm();
} elseif ($uri === '/login') {
    $c = new AuthController();
    $method === 'POST' ? $c->login() : $c->loginForm();
} elseif ($uri === '/logout') {
    (new AuthController())->logout();
} elseif ($uri === '/profile') {
    (new ProfileController())->show();
} elseif ($uri === '/profile/update') {
    $method === 'POST' ? (new ProfileController())->update() : redirect('/profile');
} elseif ($uri === '/profile/password') {
    $method === 'POST' ? (new ProfileController())->changePassword() : redirect('/profile');
} elseif ($uri === '/products') {
    (new ProductController())->index();
} elseif ($uri === '/products/show') {
    (new ProductController())->show();
} elseif ($uri === '/api/products/search') {
    (new ProductController())->apiSearch();
} elseif ($uri === '/api/products') {
    (new ProductController())->apiFilter();
} elseif (preg_match('#^/api/products/(\d+)/reviews$#', $uri, $matches)) {
    (new Task4ReviewController())->productReviews((int)$matches[1]);
} elseif ($uri === '/cart') {
    (new CartController())->index();
} elseif ($uri === '/api/cart/add') {
    (new CartController())->apiAdd();
} elseif ($uri === '/api/cart/update') {
    (new CartController())->apiUpdate();
} elseif ($uri === '/api/cart/remove') {
    (new CartController())->apiRemove();
} elseif ($uri === '/checkout') {
    $c = new CheckoutController();
    $method === 'POST' ? $c->place() : $c->form();
} elseif ($uri === '/checkout/confirm') {
    (new CheckoutController())->confirm();
// TASK 4 PART START: Customer order and review routes
} elseif ($uri === '/orders') {
    (new Task4OrderController())->myOrders();
} elseif ($uri === '/orders/show') {
    (new Task4OrderController())->show();
} elseif ($uri === '/api/reviews') {
    (new Task4ReviewController())->create();
// TASK 4 PART END
} elseif ($uri === '/admin') {
    require_admin();
    redirect('/admin/dashboard');
} elseif ($uri === '/admin/dashboard') {
    require_admin();
    include BASE_PATH . '/View/2_adminDashboard.php';
} elseif ($uri === '/admin/categories') {
    require_admin();
    include BASE_PATH . '/View/2_categoryList.php';
} elseif ($uri === '/admin/categories/create') {
    require_admin();
    if ($method === 'POST') {
        include BASE_PATH . '/Controller/2_categorySave.php';
    } else {
        include BASE_PATH . '/View/2_categoryForm.php';
    }
} elseif ($uri === '/admin/categories/edit') {
    require_admin();
    if ($method === 'POST') {
        include BASE_PATH . '/Controller/2_categorySave.php';
    } else {
        include BASE_PATH . '/View/2_categoryForm.php';
    }
} elseif ($uri === '/admin/categories/delete') {
    require_admin();
    include BASE_PATH . '/Controller/2_categoryDelete.php';
} elseif ($uri === '/admin/products') {
    require_admin();
    include BASE_PATH . '/View/2_productList.php';
} elseif ($uri === '/admin/products/create') {
    require_admin();
    if ($method === 'POST') {
        include BASE_PATH . '/Controller/2_productSave.php';
    } else {
        include BASE_PATH . '/View/2_productForm.php';
    }
} elseif ($uri === '/admin/products/edit') {
    require_admin();
    if ($method === 'POST') {
        include BASE_PATH . '/Controller/2_productSave.php';
    } else {
        include BASE_PATH . '/View/2_productForm.php';
    }
} elseif ($uri === '/admin/products/delete') {
    require_admin();
    include BASE_PATH . '/Controller/2_productDelete.php';
// TASK 4 PART START: Admin order routes
} elseif ($uri === '/admin/orders') {
    (new Task4OrderController())->adminOrders();
} elseif (preg_match('#^/api/products/(\d+)/availability$#', $uri, $matches)) {
    require_admin();
    $_GET['id'] = $matches[1];
    include BASE_PATH . '/Controller/2_productAvailabilityToggle.php';
} elseif (preg_match('#^/api/orders/(\d+)$#', $uri, $matches)) {
    (new Task4OrderController())->apiUpdateStatus((int)$matches[1]);
// TASK 4 PART END
} else {
    http_response_code(404);
    include BASE_PATH . '/View/404.php';
}
