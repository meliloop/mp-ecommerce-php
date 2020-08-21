<?php
    if( $_GET['debug'] ):
        ini_set('display_errors',1);
        error_reporting(E_ALL);
    endif;

    if( !class_exists('Tienda_Manager') )
    {
        class Tienda_Manager
        {
            protected function getConfig()
            {
                return parse_ini_file("config.ini", true);
            }

            public function createPreference()
            {
                require_once 'vendor/autoload.php';

                $config     =   $this->getConfig();

                $base_url   =   $config['general']['base_url'];
                MercadoPago\SDK::setAccessToken($config['mpago']['access_token']);
                MercadoPago\SDK::setIntegratorId($config['mpago']['integrator_id']);

                $preference                     =   new MercadoPago\Preference();
                $preference->external_reference =   $config['preference']['external_reference'];
                $preference->payment_methods    =   array(
                                                        "excluded_payment_methods" =>   array(
                                                                                            array(
                                                                                                "id" => $config['preference']['excluded_payment_methods']
                                                                                            )
                                                                                        ),
                                                        "excluded_payment_types"   =>   array(
                                                                                            array(
                                                                                                "id" => $config['preference']['excluded_payment_types']
                                                                                            )
                                                                                        ),
                                                        "installments"             =>   intval($config['preference']['installments'])
                                                    );
                $preference->notification_url   =   $base_url.$config['preference']['notification_url'];
                $preference->back_urls          =   array(
                                                        "success" => $base_url.$config['preference']['success_url'],
                                                        "failure" => $base_url.$config['preference']['failure_url'],
                                                        "pending" => $base_url.$config['preference']['pending_url']
                                                    );
                $preference->auto_return        =   $config['preference']['auto_return'];

                //  purchase item
                $item             = new MercadoPago\Item();
                $item->id         = $_POST['item_id'];
                $item->title      = $_POST['item_name'];
                $item->description= $_POST['item_description'];
                $item->picture_url= $base_url.$_POST['item_img'];
                $item->currency_id= "ARS";
                $item->quantity   = intval($_POST['item_qty']);
                $item->unit_price = floatval($_POST['item_price']);

                //  payer data
                $payer            = new MercadoPago\Payer();
                $payer->first_name= $config['payer']['name'];
                $payer->last_name = $config['payer']['surname'];
                $payer->email     = $config['payer']['email'];
                $payer->phone     = array(
                                        "area_code" => $config['payer']['phone_area'],
                                        "number"    => $config['payer']['phone_number']
                                    );
                $payer->address   = array(
                                        "street_name"   => $config['payer']['street_name'],
                                        "street_number" => $config['payer']['street_number'],
                                        "zip_code"      => $config['payer']['zip_code']
                                    );

                $preference->items = array($item);
                $preference->payer = $payer;
                $preference->save();

                return $preference->init_point;
            }

            public function getResponse()
            {
                $config     =   $this->getConfig();

                switch( $_GET['result'] )
                {
                    case 'failure':
                        return '<h2 class="response error">'.$config['text']['failure'].'</h2>';
                        break;
                    case 'pending':
                        return '<h2 class="response pending">'.$config['text']['pending'].'</h2>';
                        break;
                    case 'success':
                        return '<h2 class="response success">'.$config['text']['success'].'</h2>
                                <div class="data__response">
                                    <div class="row">
                                        <label>Identificador de pago:</label> '.$_GET['collection_id'].'
                                    </div>
                                    <div class="row">
                                        <label>Metodo de pago:</label> '.$_GET['payment_type'].'
                                    </div>
                                    <div class="row">
                                        <label>Referencia:</label> '.$_GET['external_reference'].'
                                    </div>
                                </div>';
                        break;
                }
            }

            public function checkWebhooks()
            {
                require_once 'vendor/autoload.php';

                $config     =   $this->getConfig();
                /*
                MercadoPago\SDK::setAccessToken($config['mpago']['access_token']);

                switch($_POST["type"])
                {
                    case "payment":
                        $payment = MercadoPago\Payment.find_by_id($_POST["id"]);
                        break;
                    case "plan":
                        $plan = MercadoPago\Plan.find_by_id($_POST["id"]);
                        break;
                    case "subscription":
                        $plan = MercadoPago\Subscription.find_by_id($_POST["id"]);
                        break;
                    case "invoice":
                        $plan = MercadoPago\Invoice.find_by_id($_POST["id"]);
                        break;
               }
               */
               $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
               $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
               $cabeceras .= 'To: Meli <casla86@gmail.com>' . "\r\n";
               $mensaje =   var_export($_POST,true);
               mail($config['preference']['external_reference'], $mensaje, $cabeceras);
           }

           public function getPreference($id)
           {
               require_once 'vendor/autoload.php';

               MercadoPago\SDK::setAccessToken($config['mpago']['access_token']);
               $json    =   MercadoPago\Preference::find_by_id($id);
               echo '<pre>';
               var_dump($json);
               echo '</pre>';
           }
        }
    }
?>
