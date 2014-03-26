<?php


namespace App\Controllers;

use T4\Auth\Identity;
use T4\Core\Exception;
use T4\Mvc\Controller;

class Index
    extends Controller
{

    public function actionDefault() {
        $this->data->content = 'Hello, world!';
    }

    public function actionLogin($email=null, $password=null)
    {
        $this->data->error = $this->app->flash->error;
        if (!empty($email) && !empty($password)) {
            try {
                Identity::authenticate($email, $password);
                $this->redirect('/');
            } catch (Exception $e) {
                $this->app->flash->error = $e->getMessage();
            }
        }
        $this->data->email = $email;
    }

    public function actionLogout()
    {
        Identity::logout();
        $this->redirect('/');
    }

}