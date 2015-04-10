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
        
        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest();//TESSSSSSSSSSSSST
        $db = new Database($credents);
        $uid = $db->validateUser($_POST['userLogin'], $_POST['pswd']);
        if($uid > -1){
            //load user data
            $profile = $db->getUser($uid);
            $products = $db->getProducts($uid);//possible products
            session_start();
            $_SESSION['uid'] = $uid;
            require_once 'home.php';
            exit();
        }
    }
    header("HTTP/1.1 404 Not Found");
} elseif ($action == 'register') {
    if (isset($_POST['userLogin']) && isset($_POST['pswd']) && isset($_POST['email']) && !empty($_POST['userLogin']) && !empty($_POST['pswd']) && !empty($_POST['email'])) {
        
        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest();//TESSSSSSSSSSSSST
        $db = new Database($credents);
        $uid = $db->emailExists($_POST['email']);
        if($uid > -1){
            $db->setUserPswd($uid, $_POST['userLogin'], $_POST['pswd']);
        } else {
            $db->insertUser($_POST['userLogin'], $_POST['pswd'], $_POST['email']);
            $uid = $db->emailExists($_POST['email']);
        }
        //load user data
        $profile = $db->getUser($uid);
        $products = $db->getProducts($uid);//possible products
        session_start();
        $_SESSION['uid'] = $uid;
        require_once 'home.php';
        exit();
    }
    header("HTTP/1.1 404 Not Found");
} elseif ($action == 'google') {
    //$code = explode(",", file_get_contents('php://input'));

    if (isset($_POST['code'])) {
        $config = json_decode(file_get_contents('../app/config.json'));
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
        
        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest();//TESSSSSSSSSSSSST
        $db = new Database($credents);
        $uid = $db->emailExists($email);
        if($uid > -1){
            $db->setFirstLast($uid, $firstName, $lastName);
        } else {
            $db->insertUserVendor($email, $firstName, $lastName);
            $uid = $db->emailExists($email);
        }
        //load user data
        $profile = $db->getUser($uid);
        $products = $db->getProducts($uid);//possible products
        $_SESSION['uid'] = $uid;
        require_once 'home.php';
        exit();
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
} else if($action == 'add'){
    //Post product add
    if(isset($_POST['uid']) && isset($_POST['asin']) && !empty($_POST['uid']) && !empty($_POST['asin'])){
        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest();//TESSSSSSSSSSSSST
        $db = new Database($credents);
        $db->insertProduct($_POST['uid'], $_POST['asin'], $_POST['title'], $_POST['maker']);
    }
} else if($action == 'remove'){
    
}
echo "Not found";

