<?php

/*
 * App Functionality(Services).
 * 
 * Handles all ajax calls from app.
 * Hybrid Controller: no url rewriting.
 * Use demodb database
 */
require_once '../vendor/autoload.php';

class Router {//Controller 2.0
    
    public function login($username, $pswd){
        
    }
    public function register($username, $pswd, $email){
        
    }
    public function googleLogin($code){
        require_once 'google.php';
        $google = new Google;
        $google->authenticate($code);
        $profile = $google->getUserData();
        //ensure google sent enough data to identify user.
        if($this->validateProfile($profile)){
            require_once 'database.php';
            $db = new Database();
            $uid;
            if(($uid = $db->userExistence($profile))){
                //user found, pull account
            } else {
                //first time, register
            }
            exit();
        }
        header("HTTP/1.1 404 Not Found");
    }
    public function addProduct(){
        
    }
    public function search($params){
        require_once 'amazon.php';
        $amazon = new Amazon;
        $result = $amazon->search($params);
        require_once 'view.php';
        if(!empty($result)){
            echo (new View)->renderSearch($result);
        } else {
            echo (new View)->renderFailedSearch();
        }
    }
    /*
     * Internal handling of failure on Google's part
     * to return the proper profile information.
     */
    private function validateProfile($profile){
        if(!empty($profile['email'])) return true;
        if(!empty($profile['first']) and !empty($profile['last']) and !empty($profile['birthday'])){
            return true;
        }
        if(!empty($profile['first']) and !empty($profile['last'])){
            //decide::
            return true;//for now????
        }
        return false;
    }
}
