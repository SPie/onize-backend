<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UsersController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

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

$router->group(
    [
        'prefix'     => 'api',
        'middleware' => [
            'cors'
        ],
    ],
    function (Router $router) {

        //region Auth calls

        $router->group(['prefix' => 'auth'], function (Router $router) {
            $router->post('login', ['as' => AuthController::ROUTE_NAME_LOGIN, 'uses' => 'Auth\AuthController@login']);

            $router->group(['middleware' => 'token'], function (Router $router) {
                $router->get('user', ['as'    => AuthController::ROUTE_NAME_USER, 'uses' => 'Auth\AuthController@authenticatedUser']);
                $router->get('refresh', ['as' => AuthController::ROUTE_NAME_REFRESH, 'uses' => 'Auth\AuthController@refreshAccessToken']);
            });

            $router->group(['middleware' => 'auth'], function (Router $router) {
                $router->post('logout', ['as' => AuthController::ROUTE_NAME_LOGOUT, 'uses'  => 'Auth\AuthController@logout']);
            });
        });

        //endregion

        //region Users calls

        $router->group(['prefix' => 'users'], function (Router $router) {
            $router->post('', ['as' => UsersController::ROUTE_NAME_REGISTER, 'uses' => 'User\UsersController@register']);

            $router->group(['middleware' => ['token']], function (Router $router) {
                $router->patch('', ['as' => UsersController::ROUTE_NAME_CHANGE_PASSWORD, 'uses' => 'User\UsersController@changePassword']);
            });
        });

        //endregion

    }
);
