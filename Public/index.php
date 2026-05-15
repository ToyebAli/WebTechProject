
require_once BASE_PATH . '/controllers/ProductController.php';
require_once BASE_PATH . '/controllers/CartController.php';
require_once BASE_PATH . '/controllers/CheckoutController.php';

} elseif ($uri === '/products') {
    (new ProductController())->index();

} elseif ($uri === '/products/show') {
    (new ProductController())->show();

} elseif ($uri === '/api/products/search') {
    (new ProductController())->apiSearch();

} elseif ($uri === '/api/products') {
    (new ProductController())->apiFilter();

} elseif (preg_match('#^/api/products/(\d+)/reviews$#', $uri, $m)) {
    $_GET['id'] = $m[1];
    (new ProductController())->apiReviews();

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