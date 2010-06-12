<?php
//error_reporting( E_ALL );
require_once( dirname( __FILE__ ) .  "../../common.inc.php" );
//session_start(  );
/*
 * TODO: Deal with token refreshing
 */

// yos-social-php5 is broken. Using the old one then. FIXME: fix the OAuth 
#require_once( dirname( __FILE__ ) . './../../lib/working_OAuth/Yahoo.inc' );
require_once(dirname( __FILE__ ) . '/../../lib/Yahoo/YahooMeme.class.php' );
$action = null;
if ( isset($_REQUEST['action']) ) {
    $action = $_REQUEST['action'];
}
$callback = 'http://localhost/yos/examples/meme/oAuthExample.php?action=authorized';

$app = new YahooOAuthApplication( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID, OAUTH_DOMAIN ); 
if ( !$action || $action == 'request_token' ) {
  $request_token = $app->getRequestToken( $callback );
  $_SESSION['request_token_key'] = $request_token->key;
  $_SESSION['request_token_secret'] = $request_token->secret;
  $redirect_url = $app->getAuthorizationUrl( $request_token );
  Header( "Location: $redirect_url");

} else if ( $action == "authorized" ) {

  $request_token = new OAuthConsumer($_SESSION['request_token_key'], $_SESSION['request_token_secret']);
  $response = $app->getAccessToken($request_token, $_GET['oauth_verifier'] );
  parse_str( $response, $params);
    
  $access_token = $params['oauth_token'];
  $access_token_secret = $params['oauth_token_secret'];
  $_SESSION['ACCESS_TOKEN'] = $access_token;
  $_SESSION['ACCESS_TOKEN_SECRET'] = $access_token_secret;
} else if( $action == "post" ) {
    $token = new OAuthToken(  $_SESSION['ACCESS_TOKEN'], $_SESSION['ACCESS_TOKEN_SECRET']);
    //wtf( $token );
    $app->token = $token;
    /* Congratulations! You've just logged in into Yahoo! and now are able to 
     * post on meme :P' */
    $meme = new MemeRepository(  );
    wtf($meme->insert($app, 'text', $_REQUEST['content'] ));
    print "<h1>CONGRATS! YOU DID IT</h1>";

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
