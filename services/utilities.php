<?php

function getDistanceBetween($lat1, $long1, $lat2, $long2){
    $theta = $long1 - $long2;
    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
    $miles = acos($miles);
    $miles = rad2deg($miles);
    $miles = $miles * 60 * 1.1515;
    $feet = $miles * 5280;
    $yards = $feet / 3;
    $kilometers = $miles * 1.609344;
    $meters = $kilometers * 1000;
    return compact('miles', 'feet', 'yards', 'kilometers', 'meters');
}
function distanceUsage(){
    $point1 = [ 'lat' => 40.770623, 'long' => -73.964367 ];
    $point2 = [ 'lat' => 40.758224, 'long' => -73.917404 ];
    $distance = getDistanceBetween($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
    foreach ($distance as $unit => $val){
        echo $unit . ': ' . number_format($val, 4) . '<br/>';
    }
}
function getTweets($hash_tag){
    $url = 'http://search.twitter.com/search.atom?q=' . urlencode($hash_tag);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $xml = curl_exec($ch);
    curl_close($ch);
    echo "<p>Response</p>";
    echo "<pre>" . htmlspecialchars($xml) . "</pre>";
    $affected = 0;
    $twelement = new SimpleXMLElement($xml);
    foreach ($twelement->entry as $entry) {
        $text = trim($entry->title);
        $author = trim($entry->author->name);
        $time = strtotime($entry->published);
        $id = $entry->id;
        echo "<p>Tweet from $author: <strong>$text</strong> <em>Posted " . date('n/j/y g:i a', $time) . "</em></p>";
    }
    return true;
}
//getTweets('#cats');
function timeStuff(){
    $now = time();//unix timestamp
    $millis = microtime();//millis
    echo date('r', $now);
    
    $epoch = new DateTime($now);
}
function dynamicObject(){
    $thing = new stdClass;
    $thing->name = 'Bob';
    echo $thing->name;
}
dynamicObject();

/*
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

        if (!mail("$number@{$carrierMap[$carrier]}", $subject, $pmsg)) {
            echo "Mailing failed.";
        }
    }
}

//sendText($tci, 'tmobile', 'pimp', 'im a machine!!!!');

function sendEmail($dest, $subject, $message) {
    if (isset($dest) && isset($message)) {
        $pmsg = wordwrap($message);
        if (!mail($dest, $subject, $pmsg)) {
            echo "Email Failed.";
        }
    }
}

//sendEmail('traciwelden@gmail.com', 'pimp my ride', 'ur sink is on');

function parseXml($file) {
    require_once 'stuff.xml';
    $data = simplexml_load_file($file);
    echo "<br/>" . $data->Items->Item->OfferSummary->LowestNewPrice->Amount;
}

function parseConfig() {
    $data = file_get_contents("../app/config.json");
    $json = json_decode($data, true); //true converts objs to assoc arrays
    foreach ($json as $key => $value) {
        if (!is_array($value)) {
            
        }
    }
}

/*
 * $url = 'http://www.example.com/home/bob/stuff?app=pimp&first=bob&last=smith';
 * returns: [ 0 => ['name' => 'app', 'value' => 'pimp' ], 1 => ['name' => 'first', 'value' => 'bob' ] ]
 */

function parseUriParams($uri) {
    $pms = parse_url($url, PHP_URL_QUERY);
    $params = [];
    if ($pms and strpos($pms, '=')) {
        
        foreach (explode('&', $pms) as $pair) {
            $snag = explode('=', $pair);
            array_push($params, ['name' => $snag[0], 'value' => $snag[1]]);
        }
        //var_dump($params);
        //echo $params[2]['value'];
        return $params;
    }
    return null;
}
function toQueryString($assocArray){
    return http_build_query($assocArray);
}

function getDatabaseCredentials() {
    $config = json_decode(file_get_contents('../app/config.json'));
    return [
        'dsn' => $config->db_dsn,
        'host' => $config->db_host,
        'user' => $config->db_user,
        'pswd' => $config->db_pswd,
        'database' => $config->db_name
    ];
}

function getDatabaseCredentialsTest() {
    $config = json_decode(file_get_contents('../app/config.json'));
    return [
        'dsn' => $config->test_db_dsn,
        'host' => $config->test_db_host,
        'user' => $config->test_db_user,
        'pswd' => $config->test_db_pswd,
        'database' => $config->test_db_name
    ];
}

//Amazon
//returns associative array of results
function conductProductSearch($params) {
    //construct request uri
    $uri = createItemSearchRequest($params);
    if (empty($uri))
        return null;
    $xml = send($uri);
    if (empty($xml))
        return null;
    return processSearchResults($xml);
}

function conductProductOffers($asin, $condition = 'All') {
    if (!isset($asin) || empty($asin)) {
        return;
    }
    $uri = createProductOffers($asin, $condition);

    if (empty($uri))
        return null;
    $xml = send($uri);
    if (empty($xml))
        return null;
    return processOfferResults($xml);
}

function send($uri) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $uri
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

/**
 * echo $d['items'][0]['image'];
 * echo $d['items'][0]['img_width']
 * @param type $res
 * @return array
 */
function processSearchResults($res) {
    if (!isset($res) || empty($res)) {
        return "Empty";
    }
    $data = [];
    $xml = simplexml_load_string($res);
    //$xml = simplexml_load_file('../stuff.xml');
    if ($xml->Items->Request->IsValid) {
        $data['page'] = $xml->Items->Request->ItemSearchRequest->ItemPage;
        $data['totalResults'] = $xml->Items->TotalResults;
        $data['totalPages'] = $xml->Items->TotalPages;

        $data['items'] = [];
        foreach ($xml->Items->Item as $item) {
            $row['asin'] = $item->ASIN;
            $row['title'] = $item->ItemAttributes->Title;
            $row['manufacturer'] = $item->ItemAttributes->Manufacturer;
            $row['url'] = $item->DetailPageURL;
            $row['image'] = $item->SmallImage->URL;
            $row['img_width'] = $item->SmallImage->Width;
            $row['img_height'] = $item->SmallImage->Height;

            array_push($data['items'], $row);
        }
        return $data;
    }
}

function processOfferResults($res) {
    if (!isset($res) || empty($res)) {
        return "Empty";
    }
    $data = [];
    $xml = simplexml_load_string($res);

    if ($xml->Items->Request->IsValid) {
        $data['asin'] = $xml->Items->Item->ASIN;
        $data['lowest_new'] = $xml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice;
        $data['lowest_used'] = $xml->Items->Item->OfferSummary->LowestUsedPrice->FormattedPrice;
        $data['total_new'] = $xml->Items->Item->OfferSummary->TotalNew;
        $data['total_used'] = $xml->Items->Item->OfferSummary->TotalUsed;
        $data['link'] = $xml->Items->Item->Offers->MoreOffersUrl;
        $data['offers'] = [];
        foreach ($xml->Items->Item->Offers->Offer as $offer) {
            $row['vendor'] = $offer->Merchant->Name;
            $row['condition'] = $offer->OfferAttributes->Condition;
            $row['price'] = $offer->OfferListing->Price->FormattedPrice;

            array_push($data['offers'], $row);
        }
        return $data;
    }
}

//offers are based on an ASIN and a condition
function createProductOffers($asin, $condition = 'All') {
    $params = [
        'ItemId' => $asin,
        'IdType' => 'ASIN',
        'Condition' => $condition,
        'Operation' => 'ItemLookup',
        'ResponseGroup' => 'OfferFull'
    ];
    return createUri($params);
}

/*
 * Performs a query against the Amazon database.
 * 
 * Keywords(Optional): string with spaces ok(they are converted to underscore)
 * Condition: New, Used, All
 * Category: All, Books, Beauty, Electronics, ...
 * Requested Page: string like '1'
 * Minimum Price: string '3241' is $32.41 default: None
 * Maximum Price: same
 * ItemPage => '3' to get page 3
 * 
 * Usage: Associative Array
 *   itemSearch(['keyword' => 'Kat Von D', 'page' => 3]);
 *   itemSearch(['keyword' => 'Kat Von D', 'page' => 3]);
 * 
 * Constraints: Search must utilize either a keyword or a category.
 */

function createItemSearchRequest($params) {
    //Must be at least 1 parameter specified.
    if (!isset($params) || empty($params)) {
        return "No params specified";
    }
    $condition = 'All';
    $category = 'All';
    $page = 1;
    $min = 'None';
    $max = 'None';
    if (!isset($params['keyword']) || empty($params['keyword'])) {
        //if no keyword, then there must be at least 1 param
        //that differs from the defaults
        return;
    }
    if (isset($params['condition']) && !empty($params['condition'])) {
        $condition = $params['condition'];
    }
    if (isset($params['category']) && !empty($params['category'])) {
        $category = $params['category'];
    }
    if (isset($params['page']) && !empty($params['page'])) {
        $page = $params['page'];
    }
    if (isset($params['min']) && !empty($params['min'])) {
        $min = $params['min'];
    }
    if (isset($params['max']) && !empty($params['max'])) {
        $max = $params['max'];
    }

    $query = [
        'Operation' => 'ItemSearch',
        'SearchIndex' => $category,
        'Condition' => $condition,
        'ItemPage' => (string) $page,
        'MinimumPrice' => $min,
        'MaximumPrice' => $max,
        'ResponseGroup' => 'Small,Images'
    ];
    if (isset($params['keyword']) && !empty($params['keyword'])) {
        $query['Keywords'] = rawurlencode(str_replace(' ', '_', $params['keyword']));
    }

    return createUri($query);
}

/*  cruncher
 *  needs: ResponseGroup
 */

function createUri($params) {
    //acquire keys
    $config = json_decode(file_get_contents('../app/config.json'), true);

    $method = 'GET';
    $host = 'webservices.amazon.com';
    $uri = '/onca/xml';
    $private_key = $config['amazonSecret'];
    $params['Service'] = 'AWSECommerceService';
    $params['AWSAccessKeyId'] = $config['amazonAccessKey'];
    $params['AssociateTag'] = $config['associateKey'];
    $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
    $params['Version'] = '2011-08-01';
    // sort the parameters
    ksort($params);
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param => $value) {
        $param = str_replace('%7E', '~', rawurlencode($param));
        $value = str_replace('%7E', '~', rawurlencode($value));
        $canonicalized_query[] = $param . '=' . $value;
    }
    $canonicalized_query = implode('&', $canonicalized_query);
    // create the string to sign
    $string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;
    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $private_key, TRUE));
    // encode the signature for the request
    $signature = str_replace('%7E', '~', rawurlencode($signature));
    // create request
    $request = 'http://' . $host . $uri . '?' . $canonicalized_query . '&Signature=' . $signature;
    return $request;
}
