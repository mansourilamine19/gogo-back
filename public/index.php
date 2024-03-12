<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
   //dd("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");
//    header('Access-Control-Allow-Origin:http://localhost:4200');
//    header('Access-Control-Allow-Headers:*');
//    header('Access-Control-Allow-Credentials:true');
//    header('Access-Control-Allow-Headers:X-Requested-With, Content-Type, withCredentials');
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
