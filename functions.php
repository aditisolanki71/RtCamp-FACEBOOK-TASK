<?php 
require_once 'lib/google-api-php-client/src/Google/Client.php';
require_once "lib/google-api-php-client/src/Google/Service/Oauth2.php";

header('Content-Type: text/html; charset=utf-8');

// Get your app info from JSON downloaded from google dev console
$json = json_decode(file_get_contents("lib/GoogleClientId.json"), true);

$CLIENT_ID = $json['web']['client_id'];
$CLIENT_SECRET = $json['web']['client_secret'];
$REDIRECT_URI = $json['web']['redirect_uris'][0];

// Set the scopes you need
$SCOPES = array(
	'https://www.googleapis.com/auth/drive.file',
	'https://www.googleapis.com/auth/userinfo.email',
	'https://www.googleapis.com/auth/userinfo.profile');

function storeCredentials($userId, $credentials, $userInfo) {
	$_SESSION["userInfo"] = $userInfo;
	setcookie("userId", $userId, time() + (86400 * 30), "/");
	setcookie("credentials", $credentials, time() + (86400 * 30), "/");
}

function getStoredCredentials($userId) {
	// TODO: Integrate with a database
	if(isset($_COOKIE["credentials"])) {
		return $_COOKIE["credentials"];
	}else {
		return null;
	}
}

function getAuthorizationUrl($emailAddress, $state) {
	global $CLIENT_ID, $REDIRECT_URI, $SCOPES;
	$client = new Google_Client();

	$client->setClientId($CLIENT_ID);
	$client->setRedirectUri($REDIRECT_URI);
	$client->setAccessType('offline');
	$client->setApprovalPrompt('auto');
	$client->setState($state);
	$client->setScopes($SCOPES);
	$tmpUrl = parse_url($client->createAuthUrl());
	$query = explode('&', $tmpUrl['query']);
	$query[] = 'user_id=' . urlencode($emailAddress);
	
	return
	$tmpUrl['scheme'] . '://' . $tmpUrl['host'] .
	$tmpUrl['path'] . '?' . implode('&', $query);
}

function exchangeCode($authorizationCode) {
	try {
		global $CLIENT_ID, $CLIENT_SECRET, $REDIRECT_URI;
		$client = new Google_Client();

		$client->setClientId($CLIENT_ID);
		$client->setClientSecret($CLIENT_SECRET);
		$client->setRedirectUri($REDIRECT_URI);
		return $client->authenticate($authorizationCode);
	} catch (Exception $e) {
		print 'An error occurred: ' . $e->getMessage();
	}
	
}
function getCredentials($authorizationCode, $state) {
	$emailAddress = '';
	try {
		$credentials = exchangeCode($authorizationCode);
		$userInfo = getUserInfo($credentials);
		$emailAddress = $userInfo->getEmail();
		$userId = $userInfo->getId();
		$credentialsArray = json_decode($credentials, true);
		if (isset($credentialsArray['refresh_token'])) {
			storeCredentials($userId, $credentials, $userInfo);
			return $credentials;
		} else {
			$credentials = getStoredCredentials($userId);
			if ($credentials != null && isset($credentials)) {
				storeCredentials($userId, $credentials, $userInfo);
			return $credentials;
		} else {
			echo "Unexpected error.";die;
		}
	}
} catch (CodeExchangeException $e) {
	print 'An error occurred during code exchange.';
	$e->setAuthorizationUrl(getAuthorizationUrl($emailAddress, $state));
	throw $e;
} catch (NoUserIdException $e) {
	print 'No e-mail address could be retrieved.';
}
$authorizationUrl = getAuthorizationUrl($emailAddress, $state);
}
function getUserInfo($credentials) {
	$apiClient = new Google_Client();
	$apiClient->setAccessToken($credentials);
	$userInfoService = new Google_Service_Oauth2($apiClient);
	try {
		$userInfo = $userInfoService->userinfo->get();

		if ($userInfo != null && $userInfo->getId() != null) {
			return $userInfo;
		} else {
			echo "No user ID";
		}
	} catch (Exception $e) {
		print 'An error occurred: ' . $e->getMessage();
	}	
}


function insertFile($service, $title, $filename, $folderName) {
  $file = new Google_Service_Drive_DriveFile();

  // Set the metadata
  $file->setTitle($title);

       $parent = new Google_Service_Drive_ParentReference();
      $parent->setId($folderName);
      $file->setParents(array($parent));
    
  
  try {
    // Get the contents of the file uploaded
    $data = file_get_contents($filename);
    // Try to upload the file, you can add the parameters e.g. if you want to convert a .doc to editable google format, add 'convert' = 'true'
    $createdFile = $service->files->insert($file, array(
      'data' => $data,
      'mimeType' => 'image/jpeg',
      'uploadType'=> 'multipart'
      ));
  } catch (Exception $e) {
    print "An error occurred: " . $e->getMessage();
  }
}

function getFolderExistsCreate($service, $folderName, $folderDesc) {
  // List all user files (and folders) at Drive root
		  $files = $service->files->listFiles();
		  $found = false;
		  // Go through each one to see if there is already a folder with the specified name
		  foreach ($files['items'] as $item) {
		    if ($item['title'] == $folderName) {
		      $found = true;
		      return $item['id'];
		      break;
		    }
		  }

		  // If not, create one
		  if ($found == false) {
		    $folder = new Google_Service_Drive_DriveFile();

		    //Setup the folder to create
		    $folder->setTitle($folderName);

		    if(!empty($folderDesc))
		      $folder->setDescription($folderDesc);

		    $folder->setMimeType('application/vnd.google-apps.folder');

		    //Create the Folder
		    try {
		      $createdFile = $service->files->insert($folder, array(
		        'mimeType' => 'application/vnd.google-apps.folder',
		        ));

		      // Return the created folder's id
		      return $createdFile->id;
		    } catch (Exception $e) {
		      print "An error occurred: " . $e->getMessage();
		    }
		  }

}


?>