<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 16/06/16 15:17
 */

namespace App\Repository;

use Adldap\Connections\Provider;

class Repository{

    protected $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param Provider $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    public function getBaseDN()
    {
        return $this->getProvider()->getConfiguration()->getBaseDn();
    }
}