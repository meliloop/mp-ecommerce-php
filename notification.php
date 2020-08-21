<?php
    try{
        require_once('lib/manager.php');

        $error      =   false;
        $mgr        =   new Tienda_Manager();

        if( $_GET['preference'] ):
            $mgr->getPreference($_GET['preference']);
        endif;

        $mgr->checkWebhooks();
    }catch(Exception $e){
        $error      =   $e->getMessage();
    }
?>
