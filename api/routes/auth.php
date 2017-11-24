<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Register
$app->post('/register', function(Request $request, Response $response){

  $name     = $request->getParam('name');
  $username = $request->getParam('username');
  $pass     = $request->getParam('password');
  $email    = $request->getParam('email');

  try{

    $res = [
      'error' => [
        'text' => 'Internal Server Error'
      ]
    ];

    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();


    $id = "_" . bin2hex(random_bytes(11));
    $hash   = password_hash($pass, PASSWORD_BCRYPT);
    if(password_verify($pass, $hash)){
      $keys   = array( 'id','name', 'username', 'email', 'pass');
      $values = array( $id, $name,  $username,  $email,  $hash );

      $stmt   = $db->insert($keys)
        ->into('users')
        ->values($values);

      $stmt->execute();

      $res = ['user_id' => $id, 'user_hash' => $pass,'notice' => ['text' => 'User Added']];

    }

    return $response->withJson($res);

  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);
  }
});

// Login
$app->post('/login', function(Request $request, Response $response){
  $username = $request->getParam('username');
  $pass = $request->getParam('password');

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db = $db->connect();

    $stmt = $db->select()
      ->from('users')
      ->where('username', '=', $username)
      ->where('auth', '=', 1);

    $stmt = $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_OBJ);


    $res = ["msg"=>"no User Found", "status" => 0];

    foreach ($users as $user){

      if(password_verify( $pass, $user->pass )){

        $auth = new auth();

        $token = $auth->getToken($user->id);

        $res["msg"] = "Welcome, " . $user->name;
        $res["status"] = 1;
        $exp = time() + (1*24*60*60);
        $res["auth"] = ["token"=> $token, "exp" => $exp];

        $keys = array('token', 'user_id', 'exp');
        $values = array($token, $user->id, $exp);

        $stmt = $db->insert($keys)
                  ->into('session')
                  ->values($values);

        $stmt->execute();
      }
    }

    $db = null;

    return $response->withJson($res);

  } catch(PDOException $e){

    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $response->withJson($err);

  }
});

$app->get('/token', function (Request $req, Response $res){
  return $res->withJson(['status' => 1]);
})->add($authentication);

$app->delete('/logout', function (Request $req, Response $res){
  $token = $req->getHeader('Token');
  $auth = new auth();

  // Remove Token if Exists
  $auth->removeToken($token[0]);
  return $res->withJson(['notice' => ['text' => 'Logged Out']]);
})->add($authentication);

$app->get('/user/{user_id}', function (Request $req, Response $res) {

  $route = $req->getAttribute('route');
  $user_id = $route->getArgument('user_id');

  try{
    // Get DB Object
    $db = new db();
    // Connect
    $db= $db->connect();

    $selectStatement = $db->select(array('id', 'name', 'username', 'email'))
                          ->from('users')
                          ->where('id', '=', $user_id);

    $stmt = $selectStatement->execute();
    $data = $stmt->fetch();
    return $res->withJson($data);
  } catch(PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $res->withJson($err);
  }
})->add($authentication);

$app->put('/user', function (Request $req, Response $res) {
  $token = $req->getHeader('Token');

  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $res->withJson(["msg"=>"You Need to Login again sir", "status"=>0]);

  $fullName = $req->getParam('fullName');
  $username = $req->getParam('username');
  $pass = $req->getParam('changedPass');
  $rpass = $req->getParam('repeatPass');
  $email = $req->getParam('email');

  $sql = "UPDATE users SET
            name = :fullName,
            username = :username,
            pass     = :pass,
            email    = :email
          WHERE id   = (
            SELECT user_id FROM session
            WHERE token = :token
          )";

  if($pass === $rpass){
    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();
      $stmt = $db->prepare($sql);

      $pass = password_hash($pass, PASSWORD_BCRYPT);

      $stmt->bindParam(':token',$token[0]);
      $stmt->bindParam(':fullName',  $fullName);
      $stmt->bindParam(':username',    $username);
      $stmt->bindParam(':pass',   $pass);
      $stmt->bindParam(':email',   $email);
      $stmt->execute();

      $db = null;
      return $res->withJson(['notice' => ['text' => 'user Updated']]);
    } catch(PDOException $e){
      $err = [ 'error' => ['text'=> $e->getMessage()]];
      return $res->withJson($err);
    }
  } else {
    $err = [ 'notice' => ['text'=> 'Passwords are not same']];
    return $res->withJson($err);
  }
});