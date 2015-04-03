<?php

/*
 * Handles all ajax calls from app.
 * Hybrid Controller: no url rewriting.
 * Use demodb database
 */
require_once '../vendor/autoload.php'; //loads the library

$action;
$disaster = true;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['action']) && !empty($_GET['action'])) {
        $action = $_GET['action'];
        $disaster = false;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && !empty($_POST['action'])) {
        $action = $_POST['action'];
        $disaster = false;
    }
}
if ($disaster) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
//Activate the appropriate service
if ($action == 'login') {
    if (isset($user) && isset($pswd) && !empty($user) && !empty($pswd)) {
        require_once 'app/Database.php';
        $uid = -1;
        if (($uid = \app\db\Database::validateUser($user, $pswd)) > -1) {
            session_start();
            $_SESSION['uid'] = $uid;
            $_SESSION['username'] = $user;
            //header('Location: /Demo/home');
            require_once 'home.php';
            exit();
        }
    }
    header("HTTP/1.1 404 Not Found");
} elseif ($action == 'register') {
    
} elseif ($action == 'google') {
    //$code = explode(",", file_get_contents('php://input'));

    if (isset($_POST['code'])) {
        $config = json_decode(file_get_contents('../config.json'));
        $client = new Google_Client();
        //$client->setClientId('961741834099-nv3c7j13nm3fmis23sm1g8g83ctr995l.apps.googleusercontent.com');
        //$client->setClientSecret('YMOaxU6CAXfEI_4fDIyg15Z7');
        $client->setClientId($config->googleClientId);
        $client->setClientSecret($config->googleClientSecret);

        $client->setRedirectUri('postmessage');
        $client->setScopes(array(
            'https://www.googleapis.com/auth/plus.login',
            'https://www.googleapis.com/auth/plus.me',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ));
        $plus = new Google_Service_Plus($client);

        $client->authenticate($_POST['code']);
        $token = json_decode($client->getAccessToken());

        //$attribs = $client->verifyIdToken($token->id_token, "clientID")->getAttributes();
        //$gplus_id = $attribs['payload']['sub'];
        //save token
        session_start();
        $_SESSION['token'] = json_encode(token);

        $userProfile = $plus->people->get('me');
        $emails = $userProfile->getEmails();

        $lastName = $userProfile->name->familyName;
        $firstName = $userProfile->name->givenName;
        $email = $emails[0]->value;

        //echo "<p>$firstName</p><p>$lastName</p><p>$email</p>";
        require_once 'home.php';
    } else {
        echo "<p>Invalid Code</p>";
    }
    exit();
} else if ($action == 'userSniff') {
    if (isset($_POST['code'])) {
        //determine if member and auto-login
    }
}
echo "Not found";

