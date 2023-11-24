<?php
/**
 * Created by IntelliJ IDEA.
 * User: mvscoelho
 * Date: 15/11/15
 * Time: 10:27
 */

error_reporting(E_ALL);
ini_set("display_errors", true);

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

$config = [
    'app_id' => 'APPID',
    'app_secret' => 'APPSECRET',
    'default_graph_version' => 'v2.2',
    'fileUpload' => false,
    'default_access_token' => 'APPTOKENk'
];

$fbcallback = PROT . URL . '/fb-callback';

$tokenUrl = "https://graph.facebook.com/oauth/access_token?client_id=APPCLIENTID&client_secret=APPCLIENTSECRET&redirect_uri=$fbcallback&grant_type=client_credentials";

//echo '<a href="' . htmlspecialchars($tokenUrl) . '">GetToken!</a><br>';

$fb = new \Facebook\Facebook($config);

$helper = $fb->getRedirectLoginHelper();

$permissions = [
    'email',
    'user_posts',
    'publish_to_groups',
    'manage_pages',
    'publish_pages',
    'publish_video'
]; // Optional permissions

$loginUrl = $helper->getLoginUrl($fbcallback, $permissions);

echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
exit(0);
