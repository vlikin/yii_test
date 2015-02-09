<?php
require_once(dirname(__FILE__) . '/qiwi_gateway.class.php');

$cookie_file = "./cookie.2.data";

$qg = new QiwiGateway($cookie_file);
$balances = $qg->check();
print_r($balances);
exit();

