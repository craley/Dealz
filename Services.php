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
 * Now, they need to be sorted:
 * 
 * AWSAccessKeyId=---------
 * AssociateTag=-------
 *  Condition=New
 *  Keywords=kat%20von%20d
 *  Operation=ItemSearch
 * ResponseGroup=Images%2CItemAttributes%2COffers
 * SearchIndex=Beauty
 * Service=AWSECommerceService
 * Timestamp=2015-03-28T14%3A41%3A24.000Z
 *   Version=2011-08-01
 * 
 * String to Sign: GET
 *   webservices.amazon.com/onca/xml
 *   AWSAccessKeyId=--------&AssociateTag=--------&Condition=New&Keywords=kat%20von%20d&Operation=ItemSearch&ResponseGroup=Images%2CItemAttributes%2COffers&SearchIndex=Beauty&Service=AWSECommerceService&Timestamp=2015-03-28T14%3A41%3A24.000Z&Version=2011-08-01
 * 
 * Finished signed url:
 * 
 * http://webservices.amazon.com/onca/xml?AWSAccessKeyId=-------&AssociateTag=------&Condition=New&Keywords=kat%20von%20d&Operation=ItemSearch&ResponseGroup=Images%2CItemAttributes%2COffers&SearchIndex=Beauty&Service=AWSECommerceService&Timestamp=2015-03-28T14%3A41%3A24.000Z&Version=2011-08-01&Signature=---------------
 * 
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
 * String to sign:
 *  GET
 *  webservices.amazon.com/onca/xml
 *  AWSAccessKeyId=--------------&AssociateTag=-------------&Condition=New&IdType=ASIN&ItemId=B00SB0RKT0&Operation=ItemLookup&ResponseGroup=Images%2CItemAttributes%2COffers&Service=AWSECommerceService&Timestamp=2015-03-28T14%3A57%3A36.000Z&Version=2011-08-01
 * 
 * 
 */


//get all offers: Offers
//get best offer: OfferSummary

//$uri = awsRequest("VideoGames", "call of duty", "Images", "ItemSearch", "1");


//$uri = getOffers('B00SB0RKT0');

$uri = itemSearch('Kat_Von_D', 'New', 'Beauty');
$snag = send($uri);
echo $snag;

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
 * keywords: string
 * ItemPage => '3' to get page 3
 */
function itemSearch($keywords, $condition = 'New', $category = 'All') {
    
    $params = [
        'Operation' => 'ItemSearch',
        'SearchIndex' => $category,
        'Keywords' => rawurlencode($keywords)
    ];
    return createUri($params);
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
    $method = 'GET';
    $host = 'webservices.amazon.com';
    $uri = '/onca/xml';
    $private_key = '-----------------------';
    $params['Service'] = 'AWSECommerceService';
    $params['AWSAccessKeyId'] = '---------------';
    $params['AssociateTag'] = '----------------';
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
