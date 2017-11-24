<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$authentication =function(Request $req, Response $res, $next)
{
  $token = $req->getHeader('Authorization');

  if(is_array($token)) $token = preg_split("/ /", $token[0])[1];

  $auth = new auth();

  $check = $auth->checkToken($token);
  if ($check) {
    return $next($req, $res);
  } else {
    return $res->withStatus(403)->withJson(["msg" => "You Need to Login again sir", "status" => 0]);
  }
};

$authorization = function(Request $req, Response $res, $next)
{
  $token = $req->getHeader('Authorization');
  $uid = $req->getParam('user_id');

  if(!isset($uid) || is_null($uid)) {
    $route = $req->getAttribute('route');
    $uid = $route->getArgument('user_id');
  }
  if(is_array($token)) $token = preg_split("/ /", $token[0])[1];

  $auth = new auth();

  $check = $auth->checkAuthorized($token, $uid);
  if($check){
    return $next($req, $res);
  }else {
    return $res->withJson(["msg" => "You are not authorized", "status" => 0]);
  }
};