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
    if (isset($_POST['userLogin']) && isset($_POST['pswd']) && !empty($_POST['userLogin']) && !empty($_POST['pswd'])) {
        require_once '../app/Database.php';
        $uid = -1;
        if (($uid = \app\db\Database::validateUser($_POST['userLogin'], $_POST['pswd'])) > -1) {
            session_start();
            $_SESSION['uid'] = $uid;
            $_SESSION['username'] = $_POST['userLogin'];
            require_once 'home.php';
            exit();
        }
    }
    header("HTTP/1.1 404 Not Found");
} elseif ($action == 'register') {
    if (isset($_POST['userLogin']) && isset($_POST['pswd']) && isset($_POST['email']) && !empty($_POST['userLogin']) && !empty($_POST['pswd']) && !empty($_POST['email'])) {
        require_once '../app/Database.php';
        $uid = -1;
        require_once 'app/Database.php';
        /*
         * The access key is really the email. Only it must
         * be unique.
         */
        if(\app\db\Database::isUserUnique($user, $email)){
            //new person, just add them
            \app\db\Database::insertUser($user, $pswd, $email);
            $uid = \app\db\Database::validateUser($user, $pswd);
            
            session_start();
            $_SESSION['uid'] = $uid;
            $_SESSION['username'] = $user;
            require_once 'home.php';
            exit();
        } elseif(!\app\db\Database::isUserNameUnique($username)) {//change to email, same usernames ok
            //username is not unique
            require_once 'home.php';
            exit();
        } else {
            //have unique username, but email exists
            //have they already set a username and pswd?
            $uInfo = \app\db\Database::getUserInfoByEmail($email);
            //temporary fixx, just overwrite any existing username, pswd
            \app\db\Database::appendUserPswd($email, $user, $pswd);
            //create All category
            \app\db\Database::createAllCategory($uInfo['uid']);
            session_start();
            $_SESSION['uid'] = $uInfo['uid'];
            $_SESSION['username'] = $uInfo['username'];
            require_once 'home.php';
            exit();
        }
    }
    header("HTTP/1.1 404 Not Found");
} elseif ($action == 'google') {
    //$code = explode(",", file_get_contents('php://input'));

    if (isset($_POST['code'])) {
        $config = json_decode(file_get_contents('../config.json'));
        $client = new Google_Client();
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
        $_SESSION['token'] = json_encode($token);

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
} else if($action == 'query'){
    
    require_once 'utilities.php';
    $data = conductProductSearch([
        'keyword' => $_GET['keyword'], 
        'category' => $_GET['category'], 
        'condition' => $_GET['condition'], 
        'page' => $_GET['page']]);
    if(!empty($data)){
        require_once 'query.php';
    }
    exit();
}
echo "Not found";

