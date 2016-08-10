<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 16/06/16 15:17
 */

namespace App\Repository;

use Adldap\Connections\Provider;
use Adldap\Objects\DistinguishedName;

class Repository{

    protected $provider;
    protected $baseDN;

    public function __construct(Provider $provider, $baseCN)
    {
        $this->provider = $provider;
        $this->baseDN = $this->makeDN($this->getBaseDN(), $baseCN);
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

    /**
     * Make DN with given name and baseDN.
     *
     * @param $baseDN
     * @param $name
     * @return string
     */
    public function makeDN($baseDN, $name)
    {
        $dnBuilder = new DistinguishedName();
        $dnBuilder->addCn($name);
        $dnBuilder->setBase($baseDN);

        return $dnBuilder->get();
    }
}