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
        if($this->connect()){

            $result = $this->getProvider()->search()->groups()->get();

            foreach ($result as $item) {

                if($item instanceof Group){
                    echo $item->getDn() . "<br>";
                    var_dump($item->getMemberNames());
                }
            }
        }
    }

    public function getGroup(ServerRequestInterface $req, ResponseInterface $resp, $group_id)
    {
        $groupRepo = new GroupRepository($this->getProvider());
        $group = $groupRepo->getAGroup('Team1', 'CN=blabla');

        if(empty($group)){
            $resp = $resp->withStatus(204);
        }else{
            $resp = $resp->withHeader('Content-type', 'application/json')->withStatus(200);
            $resp->write(json_encode($group));
        }
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
        var_dump("asda");
    }
}