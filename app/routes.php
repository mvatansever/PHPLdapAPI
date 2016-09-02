<?php
/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 15:40
 */

/*
 * All routes is here
 */

$app->group('/user', function() use($app){

    $app->get('', 'App\Controllers\UserController:getAllUsers');
    $app->get('/{user_id}', 'App\Controllers\UserController:getUser');
    $app->post('', 'App\Controllers\UserController:createUser');
    $app->put('/{user_id}', 'App\Controllers\UserController:updateUser');
    $app->put('/{user_id}/passChange', 'App\Controllers\UserController:changePassword');
    $app->post('/{user_id}/resetPassword', 'App\Controllers\UserController:resetPassword');
});



$app->group('/group', function() use($app){

    $app->get('', 'App\Controllers\GroupController:getAllGroups');
    $app->get('/{group_id}', 'App\Controllers\GroupController:getGroup');
    $app->post('', 'App\Controllers\GroupController:createGroup');
    $app->put('/{group_id}', 'App\Controllers\GroupController:updateGroup');
    $app->delete('/{group_id}', 'App\Controllers\GroupController:deleteGroup');

});

