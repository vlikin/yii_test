<?php
require_once(dirname(__FILE__) . '/qiwi_gateway.class.php');

$phone = '+3806********';
$amount = 3;
$cookie_file = "./cookie.2.data";

$qg = new QiwiGateway($cookie_file);
$result = $qg->transfer($phone, $amount);
print_r($result);
exit();
