<?php

session_start();
require_once('facebook-config.php');
 $accessToken = $_SESSION['facebook_access_token'];
  if (isset($accessToken)) {
      $_SESSION['facebook_access_token'] = (string) $accessToken;
  }
  $fb = new Facebook\Facebook([
    'app_id' => APP_ID, 
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.2',
    'default_access_token' => isset($_SESSION['facebook_access_token']) ? $_SESSION['facebook_access_token']  : APP_SECRET
    ]);
try
{
    $response = $fb->get(
        '/'.$_GET['imageid'].'?fields=images',
        $accessToken
      );
    $graphNode = $response->getGraphNode();
    $a0 = $graphNode['images'][0];   

    echo $a0['source'];
}catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}
?>