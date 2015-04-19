<?php

/*
 * Amazon Product Api
 * 
 * Operations: ItemSearch, ItemLookup
 * 
 *  ItemSearch:
 * 
 *  ItemLookup: 
 * responseGroup = small | medium | large
 * search categories: All, Books, Beauty, etc.
 */

/*
 * Pre-Signed: 
 * http://webservices.amazon.com/onca/xml?
 * Service=AWSECommerceService&
 * Operation=ItemSearch&
 * SubscriptionId=-----&
 * AssociateTag=----&
 * Version=2011-08-01&
 * SearchIndex=Beauty&
 * Condition=New&
 * Keywords=kat von d&
 * ResponseGroup=Images,ItemAttributes,Offers
 * 
 * String to Sign: GET
 *   webservices.amazon.com/onca/xml
 */

/*
 *  AWSAccessKeyId=-----------------
 *  AssociateTag=------------------
 *  Condition=New                         Possible: All, New, Used, Refurbished, Collectible
 *  IdType=ASIN                           Possible: ASIN, ISBN, UPC
 *  ItemId=B00SB0RKT0
 *  Operation=ItemLookup
 *  ResponseGroup=Images%2CItemAttributes%2COffers   Possible: Images,ItemAttributes,Offers
 *  Service=AWSECommerceService
 *  Timestamp=2015-03-28T14%3A57%3A36.000Z
 *  Version=2011-08-01
 * 
 * 
 */

//$uri = awsRequest("VideoGames", "call of duty", "Images", "ItemSearch", "1");


//$uri = getOffers('B00SB0RKT0');

//$uri = itemSearch('Kat_Von_D', 'New', 'Beauty');
//$snag = send($uri);
//echo $snag;

function send($uri){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $uri
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function readXml($xstring){
    //creates a tree of objects.
    $data = simplexml_load_string($xstring);
    //get the lowest price
    $lowest = $data->Items->Item->OfferSummary->LowestNewPrice->Amount;
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
function itemSearch($params) {
    //Must be at least 1 parameter specified.
    if(!isset($params) || empty($params)){
        return "No params specified";
    }
    $condition = 'All';
    $category = 'All';
    $page = 1;
    $min = 'None';
    $max = 'None';
    if(!isset($params['keyword']) || empty($params['keyword'])){
        //if no keyword, then there must be at least 1 param
        //that differs from the defaults
        return;
    }
    if(isset($params['condition']) && !empty($params['condition'])){
        $condition = $params['condition'];
    }
    if(isset($params['category']) && !empty($params['category'])){
        $category = $params['category'];
    }
    if(isset($params['page']) && !empty($params['page'])){
        $page = $params['page'];
    }
    if(isset($params['min']) && !empty($params['min'])){
        $min = $params['min'];
    }
    if(isset($params['max']) && !empty($params['max'])){
        $max = $params['max'];
    }
    
    $query = [
        'Operation' => 'ItemSearch',
        'SearchIndex' => $category,
        'Condition' => $condition,
        'ItemPage' => (string)$page,
        'MinimumPrice' => $min,
        'MaximumPrice' => $max,
        'ResponseGroup' => 'Small,Images'
    ];
    if(isset($params['keyword']) && !empty($params['keyword'])){
        $query['Keywords'] = rawurlencode(str_replace(' ', '_', $params['keyword']));
    }
    
    return createUri($query);
}
//test itemSearch:
$uri = itemSearch(['keyword' => 'html5']);
//echo $uri;
$snag = send($uri);
echo $snag;

//$d = processSearchResults(NULL);
//echo $d['items'][0]['title'];
//echo $d['items'][0]['image'];
//echo $d['items'][0]['img_width'];
//echo count($d['items']);


//Process Search Results: open new tab to follow link!
function processSearchResults($res){
//    if(!isset($res) || empty($res)){
//        return "Empty";
//    }
    $data = [];
    //$xml = simplexml_load_string($xstring);
    $xml = simplexml_load_file('stuff.xml');
    if($xml->Items->Request->IsValid){
        $data['page'] = $xml->Items->Request->ItemSearchRequest->ItemPage;
        $data['totalResults'] = $xml->Items->TotalResults;
        $data['totalPages'] = $xml->Items->TotalPages;
        
        $data['items'] = [];
        foreach($xml->Items->Item as $item){
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
function showSearch($data){
    echo "Page: " . $data['page'] . "br/>";
}

function itemLookup($ASIN, $condition = 'New') {
    
    $params = [
        'itemId' => $ASIN,
        'IdType' => 'ASIN',
        'Condition' => $condition,
        'Operation' => 'ItemLookup',
        'ResponseGroup' => 'Images,ItemAttributes,Offers'
    ];
    return createUri($params);
}
function getDeals($ASIN){
    $params = [
        'ItemId' => $ASIN,
        'IdType' => 'ASIN',
        'Operation' => 'ItemLookup',
        'ResponseGroup' => 'Offers'
    ];
    return createUri($params);
}

/*  cruncher
 *  needs: ResponseGroup
 */
function createUri($params){
    //acquire keys
    $config = json_decode(file_get_contents('app/config.json'), true);
    
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
    foreach ($params as $param=>$value){
        $param = str_replace('%7E', '~', rawurlencode($param));
        $value = str_replace('%7E', '~', rawurlencode($value));
        $canonicalized_query[] = $param.'='.$value;
    }
    $canonicalized_query = implode('&', $canonicalized_query);
    // create the string to sign
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $private_key, TRUE));
    // encode the signature for the request
    $signature = str_replace('%7E', '~', rawurlencode($signature));
    // create request
    $request = 'http://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;
    return $request;
}
function getOffers($ASIN, $condition = 'New'){//use this
    $method = 'GET';
    $host = 'webservices.amazon.com';
    $uri = '/onca/xml';
    $private_key = '---------------';
    $params = [
        'AWSAccessKeyId' => '--------------------',
        'ItemId' => $ASIN,
        'Service' => 'AWSECommerceService',
        'AssociateTag' => '---------------',
        'IdType' => 'ASIN',
        'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        'Version' => '2011-08-01',
        'Condition' => $condition,
        'Operation' => 'ItemLookup',
        'ResponseGroup' => 'Offers'
    ];
    
    // sort the parameters
    ksort($params);
    
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param=>$value){
        $param = str_replace('%7E', '~', rawurlencode($param));
        $value = str_replace('%7E', '~', rawurlencode($value));
        $canonicalized_query[] = $param.'='.$value;
    }
    $canonicalized_query = implode('&', $canonicalized_query);
    
    // create the string to sign
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
    
    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $private_key, TRUE));
    
    // encode the signature for the request
    $signature = str_replace('%7E', '~', rawurlencode($signature));
    
    // create request
    $request = 'http://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;
    
    return $request;
}

function awsRequest($searchIndex, $keywords, $responseGroup = false, $operation = 'ItemSearch', $pageNumber = 1) {//the encryption fails!!
    $service_url = "http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService";
    $associate_tag = "--------------";
    $secret_key = "-----------";
    $access_key = "--------------";

    $request = "$service_url&Operation=$operation&AssociateTag=$associate_tag&SearchIndex=$searchIndex&Keywords=" . urlencode($keywords) . "&ItemPage=$pageNumber";
    //var_dump($request);
    //parse request into params
    $uri_elements = parse_url($request);
    $request = $uri_elements['query'];//gets the params after '?'
    parse_str($request, $parameters);

    //add new params
    $parameters['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
    $parameters['Version'] = '2011-08-01';
    $parameters['AWSAccessKeyId'] = $access_key;
    if ($responseGroup) {
        $parameters['ResponseGroup'] = $responseGroup;
    }
    ksort($parameters);
    //encode params and values
    foreach ($parameters as $param => $value) {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $request_array[] = $param . '=' . $value;
    }
    $new_request = implode('&', $request_array);
    //make it so
    $signature_string = "GETn{$uri_elements['host']}n{$uri_elements['path']}n{$new_request}";
    $signature = urlencode(base64_encode(hash_hmac('sha256', $signature_string, $secret_key, TRUE)));//prolly needs rawurlencode here

    //return signed request uri
    return "http://{$uri_elements['host']}{$uri_elements['path']}?{$new_request}&Signature={$signature}";
}

function useDaShit() {
    $xml = simplexml_load_string(awsRequest("VideoGames", "call of duty", "Images", "ItemSearch", "1"));//wrong, not file but string!
    //retrieve some data
    $totalPages = $xml->Items->TotalPages;
    echo "<p>There are $totalPages pages in XML results.</p>";

    echo "<ul>n";
    foreach ($xml->Items->Item as $item) {
        echo "<li>" . $item->ASIN . "</li>n";
    }
    echo "</ul>n";
}
