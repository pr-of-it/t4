<?php

namespace T4\MVC;


class Controller
{

    public function beforeAction()
    {

    }

    public function afterAction()
    {

    }

    public function action($name, array $params = [])
    {
        $actionMethodName = 'action' . $name;
        if (method_exists($this, $actionMethodName)) {
            $this->beforeAction();
            $ret = call_user_func_array([$this, $actionMethodName], $params);
            $this->afterAction();
            return $ret;
        } else {
            throw new EControllerException('Action ' . $name . ' is not found in controller ' . get_class());
        }
    }

}