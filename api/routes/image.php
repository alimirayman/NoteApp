<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$container = $app->getContainer();
$container['upload_directory'] = dirname(__DIR__) . '\images' . '\\';

// Get All Customers
$app->post('/images', function(Request $request, Response $response){
  $token = $request->getHeader('Token');
  $auth = new auth();

  $check = $auth->checkToken($token[0]);
  if(!$check) return $response->withJson(["msg"=>"You are not Authorized My Friend", "status"=>0]);

  $base64_string = $request->getParam('image');

  $directory = $this->get('upload_directory');

  $extension = "jpg";
  $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
  $filename = sprintf('%s.%0.8s', $basename, $extension);
  $output_file = $directory . $filename;
  $image = base64_to_jpeg($base64_string, $output_file);


  if($image){
    return $response->withJson($filename);
  }else{
    return $response->withStatus(404);
  }


});


function base64_to_jpeg($base64_string, $output_file) {
  // open the output file for writing
  $ifp = fopen( $output_file, 'wb' );

  // split the string on commas
  // $data[ 0 ] == "data:image/png;base64"
  // $data[ 1 ] == <actual base64 string>
  $data = explode( ',', $base64_string );

  // we could add validation here with ensuring count( $data ) > 1
  fwrite( $ifp, base64_decode( $data[ 1 ] ) );

  // clean up the file resource
  fclose( $ifp );

  return $output_file;
}
