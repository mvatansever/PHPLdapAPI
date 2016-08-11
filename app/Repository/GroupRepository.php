<?php
/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 02/07/16
 * Time: 14:37
 */

namespace App\Repository;


use Adldap\Models\Group;
use Adldap\Models\Printer;
use Adldap\Models\User;

class GroupRepository extends Repository
{
    /**
     * Get group informations with members from Ldap. If don't want to get members of group $members parameter set to false.
     *
     * @param $accountName      string
     * @param $members          bool
     * @return array
     * @throws \Exception
     */
    public function getAGroup($accountName, $members = true)
    {
        $qb = $this->getProvider()->search()->groups();
        $qb->setDn($this->baseDN);

        $group = $qb->findBy($this->getProvider()->getSchema()->accountName(), $accountName);

        if ($group instanceof Group)
        {
            $groupArray['group'] = $group;

            if ($members) {
                $groupArray['members'] = $this->getGroupMembers($group);
            }
        } else {
            throw new \Exception("Not found Group at LDAP");
        }

        return $groupArray;
    }

    /**
     * Get all groups from passed base DN. If don't want to get members of group $members parameter set to false.
     *
     * @param bool $members
     * @return array
     * @throws \Exception
     */
    public function getAllGroups($members = true)
    {
        $qb = $this->getProvider()->search()->groups();
        $qb->setDn($this->baseDN);
        $groupsResult = $qb->get();

        if($groupsResult->count() == 0){
            throw new \Exception("Not found Group at LDAP");
        }

        foreach ($groupsResult->all() as $group) {
            $groupArray[]['group'] = $group;

            if ($members)
            {
                $count = count($groupArray);
                $groupArray[$count]['members'] = $this->getGroupMembers($group);
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
    private function getGroupMembers(Group $group)
    {
        $groupMembers = [];
        foreach ($group->getMembers() as $member)
        {
            if ($member instanceof Printer)
            {
                $groupMembers['printers'][] = [
                    'accountName' => $member->getAccountName(),
                    'printerName' => $member->getPrinterName(),
                    'serverName'  => $member->getServerName(),
                    'drivername'  => $member->getDriverName(),
                    'location'    => $member->getLocation(),
                    'DN'          => $member->getDn(),
                    'createdAt'   => $member->getCreatedAt(),
                    'updatedAt'   => $member->getUpdatedAt()
                ];
            }else if($member instanceof User){
                $groupMembers['users'][] = [
                    'accountName' => $member->getAccountName(),
                    'pwdLastSet'  => $member->getPasswordLastSet(),
                    'DN'          => $member->getDn(),
                    'name' => $member->getName(),
                    'surname' => $member->getAttribute('sn',0),
                    'displayName' => $member->getDisplayName(),
                    'mail' => $member->getEmail(),
                    'jobTitle' => $member->getTitle(),
                    'department' => $member->getDepartment(),
                    'company' => $member->getCompany(),
                    'office' => $member->getPhysicalDeliveryOfficeName(),
                    'manager' => $member->getAttribute('manager'),
                    'directReports' => $member->getAttribute(strtolower('directReports')),
                    'mobile' => $member->getAttribute('mobile',0),
                    'phone' => $member->getTelephoneNumber(),
                    'isLocked' => $member->getLockoutTime() == "" ? false : true,
                    'createdAt'   => $member->getCreatedAt(),
                    'updatedAt'   => $member->getUpdatedAt()
                ];
            }else if ($member instanceof Group){
                $groupMembers['groups'][] = [
                    'accountName' => $member->getAccountName(),
                    'DN'          => $member->getDn(),
                    'memberNames' => $member->getMemberNames(),
                    'createdAt'   => $member->getCreatedAt(),
                    'updatedAt'   => $member->getUpdatedAt()
                ];
            }else{
                $groupMembers['others'][] = [
                    'accountName' => $member->getAccountName(),
                    'DN'          => $member->getDn(),
                    'createdAt'   => $member->getCreatedAt(),
                    'updatedAt'   => $member->getUpdatedAt()
                ];
            }
        }
        return $groupMembers;
    }

    /**
     * Create a group on LDAP.
     *
     * @param array $group_info
     * @return bool
     */
    public function storeGroup($group_info)
    {
        $group = $this->getProvider()->make()->group();

        // Set special name attributes
        $group = $this->setNameOfGroup($group, $group_info['name']);

        if ($group_info['description']) {
            $group->setDescription($group_info['description']);
        }
        
        $baseDN = $this->makeDN($this->baseDN, $group_info['name']);
        $group->setDn($baseDN);

        return $group->save();
    }

    /**
     * Update a group on LDAP.
     *
     * @param $group_info   array
     * @param $accountName  string
     * @return bool
     */
    public function updateGroup($group_info, $accountName)
    {
        $group = $this->getProvider()->search()->groups()->findBy(
            $this->getProvider()->getSchema()->accountName(),
            $accountName
        );

        if ($group instanceof Group) {

            if($group_info['name']){

                // Rename group
                if ($group->rename($group_info['name'])) {
                    // After group's rename change the account name of Group
                    $group->setAccountName($group_info['name']);
                }
            }

            if($group_info['description']){
                $group->setDescription($group_info['description']);
            }

            return $group->save();
        }

        return false;
    }

    /**
     * Delete a group on LDAP.
     *
     * @param $accountName string
     * @return bool
     * @throws \Adldap\Exceptions\AdldapException
     * @throws \Adldap\Exceptions\ModelNotFoundException
     */
    public function deleteGroup($accountName)
    {
        $group = $this->getProvider()->search()->groups()->findBy(
            $this->getProvider()->getSchema()->accountName(),
            $accountName
        );

        if($group instanceof Group){
            return $group->delete();
        }

        return false;
    }

    /**
     * Set user's special name attributes.
     * CN, Name and sAMAccountName attributes setting here.
     *
     * @param Group $group
     * @param $name
     * @return User
     */
    private function setNameOfGroup(Group $group, $name){

        $group->setAccountName($name);
        $group->setCommonName($name);
        $group->setName($name);

        return $group;
    }
}