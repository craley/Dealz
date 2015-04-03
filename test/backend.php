<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function obtain($val){
    $json = json_decode(file_get_contents('../config.json'));
    return $json->$val;
}
$snag = obtain('googleClientId');
echo $snag;
exit();

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    if(isset($_GET['userLogin'])){
        echo "GET:: " . $_GET['userLogin'];
    } else {
        echo "failed GET";
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['userLogin'])){
        echo "POST:: " . $_POST['userLogin'];
    } else {
        echo "failed POST";
    }
}