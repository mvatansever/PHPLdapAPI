<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 16/06/16 15:15
 */

namespace App\Repository;

use Adldap\Models\User;

class UserRepository extends Repository{

    /**
     * Return all users from LDAP DN
     *
     * @param string $base_cn
     * @return array
     */
    public function getAllUsers($base_cn = "CN=Users")
    {
        $returnArray = [];

        $base_dn = $base_cn . "," . $this->getBaseDN();
        $this->getProvider()->getConfiguration()->setBaseDn($base_dn);

        $users = $this->getProvider()->search()->users()->get();

        foreach ($users as $user) {

            if($user instanceof User){
                $returnArray[] = [
                    'name' => $user->getName(),
                    'surname' => $user->getAttribute('sn',0),
                    'displayName' => $user->getDisplayName(),
                    'mail' => $user->getEmail(),
                    'jobTitle' => $user->getTitle(),
                    'department' => $user->getDepartment(),
                    'company' => $user->getCompany(),
                    'office' => $user->getPhysicalDeliveryOfficeName(),
                    'manager' => $user->getAttribute('manager'),
                    'directReports' => $user->getAttribute(strtolower('directReports')),
                    'mobile' => $user->getAttribute('mobile',0),
                    'phone' => $user->getTelephoneNumber(),
                    'isLocked' => $user->getLockoutTime() == "" ? false : true,
                ];
            }
        }

        return $returnArray;
    }

    /**
     * Get user from LDAP
     *
     * @param string $user_cn
     * @param string $own_base_cn
     * @return array
     */
    public function getUser($user_cn, $own_base_cn = "CN=Users")
    {

        $userArray = [];
        $base_dn = $own_base_cn . "," . $this->getBaseDN();
        $qb = $this->getProvider()->search()->newQuery($base_dn);

        $users = $qb->whereEquals('cn', $user_cn)->get();

        foreach ($users as $item) {

            if($item instanceof User){

                /**
                 * PHP array keys are case-sensitive
                 * And don't want to use name with changed name
                 * For this reason used strtolower function
                 */
                $userArray = [
                    'name' => $item->getName(),
                    'surname' => $item->getAttribute('sn',0),
                    'displayName' => $item->getDisplayName(),
                    'mail' => $item->getEmail(),
                    'jobTitle' => $item->getTitle(),
                    'department' => $item->getDepartment(),
                    'company' => $item->getCompany(),
                    'office' => $item->getPhysicalDeliveryOfficeName(),
                    'manager' => $item->getAttribute('manager'),
                    'directReports' => $item->getAttribute(strtolower('directReports')),
                    'mobile' => $item->getAttribute('mobile',0),
                    'phone' => $item->getTelephoneNumber(),
                    'isLocked' => $item->getLockoutTime() == "" ? false : true,
                ];
            }
        }

        return $userArray;
    }

    /**
     * Create a user on LDAP
     *
     * @param array $user_informations
     * @param string $own_base_cn
     * @return bool
     */
    public function createUser($user_informations = [], $own_base_cn = "CN=Users")
    {

        $base_dn = $own_base_cn . "," . $this->getBaseDN();

        $user = $this->getProvider()->make()->user();

        // Validation BEGIN
        if($user_informations['name'] != ""){
            $user->setCommonName($user_informations['name']);
        }

        if($user_informations['displayName'] != ""){
            $user->setDisplayName($user_informations['displayName']);
        }

        if($user_informations['surname'] != ""){
            $user->setAttribute('sn', $user_informations['surname']);
        }

        if($user_informations['jobTitle'] != ""){
            $user->setTitle($user_informations['jobTitle']);
        }

        if($user_informations['mail'] != ""){
            $user->setEmail($user_informations['mail']);
        }

        if($user_informations['name'] != ""){
            $user->setName($user_informations['name']);
        }

        if($user_informations['department'] != ""){
            $user->setDepartment($user_informations['department']);
        }

        if($user_informations['company'] != ""){
            $user->setCompany($user_informations['company']);
        }

        if($user_informations['office'] != ""){
            $user->setPhysicalDeliveryOfficeName($user_informations['office']);
        }

        if($user_informations['manager'] != ""){

            $qb = $this->getProvider()->search()->users();
            $qb->whereEquals('cn', $user_informations['manager']);

            $manager = $qb->setDn($base_dn)->get()[0];

            if($manager instanceof User){
                $user->setManager($manager->getDn());
            }
        }

        if($user_informations['phone'] != ""){
            $user->setAttribute('telephoneNumber', $user_informations['phone']);
        }

        if($user_informations['mobile'] != ""){
            $user->setAttribute('mobile', $user_informations['mobile']);
        }
        // Validation END


        $dnBuilder = $user->getDnBuilder();
        $dnBuilder->addCn($user->getCommonName());
        $dnBuilder->setBase($base_dn);

        $user->setDn($dnBuilder);

        return $user->save();
    }

    /**
     * Update user on LDAP
     *
     * @param $user_id
     * @param array $user_informations
     * @param string $base_cn
     * @return bool | User
     */
    public function updateUser($user_id, $user_informations = [], $base_cn = "CN=Users")
    {
        $base_dn = $base_cn . "," . $this->getBaseDN();
        $user_base_dn = "CN=" . $user_id . "," . $base_dn;

        $user = $this->getProvider()->search()->users()->findByDn($user_base_dn);

        // To make IDE-friendly
        if($user instanceof User){

            /**
             * My base values are here: http://www.kouti.com/tables/userattributes.htm
             */
            if(
                $displayName = existValueError($user, $user_informations['displayName'], $user->getSchema()->displayName())
            ){
                $user->setDisplayName($displayName);
            }

            if(
                $jobTitle = existValueError($user, $user_informations['jobTitle'], $user->getSchema()->title())
            ){
                $user->setTitle($jobTitle);
            }

            if(
                $mail = existValueError($user, $user_informations['mail'], $user->getSchema()->email())
            ){
                $user->setEmail($mail);
            }

            if(
                $department = existValueError($user, $user_informations['department'], $user->getSchema()->department())
            ){
                $user->setDepartment($department);
            }

            if(
                $company = existValueError($user, $user_informations['company'], $user->getSchema()->company())
            ){
                $user->setCompany($company);
            }

            if(
                $office = existValueError($user, $user_informations['office'], $user->getSchema()->physicalDeliveryOfficeName())
            ){
                $user->setPhysicalDeliveryOfficeName($office);
            }

            if(
                $phone = existValueError($user, $user_informations['phone'], $user->getSchema()->telephone())
            ){
                $user->setTelephoneNumber($phone);
            }

            if(
                $mobile = existValueError($user, $user_informations['mobile'], 'mobile')
            ){
                $user->setAttribute('mobile', $mobile);
            }

            // Managers will search in LDAP which found managers to be merge with exists managers and will added to "manager" attribute
            // For this reason the attribute has got specifically processes
            if( isset($user_informations['manager']) ){

                $qb = $this->getProvider()->search()->users();

                $qb->whereEquals('cn',$user_informations['manager']);

                $manager = $qb->setDn($base_dn)->get()[0];

                if($manager instanceof User){

                    if(
                        $resultManager = existValueError($user, $manager->getDn(), $user->getSchema()->manager(), true)
                    ){
                        $user->setManager($manager->getDn());
                    }
                }

            }

            if($user->save()){

                return $user;
            }else{

                return false;
            }

        }

        return false;

    }
}