<?php

namespace App\Controllers;

use App\Components\Auth\Identity;
use T4\Core\Std;
use T4\Mvc\Controller;

class Index
    extends Controller
{

    public function actionDefault()
    {
        $this->data->content = new Std();
        $this->data->content->header = 'Hello, T4!';
        $this->data->content->text = 'This is a template for a simple marketing or informational website. It includes a large callout called a jumbotron and three supporting pieces of content. Use it as a starting point to create something more unique.';
    }

    public function actionLogin($email = null, $password = null)
    {
        if (!empty($this->app->user)) {
            $this->redirect('/');
        }

        if (!is_null($email) || !is_null($password)) {
            try {
                $identity = new Identity();
                $user = $identity->authenticate(new Std(['email' => $email, 'password' => $password]));
                $this->app->flash->message = 'Welcome back, ' . $user->email . '!';
                $this->redirect('/');
            } catch (\T4\Auth\Exception $e) {
                $this->app->flash->error = $e->getMessage();
            }
        }

        $this->data->email = $email;
    }

    public function actionLogout()
    {
        $identity = new Identity();
        $identity->logout();
        $this->redirect('/');
    }

}