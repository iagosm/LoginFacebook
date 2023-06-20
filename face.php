<?php
session_start();
// unset($_SESSION['face_access_token']);
include_once 'conexao.php';
// require_once __DIR__ . '/vendor/autoload.php'; // change path as needed
require_once 'lib/Facebook/autoload.php';
$fb = new \Facebook\Facebook([
  'app_id' => '1754506961665500',
  'app_secret' => '0a457b246a253497c619eb2d419a102e',
  'default_graph_version' => 'v2.9',
  //'default_access_token' => '{access-token}', // optional
]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email'];
// var_dump($helper);

  try {  // TryCatch criado para accessToken
    if(isset($_SESSION['face_access_token'])){
      $accessToken = $_SESSION['face_access_token'];
    }else{
      $accessToken = $helper->getAccessToken(); //obter as informações do usuario
    }

} catch(Facebook\Exceptions\FacebookResponseException $e) { // erro relacionado quando o usuario n permite o sistema não acessar alguma informação, como : amigos, email ...
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) { // Erro relacionado ao SDK
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
}



if(!isset($accessToken)) {
  $url_login = 'http://localhost/loginface/face.php';
  $loginUrl = $helper->getLoginUrl($url_login, $permissions);
}else {
  $url_login = 'http://localhost/loginface/face.php';
  $loginUrl = $helper->getLoginUrl($url_login, $permissions);
  //verificar usuario ja autenticado
  if(isset($_SESSION['face_access_token'])) {
    $fb->setDefaultAccessToken($_SESSION['face_access_token']);
  } else { // Quando usuario não estiver autenticado
    $_SESSION['face_access_token'] = (string) $accessToken; //Personalizada que ira receber como string a variavel
    $oAuth2Client = $fb->getOAuth2Client();
    $_SESSION['face_access_token'] = (string) $oAuth2Client->getLongLivedAccessToken($_SESSION['face_access_token']); // prolongar a duração logada 
    $fb->setDefaultAccessToken($_SESSION['face_access_token']);
  }

  try {
    //Returna ao facebook\facebookresponse objeto
    $response = $fb->get('/me?fields=name, picture, id, email'); // Já está sendo definido acima o acess token de forma default
    $user = $response->getGraphUser();
    // var_dump($user);
    $result_usuario = "SELECT id, nome, email FROM usuarios WHERE usuario='".$user['id'] ."' LIMIT 1";
		$resultado_usuario = mysqli_query($conn, $result_usuario);
		if($resultado_usuario){
			$row_usuario = mysqli_fetch_assoc($resultado_usuario);
			  $_SESSION['id'] = $row_usuario['id'];
				$_SESSION['nome'] = $row_usuario['nome'];
				$_SESSION['email'] = $row_usuario['email'];
				header("Location: administrativo.php");
			}
		

  } catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
	}
}
?>

