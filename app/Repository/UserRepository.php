<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 16/06/16 15:15
 */

namespace App\Repository;

use Adldap\Models\User;
use Adldap\Objects\AccountControl;

class UserRepository extends Repository{

    /**
     * Return all users from LDAP DN
     *
     * @return array
     */
    public function getAllUsers()
    {
        $returnArray = [];

        $this->getProvider()->getConfiguration()->setBaseDn($this->baseDN);
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
     * @param string $accountName
     * @return array
     * @throws \Exception
     */
    public function getUser($accountName)
    {
        $qb = $this->getProvider()->search()->newQuery($this->baseDN);

        $user = $qb->whereEquals(
            $this->getProvider()->getSchema()->accountName(),
            $accountName
        )->get();

        if($user->count() < 1){
            throw new \Exception("Not found user at LDAP");
        }

        // Get first record
        $user = $user[0];

        /**
         * PHP array keys are case-sensitive
         * And don't want to use name with changed name
         * For this reason used strtolower function
         */
        $userArray = [
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

        return $userArray;
    }

    /**
     * Create a user on LDAP
     *
     * @param array $user_informations
     * @return bool
     */
    public function storeUser($user_informations = [])
    {
        $user = $this->getProvider()->make()->user();

        // Set name of User
        $user = $this->setNameOfUser($user, $user_informations['name']);

        // Validation BEGIN
        if(isset($user_informations['displayName']))
        {
            $user->setDisplayName($user_informations['displayName']);
        }

        if(isset($user_informations['surname']))
        {
            $user->setAttribute('sn', $user_informations['surname']);
        }

        if(isset($user_informations['jobTitle']))
        {
            $user->setTitle($user_informations['jobTitle']);
        }

        if(isset($user_informations['mail']))
        {
            $user->setEmail($user_informations['mail']);
        }

        if(isset($user_informations['office']))
        {
            $user->setPhysicalDeliveryOfficeName($user_informations['office']);
        }

        if(isset($user_informations['manager']))
        {
            $user = $this->setManager($user, $user_informations['manager']);
        }

        if(isset($user_informations['phone']))
        {
            $user->setAttribute('telephoneNumber', $user_informations['phone']);
        }

        if(isset($user_informations['mobile']))
        {
            $user->setAttribute('mobile', $user_informations['mobile']);
        }
        // Validation END

        // Normal account saved for set password
        $user->setAttribute(
            $this->getProvider()->getSchema()->userAccountControl(),
            AccountControl::NORMAL_ACCOUNT
        );

        // Set user DN
        $userBaseDN = $this->makeDN($this->baseDN, $user_informations['name']);
        $user->setDn($userBaseDN);

        // Save user to LDAP
        if( $user->save() ) {
            // Set password after user is saved
            return $this->setPassword($user, $user_informations['password']);
        }

        return false;
    }

    /**
     * Update user on LDAP
     *
     * @param $accountName        string
     * @param $user_informations  array
     * @return bool | array
     */
    public function updateUser($accountName, $user_informations = [])
    {
        $updatedFields = [];
        $user = $this->getProvider()->search()->users()->findBy(
            $this->getProvider()->getSchema()->accountName(),
            $accountName
        );

        if($user instanceof User){
            if(isset($user_informations['displayName'])){
                $updatedFields["displayName"] = $user_informations['displayName'];
                $user->setDisplayName($user_informations['displayName']);
            }

            if(isset($user_informations['jobTitle'])){
                $updatedFields["jobTitle"] = $user_informations['jobTitle'];
                $user->setTitle($user_informations['jobTitle']);
            }

            if(isset($user_informations['mail'])){
                $updatedFields["mail"] = $user_informations['mail'];
                $user->setEmail($user_informations['mail']);
            }

            if(isset($user_informations['department'])){
                $updatedFields["department"] = $user_informations['department'];
                $user->setDepartment($user_informations['department']);
            }

            if(isset($user_informations['company'])){
                $updatedFields["company"] = $user_informations['company'];
                $user->setCompany($user_informations['company']);
            }

            if(isset($user_informations['office'])){
                $updatedFields["office"] = $user_informations['office'];
                $user->setPhysicalDeliveryOfficeName($user_informations['office']);
            }

            if(isset($user_informations['phone'])){
                $updatedFields["phone"] = $user_informations['phone'];
                $user->setTelephoneNumber($user_informations['phone']);
            }

            if(isset($user_informations['mobile'])){
                $updatedFields["mobile"] = $user_informations['mobile'];
                $user->setAttribute('mobile', $user_informations['mobile']);
            }

            // Managers will search in LDAP which found managers to be merge
            // with exists managers and will added to "manager" attribute.
            // For this reason the attribute has got specifically processes
            if( isset($user_informations['manager']) )
            {
                $updatedFields["manager"] = $user_informations['manager'];
                $user = $this->setManager($user, $user_informations['manager']);
            }

            if($user->save()) {
                return $updatedFields;
            }
        }

        return false;

    }

    /**
     * Set user's special name attributes.
     * CN, Name and sAMAccountName attributes setting here.
     *
     * @param User $user
     * @param $name
     * @return User
     */
    private function setNameOfUser(User $user, $name){

        $user->setAccountName($name);
        $user->setCommonName($name);
        $user->setName($name);

        return $user;
    }

    /**
     * Set user's manager
     *
     * @param User $user
     * @param $managerName
     * @return User
     */
    private function setManager(User $user, $managerName)
    {
        $qb = $this->getProvider()->search()->users();
        $qb->whereEquals($this->getProvider()->getSchema()->accountName(), $managerName);

        $manager = $qb->setDn($this->baseDN)->get()[0];

        if($manager instanceof User){
            $user->setManager($manager->getDn());
        }

        return $user;
    }

    /**
     * Set user's password
     *
     * @param User $user
     * @param $password
     * @return bool
     * @throws \Adldap\Exceptions\AdldapException
     */
    private function setPassword(User $user, $password)
    {
        $user->setPassword($password);

        return $user->save();
    }
}