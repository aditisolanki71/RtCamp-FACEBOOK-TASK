<?php

  session_start();
  require_once('facebook-config.php');

  $fb = new Facebook\Facebook([
      'app_id' => APP_ID, // Replace {app-id} with your app id
      'app_secret' => APP_SECRET,
      'default_graph_version' => 'v2.2',
      'default_access_token' => isset($_SESSION['facebook_access_token']) ? $_SESSION['facebook_access_token']  : APP_SECRET
  ]);
  try
  {
    $accessToken= $_SESSION['facebook_access_token'];
    if(!isset($accessToken))
      header('location:index.php');
    else{
      $album_img2= $fb->get('/'.$_REQUEST["albumid"].'/photos',$accessToken);
      $user2 = $album_img2->getGraphEdge();
    }
  }
  catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo $e->getMessage();
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>My Slider</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script>
  function goFS(){
    var temp=document.getElementById("myCarousel");
      if (temp.requestFullscreen) {
          temp.requestFullscreen();
      } else if (temp.webkitRequestFullscreen) {
          temp.webkitRequestFullscreen();
      } else if (temp.mozRequestFullScreen) {
          temp.mozRequestFullScreen();
      } else if (temp.msRequestFullscreen) {
          temp.msRequestFullscreen();
      }
    }
  </script>
  <style type="text/css">
    #myCarousel{
      height: 100%;
      width: 100%;
    }
    .carousel-inner img{
      margin:auto;
    }
  </style>
</head>
<body onClick="goFS();">
<div class="container" style="width: 100%">
  <div id="myCarousel" class="carousel slide" data-ride="carousel" >
    <div class="carousel-inner" style="height:100vh;">
        <?php   
          for($i=0;$i<count($user2);$i++){
            $album_img3= $fb->get('/'.$user2[$i]['id'].'?fields=images',$accessToken);
            $user3 = $album_img3->getGraphNode();
            $im=$user3['images'][0];

            if($i == 0){
              echo '<div class="item active">
                      <img src="'.$im['source'].'" alt="Los Angeles" style="width:100vh;">
                    </div>';
            }
            else{
              echo '<div class="item">
                <img src="'.$im['source'].'" alt="Los Angeles" style="width:100vh;">
                </div>';
             }
          }
        ?>
      </div>
    <a class="left carousel-control" href="#myCarousel" data-slide="prev">
      <span class="glyphicon glyphicon-chevron-left"></span>
      <span class="sr-only">Previous</span>  
    </a>
    <a class="right carousel-control" href="#myCarousel" data-slide="next">
      <span class="glyphicon glyphicon-chevron-right"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
</div>
</body>
</html>



