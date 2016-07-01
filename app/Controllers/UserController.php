<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 15:12
 */

namespace App\Controllers;

use Adldap\Models\User;
use App\Repository\UserRepository;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Exception;

class UserController extends Controller{

    /**
     * UserController constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->setOwnBaseDn("CN=Users");
    }

    /**
     * Get all users from LDAP
     *
     * @param ServerRequestInterface $req
     * @param ResponseInterface $resp
     * @return ResponseInterface|static
     */
    public function getAllUsers(ServerRequestInterface $req , ResponseInterface $resp){

        $returnUsers = [];
        if($this->connect()){

            $user_repo = new UserRepository($this->getProvider());
            $returnUsers['results'] = $user_repo->getAllUsers();

            if(empty($returnUsers)){

                $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(204);
            }else{

                $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(200);
                $resp->getBody()->write(json_encode($returnUsers));
            }
        }

        return $resp;

    }

    /**
     * Create a user and add to LDAP
     *
     * @param ServerRequestInterface $req
     * @param ResponseInterface $resp
     * @return ResponseInterface|static
     * @throws \Exception
     */
    public function createUser(ServerRequestInterface $req, ResponseInterface $resp)
    {
        $parameters = (array)$req->getParsedBody();
        if(empty($parameters['mail'])){

            throw new Exception('E-Mail adresi bilgisi girilmemis şarttır.');
        }
        
        if($this->connect()){
            
            $user_repo = new UserRepository($this->getProvider());


            if($user_repo->createUser($parameters)){

                $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(201);
                $resp->getBody()->write(json_encode($parameters));
            }else{

                $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(501);
            }
        }

        return $resp;
    }

    /**
     * Get user information
     *
     * @param ServerRequestInterface $req
     * @param ResponseInterface $resp
     * @param array $args
     * @return ResponseInterface|static
     */
    public function getUser(ServerRequestInterface $req, ResponseInterface $resp, $args)
    {

        $user_id = $args['user_id'];
        if($this->connect()){

            $user_repo = new UserRepository($this->getProvider());
            $user = $user_repo->getUser($user_id);

            if(empty($user)){

                $resp = $resp->withStatus(204);
            }else{

                $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
                $resp->write(json_encode($user));
            }
        }

        return $resp;
    }

    /**
     * Update user information
     *
     * @param ServerRequestInterface $req
     * @param ResponseInterface $resp
     * @param $args
     * @return ResponseInterface|static
     */
    public function updateUser(ServerRequestInterface $req, ResponseInterface $resp, $args)
    {
        $user_id = $args['user_id'];
        $user_informations = (array) $req->getParsedBody();

        if($this->connect()){
            
            $user_repo = new UserRepository($this->getProvider());
            $update_user = $user_repo->updateUser($user_id, $user_informations);

            if($update_user instanceof User){

                $attributes = $update_user->getAttributes();
                unset($attributes['objectclass']);
                unset($attributes['objectcategory']);

                $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
                $resp->write(json_encode($attributes));
            }else{

                $resp->withStatus(409);
            }
        }

        return $resp;

    }

    /**
     * Disable/lock user
     *
     * @param ServerRequestInterface $req
     * @param ResponseInterface $resp
     * @param array $args
     */
    public function disableUser(ServerRequestInterface $req, ResponseInterface $resp, $args)
    {
        var_dump($args);
    }

    /**
     * Enable/unlock user
     *
     * @param ServerRequestInterface $req
     * @param ResponseInterface $resp
     * @param array $args
     */
    public function activateUser(ServerRequestInterface $req, ResponseInterface $resp, $args)
    {
        var_dump($args);
    }
}