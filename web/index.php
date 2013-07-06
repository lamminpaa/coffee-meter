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
    $req = $app['db']->prepare('INSERT INTO cafe(barista, blend, timestamp, comment, location) VALUES(:barista, :blend, :timestamp, :comment, :location)');
    $req->execute(array(
        'barista'      => $app->escape($request->get('name')),
        'blend'      => $app->escape($request->get('blend')),
        'timestamp'      => $app->escape(time()),
        'location'  =>  $app->escape($request->get('location')),
        'comment'     => $app->escape($request->get('comment'))
    ));

    return $app->redirect('/cafe/'.$app['db']->lastInsertId());
});
$app->post('/cafe/{id}/comment', function (Request $request, $id) use ($app) {
    $req = $app['db']->prepare('INSERT INTO comment(comment, cafe_id) VALUES(:comment, :cafe_id)');
    $req->execute(array(
        'comment'      => $app->escape($request->get('comment')),
        'cafe_id'      => $app->escape($id)
    ));

    return $app->redirect('/cafe/'.$id);
});
$app->get('/cafe/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM cafe WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array($id));

    $sql = "SELECT * FROM comment WHERE cafe_id = ?";
    $comments = $app['db']->fetchAll($sql, array($id));

    $cafeTime = round((time() - $post['timestamp'])/60);
    return $app['twig']->render('cafe.twig', array(
        'barista' => $app->escape($post['barista']),
        'blend' => $app->escape($post['blend']),
        'timestamp' => $app->escape($post['timestamp']),
        'comment' => $app->escape($post['comment']),
        'cafeId' => $app->escape($id),
        'cafe_time' => $cafeTime,
        'comments' => $comments
    ));
});


$app->run();