<?php

/*
 * TODO: Deal with token refreshing
 */
error_reporting( E_ALL );

# openid/oauth credentials
define('OAUTH_CONSUMER_KEY', 'dj0yJmk9eVFId1B4N3BNZGEzJmQ9WVdrOWJWRnBhSFpZTlRnbWNHbzlNemM1T1RRM01EazQmcz1jb25zdW1lcnNlY3JldCZ4PWE3'); // bigo test key; please replace with your own.
define('OAUTH_CONSUMER_SECRET', 'ee84086f94c111c1ea40dee7d040e69ff521b2e4');
define('OAUTH_DOMAIN', 'localhost');
define('OAUTH_APP_ID', 'mQihvX58');

# date time
date_default_timezone_set('America/Los_Angeles');

session_start( );
# utf8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ob_start('mb_output_handler');

# debug settings
error_reporting(E_ALL); #  | E_STRICT -- hide strict as openid lib is verbose
ini_set('display_errors', true);

# set include path (required for openid, oauth, opensocial libs)
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../lib/OpenID/'.PATH_SEPARATOR.dirname(__FILE__).'/../lib/');
// yos-social-php5 is broken. Using the old one then. FIXME: fix the OAuth 
// client in ysp5. but later... i better focus on meme ;P
require_once( dirname( __FILE__ ) . './../../lib/working_OAuth/Yahoo.inc' );
require_once(dirname( __FILE__ ) . '/../../lib/Yahoo/YahooMeme.class.php' );
$action = null;
if ( isset($_REQUEST['action']) ) {
    $action = $_REQUEST['action'];
}
$consumer = new OAuthConsumer( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, NULL );
$callback = 'http://localhost/yos/examples/meme/oAuthExample.php?action=authorized';
$params = array( "oauth_callback" => $callback  );
if ( !$action || $action == 'request_and_authenticate' ) {
    $req_req = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", "https://api.login.yahoo.com/oauth/v2/get_request_token",$params);
    $req_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1( ) , $consumer, NULL);

    $request  = $req_req->to_url( );
    $session = curl_init( $request);
    curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1);
    // Make the request
    $response = curl_exec( $session);

    //Error Handling:
    // there is an error while executing the request, 
    if ( !$response) {
       $response = curl_error( $curl);  
    }  
    curl_close($session);
    parse_str( $response, $params);
    $oauth_token = $params['oauth_token'];
    $oauth_token_secret = $params['oauth_token_secret'];
    $_SESSION['CONSUMER_KEY'] =  OAUTH_CONSUMER_KEY;
    $_SESSION['CONSUMER_SECRET'] = OAUTH_CONSUMER_SECRET;
    $_SESSION['REQUEST_TOKEN'] = $oauth_token;
    $_SESSION['REQUEST_TOKEN_SECRET'] = $oauth_token_secret;

    $auth_url = 'https://api.login.yahoo.com/oauth/v2/request_auth?oauth_token='.$oauth_token.'&oauth_callback='.urlencode( $callback_url);
    Header( "Location: $auth_url");

} else if ( $action == "authorized" ) {

    $request_token = $_SESSION['REQUEST_TOKEN'];
    $request_token_secret = $_SESSION['REQUEST_TOKEN_SECRET'];
    $consumer_key = $_SESSION['CONSUMER_KEY'];
    $consumer_secret = $_SESSION['CONSUMER_SECRET'];
    $access_url = "https://api.login.yahoo.com/oauth/v2/get_token";
    $access_consumer = new OAuthConsumer( $consumer_key, $consumer_secret, NULL);
    $access_token = new OAuthConsumer( $request_token, $request_token_secret);
    $parsed = parse_url( $access_url);
    $params = array( "oauth_verifier" => $_GET['oauth_verifier'] );
    $acc_req = OAuthRequest::from_consumer_and_token( $consumer, $access_token, "GET", $access_url, $params);
    $acc_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1( ), $consumer, $access_token);
    $request  = $acc_req->to_url( );
    print urldecode( $acc_req->to_url(  ) );
    $session = curl_init( $request);
    curl_setopt( $session, CURLOPT_RETURNTRANSFER, 1);
    // Make the request
    $response = curl_exec( $session);
    if ( !$response) {;
        $response = curl_error($curl);  
    }  
    curl_close( $session);
    parse_str( $response, $params);
    $access_token = $params['oauth_token'];
    $access_token_secret = $params['oauth_token_secret'];
    $_SESSION['ACCESS_TOKEN'] = $access_token;
    $_SESSION['ACCESS_TOKEN_SECRET'] = $access_token_secret;
} else if( $action == "post" && $_SESSION['ACCESS_TOKEN'] && $_SESSION['ACCESS_TOKEN_SECRET'] ) {

    $consumer = new OAuthConsumer( $_SESSION['CONSUMER_KEY'], $_SESSION['CONSUMER_SECRET']);
    $token = new OAuthToken( $_SESSION['ACCESS_TOKEN'], $_SESSION['ACCESS_TOKEN_SECRET']);

    /* Congratulations! You've just logged in into Yahoo! and now are able to 
     * post on meme :P' */
    $meme = new MemeRepository(  );
    wtf($meme->insert($consumer, $token,  'text', $_GET['content'] ));
    print "CONGRATS! YOU DID IT";

}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <title>Yahoo! Meme posting example!</title>
    </head>
    <body>
        <p>The goal of this example is to show you how to add content to Meme using your own app ;P</p>
        <form method="GET">
            <input type="hidden" name="action" value="post" />
            <textarea name="content" cols="60" rows="6"></textarea>
            <input type="submit" value="post this on meme!" />
        </form>
    </body>
</html>
