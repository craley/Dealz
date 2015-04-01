<?php

/* 
 * API key: 
 */

//require_once 'vendor/google/apiclient/src/Google/Client.php';
//require_once 'googlesdk/src/Auth/Oauth2.php';

//$client = new Google_Client();
//$client->setApplicationName("Google UserInfo Starter App");
//$oauth2 = new Google_Auth_OAuth2($client);

class GoogleAuth {
    
    protected $client;
    public $payload;
    public $userinfo;
    
    public function __construct(Google_Client $googleClient = null) {
        $this->client = $googleClient;
        if($this->client){
            $this->client->setClientId('----------------');
            $this->client->setClientSecret('---------------');
            $this->client->setRedirectUri('http://localhost/Demo/oauth2callback');
            $this->client->setScopes(array(
                'https://www.googleapis.com/auth/plus.login', 
                'https://www.googleapis.com/auth/plus.me', 
                'https://www.googleapis.com/auth/userinfo.email', 
                'https://www.googleapis.com/auth/userinfo.profile'
                ));
        }
    }
    public function isLoggedIn(){
        return isset($_SESSION['access_token']);
    }
    public function getAuthUrl(){
        return $this->client->createAuthUrl();
    }
    public function checkRedirectCode($code){
        if(isset($code)){
            $this->client->authenticate($code);
            //$this->setToken($this->client->getAccessToken());
            $token = $this->client->getAccessToken();
            $_SESSION['access_token'] = $token;
            $this->client->setAccessToken($token);
            //$this->payload = $this->client->verifyIdToken()->getAttributes()['payload'];
            
            //need to handle failure!
            $plus = new Google_Service_Plus($this->client);
            $userProfile = $plus->people->get('me');
            $emails = $userProfile->getEmails();
            
            $lastName = $userProfile->name->familyName;
            $firstName = $userProfile->name->givenName;
            $email = $emails[0]->value;
            
            $this->userinfo = [
                'first' => $firstName,
                'last' => $lastName,
                'email' => $email,
            ];
            return true;
        }
        return false;
    }
    public function processCode($code){
        $service = new Google_Service_Plus($this->client);
        $oauth2 = new Google_Service_Oauth2($this->$client);
        $this->client->authenticate($code);//attempt to exchange code for auth token
        $_SESSION['token'] = $this->client->getAccessToken();
        if(isset($_SESSION['token'])){
            $set_asess_token = $this->client->setAccessToken($_SESSION['token']);
        }
        if($this->client->getAccessToken()){
            $data = $service->people->get('me');
            $user_data = $oauth2->userinfo->get();
        }
    }
    public function setToken($token){
        $_SESSION['access_token'] = $token;
        $this->client->setAccessToken($token);
    }
    public function logout(){
        unset($_SESSION['access_token']);
    }
    /*
     * external id:  payload['id']
     * email:        payload['email']
     */
    public function getPayload(){
        $payload = $this->client->verifyIdToken()->getAttributes()['payload'];
        return $payload;
    }
    public function getEmail(){
        if(isset($this->userinfo)){
            return $this->userinfo['email'];
        }
    }
    public function getUserInfo(){
        if(isset($this->userinfo)){
            return $this->userinfo;
        }
    }
    public function test(){
        var_dump($this->payload);
    }
}
