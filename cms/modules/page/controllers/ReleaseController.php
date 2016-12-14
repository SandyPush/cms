<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'template/models/Templates.php';
require_once MODULES_PATH . 'page/models/Page.php';
require_once MODULES_PATH . 'object/models/Objects.php';
require_once MODULES_PATH . 'object/models/ObjContents.php';
require_once LIBRARY_PATH . 'Publish/Page.php';

class Page_ReleaseController extends BaseController
{


    public function indexAction()
    {

        exec("/VODONE/www/vodone.cms/publish/h5/release.sh",$a);
       // exec("/Applications/XAMPP/xamppfiles/htdocs/h5_designRELEASE.sh",$a);
        if($a){

            foreach($a as $v){

                echo $v."<br>";
            }
        }

        exit;
    }

}