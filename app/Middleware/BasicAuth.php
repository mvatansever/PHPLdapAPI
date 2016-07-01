<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 16/06/16 10:34
 */

namespace App\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BasicAuth{

    protected $users = array();

    public function __construct($users = array())
    {
        foreach ($users as $user) {

            $this->users[$user['user']] = $user['password'];
        }
    }

    public function __invoke(RequestInterface $req, ResponseInterface $resp, callable $next)
    {
        $auth_name = $req->getHeaderLine('BASIC-AUTH-USERNAME');
        $auth_pass = $req->getHeaderLine('BASIC-AUTH-PASSWORD');

        if(isset($this->users[$auth_name])){

            if($this->users[$auth_name] == $auth_pass){

                return $next($req,$resp);
            }
        }

        return $resp->withStatus(401)->withJson(['error' => 'Not authenticated']);
    }
}