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

    use LdapController;
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

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $connected = false;
    protected $own_base_dn;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->init();
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