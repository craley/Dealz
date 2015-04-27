<?php

/* 
 * Uses the twig template engine to render html components.
 */

require_once 'vendor/autoload.php';

class View {
    private $twig;
    const TEMPLATE_DIR = 'templates';


    public function __construct() {
        $loader = new Twig_Loader_Filesystem(self::TEMPLATE_DIR);
        $this->twig = new Twig_Environment($loader, array());
    }
    public function renderSearch($params){
        $this->twig->render('query.html.twig', $params);
    }
    public function renderFailedSearch(){
        
    }
}