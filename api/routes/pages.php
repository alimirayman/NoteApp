<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


// Get All Events
$app->get('/pages', function(Request $request, Response $response){
  $sql = "SELECT * FROM pages WHERE is_visible = 1";

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

$app->post('/pages', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);

  $title  = $request->getParam('title');
  $subtitle = $request->getParam('subTitle');
  $img    = $request->getParam('img');
  $heading = $request->getParam('heading');
  $body   = $request->getParam('body');
  $excerpt= $request->getParam('excerpt');
  $slug   = $request->getParam('url');

  $sql = "INSERT INTO pages (title,subTitle,img,heading,body,excerpt,slug) VALUES
    (:title,:subtitle,:img,:heading,:body,:excerpt,:slug)";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':title',    $title);
    $stmt->bindParam(':subtitle', $subtitle);
    $stmt->bindParam(':img',      $img);
    $stmt->bindParam(':heading',  $heading);
    $stmt->bindParam(':body',     $body);
    $stmt->bindParam(':slug',     $slug);
    $stmt->bindParam(':excerpt',  $excerpt);

    $stmt->execute();

    $res = ['notice' => ['text' => 'Page Added']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }

});

$app->delete('/page/{id}', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);
  $id = $request->getAttribute('id');

  $sql = "DELETE FROM pages WHERE id = $id";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $db = null;

    $res = ['notice' => ['text' => 'Page Deleted']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});

// Get Single Event
$app->get('/page/{slug}', function(Request $request, Response $response){
  $slug = $request->getAttribute('slug');

  $sql = "SELECT * FROM pages WHERE slug = :slug && is_visible = 1";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':slug',  $slug);

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
$app->put('/page/{uid}', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);
  $uid = $request->getAttribute('uid');
  $title = $request->getParam('title');
  $subtitle = $request->getParam('subTitle');
  $excerpt = $request->getParam('excerpt');
  $heading = $request->getParam('heading');
  $img = $request->getParam('img');
  $body = $request->getParam('body');
  $slug = $request->getParam('slug');

  $sql = "UPDATE pages SET
				title     = :title,
				subTitle  = :subtitle,
				img       = :image,
        body      = :body,
        heading   = :heading,
        excerpt   = :excerpt,
        slug      = :slug
        WHERE id  = $uid";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

//    $stmt->bindParam(':id',  $uid);
    $stmt->bindParam(':title',    $title);
    $stmt->bindParam(':subtitle', $subtitle);
    $stmt->bindParam(':image',    $img);
    $stmt->bindParam(':heading',  $heading);
    $stmt->bindParam(':body',     $body);
    $stmt->bindParam(':excerpt',  $excerpt);
    $stmt->bindParam(':slug',     $slug);

    $stmt->execute();

    $db = null;

    $res = ['notice' => ['text' => 'Page Updated']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});
