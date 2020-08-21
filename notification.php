<?php
    try{
        $error      =   false;
        $mgr        =   new Tienda_Manager();
        $mgr->checkWebhooks();
    }catch(Exception $e){
        $error      =   $e->getMessage();
    }
?>
