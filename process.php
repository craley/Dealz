<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



function parseXml($file){
    require_once 'stuff.xml';
    $data = simplexml_load_file($file);
    echo "<br/>" . $data->Items->Item->OfferSummary->LowestNewPrice->Amount;
}

parseXml('stuff.xml');