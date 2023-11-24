<?php
/**
 * Created by IntelliJ IDEA.
 * User: mvscoelho
 * Date: 15/11/15
 * Time: 10:27
 */

$debug = false;

if ($debug) {
    error_reporting(E_ALL);
    ini_set("display_errors", true);
}

$config = [
    'app_id' => 'APPID',
    'app_secret' => 'APPSECRET',
    'default_graph_version' => 'v2.2',
    'fileUpload' => false,
    'default_access_token' => 'APPTOKENk'
];

$clas_dir = PATH_CONTENT.'app/Library/includes';

require $clas_dir.'/Facebook/Facebook.php';
require $clas_dir.'/Facebook/FacebookApp.php';
require $clas_dir.'/Facebook/FacebookClient.php';
require $clas_dir.'/Facebook/FacebookRequest.php';
require $clas_dir.'/Facebook/FacebookResponse.php';

require $clas_dir.'/Facebook/Authentication/AccessToken.php';
require $clas_dir.'/Facebook/Authentication/AccessTokenMetadata.php';
require $clas_dir.'/Facebook/Authentication/OAuth2Client.php';

require $clas_dir.'/Facebook/Http/RequestBodyInterface.php';
require $clas_dir.'/Facebook/Http/GraphRawResponse.php';
require $clas_dir.'/Facebook/Http/RequestBodyMultipart.php';
require $clas_dir.'/Facebook/Http/RequestBodyUrlEncoded.php';

require $clas_dir.'/Facebook/HttpClients/HttpClientsFactory.php';
require $clas_dir.'/Facebook/HttpClients/FacebookHttpClientInterface.php';
require $clas_dir.'/Facebook/HttpClients/FacebookCurlHttpClient.php';
require $clas_dir.'/Facebook/HttpClients/FacebookCurl.php';

require $clas_dir.'/Facebook/Helpers/FacebookRedirectLoginHelper.php';

require $clas_dir.'/Facebook/GraphNodes/GraphNodeFactory.php';
require $clas_dir.'/Facebook/GraphNodes/Collection.php';
require $clas_dir.'/Facebook/GraphNodes/GraphNode.php';
require $clas_dir.'/Facebook/GraphNodes/GraphUser.php';

require $clas_dir.'/Facebook/Exceptions/FacebookSDKException.php';
require $clas_dir.'/Facebook/Exceptions/FacebookResponseException.php';
require $clas_dir.'/Facebook/Exceptions/FacebookAuthenticationException.php';

require $clas_dir.'/Facebook/Url/FacebookUrlManipulator.php';
require $clas_dir.'/Facebook/Url/UrlDetectionInterface.php';
require $clas_dir.'/Facebook/Url/FacebookUrlDetectionHandler.php';

require $clas_dir.'/Facebook/PersistentData/PersistentDataFactory.php';
require $clas_dir.'/Facebook/PersistentData/PersistentDataInterface.php';
require $clas_dir.'/Facebook/PersistentData/FacebookMemoryPersistentDataHandler.php'; // For Composer
require $clas_dir.'/Facebook/PersistentData/FacebookSessionPersistentDataHandler.php';

require $clas_dir.'/Facebook/PseudoRandomString/PseudoRandomStringGeneratorFactory.php';
require $clas_dir.'/Facebook/PseudoRandomString/PseudoRandomStringGeneratorInterface.php';
require $clas_dir.'/Facebook/PseudoRandomString/PseudoRandomStringGeneratorTrait.php';
require $clas_dir.'/Facebook/PseudoRandomString/McryptPseudoRandomStringGenerator.php';
require $clas_dir.'/Facebook/PseudoRandomString/RandomBytesPseudoRandomStringGenerator.php';

$fb = new Facebook\Facebook($config);

$helper = $fb->getRedirectLoginHelper();

try {
    $accessToken = $helper->getAccessToken();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    if ($debug) {
        echo 'Graph returned an error: '.$e->getMessage();
        exit;
    }
    header("Location: ".PROT.URL.'/anunciante/login?fb=1');
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    if ($debug) {
        echo '1 Facebook SDK returned an error: '.$e->getMessage();
        exit;
    }
    header("Location: ".PROT.URL.'/anunciante/login?fb=2');
}

if (!isset($accessToken)) {
    if ($debug) {
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: ".$helper->getError()."\n";
            echo "Error Code: ".$helper->getErrorCode()."\n";
            echo "Error Reason: ".$helper->getErrorReason()."\n";
            echo "Error Description: ".$helper->getErrorDescription()."\n";
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
        }
    }
    exit;
}

if ($debug) {
// Logged in
    echo '<h2>Access Token</h2>';
    var_dump($accessToken->getValue());
}
// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($accessToken);
if ($debug) {
    echo '<h2>Metadata</h2>';
    var_dump($tokenMetadata);
}

// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId($fb->getApp()->getId()); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
$tokenMetadata->validateExpiration();

if (!$accessToken->isLongLived()) {
    // Exchanges a short-lived access token for a long-lived one
    try {
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        if ($debug) {
            echo "<p>Error getting long-lived access token: ".$e->getMessage()."</p>\n\n";
        }
        exit;
    }
    if ($debug) {
        echo '<h2>Long-lived</h2>';
        var_dump($accessToken->getValue());
    }
}

$_SESSION['fb_access_token'] = (string)$accessToken;

// User is logged in with a long-lived access token.
// You can redirect them to a members-only page.
//header('Location: https://example.com/members.php');

try {
    // Returns a `Facebook\FacebookResponse` object
    $response = $fb->get('/me?fields=birthday,name,email,gender', $accessToken);
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    if ($debug) {
        echo 'Graph returned an error: '.$e->getMessage();
    }
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    if ($debug) {
        echo '2 Facebook SDK returned an error: '.$e->getMessage();
    }
    exit;
}
$user = $response->getGraphUser();

if ($user) {
    global $session_login;
    $login = $session_login->LoginParticularByFacebook($user->getEmail(), $_SESSION['fb_access_token']);
    $result = $session_login->loginToSession($login);

    if ($debug) {
        echo '<h2>$_SESSION</h2>';
        var_dump($_SESSION);
    }
    if ($result) {
        header("Location: ".PROT.URL.'/anunciante/painel/');
    }
}

if ($debug) {
    echo '<h2>Userdata</h2>';
    var_dump($user);
    echo '<br>';
}
exit;
