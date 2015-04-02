<?php
/*
 * Handles all ajax calls from app.
 * Hybrid Controller: no url rewriting.
 * 
 */

$action;
$disaster = true;

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    if(isset($_GET['action']) && !empty($_GET['action'])){
        $action = $_GET['action'];
        $disaster = false;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action']) && !empty($_POST['action'])){
        $action = $_POST['action'];
        $disaster = false;
    }
}
if($disaster){
    header("HTTP/1.1 404 Not Found");
    exit();
}
//Activate the appropriate service

