<?php

require '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$dot_env = new Dotenv\Dotenv("../");
$dot_env->load();

require '../config/db.php';
require '../config/auth.php';

$config = [
  'settings' => [
    'displayErrorDetails' => true,

    'logger' => [
      'name' => 'slim-app',
      'level' => Monolog\Logger::DEBUG,
      'path' => __DIR__ . '/../logs/app.log',
    ],
  ],
];
$app = new \Slim\App($config);

$app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
  return $response;
});

// Middleware
require '../Middleware/Auth.php';
$app->add(function (Request $req, Response $res, $next) {
  $response = $next($req, $res);
  return $response
    ->withHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->get('/', function (Request $request, Response $response) {
  $test = [ "api" => "Developed By Mir Ayman Ali", "url" => "http://mirayman.com"];

  return $response->withJson($test);
});

$app->get('/mail', function (Request $request, Response $response) {
  try{
    $mail = new mail();
    $data = $mail->sendMail('alimirayman@outlook.com', 'Mir Ayman Ali');

    return $response->withJson(['status'=> 1, 'data' => $data]);

  } catch (Exception $e){
    return $response->withJson(['status'=> 0, 'error'=>['text'=>$e->getMessage()]]);
  }
});

$app->get('/check', function(Request $req, Response $res){

  return $res->withJson($ret);
});

require '../routes/auth.php';
require '../routes/image.php';

$app->run();