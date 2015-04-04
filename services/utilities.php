<?php

/*
 * 9044155045
 * traciwelden@gmail.com
 * TMobile: tmomail.net
 * Verizon: vtext.com
 */

function sendText($number, $carrier, $subject, $message) {
    $carrierMap = [
        'tmobile' => 'tmomail.net',
        'verizon' => 'vtext.com',
        'atat' => 'mobile.att.net',
        'metro' => 'number@mymetropcs.com',
        'sprint' => 'messaging.sprintpcs.com'
    ];
    
    if (isset($number) && isset($carrierMap[$carrier])) {
        $pmsg = wordwrap($message, 70);
        $dest = "$number@{$carrierMap[$carrier]}";
        
        if(!mail("$number@{$carrierMap[$carrier]}", $subject, $pmsg)){
            echo "Mailing failed.";
        }
    }
}
$traci = '9044155045';
$chris = '9044343696';
//sendText($traci, 'tmobile', 'pimp', 'im a fucking machine!!!!');

function sendEmail($dest, $subject, $message){
    if(isset($dest) && isset($message)){
        $pmsg = wordwrap($message);
        if(!mail($dest, $subject, $pmsg)){
            echo "Email Failed.";
        }
    }
}
//sendEmail('traciwelden@gmail.com', 'pimp my ride', 'ur sink is on');

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

//parseXml('../stuff.xml');