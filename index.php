<?php
    require_once('facebook-config.php');
    session_start();
    $fb = new Facebook\Facebook([
        'app_id' => APP_ID,
        'app_secret' => APP_SECRET,
        'default_graph_version' => 'v2.2',
        'default_access_token' => isset($_SESSION['facebook_access_token']) ? $_SESSION['facebook_access_token']  : APP_SECRET
        ]);
        $helper = $fb->getRedirectLoginHelper();
        $permissions=['email','user_photos'];
        $loginUrl= $helper->getLoginUrl(REDIRECT_URL,$permissions);
        $accessToken= $helper->getAccessToken();
        if(isset($accessToken))
            $_SESSION['facebook_access_token']= (string) $accessToken;

        if(isset($_SESSION['facebook_access_token']))
            header('location:'.WEB.'display-albums.php');
    
?>
<!DOCTYPE html>
<html>
    <head>
         <title>My Home Page</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="lib/css/Mycss.css">
        <link rel="stylesheet" href="lib/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="lib/js/bootstrap.min.js"></script>
        <style type="text/css">
        .clr
        {
            color:white;
            background-color:#4267B2;
        }
        </style>
    </head>
<body style="background-image: url('images/facebook_backgroundimg.jpg');height:100vh;background-position: center;background-repeat: no-repeat;background-size: cover;">
    <nav class="navbar navbar-expand-sm clr">

 
  <ul class="navbar-nav">
    <li class="nav-item">
        <b>RtCamp Challenge</b>
    </li>
  </ul>

</nav>
<div class="container margintop" style="margin-top:200px;">
    <center>
    <figcaption>
        <div class="w3-container w3-center w3-animate-bottom">
        <a href="<?php echo $loginUrl; ?>">
                    <button type="button" class="btn" style="background-color:#4267B2;">Login With Facebook</button>
        </a> 
        </div>  
    </figcaption>
</center>
</div>
</body>
</html>

