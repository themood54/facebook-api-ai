<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => '298825430560300',
  'app_secret' => '4ac8cfdbd7eb8f655606244856d7e610',
  'default_graph_version' => 'v2.9',
  ]);

$helper = $fb->getRedirectLoginHelper();

//noda
define('APP_URL', 'http://livewebsite.org/Facebook/');

$permissions = ['email','publish_actions']; // optional
	
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
    
    // validating the access token
	try {
		$user = $fb->get('/me');
		$user = $user->getGraphNode()->asArray();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' .$e->getMessage();
		session_destroy();
		//if token invalid
			exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	
	//you replace q= mark moody or what ever with your search to load the page with data from the form on same page need video submit data without refreshing/reloading the page jquery ajaax to get code use the phpdev.facebook app it will give you the cosde for any search
	$search = $fb->get('/search?q=mark moody&type=page');
	$search = $search->getGraphEdge()->asArray();

	foreach ($search as $key) {
		echo $key['name'] . '<br>';
	}
	
	//echo $_SESSION['facebook_access_token'];
  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
	
	$loginUrl = $helper->getLoginUrl('http://livewebsite.org/Facebook/fbsearch.php/', $permissions);
	echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
}





