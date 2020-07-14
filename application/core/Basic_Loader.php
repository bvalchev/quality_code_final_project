<?php
/**
 * Created by PhpStorm.
 * User: Boyan
 * Date: 30.4.2019 г.
 * Time: 19:53
 */
class  Basic_Loader extends CI_Loader {
    public function __construct() {

        parent::__construct();

    }

    public function iface($strInterfaceName) {
       include APPPATH . "/interfaces/" . $strInterfaceName . ".php";
    }

    public function base_view($view, $vars = array(), $get = FALSE) {
        //  ensures leading /
        if ($view[0] != '/') $view = '/' . $view;
        //  ensures extension   
        $view .= ((strpos($view, ".", strlen($view)-5) === FALSE) ? '.php' : '');
        //  replaces \'s with /'s
        $view = str_replace('\\', '/', $view);

        if (!is_file($view)) if (is_file($_SERVER['DOCUMENT_ROOT'].$view)) $view = ($_SERVER['DOCUMENT_ROOT'].$view);

        if (is_file($view)) {
            if (!empty($vars)) extract($vars);
            ob_start();
            include($view);
            $return = ob_get_clean();
            if (!$get) echo($return);
            return $return;
        }

        return show_404($view);
    }
}