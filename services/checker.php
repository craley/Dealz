<?php

//1. deduce anyone with product priority not zero
//2. check current lowest price
//3. if less, notify

function getProspects() {
    require_once 'utilities.php';
    require_once 'database.php';
    $credents = getDatabaseCredentialsTest(); //TESSSSSSSSSSSSST
    $db = new Database($credents);
    //data format [    0 => [ uid => 1, asin => 34343, lowest => 36.78 ]
    //                 1 => [ uid => 1, asin => 34343, lowest => 89.43 ]    ]
    $data = $db->getUpdates();
    if (!$data)
        return;
    foreach ($data as $record) {
        $uid = $record['uid'];
        $asin = $record['asin'];
        
    }
}
$stack = new SplStack();
$stack->push("bob");
$stack->push("ted");
while(!$stack->isEmpty()){
    echo $stack->pop() . "<br/>";
}
$map = new SplDoublyLinkedList();
$map->unshift("plug");
