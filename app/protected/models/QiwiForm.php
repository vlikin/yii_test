<?php

class QiwiForm extends CFormModel {
  public $user_name_1;
  public $user_name_2;

  public function rules() {
    return array(
      array('user_name_1, user_name_2', 'required'),
      array('user_name_1', 'authenticate'),
    );
  }

  public function authenticate($attribute, $params) {
    $this->_identity=new UserIdentity($this->username,$this->password);
    if(!$this->_identity->authenticate())
      $this->addError('password','Неправильное имя пользователя или пароль.');
  }
}