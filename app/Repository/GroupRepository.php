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

    /**
     * Get all groups from passed base DN. If don't want to get members of group $members parameter set to false.
     *
     * @param string $own_base_dn
     * @param bool $members
     * @return array
     */
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

    /**
     * Get members of group passed to parameter.
     *
     * @param Group $group
     * @return array
     */
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


    /**
     * Create a group on LDAP.
     *
     * @param array $group_info
     * @param string $base_dn
     * @return bool
     */
    public function createGroup($group_info, $base_dn)
    {
        $base_dn = $base_dn . "," . $this->getBaseDN();
        $base_dn = trim($base_dn,',');

        $group = $this->getProvider()->make()->group();

        $group->setName($group_info['name']);

        if ($group_info['description']) {
            $group->setDescription($group_info['description']);
        }

        $dnBuilder = $group->getDnBuilder();
        $dnBuilder->addOu($group_info['name']);
        $dnBuilder->setBase($base_dn);

        $group->setDn($dnBuilder);

        return $group->save();
    }

    /**
     * Update a group on LDAP.
     *
     * @param array $group_info
     * @param string $group_ou
     * @param string $base_cn
     * @return bool
     */
    public function updateGroup($group_info, $group_ou, $base_cn)
    {

        $base_dn = $base_cn . "," . $this->getBaseDN();
        $group_base_dn = "OU=" . $group_ou . "," . $base_dn;

        $group = $this->getProvider()->search()->groups()->findByDn($group_base_dn);

        if ($group instanceof Group) {

            if($group_info['name']){
                $group->setName($group_info['name']);
            }

            if($group_info['description']){
                $group->setDescription($group_info['description']);
            }

            return $group->save();
        }

        return false;
    }
}