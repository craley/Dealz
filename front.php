<?php
/*
 * Frontside Controller with No htaccess file.
 * Primary abstraction layer: connects http query params to php functions.
 * Request pools params from GET and POST.
 * Action parameter MUST be present.
 */

if(count($_REQUEST) > 0 and !empty($_REQUEST['action'])){
    $action = $_REQUEST['action'];
    $params = [];
    foreach ($_REQUEST as $name => $val) {
        //screens action param and broken name-value pairs.
        if(!empty($name) and !empty($val) and $name != 'action'){
            $params[$name] = $val;
        }
    }
    require_once 'services/router.php';
    $router = new Router();
    if(method_exists($router, $action)){
        //success determined
        if(count($params) > 0){
            call_user_func_array(array($router, $action), $params);
        } else {
            call_user_func(array($router, $action));
        }
        exit();
    }
}
header("HTTP/1.1 404 Not Found");