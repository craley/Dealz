<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$test = 'my french cat';
//echo str_replace(' ', '_', $test);

function gear($a = 2, $b = 1){
    echo "a: $a  and b: $b";
}
gear(2, 44);

function parseXml($file){
    require_once 'stuff.xml';
    $data = simplexml_load_file($file);
    echo "<br/>" . $data->Items->Item->OfferSummary->LowestNewPrice->Amount;
}
function parseConfig(){
    $data = file_get_contents("config.json");
    $json = json_decode($data, true);//true converts objs to assoc arrays
    foreach($json as $key => $value){
        if(!is_array($value)){
            
        }
    }
}

//parseXml('stuff.xml');