<?php

/* 
 * 3 Flows:
 *  1. Client-side flow.(Not Recommended)
 *  2. Hybrid flow.**
 *  3. Pure Server flow.(Not Recommended)
 * 
 * Hybrid:
 *  1. user on client presses button, authenticates at google.com
 *  2. google returns a One-time token to client(browser).
 *  3. client sends One-time token to server via ajax.
 *  4. Server exchanges One-time token to google for Access and Refresh tokens.
 *  5. Use Access token to make Google+ Api calls on user's behalf with Google Services.
 */

class Google {
    
    const ACCESS = '../app/config.json';
    private $client;
    private $plus;
    private $accessToken;
    private $refreshToken;

    public function __construct() {
        $config = json_decode(file_get_contents(self::ACCESS));
        $this->client = new Google_Client();
        $this->client->setClientId($config->googleClientId);
        $this->client->setClientSecret($config->googleClientSecret);
        $this->client->setDeveloperKey($config->googleApiKey);
        $this->client->setRedirectUri('postmessage');
        $this->client->setScopes(array(
            'https://www.googleapis.com/auth/plus.login',
            'https://www.googleapis.com/auth/plus.me', 
            'https://www.googleapis.com/auth/userinfo.email', 
            'https://www.googleapis.com/auth/userinfo.profile'
        ));
    }
    /*
     * getAccessToken returns:
     * { "access_token":"TOKEN", "refresh_token":"TOKEN", "token_type":"Bearer", "expires_in":3600,"id_token":"TOKEN", "created":1320790426 }
     */
    public function authenticate($one_time_token){
        //exchange for access and refresh tokens
        $this->client->authenticate($one_time_token);
        //now retrieve Access token
        $accessTokenString = $this->client->getAccessToken();//Returns a json encoded string
        //to apply token to new instance of client: client->setAccessToken()
        //now build a service object for the api to call by passing client to it
        
        //Next, convert access string to object
        $accessObject = json_decode($accessTokenString);
        $this->accessToken = $accessObject->access_token;
        $this->refreshToken = $accessObject->refresh_token;
        return $this->accessToken;//allow caller to store in Session
    }
    public function getUserData(){
        $this->plus = new Google_Service_Plus($client);
        //Acquire profile of currently authenticated user.
        $userProfile = $plus->people->get('me');
        
        $lastName = $userProfile->name->familyName;
        $firstName = $userProfile->name->givenName;
        $birthday = $userProfile->birthday;
        $email = $this->plus['emails'][0]['value'];
        return [ 'first' => $firstName, 'last' => $lastName, 'email' => $email, 'birthday' => $birthday ];
    }
    /*
     * Requires Drive scope to work.
     */
    public function getDriveFiles($client){
        $driveService = new Google_Service_Drive($client);
        $files = $driveService->files->listFiles(array())->getItems();
        return $files;
    }
    /*
     * Initializing client from a preexisting Access token.
     */
    public function reestablish($existingAccessToken){
        $this->client->setAccessToken($accessToken);
        $accessTokenString = $this->client->getAccessToken();
        $accessObject = json_decode($accessTokenString);
        $this->accessToken = $accessObject->access_token;
        $this->refreshToken = $accessObject->refresh_token;
        return $this->accessToken;
    }
}