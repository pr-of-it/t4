<?php

namespace App\Modules\Admin\Controllers;

use T4\Mvc\Controller;

class Index
    extends Controller
{

    protected function access($action)
    {
        return !empty($this->app->user);
    }

    public function actionDefault()
    {
    }

} 