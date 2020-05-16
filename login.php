<!DOCTYPE html>
<head>
  <title>Log In to Riverside Rocks</title>
</head>
<body>
</body>
<?php
        
$usr = array(
    '466262009256869889',
);
        
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes. In case if your CURL is slow and is loading too much (Can be IPv6 problem)

error_reporting(E_ALL);

define('OAUTH2_CLIENT_ID', '711349647666315295');
define('OAUTH2_CLIENT_SECRET', 'LZYzZeAwcqloE15-x4YS0fpgygOM4lW3');

$authorizeURL = 'https://discordapp.com/api/oauth2/authorize';
$tokenURL = 'https://discordapp.com/api/oauth2/token';
$apiURLBase = 'https://discordapp.com/api/users/@me';

session_start();

// Start the login process by sending the user to Discord's authorization page
if(get('action') == 'login') {

  $params = array(
    'client_id' => OAUTH2_CLIENT_ID,
    'redirect_uri' => 'https://dashboard.riverside.rocks/home',
    'response_type' => 'code',
    'scope' => 'identify guilds'
  );

  // Redirect the user to Discord's authorization page
  header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params));
  die();
}


// When Discord redirects the user back here, there will be a "code" and "state" parameter in the query string
if(get('code')) {

  // Exchange the auth code for a token
  $token = apiRequest($tokenURL, array(
    "grant_type" => "authorization_code",
    'client_id' => OAUTH2_CLIENT_ID,
    'client_secret' => OAUTH2_CLIENT_SECRET,
    'redirect_uri' => 'https://dashboard.riverside.rocks/home',
    'code' => get('code')
  ));
  $logout_token = $token->access_token;
  $_SESSION['access_token'] = $token->access_token;


  header('Location: ' . $_SERVER['PHP_SELF']);
}

if(session('access_token')) {
  $user = apiRequest($apiURLBase);

  echo '<h3>Logged In</h3>';
  echo '<h4>Welcome, ' . $user->username . '#' . $user->discriminator . '</h4>';
  echo '<pre>';
  echo '</pre>';
  if(in_array($user->id, $usr) == true){
    echo "<h1>Riverside Rocks Plus - Dashboard</h1>";
    echo "Congrats! You have been whitelisted and are now in the Riverside Rocks Plus program.";
    echo "We are still getting everything set up, so stick around.";
  }
  else{
    echo "<h1>That sucks!</h1>";
    echo "<h3>It appears that you are not in the Riverside Rocks Plus program.</h3>";
    echo "<h3>You can apply <a href='/contact'>here</a>. Be sure to tell us that you want to apply.</h3>";
  }
  echo '<p><a href="?action=logout">Log Out</a></p>';
  

} else {
  echo '<h3>Sign in.</h3>';
  echo '<p style="font-size:20px;"><a href="?action=login">Log In<i class="fab fa-discord"></i></a></p>';
}


if(get('action') == 'logout') {
  // This must to logout you, but it didn't worked(

      session_unset();


  // Redirect the user to Discord's revoke page
  header("/discord");
  die();
}

function apiRequest($url, $post=FALSE, $headers=array()) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $response = curl_exec($ch);


  if($post)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

  $headers[] = 'Accept: application/json';

  if(session('access_token'))
    $headers[] = 'Authorization: Bearer ' . session('access_token');

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $response = curl_exec($ch);
  return json_decode($response);
}

function get($key, $default=NULL) {
  return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function session($key, $default=NULL) {
  return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

?>