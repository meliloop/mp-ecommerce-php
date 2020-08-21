<?php
    try{
        require_once('lib/manager.php');

        $error      =   false;
        $mgr        =   new Tienda_Manager();

        $mgr->checkWebhooks();
    }catch(Exception $e){
        $error      =   $e->getMessage();
        var_dump($e);
    }
?>
