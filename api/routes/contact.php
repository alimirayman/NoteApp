<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


// Get All Events
$app->get('/contact', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);

  $sql = "SELECT * FROM contact";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    return $response->withJson($events);
  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});

$app->post('/contact', function(Request $request, Response $response){

  $name    = $request->getParam('name');
  $email   = $request->getParam('email');
  $phone   = $request->getParam('phone');
  $msg     = $request->getParam('msg');

  $sql = "INSERT INTO contact (name,email,phone,msg) VALUES
    (:name,:email,:phone,:msg)";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':name',    $name);
    $stmt->bindParam(':email',   $email);
    $stmt->bindParam(':phone',   $phone);
    $stmt->bindParam(':msg',     $msg);

    $stmt->execute();

    $res = ['notice' => ['text' => 'Contacted'], 'status'=>1];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }

});