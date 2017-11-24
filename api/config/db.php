<?php
class db{
  // Properties
  private $dbhost;
  private $dbuser;
  private $dbpass;
  private $dbname;

  private static $instance;

  public function __construct()
  {
    if(!self::$instance)
    {
      $this->setDb();
      self::$instance = $this;
    }
    return self::$instance;

  }

  // Connect
  public function connect()
  {
    $dsn = "mysql:host=$this->dbhost;dbname=$this->dbname;charset=utf8";
    $pdo = new \Slim\PDO\Database($dsn, $this->dbuser, $this->dbpass);

    return $pdo;
  }

  private function setDb(){
    $this->dbhost = getenv('DB_HOST');
    $this->dbuser = getenv('DB_USER');
    $this->dbpass = getenv('DB_PASS');
    $this->dbname = getenv('DB_NAME');
  }
}