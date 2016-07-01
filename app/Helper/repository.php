<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 20/06/16 15:07
 */


/**
 *
 * To prevent 'Type or value exists' errors
 * More: https://ff1959.wordpress.com/2011/07/28/type-or-value-exists/
 *
 * @Author Mesut Vatansever
 *
 * @param \Adldap\Models\User $user
 * @param $value
 * @param $attr
 * @return false | array | string
 */
function existValueError(\Adldap\Models\User $user, $value, $attr, $multi = false){

    if($value == "" || $value == null){

        return false;
    }

    if($multi == false && is_array($value)){

        return false;
    }

    if(array_key_exists($attr, $user->getOriginal())){

        $attribute = $user->getOriginal()[$attr];

        if(is_array($attribute)){

            $attribute = array_map('strtolower', array_map('trim', $attribute));

            $attribute_values = array_values($attribute);

            // If value is array (maybe multiple manager etc..)
            if(is_array($value)){

                $value = array_map('strtolower', array_map('trim', $value));

                // To merge values and exists attribute's values if value is in array
                foreach ($value as $key => $v) {

                    if(in_array($v, $attribute_values)){

                        unset($value[$key]);
                    }
                }

                // array_values used for "A 'values' array must have consecutive indices" errors
                return array_values($value);
            }
            else{

                $value = strtolower($value);
                if(in_array($value, $attribute_values)){
                    return false;
                }
            }

            return $value;

        }else{

            // If attribute's value is array
            if(is_array($value)){

                if( ($key = array_search($attribute, $value)) ){
                    unset($value[$key]);
                }

                // array_values used for "A 'values' array must have consecutive indices" errors
                return array_values($value);

            }elseif($attribute == $value){

                return false;
            }

        }
    }

    return $value;
}