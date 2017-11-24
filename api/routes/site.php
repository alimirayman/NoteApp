<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


// Get All Social Links
$app->get('/social', function(Request $request, Response $response){
  $sql = "SELECT * FROM social";

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

$app->put('/social', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);

  $facebook  = $request->getParam('facebook');
  $instagram = $request->getParam('instagram');
  $skype     = $request->getParam('skype');
  $twitter   = $request->getParam('twitter');

  $sql = "UPDATE social SET
            url = :facebook
          WHERE name = 'facebook';
          UPDATE social SET
            url = :instagram
          WHERE name = 'instagram';
          UPDATE social SET
            url = :skype
          WHERE name = 'skype';
          UPDATE social SET
            url = :twitter
          WHERE name = 'twitter';
        ";

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':facebook',  $facebook);
    $stmt->bindParam(':twitter',   $twitter);
    $stmt->bindParam(':skype',     $skype);
    $stmt->bindParam(':instagram', $instagram);

    $stmt->execute();

    $res = ['notice' => ['text' => 'Social Urls Updated']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }

});

// Get All Partners
$app->get('/partners', function(Request $request, Response $response){
  $sql = "SELECT * FROM partners";

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



$app->put('/partners', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);

  $params = $request->getParams();
  $sql = '';
  foreach ($params as $el) {
    $el = (object) $el;
    $sql .= "UPDATE partners SET
            name = '$el->name',
            img = '$el->img'
          WHERE id = '$el->id';";
  }

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->prepare($sql);

    $stmt->execute();

    $res = ['notice' => ['text' => 'Partners Updated']];

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }

});