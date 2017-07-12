<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';

$db = new mysqli('localhost', 'themood1', 'badmood54', 'fbapp1');
// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
} 
echo "Connected successfully";



$fb = new Facebook\Facebook([
  'app_id' => '298825430560300',
  'app_secret' => '4ac8cfdbd7eb8f655606244856d7e610',
  'default_graph_version' => 'v2.9',
  ]);

$helper = $fb->getRedirectLoginHelper();
define('APP_URL', 'http://livewebsite.org/Facebook/');

$permissions = []; // optional

try {
	if (isset($_SESSION['facebook_access_token'])) {
		$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
	// When Graph returns an error
 	echo 'Graph returned an error: ' . $e->getMessage();
  	
  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
 	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }

if (isset($accessToken)) {
	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
		// getting short-lived access token
		$_SESSION['facebook_access_token'] = (string) $accessToken;
	  	
	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();
				// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
		
		// setting default access token to be used in script
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
    
    //redirect to same page if it has code
    if (isset($_GET['code'])) {
		header('Location: ./');
	}
    
   // getting basic info about user
	try {
		$profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
		$profile = $profile_request->getGraphNode()->asArray();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		unset($_SESSION['facebook_access_token']);
		echo "<script>window.top.location.href='https://apps.facebook.com/APP_NAMESPACE/'</script>";
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	//echo $_SESSION['facebook_access_token'];
  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
	$name = $profile['id'];
	$first_name = $profile['first_name'];
	$sql = "INSERT INTO users (name, token, first_name)
	VALUES ('{$name}', '{$accessToken}', '{$first_name}')"; 
if ($db->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$db->close();

} else {
	
	$loginUrl = $helper->getLoginUrl('http://livewebsite.org/Facebook/index2.php/', $permissions);
	echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
}





