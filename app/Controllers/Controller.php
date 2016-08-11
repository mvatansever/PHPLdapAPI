<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 15:10
 */

namespace App\Controllers;

use Adldap\Connections\Ldap;
use Adldap\Connections\Provider;
use Interop\Container\ContainerInterface;
use Adldap\Connections\Provider as adLDAPProvider;

class Controller{

    /**
     * Store Adldap Provider
     * @var Provider
     */
    protected $provider;

    /**
     * Store Ldap Configurations
     * @var array
     */
    protected $ldapConfig;

    /**
     * Store Ldap Connection
     * @var Ldap
     */
    protected $ldapConnection;

    protected $connected = false;
    protected $own_base_dn;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
     * Return connection.ldap configurations.
     *
     * @return array
     */
    public function getConfiguration()
    {
        $ldapConfiguration = $this->getContainer()->get('connection')['ldap'];

        return $ldapConfiguration;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return null | adLDAPProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}