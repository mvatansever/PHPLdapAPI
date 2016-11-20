<?php
/**
 * Created by PhpStorm.
 * User: mesut.vatansever
 * Date: 20/11/16
 * Time: 06:54
 */

namespace App\Controllers;

use Adldap\Connections\Ldap;
use Adldap\Connections\Provider;

trait LdapController
{

    /**
     * Initialize the Ldap Provider.
     */
    public function init()
    {
        $this->ldapConfig = $this->getConfiguration();
        $this->ldapConnection = $this->getLdapConnection();
        $this->provider = new Provider($this->ldapConfig, $this->ldapConnection);
        $this->provider->connect();
    }

    /**
     * Return new Ldap class with new ldap connection.
     *
     * @param bool $tls
     * @return Ldap
     */
    public function getLdapConnection($tls = true)
    {
        $ldap = new Ldap();
        $ldap->connect($this->ldapConfig['domain_controllers'][0]);

        if ($tls) {
            $ldap->startTLS();
            $ldap->useTLS();
        }

        return $ldap;
    }

    /**
     * @return null | Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Return connection.ldap configurations.
     *
     * @return array
     */
    public function getConfiguration()
    {
        $ldapConfiguration = $this->getContainer()->get('connection')['ldap'];

        return $ldapConfiguration;
    }
}