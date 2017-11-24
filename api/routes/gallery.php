<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


// Get All Events
$app->get('/gallery', function(Request $request, Response $response){
  $sql = "SELECT * FROM gallery";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->query($sql);
    $gallery = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    return $response->withJson($gallery);
  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});

$app->post('/gallery', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);

  $name    = $request->getParam('name');
  $image   = $request->getParam('img');
  $caption = $request->getParam('caption');

  $sql = "INSERT INTO gallery (name,img,caption) VALUES
    (:name,:image,:caption)";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':name',    $name);
    $stmt->bindParam(':image',   $image);
    $stmt->bindParam(':caption', $caption);

    $stmt->execute();

    $res = ['notice' => ['text' => 'Image Added'], 'status'=>1];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }

});

$app->delete('/gallery/{id}', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);
  $id = $request->getAttribute('id');

  $sql = "DELETE FROM gallery WHERE id = $id";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $db = null;

    $res = ['notice' => ['text' => 'Image Deleted']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});