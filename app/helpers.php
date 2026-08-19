<?php

function send_notification_FCM($token_id, $title, $message, $id, $type)
{

    $accesstoken = env('FIREBASE_KEY');

    $URL = 'https://fcm.googleapis.com/fcm/send';


    // $post_data = '{
    //     "to" : "' . $token_id . '",
    //     "data" : {
    //       "body" : "",
    //       "title" : "' . $title . '",
    //       "type" : "' . $type . '",
    //       "id" : "' . $id . '",
    //       "message" : "' . $message . '",
    //       "priority"   :"high"
    //     },
    //     "notification" : {
    //          "body" : "' . $message . '",
    //          "title" : "' . $title . '",
    //           "type" : "' . $type . '",
    //          "id" : "' . $id . '",
    //          "message" : "' . $message . '",
    //         "icon" : "new",
    //         "sound" : "default",
    //         "priority"   :"high"
    //         },

    //   }';
    // print_r($post_data);die;
    $data = [
        "registration_ids" => $token_id,
        "notification" => [
            "title" => $title,
            "body" => $message,
        ],
        "data" => [
            "title" => $title,
            "body" => $message,
        ],
        'headers' => [
            'apns-priority' => '10',
        ],
        'payload' => [
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body' => $message,
                ],
                'badge' => 42,
            ],
        ],
        'aps' => [
            'alert' => [
                'title' => $title,
                'body' => $message,
            ],
            'badge' => 42,
        ],
        'apns' => [
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $message,
                    ],
                    'badge' => 42,
                ],
            ],
        ],
    ];
    $post_data = json_encode($data);

    $crl = curl_init();
    $headr = array();
    $headr[] = 'Content-type: application/json';
    $headr[] = 'Authorization: key=' . $accesstoken;


    // echo "<pre>";
    // print_r($URL);
    // print_r($headr);
    // print_r($post_data);
    // echo "</pre>";

    curl_setopt($crl, CURLOPT_URL, $URL);
    curl_setopt($crl, CURLOPT_POST, true);
    curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($crl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);

    $rest = curl_exec($crl);

    if ($rest == false) {
        throw new Exception('Curl error: ' . curl_error($crl));
        print_r('Curl error: ' . curl_error($crl));
        $result_noti = 0;
    } else {
        $result_noti = 1;

        // echo "<pre>";
        // print_r($rest);
        // echo "</pre>";
    }

    curl_close($crl);
    //echo "result : ".$result_noti;
    //die;
    return $result_noti;
}

if (!function_exists('layoutConfig')) {
    function layoutConfig()
    {

        if (Request::is('modern-light-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.vlm');
        } else if (Request::is('modern-dark-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.vdm');
        } else if (Request::is('collapsible-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.cm');
        } else if (Request::is('horizontal-light-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.hlm');
        } else if (Request::is('horizontal-dark-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.hlm');
        }

        // RTL

        else if (Request::is('rtl/modern-light-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.vlm-rtl');
        } else if (Request::is('rtl/modern-dark-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.vdm-rtl');
        } else if (Request::is('rtl/collapsible-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.cm-rtl');
        } else if (Request::is('rtl/horizontal-light-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.hlm-rtl');
        } else if (Request::is('rtl/horizontal-dark-menu/*')) {

            $__getConfiguration = Config::get('app-config.layout.hdm-rtl');
        }



        // Login

        else if (Request::is('login')) {

            $__getConfiguration = Config::get('app-config.layout.vlm');
        } else {
            $__getConfiguration = Config::get('barebone-config.layout.bb');
        }

        return $__getConfiguration;
    }
}


if (!function_exists('getRouterValue')) {
    function getRouterValue()
    {

        if (Request::is('modern-light-menu/*')) {

            $__getRoutingValue = '/modern-light-menu';
        } else if (Request::is('modern-dark-menu/*')) {

            $__getRoutingValue = '/modern-dark-menu';
        } else if (Request::is('collapsible-menu/*')) {

            $__getRoutingValue = '/collapsible-menu';
        } else if (Request::is('horizontal-light-menu/*')) {

            $__getRoutingValue = '/horizontal-light-menu';
        } else if (Request::is('horizontal-dark-menu/*')) {

            $__getRoutingValue = '/horizontal-dark-menu';
        }

        // RTL

        else if (Request::is('rtl/modern-light-menu/*')) {

            $__getRoutingValue = '/rtl/modern-light-menu';
        } else if (Request::is('rtl/modern-dark-menu/*')) {

            $__getRoutingValue = '/rtl/modern-dark-menu';
        } else if (Request::is('rtl/collapsible-menu/*')) {

            $__getRoutingValue = '/rtl/collapsible-menu';
        } else if (Request::is('rtl/horizontal-light-menu/*')) {

            $__getRoutingValue = '/rtl/horizontal-light-menu';
        } else if (Request::is('rtl/horizontal-dark-menu/*')) {

            $__getRoutingValue = '/rtl/horizontal-dark-menu';
        }

        // Login

        else if (Request::is('login')) {

            $__getRoutingValue = '/modern-light-menu';
        } else {
            $__getRoutingValue = '';
        }


        return $__getRoutingValue;
    }
}
