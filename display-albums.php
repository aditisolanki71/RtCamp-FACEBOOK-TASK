<?php
  session_start();
  require_once('facebook-config.php');
  if(!isset($_SESSION['facebook_access_token']))
        header('location:'.WEB.'index.php');

  $fb = new Facebook\Facebook([
    'app_id' => APP_ID, 
    'app_secret' => APP_SECRET,
    'default_graph_version' => 'v2.2',
    'default_access_token' => isset($_SESSION['facebook_access_token']) ? $_SESSION['facebook_access_token']  : APP_SECRET
    ]);
  try
  { 
    $accessToken= $_SESSION['facebook_access_token'];
    $response= $fb->get('/me?fields=albums',$accessToken);
    $user = $response->getGraphUser();
    if(isset($_GET['btn-download']) || isset($_GET['download-selected-id']))
    {

      $downloadLinks="";
      if(isset($_GET['btn-download']))
        $albumIds=explode("_",$_GET['ids']);

      if(isset($_GET['download-selected-id']))
        $albumIds=explode("_",$_GET['download-selected-id']);

      for($i=0;$i<count($albumIds)-1;$i+=2)
      {
        if($albumIds[$i]!=""){
          $album_img2= $fb->get('/'.$albumIds[0].'/photos?limit=500',$accessToken);
          $user2 = $album_img2->getGraphEdge();

          $zip = new ZipArchive;
          
          if ($zip->open('zip/'.$albumIds[$i+1].'.zip', ZIPARCHIVE::CREATE) != TRUE) {
              die ("Could not open archive");
          }

          for($j=0;$j<count($user2);$j++){
            $album_img3= $fb->get('/'.$user2[$j]['id'].'?fields=images',$accessToken);
            $user3 = $album_img3->getGraphNode();
            $im=$user3['images'][0];
            $zip->addFromString($j.'.jpg', file_get_contents($im['source']));
          }
          $zip->close();
          $downloadLinks=$downloadLinks.'_zip/'.$albumIds[$i+1].'.zip';

           echo '<iframe src="download.php?link='.basename($downloadLinks).'" id="ifame" style="display:none"></iframe>';


          }
    }
  }
}
  catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo $e->getMessage();
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
  }
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Albums</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="lib/css/Mycss.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="lib/js/bootstrap.min.js"></script>

</head>
<style type="text/css">
  .bg-img{
    background-image: url('images/facebook_backgroundimg.jpg');
    height:100vh;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
  }
 .mynav{
    color:white;
    background-color:#4267B2;
  }
    .modal-full {
    min-width: 100%;
    margin: 0;
}

.modal-full .modal-content {
    min-height: 100vh;
}
</style>
<body class="bg-img">
  <nav class="navbar navbar-expand-lg navbar-light mynav">
    <b><a class="navbar-brand" href="#">RtCamp Challenge</a></b>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
      </ul>
      <div class="form-inline my-2 my-lg-0">
        <button class="btn btn-success my-2 my-sm-0" onClick="download_all_album();">Download All</button>&nbsp;&nbsp;  
        <button class="btn btn-success my-2 my-sm-0" onClick="download_selected_album();" id="download_seleted">Download Selected</button>&nbsp;&nbsp;
         <button class="btn btn-success my-2 my-sm-0" onClick="move_all_album();">Move All</button>&nbsp;&nbsp;
        <button class="btn btn-success my-2 my-sm-0" onClick="move_selected_album();" id="move_selected">Move Selected</button>&nbsp;&nbsp;
         <a href="logout.php"><button class="btn btn-success my-2 my-sm-0" type="submit">Logout</button></a>
      </div>
    </div>
  </nav>
  <div class="album py-5">
    <div class="container">
      <div class="row">
        <?php
          for($i=0;$i<count($user['albums']);$i++){
            $album_img= $fb->get('/'.$user['albums'][$i]['id'].'?fields=cover_photo,photo_count',$accessToken);
            $user1 = $album_img->getGraphNode();
            $count=$user1['photo_count'];
    if(isset($user1['cover_photo'])){
            $album_img3= $fb->get('/'.$user1['cover_photo']['id'].'?fields=images',$accessToken);
              $user3 = $album_img3->getGraphNode();
              $im=$user3['images'][0];

            ?>
        <div class="col-md-4">
          <div class="w3-container w3-center w3-animate-bottom">
            <div class="card mb-4 box-shadow">
              <img class="card-img-top" style="height:330px;" src="<?php echo $im['source'] ?>" alt="Card image cap" onClick="displaySlider(<?php echo $user['albums'][$i]['id'] ?>);">
              <div class="card-body" style="height: 110px">
                <p class="card-text">
                  <input type="checkbox" name="chk" value="<?php echo $user['albums'][$i]['id'].'_'.$user['albums'][$i]['name']; ?>" onclick="onoff()"><b><?php echo $user['albums'][$i]['name'] ?></b></p>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="btn-group">
                      <button onclick="download_album('<?php echo $user['albums'][$i]['id'].'_'.$user['albums'][$i]['name']; ?>')" class="btn btn-sm btn-primary" name="btn-download">Download</button>
                    <button type="button" class="btn btn-sm " onClick="move_album('<?php echo $user['albums'][$i]['id'].'_'.$user['albums'][$i]['name']; ?>');" style="height:31px;">Move to Drive</button>
                  </div>
                  <small class="text-muted"><b><?php echo $count.' Images' ?></b></small>
                </div>
              </div>
            </div>
          </div>
        </div>
    <?php }
        } ?>
      </div>
    </div>
  </div>
  <div class="modal fade" id="myModal" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="padding-top: 200px;">
      <div class="modal-content"  style="background-color: #2196f3; padding: 5px; width: 110px; margin: auto;color: white;" >
       <i class="fa fa-spinner fa-spin" style="font-size:100px; ">
       </i>
      </div>
    </div>
  </div>
<div class="modal" id="mySlider">
    <div class="modal-dialog modal-full" role="document">
        <div class="modal-content">
            <div id="demo" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner" id="img-container">
  
            </div>
            <a class="carousel-control-prev" href="#demo" data-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </a>
            <a class="carousel-control-next" href="#demo" data-slide="next">
              <span class="carousel-control-next-icon"></span>
            </a>
          </div>
        </div>
    </div>
</div>


<form action="" id="myForm" method="get">
  <input type="hidden" name="download-selected-id" id="newids">
</form>
</body>
</html>
<script type="text/javascript">

  $(document).ready(function() { 
    document.getElementById("ifame").remove();
  });
  

  disableAllButton();
  function onoff(){
    var selected_chk=document.querySelectorAll('input[name=chk]:checked');
    if(selected_chk.length>0)
      enableAllButton();
    else
      disableAllButton();
  }
  function enableAllButton(){
    document.getElementById("download_seleted").disabled = false; 
    document.getElementById("move_selected").disabled = false; 
  }
  function disableAllButton(){
    document.getElementById("download_seleted").disabled = true; 
    document.getElementById("move_selected").disabled = true; 
  }
  function download_album(id){
    $('#myModal').modal('toggle');
    var idField=document.getElementById("newids");
    idField.value=id;
     document.getElementById("myForm").submit();
     $('#myModal').modal('toggle');
  }
  function download_selected_album(){
    $('#myModal').modal('toggle');
    var selected_chk=document.querySelectorAll('input[name=chk]:checked');
    var selctedAlbums="";
     var idField=document.getElementById("newids");
    for(var i=0;i<selected_chk.length;i++)
    {
         selctedAlbums=selctedAlbums+selected_chk[i].value+"_";
    }
     idField.value=selctedAlbums;
     document.getElementById("myForm").submit();
     $('#myModal').modal('toggle');
   
   
  }
  function download_all_album(){
    var selected_chk=document.querySelectorAll('input[name=chk]');
    $('#myModal').modal('toggle');
    var selctedAlbums="";
    for(var i=0;i<selected_chk.length;i++)
    {
      selctedAlbums=selctedAlbums+selected_chk[i].value+"_";
    }
    $('#myModal').modal('toggle');
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        $('#myModal').modal('toggle');
        var links=this.responseText.split("_");
        for(var i=0;i<links.length;i++){
          if(links[i]!=""){
            window.open(links[i],"_blank");
          }
        }
      }
    };
    xhttp.open("GET", "download.php?albumid="+selctedAlbums, true);
    xhttp.send();
  }
  function displaySlider(id){

    document.getElementById("img-container").innerHTML = '';

    $("#img-container").append("<div class='carousel-item active'><img src='images/welcome_slideshow.jpeg'  style='height : 100vh; width:100% '></div>");
    loadImages(id);
    $('#mySlider').modal('toggle');
  }
  function move_album(id){
    var c=getCookie('credentials');
    if(c!="")
    {
      $('#myModal').modal('toggle');
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText="sucess")
            $('#myModal').modal('toggle');
        }
      };
      xhttp.open("GET", "save-to-drive.php?album="+id, true);
      xhttp.send();
    }
    else
    {
      window.location="<?php echo WEB ?>save-credentials.php";
    }


  }
  function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');

    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
  }
  function move_selected_album(){
    var selected_chk=document.querySelectorAll('input[name=chk]:checked');
    for(var i=0;i<selected_chk.length;i++){
      move_album(selected_chk[i].value);
    }
  }
  function move_all_album(){
    var selected_chk=document.querySelectorAll('input[name=chk]');
    for(var i=0;i<selected_chk.length;i++){
      move_album(selected_chk[i].value);
    }
  }

  function loadImages(id)
  {
    albumid=id;
    var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200)
        {
          var arr=this.responseText.split(',');
          for(var i=0;i<arr.length-1;i++)
          {
             var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) 
              {     
                 $("#img-container").append(" <div class='carousel-item'> <img src='"+this.responseText+"' style='height : 100vh; position: absolute; z-index:-1; width:100%; filter: blur(10px);'><img src='"+this.responseText+"' class='mx-auto d-block' style='height:100vh;'> </div>");
               }
            };
            xhttp.open("GET", "<?php echo WEB ?>load-album.php?imageid="+arr[i], true);
            xhttp.send();
          }
        }
      };
<<<<<<< HEAD
      xhttp.open("GET", "<?php echo WEB ?>get-Images.php?albumid="+id, true);
=======
      xhttp.open("GET", "http://localhost/rtCamp/get-Images.php?albumid="+id, true);
>>>>>>> 6d7e251a4da66a112a83d94012beac6bba942f81
      xhttp.send(); 
  }
</script>

