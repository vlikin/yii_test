<?php
require_once(dirname(__FILE__) . '/qiwi_gateway.class.php');

$login = "+3809********";
$password = "****";
$cookie_file = "./cookie.2.data";

$qg = new QiwiGateway($cookie_file);
$result = $qg->auth($login, $password);
if ($result['type'] == 'ERROR') {
  print $result['message'] . chr(13);
}
elseif ($result['type'] == 'NORMAL') {
  print 'You are authorized successfuly.' .chr(10);
}
exit();
