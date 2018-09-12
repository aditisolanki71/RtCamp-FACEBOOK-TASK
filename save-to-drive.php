<?php

  require_once 'lib/google-api-php-client/src/Google/Client.php';
  require_once 'lib/google-api-php-client/src/Google/Service/Oauth2.php';
  require_once 'lib/google-api-php-client/src/Google/Service/Drive.php';
  require_once('facebook-config.php');
  require_once("functions.php");

  session_start();
  if(isset($_COOKIE["credentials"])){
    $folderDesc = "";
    $folderName = "";
    $albumname = "";
    $credentials = $_COOKIE["credentials"];

    $accessToken = $_SESSION['facebook_access_token'];
    if (isset($accessToken)) {
        $_SESSION['facebook_access_token'] = (string) $accessToken;
    }

    $fb = new Facebook\Facebook([
      'app_id' => APP_ID,
      'app_secret' => APP_SECRET,
      'default_graph_version' => 'v2.2',
      'default_access_token' => isset($_SESSION['facebook_access_token']) ? $_SESSION['facebook_access_token']  : 'df3701a7d8be6009bfe2a46f5352b949'
    ]);

    $res = $fb->get('/me?fields=id,name');
    $user = $res->getGraphUser();
    $arr=explode("_",$_GET['album']);
    $folderName="facebook_".$user['name']."_albums";
    if($arr[1]!="")
      $albumname=$arr[1];
    else
      $albumname=$arr[0];

    $json = json_decode(file_get_contents("lib/GoogleClientId.json"), true);
    $CLIENT_ID = $json['web']['client_id'];
    $CLIENT_SECRET = $json['web']['client_secret'];
    $REDIRECT_URI = $json['web']['redirect_uris'][0];

    $SCOPES = array(
    	'https://www.googleapis.com/auth/drive.file',
    	'https://www.googleapis.com/auth/userinfo.email',
    	'https://www.googleapis.com/auth/userinfo.profile');

    $client = new Google_Client();
    $client->setClientId($CLIENT_ID);
    $client->setClientSecret($CLIENT_SECRET);
    $client->setRedirectUri($REDIRECT_URI);
    $client->addScope(
      'https://www.googleapis.com/auth/drive.file',
      "https://www.googleapis.com/auth/drive", 
      "https://www.googleapis.com/auth/drive.appfolder");

    $client->setAccessToken($credentials);
    $service = new Google_Service_Drive($client);

    $folderid=getFolderExistsCreate($service, $folderName, $folderDesc);

    $folder = new Google_Service_Drive_DriveFile();
    $folder->setTitle($albumname);

    $folder->setMimeType('application/vnd.google-apps.folder');
    $parent = new Google_Service_Drive_ParentReference();
    $parent->setId($folderid);
    $folder->setParents(array($parent));
 
    try {
	     $newAlbumFolder = $service->files->insert($folder, array(
		  'mimeType' => 'application/vnd.google-apps.folder',
      ));

  	  $re = $fb->get(
        '/'.$arr[0].'/photos?limit=500',
        $accessToken
      );
      $graphEdge = $re->getGraphEdge();

      for($i=0;$i<count($graphEdge);$i++){
        $response = $fb->get(
            '/'.$graphEdge[$i]['id'].'?fields=images',
            $accessToken
          );
        $graphNode = $response->getGraphNode();
        $a0 = $graphNode['images'][0];
        $driveInfo = insertFile($service, $i, $a0['source'], $newAlbumFolder->id);
        echo "sucess";
      }
  	}catch(Exception $e) {
  		print "An error occurred: " . $e->getMessage();
  	}
  }
  else{
    header("Location : ".WEB."save-credentials.php");
  }
?>