<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 17:21
 */

namespace App\Controllers;

use Adldap\Exceptions\AdldapException;
use Adldap\Exceptions\ModelNotFoundException;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Repository\GroupRepository;

class GroupController extends Controller{

    protected $groupCN;

    public function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->groupCN = $this->container->get('settings')['users_cn'];
    }

    public function getAllGroups(ServerRequestInterface $req,  ResponseInterface $resp)
    {
        $params = (array)$req->getParsedBody();
        $groupRepo = new GroupRepository($this->getProvider(), $this->groupCN);
        $group = $groupRepo->getAllGroups($params['getMember'] ? true : false); // Ouu very sexy code :))

        if(empty($group)){
            $resp = $resp->withStatus(204);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
            $resp->write(json_encode($group));
        }

        return $resp;
    }

    public function getGroup(ServerRequestInterface $req, ResponseInterface $resp, $accountName)
    {
        $groupRepo = new GroupRepository($this->getProvider(), $this->groupCN);
        $group = $groupRepo->getAGroup($accountName);

        if(empty($group)){
            $resp = $resp->withStatus(204);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
            $resp->write(json_encode($group));
        }

        return $resp;
    }

    public function updateGroup(ServerRequestInterface $req, ResponseInterface $resp, $groupName)
    {
        $params = (array)$req->getParsedBody();
        $groupRepo = new GroupRepository($this->getProvider(), $this->groupCN);

        if($groupRepo->updateGroup($params, $groupName)){
            $resp = $resp->withStatus(200);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(400);
            $resp = $resp->write(json_encode([
                'error' => 1,
                'message' => 'Group not created. Please glance at your server manually for the catch real error.'
            ]));
        }

        return $resp;
    }

    public function deleteGroup(ServerRequestInterface $req, ResponseInterface $resp, $accountName)
    {
        $groupRepo = new GroupRepository($this->getProvider(), $this->groupCN);

        try {

            if ($groupRepo->deleteGroup($accountName)) {
                $resp = $resp->withStatus(200);
            }else {
                $resp = $resp->withStatus(500);
            }

        }catch (ModelNotFoundException $modelEx){

            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(410);

        }catch (AdldapException $adEx){

            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(404);
            $resp = $resp->write(json_encode([
                'error' => 1,
                'message' => $adEx->getMessage()
            ]));

        }

        return $resp;
    }

    public function createGroup(ServerRequestInterface $req, ResponseInterface $resp)
    {
        $params = (array)$req->getParsedBody();
        $groupRepo = new GroupRepository($this->getProvider(), $this->groupCN);

        if(empty($params['name'])){
            throw new \Exception("Name attribute must be set!");
        }

        if($groupRepo->storeGroup($params)){
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