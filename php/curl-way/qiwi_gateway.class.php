<?php
class QiwiGateway {
  private $curl = NULL;
  private $cookie_file = '';

  public function __construct($cookie_file='./cookie.1.data') {
    $this->cookie_file = $cookie_file;
    $this->init_curl();
  }

  private function init_curl() {
    $this->curl = curl_init();
    curl_setopt($this->curl, CURLOPT_HEADER, 0);
    curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0');
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, array ('Accept: application/json, text/javascript, */*; q=0.01','Content-Type: application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With: XMLHttpRequest'));
    curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_file);
    curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie_file);
  }

  private function post($url, $data, $referer, $raw=FALSE) {
    sleep(2);
    if (!$raw) {
      $data = http_build_query($data);
    }
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_POST, 1);
    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($this->curl,CURLOPT_REFERER, $referer);

    return curl_exec($this->curl);
  }

  public function auth($login, $password) {
    $url = 'https://qiwi.com/auth/login.action';
    $referer = 'https://qiwi.com/';
    $data = [
      'source' => 'MENU',
      'login' => $login,
      'password' => $password
    ];
    $json_str = $this->post($url, $data, $referer);
    $json = json_decode($json_str, true);
    $token = $json['data']['token'];
    $data['loginToken'] = $token;
    $json_str = $this->post($url, $data, $referer);
    $json = json_decode($json_str, true);
    return [
      'type' => $json['code']['_name'],
      'message' => $json['message']
    ];
  }

  public function check() {
    $url = "https://qiwi.com/person/state.action";
    $referer = 'https://qiwi.com/main.action';
    $data = [];
    $json_str = $this->post($url, $data, $referer);
    $json = json_decode($json_str, true);
    if ($json['code']['_name'] == 'ERROR') {
      return FALSE;
    }
    if ($json['code']['_name'] == 'NORMAL') {
      return $json['data']['balances'];
    }
  }

  public function transfer($phone, $amount, $comment='test') {
    return TRUE;
    // Payment/
    $url = 'https://qiwi.com/user/payment/form/state.action';
    $referer = 'https://qiwi.com/transfer/form.action';
    $data = [
      "extra['account']" => $phone,
      'source' => 'qiwi_RUB',
      'amountInteger'=> $amount,
      'amountFraction' => '00',
      'currency' => 'RUB',
      "extra['comment']" => $comment,
      'state' => 'CONFIRM',
      'protected' => 'true'
    ];
    $raw_data = "extra%5B'account'%5D=%2B380638867177&source=qiwi_RUB&amountInteger=2&amountFraction=&currency=RUB&extra%5B'comment'%5D=&state=CONFIRM&protected=true";
    $url = 'https://qiwi.com/user/payment/form/state.action';
    $json_str = $this->post($url, $raw_data, $referer, TRUE);

    $json = json_decode($json_str, true);
    $data['token'] = $json['data']['token'];
    $referer = 'https://qiwi.com/payment/state.action?state=CONFIRM&protected=true';
    $html = $this->post($url, $data, $referer);
    print $html;
    exit();

    // Confirmation.
    $url = 'https://qiwi.com/user/payment/form/state.action';
    $referer = 'https://qiwi.com/payment/state.action?state=CONFIRM&protected=true';
    $data = [
      'state' => 'PAY',
    ];
    $json_str = $this->post($url, $data, $referer);
    $json = json_decode($json_str, true);
    $data['token'] = $json['data']['token'];
    $referer = 'https://qiwi.com/payment/state.action?state=PAY';
    $html = $this->post($url, $data, $referer);

    return TRUE;
  }

  private function extractToken($html) {
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xp = new DOMXpath($dom);
    $nodes = $xp->query('//input[@name="token"]');
    $node = $nodes->item(0);
    $token = $node->getAttribute('value');

    return token;
  }
}