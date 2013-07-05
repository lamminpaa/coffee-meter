<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array (
        'driver'    => 'pdo_mysql',
        'host'      => '127.0.0.1',
        'dbname'    => 'coffee-meter',
        'user'      => 'root',
        'password'  => '',
        'charset'   => 'utf8',
    ),
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/', function () use ($app) {
    return $app['twig']->render('front.twig', array(
    ));
});

$app->post('/cafe', function (Request $request) use ($app) {
    $req = $app['db']->prepare('INSERT INTO cafe(barista, blend, timestamp, comment) VALUES(:barista, :blend, :timestamp, :comment)');
    $req->execute(array(
        'barista'      => $app->escape($request->get('name')),
        'blend'      => $app->escape($request->get('blend')),
        'timestamp'      => $app->escape(time()),
        'comment'     => $app->escape($request->get('comment'))
    ));

    return new Response('OK', 201);
});
$app->get('/cafe/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM cafe WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array($id));

    $percentage = (time()-$app->escape($post['timestamp']))/60;
    return $app['twig']->render('front.twig', array(
        'barista' => $app->escape($post['barista']),
        'blend' => $app->escape($post['blend']),
        'timestamp' => $app->escape($post['timestamp']),
        'comment' => $app->escape($post['comment']),
        'progressbar' => $percentage
    ));
});

$app->run();