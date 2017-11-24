<?php

use \Firebase\JWT\JWT;

class auth{

  // Properties
  private $apikey;
  private static $instance;

  public function __construct()
  {
    if(!self::$instance){
      $this->apikey = getenv('API_KEY');
      self::$instance = $this;
    }
    return self::$instance;
  }

  // Gets the token
  public function getToken($uid){
    return $this->tokenGen($uid);
  }


  private function tokenGen(String $uid): string
  {

    $token = array(
      "iss" => "https://api.daily-task.tk",
      "aud" => "https://daily-task.tk",
      "uid" => $uid,
      "iat" => time(),
      "exp" => time() + (1 * 24 * 60 * 60 )
    );

    $jwt = JWT::encode($token, $this->apikey);

    return $jwt;

  }

  public function checkToken(string $jwt): bool
  {

    $decoded = $this->decodeToken($jwt);

    if($decoded){
      return $this->tokenExists($jwt);
    }else{
      return $this->removeToken($jwt);

    }
  }

  public function checkAuthorized(string $jwt, string $uid): bool
  {
    $decoded = $this->decodeToken($jwt);
    if($decoded->uid === $uid){
      return true;
    } else {
      return false;
    }
  }

  private function tokenExists(string $jwt_token): bool
  {
    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db
        ->select()
        ->from('session')
        ->where('token', '=', $jwt_token)
        ->where('has_expired', '=', 0)
      ;

      $tokens = $stmt->execute()->fetchAll();

      return !empty((array) $tokens);
    } catch(PDOException $e){
      return false;
    }
  }

  public function removeToken(string $jwt_token): bool
  {
    try{
      // Get DB Object
      $db = new db();
      // Connect
      $db = $db->connect();

      $stmt = $db
        ->update(array('has_expired' => 1))
        ->table('session')
        ->where('token', '=', $jwt_token)
      ;

      $stmt->execute();
    } catch(PDOException $e){
      return false;
    }
    return false;
  }

  private function decodeToken(string $jwt_token)
  {
    return JWT::decode($jwt_token, $this->apikey, array('HS256'));
  }

}