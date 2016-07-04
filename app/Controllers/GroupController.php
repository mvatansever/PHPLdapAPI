<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 17:21
 */

namespace App\Controllers;

use Adldap\Models\Group;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Repository\GroupRepository;

class GroupController extends Controller{

    public function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    public function getAllGroups(ServerRequestInterface $req,  ResponseInterface $resp)
    {

        $groupRepo = new GroupRepository($this->getProvider());
        $group = $groupRepo->getAllGroups('CN=blabla');

        if(empty($group)){
            $resp = $resp->withStatus(204);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
            $resp->write(json_encode($group));
        }

        return $resp;
    }

    public function getGroup(ServerRequestInterface $req, ResponseInterface $resp, $group_id)
    {
        $groupRepo = new GroupRepository($this->getProvider());
        $group = $groupRepo->getAGroup($group_id, 'CN=blabla');

        if(empty($group)){
            $resp = $resp->withStatus(204);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
            $resp->write(json_encode($group));
        }

        return $resp;
    }

    public function updateGroup(ServerRequestInterface $req, ResponseInterface $resp, $group_id)
    {
        var_dump($group_id);
    }

    public function deleteGroup(ServerRequestInterface $req, ResponseInterface $resp, $group_id)
    {
        var_dump($group_id);
    }

    public function createGroup(ServerRequestInterface $req, ResponseInterface $resp)
    {
        $params = (array)$req->getParsedBody();
        $groupRepo = new GroupRepository($this->getProvider());

        if($groupRepo->createGroup($params, "CN=Groups")){
            $resp = $resp->withStatus(201);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(500);
            $resp = $resp->write(json_encode([
                'error' => 1,
                'message' => 'Group not created'
            ]));
        }

        return $resp;
    }
}