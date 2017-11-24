<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get All from form exc_board_form
$app->get('/exe_form', function(Request $req, Response $res) {

  try{
    $db = new db();
    $db = $db->connect();
    $stmt = $db
      ->select()
      ->from('exc_board_form')
      ->where('form_for', '=',1)
    ;
    $data = $stmt->execute()->fetchAll();

    return $res->withJson($data);

  }catch (PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $res->withJson($err);
  }
});

// Get csv file form exc_board_form
$app->get('/exe_form_csv', function(Request $req, Response $res) {
  $token = $req->getQueryParams();
  $auth = new auth();

  $check = $auth->checkToken($token['token']);
  if(!$check) return $res->withJson(["msg"=>"You Need to Login again sir", "status"=>0]);

  $delimiter = ",";
  $filename = "executives_" . date('Y-m-d') . ".csv";

  try{
    $db = new db();
    $db = $db->connect();
    $stmt = $db
      ->select()
      ->from('exc_board_form')
      ->where('form_for', '=',1)
    ;
    $user_subs = $stmt->execute()->fetchAll();

    //create a file pointer
    $f = fopen('php://memory', 'w');

    //set column headers
    $fields = array('name', 'gender', 'nationality', 'd_birth', 'institution', 'level_of_study', 'program_name', 'ocupation', 'visa', 'invitation', 'id_number', 'contact_number', 'email', 'skype', 'facebook', 'linkedin', 'allergies', 'food', 'f_name', 'm_name', 'e_number', 'prev_exp_as_ex', 'prev_exp_as_del', 'prev_achive', 'com_pref', 'agendas', 'board_pref', 'exp_cond', 'judge', 'prev_bug_exp', 'affiate', 'role');
    fputcsv($f, $fields, $delimiter);

    //output each row of the data, format line as csv and write to file pointer
    foreach($user_subs as $user_sub){
      $user_sub = (object) $user_sub;
      $lineData = array(
        $user_sub->name,
        $user_sub->gender,
        $user_sub->nationality,
        $user_sub->d_birth,
        $user_sub->institution,
        $user_sub->level_of_study,
        $user_sub->program_name,
        $user_sub->ocupation,
        $user_sub->visa,
        $user_sub->invitation,
        $user_sub->id_number,
        $user_sub->contact_number,
        $user_sub->email,
        $user_sub->skype,
        $user_sub->facebook,
        $user_sub->linkedin,
        $user_sub->allergies,
        $user_sub->food,
        $user_sub->f_name,
        $user_sub->m_name,
        $user_sub->e_number,
        $user_sub->prev_exp_as_ex,
        $user_sub->prev_exp_as_del,
        $user_sub->prev_achive,
        $user_sub->com_pref,
        $user_sub->agendas,
        $user_sub->board_pref,
        $user_sub->exp_cond,
        $user_sub->judge,
        $user_sub->prev_bug_exp,
        $user_sub->affiate,
        $user_sub->role
      );
      fputcsv($f, $lineData, $delimiter);
    }

    //move back to beginning of file
    fseek($f, 0);

    //set headers to download file rather than displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    //output all remaining data on a file pointer
    fpassthru($f);


  }catch (PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $res->withJson($err);
  }
});

// Get All from form exc_board_form
$app->post('/exe_form', function(Request $req, Response $res) {

  $user_sub = (object) $req->getParams();
  if($user_sub->name === '' ||
    $user_sub->institution === '' ||
    $user_sub->email === ''){
    $err = [ 'error' => ['text'=> 'Required Fields Must be given']];
    return $res->withJson($err);
  }

  try{

    $db = new db();
    $db = $db->connect();

    $input_data =
      array(
        $user_sub->name,
        $user_sub->gender,
        $user_sub->nationality,
        $user_sub->d_birth,
        $user_sub->institution,
        $user_sub->level_of_study,
        $user_sub->program_name,
        $user_sub->ocupation,
        $user_sub->visa,
        $user_sub->invitation,
        $user_sub->id_number,
        $user_sub->contact_number,
        $user_sub->email,
        $user_sub->skype,
        $user_sub->facebook,
        $user_sub->linkedin,
        $user_sub->allergies,
        $user_sub->food,
        $user_sub->f_name,
        $user_sub->m_name,
        $user_sub->e_number,
        $user_sub->prev_exp_as_ex,
        $user_sub->prev_exp_as_del,
        $user_sub->prev_achive,
        $user_sub->com_pref,
        $user_sub->agendas,
        $user_sub->board_pref,
        $user_sub->exp_cond,
        $user_sub->judge,
        $user_sub->prev_bug_exp,
        $user_sub->affiate,
        $user_sub->role,
        1
      );

    $stmt = $db
      ->insert(array('name', 'gender', 'nationality', 'd_birth', 'institution', 'level_of_study', 'program_name', 'ocupation', 'visa', 'invitation', 'id_number', 'contact_number', 'email', 'skype', 'facebook', 'linkedin', 'allergies', 'food', 'f_name', 'm_name', 'e_number', 'prev_exp_as_ex', 'prev_exp_as_del', 'prev_achive', 'com_pref', 'agendas', 'board_pref', 'exp_cond', 'judge', 'prev_bug_exp', 'affiate', 'role', 'form_for'))
      ->into('exc_board_form')
      ->values($input_data)
    ;
    $data = $stmt->execute();

//    $data = json_decode($r2->getBody());

    return $res->withJson($data);

  }catch (PDOException $e){
    $err = [ 'error' => ['text'=> $e->getMessage()]];
    return $res->withJson($err);
  }
});