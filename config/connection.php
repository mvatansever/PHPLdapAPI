<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 15:40
 */

return [
    'ldap' => [
        'domain_controllers' => ['192.168.10.10'],
        'base_dn' => 'DC=Mesut,DC=com',
        'admin_username' => 'CN=mesut,CN=Users,DC=Mesut,DC=com',
        'admin_password' => '123456',
        'port' => '389',
        'use_tls' => true,
    ]
];
