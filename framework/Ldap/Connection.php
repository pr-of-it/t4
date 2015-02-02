<?php

namespace T4\Ldap;

use T4\Core\Config;

class Connection
{
    const ERROR_CODE_CONNECT = 1;
    const ERROR_CODE_BIND = 2;

    protected $domain = null;

    protected $ldap = null;
    protected $bind = null;

    public function __construct(Config $config)
    {
        if (!empty($config->domain)) {
            $this->domain = $config->domain;
        }
        $conn = ldap_connect($config->host, $config->port);
        if (false === $conn) {
            throw new Exception('Cant connect to LDAP server', self::ERROR_CODE_CONNECT);
        }
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $config->version);
        $this->ldap = $conn;
    }

    public function bind($user, $pass)
    {
        $bind = @ldap_bind($this->ldap, $user . ($this->domain ? '@' . $this->domain : ''), $pass);
        if (false === $bind) {
            throw new Exception('Cant bind to LDAP server', self::ERROR_CODE_BIND);
        }
        $this->bind = $bind;
    }

    public function search($dn, $filter, $attrs = [])
    {
        $search = ldap_search($this->ldap, $dn, $filter, $attrs);
        return ldap_get_entries($this->ldap, $search);
    }

}