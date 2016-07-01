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
    $app->post('/{user_id}/disable', 'App\Controllers\UserController:disableUser');
    $app->post('/{user_id}/activate', 'App\Controllers\UserController:activeUser');
});



$app->group('/group', function() use($app){

    $app->get('', 'App\Controllers\GroupController:getAllGroups');
    $app->get('/{group_id}', 'App\Controllers\GroupController:getGroup');
    $app->post('', 'App\Controllers\GroupController:createGroup');
    $app->put('/{group_id}', 'App\Controllers\GroupController:updateGroup');
    $app->delete('/{group_id}', 'App\Controllers\GroupController:deleteGroup');


    // Group Members
    $app->post('/{group_id}/member', 'App\Controllers\GroupMembersController:addMemberToGroup');
    $app->get('/{group_id}/member/{user_id}', 'App\Controllers\GroupMembersController:removeMemberFromGroup');


});

