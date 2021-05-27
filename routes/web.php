<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// AUTH
$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login',  ['uses' => 'AuthController@login']);
    $router->post('logout',  ['uses' => 'AuthController@logout',  'middleware' => 'auth']);
    $router->get('logged',  ['uses' => 'AuthController@me',  'middleware' => 'auth']);
    $router->get('refresh',  ['uses' => 'AuthController@refresh',  'middleware' => 'auth']);
});

// USERS
$router->group(['prefix' => 'user'], function () use ($router) {
    $router->get('/',  ['uses' => 'UserController@showAllUsers', 'middleware' => 'auth']);
    $router->get('list/count',  ['uses' => 'UserController@showAllCountUsers', 'middleware' => 'auth']);
    $router->get('byId/{id}',  ['uses' => 'UserController@showOneUser']);
    $router->get('byName/{name}',  ['uses' => 'UserController@showOneByName', 'middleware' => 'auth']);
    $router->post('/',  ['uses' => 'UserController@create']);
    $router->put('update/{id}',  ['uses' => 'UserController@update', 'middleware' => 'auth']);
    $router->delete('delete/{id}',  ['uses' => 'UserController@delete', 'middleware' => 'auth']);
    $router->post('reset_password/{id}',  ['uses' => 'UserController@updatePassword']);
});

// CLIENTS
$router->group(['prefix' => 'client'], function () use ($router) {
    $router->get('/',  ['uses' => 'ClientController@showAllClients']);
    $router->get('list/count',  ['uses' => 'ClientController@showAllCountClients']);
    $router->get('byId/{id}',  ['uses' => 'ClientController@showOneClient']);
    $router->get('byName/{name}',  ['uses' => 'ClientController@showOneByName']);
    $router->post('/',  ['uses' => 'ClientController@create', 'middleware' => 'auth']);
    $router->put('update/{id}',  ['uses' => 'ClientController@update', 'middleware' => 'auth']);
    $router->delete('delete/{id}',  ['uses' => 'ClientController@delete', 'middleware' => 'auth']);
});

// PROPOSAL
$router->group(['prefix' => 'proposal'], function () use ($router) {
    $router->get('/',  ['uses' => 'ProposalController@showAllProposals', 'middleware' => 'auth']);
    $router->get('list/count',  ['uses' => 'ProposalController@showAllCountProposals']);
    $router->get('byId/{client_id}/{id}',  ['uses' => 'ProposalController@showOneProposal']);
    $router->get('byClientId/{client_id}',  ['uses' => 'ProposalController@showAllProposalsByClient']);
    $router->get('byClientName/{client_name}',  ['uses' => 'ProposalController@showAllProposalsByClientName']);
    $router->get('byName/{title}',  ['uses' => 'ProposalController@showOneByTitle']);
    $router->post('/',  ['uses' => 'ProposalController@create', 'middleware' => 'auth']);
    $router->put('update/{id}',  ['uses' => 'ProposalController@update', 'middleware' => 'auth']);
    $router->delete('delete/{id}',  ['uses' => 'ProposalController@delete', 'middleware' => 'auth']);

    $router->group(['prefix' => 'item'], function () use ($router) {
        $router->post('/',  ['uses' => 'ProposalController@createItem', 'middleware' => 'auth']);
        $router->put('{id}',  ['uses' => 'ProposalController@updateItem', 'middleware' => 'auth']);
        $router->delete('{id}',  ['uses' => 'ProposalController@deleteItem', 'middleware' => 'auth']);
    });
});
