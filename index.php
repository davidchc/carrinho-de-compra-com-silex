<?php
session_start();
define("DIR", dirname(__FILE__));
define("DS", DIRECTORY_SEPARATOR);

include_once 'vendor/autoload.php';

$app = new \Silex\Application();
$app['debug'] = true;

$app['db'] = function () {
    $pdo = new \PDO("mysql:host=localhost;dbname=shop", "root", "");
    return $pdo;
};

$app['product.repository'] = function () use ($app) {
    $productRepository = new App\Model\Product\ProductRepositoryPDO($app['db']);
    return $productRepository;
};
$app->get('/', function () use ($app) {
    $home = new App\Controller\Home($app['product.repository']);
    return $home->index();
});

$app->mount('/cart', function ($shopping) use ($app) {
    $sessionCart = new App\Model\Shopping\CartSession();
    $cart = new App\Controller\Cart($app['product.repository'], $sessionCart);

    $shopping->get('/', function () use ($cart) {
        return $cart->index();
    });

    $shopping->post('/add', function () use ($app, $cart) {
        $cart->add();
        return $app->redirect('/cart/shopping');
    });

    $shopping->post('/update', function () use ($app, $cart) {
        $cart->update();
        return $app->redirect('/cart/shopping');
    });

    $shopping->get('/delete/{id}', function ($id) use ($app, $cart) {
        $cart->delete($id);
        return $app->redirect('/cart/shopping');
    });
});
$app->run();
