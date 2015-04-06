<?php

namespace T4\Mail;

use T4\Core\Std;
use T4\Mvc\Application;

class Sender
    extends \PHPMailer
{

    protected function getConfig()
    {
        $config = Application::getInstance()->config;
        if (empty($config->mail)) {
            $config->mail = new Std();
        }
        if (empty($config->mail->method)) {
            $config->mail->method = 'php';
        }
        return $config->mail;
    }

    public function __construct($exceptions = false)
    {
        parent::__construct($exceptions);
        $config = $this->getConfig();
        $this->CharSet = 'utf-8';
        if ('smtp' == $config->method) {
            $this->isSMTP();
            $this->Host = $config->host;
            if (!empty($config->auth)) {
                $this->SMTPAuth = true;
                $this->Username = $config->auth->username;
                $this->Password = $config->auth->password;
                $this->setFrom($config->auth->username, $config->sender);
                $this->addReplyTo($config->auth->username, $config->sender);
            }
            $this->Port = $config->port;
            $this->SMTPSecure = !empty($config->secure) ? $config->secure : '';
        }
    }

    public function sendMail($email, $theme, $answer)
    {
        $this->email = $email;
        $this->theme = $theme;
        $this->answer = $answer;
        $this->addAddress($email, 'recipient');
        $this->Subject = $theme;
        $this->msgHTML($answer);
        try {
            $this->send();
        } catch (Exception $e) {
            echo "Mailer Error: " . $this->ErrorInfo;
        }
    }

}