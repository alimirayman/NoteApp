<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


// Get All Events
$app->get('/events', function(Request $request, Response $response){
  $sql = "SELECT * FROM events WHERE is_visible = 1";

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

$app->post('/events', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);

  $title  = $request->getParam('title');
  $img    = $request->getParam('img');
  $body   = $request->getParam('body');
  $excerpt= $request->getParam('excerpt');
  $link   = $request->getParam('url');

  $sql = "INSERT INTO events (title,img,body,excerpt,link) VALUES
    (:title,:img,:body,:excerpt,:link)";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':title',    $title);
    $stmt->bindParam(':img',      $img);
    $stmt->bindParam(':body',     $body);
    $stmt->bindParam(':link',     $link);
    $stmt->bindParam(':excerpt',  $excerpt);

    $stmt->execute();

    $res = ['notice' => ['text' => 'Event Added']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }

});

$app->delete('/event/{id}', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);
  $id = $request->getAttribute('id');

  $sql = "DELETE FROM events WHERE id = $id";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $db = null;

    $res = ['notice' => ['text' => 'Event Deleted']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});

// Get Single Event
$app->get('/event/{link}', function(Request $request, Response $response){
  $link = $request->getAttribute('link');

  $sql = "SELECT * FROM events WHERE link = :link && is_visible = 1";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':link',  $link);

    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_OBJ);
    $db = null;
    return $response->withJson($event);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});

// Update Single Event
$app->put('/event/{uid}', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);
  $uid = $request->getAttribute('uid');
  $title = $request->getParam('title');
  $img = $request->getParam('img');
  $body = $request->getParam('body');
  $excerpt = $request->getParam('excerpt');
  $link = $request->getParam('link');

  $sql = "UPDATE events SET
				title     = :title,
				img       = :image,
        body      = :body,
        excerpt   = :excerpt,
        link      = :link
        WHERE id  = $uid";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

//    $stmt->bindParam(':id',  $uid);
    $stmt->bindParam(':title',    $title);
    $stmt->bindParam(':image',    $img);
    $stmt->bindParam(':body',     $body);
    $stmt->bindParam(':excerpt',  $excerpt);
    $stmt->bindParam(':link',     $link);

    $stmt->execute();

    $db = null;

    $res = ['notice' => ['text' => 'Event Updated']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});
