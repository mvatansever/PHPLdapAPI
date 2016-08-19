<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 15:12
 */

namespace App\Controllers;

use App\Repository\UserRepository;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Exception;

class UserController extends Controller{

    protected $userCN;
    /**
     * UserController constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->userCN = $this->container->get('settings')['users_cn'];
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

        $user_repo = new UserRepository($this->getProvider(), $this->userCN);
        $returnUsers['results'] = $user_repo->getAllUsers();

        if(empty($returnUsers)){
            $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(404);
        }else{
            $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(200);
            $resp->getBody()->write(json_encode($returnUsers));
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
        if(empty($parameters['name']))
        {
            throw new Exception('Name attributes must be set.');
        }

        if(empty($parameters['password']))
        {
            throw new Exception('Password must be set.');
        }

        $user_repo = new UserRepository($this->getProvider(), $this->userCN);

        if ($user_repo->storeUser($parameters)) {
            $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(201);
            $resp->getBody()->write(json_encode($parameters));
        }else{
            $resp = $resp->withHeader('Content-type', 'applicaton/json')->withStatus(500);
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
        $user_repo = new UserRepository($this->getProvider(), $this->userCN);
        $user = $user_repo->getUser($user_id);

        if (empty($user)) {
            $resp = $resp->withStatus(404);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
            $resp->getBody()->write(json_encode($user));
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

        $user_repo = new UserRepository($this->getProvider(), $this->userCN);
        $update_user = $user_repo->updateUser($user_id, $user_informations);

        if($update_user){
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
            $resp->getBody()->write(json_encode($user_informations));
        }else{

            $resp = $resp->withStatus(409);
        }

        return $resp;
    }
}