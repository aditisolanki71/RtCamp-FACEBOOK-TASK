<?php
  session_start();
  require_once('facebook-config.php');
  
  $fb = new Facebook\Facebook([
    'app_id' => APP_ID, // Replace {app-id} with your app id
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.2',
    'default_access_token' => isset($_SESSION['facebook_access_token']) ? $_SESSION['facebook_access_token']  : APP_SECRET
    ]);
  try{
    $accessToken= $_SESSION['facebook_access_token'];
    if(!isset($accessToken)){
      header('location:index.php');
    }
    else{
      $downloadLinks="";
      $albumIds=explode("_",$_GET['albumid']);

      for($i=0;$i<count($albumIds);$i++){
        if($albumIds[$i]!=""){
          $album_img2= $fb->get('/'.$albumIds[$i].'/photos',$accessToken);
          $user2 = $album_img2->getGraphEdge();

          $zip = new ZipArchive;
          
          if ($zip->open('zip/'.$albumIds[$i].'.zip', ZIPARCHIVE::CREATE) != TRUE) {
              die ("Could not open archive");
          }

          for($j=0;$j<count($user2);$j++){
            $album_img3= $fb->get('/'.$user2[$j]['id'].'?fields=images',$accessToken);
            $user3 = $album_img3->getGraphNode();
            $im=$user3['images'][0];
            $zip->addFromString($j.'.jpg', file_get_contents($im['source']));
          }
          $zip->close();
          $downloadLinks=$downloadLinks.'_zip/'.$albumIds[$i].'.zip';
        }
      }
      echo $downloadLinks;
    }
  }
  catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo $e->getMessage();
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
  }
?>
