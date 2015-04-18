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
        $credents = getDatabaseCredentialsTest(); //TESSSSSSSSSSSSST
        $db = new Database($credents);
        $uid = $db->validateUser($_POST['userLogin'], $_POST['pswd']);
        if ($uid > -1) {
            //load user data
            $profile = $db->getUser($uid);
            $products = $db->getProducts($uid); //possible products
            session_start();
            $_SESSION['uid'] = $uid;
            require_once 'home.php';
            exit();
        }
    }
    header("HTTP/1.1 404 Not Found");
    exit();
} elseif ($action == 'register') {
    if (isset($_POST['userLogin']) && isset($_POST['pswd']) && isset($_POST['email']) && !empty($_POST['userLogin']) && !empty($_POST['pswd']) && !empty($_POST['email'])) {

        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest(); //TESSSSSSSSSSSSST
        $db = new Database($credents);
        $uid = $db->emailExists($_POST['email']);
        if ($uid > -1) {
            $db->setUserPswd($uid, $_POST['userLogin'], $_POST['pswd']);
        } else {
            $db->insertUser($_POST['userLogin'], $_POST['pswd'], $_POST['email']);
            $uid = $db->emailExists($_POST['email']);
        }
        //load user data
        $profile = $db->getUser($uid);
        $products = $db->getProducts($uid); //possible products
        session_start();
        $_SESSION['uid'] = $uid;
        require_once 'home.php';
        exit();
    }
    header("HTTP/1.1 404 Not Found");
    exit();
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

        $client->authenticate($_POST['code']);
        $token = $client->getAccessToken();
        //save token
        session_start();
        $_SESSION['token'] = json_decode($token);

        if ($token) {
            $client->setAccessToken($token);
            $oath = new Google_Service_Oauth2($client);
            $plus = new Google_Service_Plus($client);
            $userData = $oath->userinfo->get();
            $userProfile = $plus->people->get('me');
            
            
            $emailList = $userProfile->getEmails();
            $lastName = $userProfile->name->familyName;
            $firstName = $userProfile->name->givenName;
            
            if($emailList){
                $email = $emailList[0]->value;
            }
            //make 2nd attempts for email and names
            if(!isset($email)){
                $email = $userData->email;
            }
            if(!isset($firstName)){
                $firstName = $userData->given_name;
            }
            if(!isset($lastName)){
                $lastName = $userData->family_name;
            }
            //Handle: nothing found
            if(!isset($email) and !isset($firstName) and !isset($lastName)){
                header("HTTP/1.1 404 Not Found");
                exit();
            }
            
            require_once 'utilities.php';
            require_once 'database.php';
            $credents = getDatabaseCredentialsTest(); //TESSSSSSSSSSSSST
            $db = new Database($credents);
            $uid = $db->googleUserExists($email, $firstName, $lastName);
            if($uid == -1){
                //create
                $db->insertUserVendor($firstName, $lastName, $email);//pssibly null email
                if(!isset($email)){
                   $uid = $db->nameExists($firstName, $lastName); 
                } else {
                    $uid = $db->emailExists($email);
                }
                
            }
            
            //load user data
            $profile = $db->getUser($uid);
            $products = $db->getProducts($uid); //possible products
            $_SESSION['uid'] = $uid;
            require_once 'home.php';
            exit();
        }
        
        //$plus = new Google_Service_Plus($client);
        //$userProfile = $plus->people->get('me');
        //$emails = $userProfile->getEmails();
        //$lastName = $userProfile->name->familyName;
        //$firstName = $userProfile->name->givenName;
        //$email = $emails[0]->value;
        //var_dump($userProfile);


        exit();
    } else {
        echo "<p>Invalid Code</p>";
    }
    exit();
} else if ($action == 'userSniff') {
    if (isset($_POST['code'])) {
        //determine if member and auto-login
    }
    exit();
} else if ($action == 'query') {

    require_once 'utilities.php';
    $data = conductProductSearch([
        'keyword' => $_GET['keyword'],
        'category' => $_GET['category'],
        'condition' => $_GET['condition'],
        'page' => $_GET['page']]);
    if (!empty($data)) {
        require_once 'query.php';
    }
    exit();
} else if ($action == 'add') {
    //Post product add
    if (isset($_POST['uid']) && isset($_POST['asin']) && !empty($_POST['uid']) && !empty($_POST['asin'])) {
        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest(); //TESSSSSSSSSSSSST
        $db = new Database($credents);
        $db->insertProduct($_POST['uid'], $_POST['asin'], $_POST['title'], $_POST['maker']);
    }
    exit();
} else if ($action == 'remove') {
    if (isset($_POST['uid']) && isset($_POST['asin']) && !empty($_POST['uid']) && !empty($_POST['asin'])) {
        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest(); //TESSSSSSSSSSSSST
        $db = new Database($credents);
        $db->removeProduct($_POST['uid'], $_POST['asin']);
    }
    exit();
} else if($action == 'offer'){
    if(isset($_GET['asin']) and !empty($_GET['asin'])){
        require_once 'utilities.php';
        $data = conductProductOffers($_GET['asin']);//condition optional
        if(isset($data) and !empty($data)){
            
            require_once 'offer.php';
        }
    }
    exit();
} else if($action == 'update'){
    $params = [];
    if(isset($_POST['uid']) and !empty($_POST['uid'])){
        if(isset($_POST['firstName']) and !empty($_POST['firstName'])){
            $params['firstName'] = $_POST['firstName'];
        }
        if(isset($_POST['lastName']) and !empty($_POST['lastName'])){
            $params['lastName'] = $_POST['lastName'];
        }
        if(isset($_POST['phone']) and !empty($_POST['phone'])){
            $params['phone'] = $_POST['phone'];
        }
        if(isset($_POST['carrier']) and !empty($_POST['carrier'])){
            $params['carrier'] = $_POST['carrier'];
        }
        if(isset($_POST['email']) and !empty($_POST['email'])){
            $params['email'] = $_POST['email'];
        }
        if(isset($_POST['username']) and !empty($_POST['username'])){
            $params['username'] = $_POST['username'];
        }
        require_once 'utilities.php';
        require_once 'database.php';
        $credents = getDatabaseCredentialsTest(); //TESSSSSSSSSSSSST
        $db = new Database($credents);
        $db->updateProfile($_POST['uid'], $params);
    }
    exit();
}
echo "Not found";

