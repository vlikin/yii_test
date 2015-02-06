<?php

class QiwiController extends Controller {
  /**
   * Displays the QIWI page
   */
  public function actionQiwi()
  {
    $qiwi_gateway = new QiwiGateway();
    $model = new QiwiForm();

    // if it is ajax validation request
    if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
    {
      echo CActiveForm::validate($model);
      Yii::app()->end();
    }

    // collect user input data
    if(isset($_POST['QiwiForm'])) {
      $model->attributes=$_POST['QiwiForm'];
      // validate user input and redirect to the previous page if valid
      if($model->validate() && $model->login())
        $this->redirect(Yii::app()->user->returnUrl);
    }
    // display the login form
    $this->render('qiwi',array('model'=>$model));
  }
}