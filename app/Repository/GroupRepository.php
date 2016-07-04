<?php
/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 02/07/16
 * Time: 14:37
 */

namespace App\Repository;


use Adldap\Models\AbstractModel;
use Adldap\Models\Group;

class GroupRepository extends Repository
{
    /**
     * Get group informations with members from Ldap. If don't want to get members of group $members parameter set to false.
     *
     * @param string $group_ou Group's name
     * @param string $own_base_dn Group's DN
     * @param bool $members
     * @return array
     */
    public function getAGroup($group_ou, $own_base_dn, $members = true)
    {
        $groupArray = [];
        $base_dn = $own_base_dn . "," . $this->getBaseDN();
        $base_dn = trim($base_dn,',');

        $qb = $this->getProvider()->search()->groups();
        $qb->setDn($base_dn);

        $groups = $qb->whereContains('ou', $group_ou)->get();

        foreach ($groups->all() as $group)
        {
            if ($group instanceof Group)
            {
                $groupArray[] = [
                    'name' => $group->getName()
                ];

                if ($members)
                {
                    $count = count($groupArray);
                    $groupArray[$count]['members'] = $this->getGroupMembers($group);
                }
            }
        }

        return $groupArray;
    }

    public function getAllGroups($own_base_dn, $members = true)
    {
        $groupArray = [];
        $base_dn = $own_base_dn . "," . $this->getBaseDN();
        $base_dn = trim($base_dn,',');

        $qb = $this->getProvider()->search()->groups();
        $qb->setDn($base_dn);
        $groupsResult = $qb->get();

        foreach ($groupsResult->all() as $group) {

            if ($group instanceof Group)
            {
                $groupArray[] = [
                    'name' => $group->getName()
                ];

                if ($members)
                {
                    $count = count($groupArray);
                    $groupArray[$count]['members'] = $this->getGroupMembers($group);
                }
            }
        }

        return $groupArray;
    }

    public function getGroupMembers(Group $group)
    {
        $groupMembers = [];
        foreach ($group->getMembers() as $member)
        {
            if ($member instanceof AbstractModel)
            {
                $groupMembers[] = $member->getDn();
            }
        }
        return $groupMembers;
    }
}