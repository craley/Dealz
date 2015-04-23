<?php

/*
 * Amazon Api
 */

class Product {

    public $asin;
    public $title;
    public $manufacturer;
    public $url;
    public $image;

}

class SearchResult {

    public $currentPage;
    public $totalPages;
    public $products;

}

class OffersResult {
    
}

class Query {
    
}

/**
 * Description of amazon
 *
 * @author chris
 */
class Amazon {

    const ACCESS = '../app/config.json';
    
    //Public Api

    public function search($keyword, $category = 'All', $condition = 'All', $page = 1, $min = 'None', $max = 'None') {

        return $result;
    }

    public function offers($asin, $cond) {
        
    }
    
    //End Public Api

    private function parseSearchResults($res) {
        if (!isset($res) || empty($res)) {
            return "Empty";
        }
        $data = [];
        $xml = simplexml_load_string($res);
        if ($xml->Items->Request->IsValid) {
            $result = new SearchResult;
            $result->currentPage = $xml->Items->Request->ItemSearchRequest->ItemPage;
            $result->totalPages = $xml->Items->TotalPages;

            foreach ($xml->Items->Item as $item) {
                $product = new Product;
                $product->asin = $item->ASIN;
                $product->title = $item->ItemAttributes->Title;
                $product->manufacturer = $item->ItemAttributes->Manufacturer;
                $product->url = $item->DetailPageURL;
                $product->image = $item->SmallImage->URL;

                array_push($result->products, $product);
            }
            return $result;
        }
        return null;
    }

    private function createSearchRequest() {
        //Must be at least 1 parameter specified.
        if (!isset($params) || empty($params)) {
            return "No params specified";
        }
        $condition = 'All';
        $category = 'All';
        $page = 1;
        $min = 'None';
        $max = 'None';
        if (empty($params['keyword'])) {
            //if no keyword, then there must be at least 1 param
            //that differs from the defaults
            return;
        }
        if (!empty($params['condition'])) {
            $condition = $params['condition'];
        }
        if (!empty($params['category'])) {
            $category = $params['category'];
        }
        if (!empty($params['page'])) {
            $page = $params['page'];
        }
        if (!empty($params['min'])) {
            $min = $params['min'];
        }
        if (!empty($params['max'])) {
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

    private function createUri($params) {
        //acquire keys
        $config = json_decode(file_get_contents(self::ACCESS), true);

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

}
