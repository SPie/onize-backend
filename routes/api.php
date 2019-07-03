<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\PasswordResetController;
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
            $router->post('login', ['as' => UsersController::ROUTE_NAME_LOGIN, 'uses' => 'User\UsersController@login']);

            $router->group(['middleware' => 'token'], function (Router $router) {
                $router->get('user', ['as'    => UsersController::ROUTE_NAME_AUTHENTICATED, 'uses' => 'User\UsersController@authenticatedUser']);
                $router->get('refresh', ['as' => UsersController::ROUTE_NAME_REFRESH_ACCESS_TOKEN, 'uses' => 'User\UsersController@refreshAccessToken']);
            });

            $router->group(['middleware' => 'auth'], function (Router $router) {
                $router->post('logout', ['as' => UsersController::ROUTE_NAME_LOGOUT, 'uses'  => 'User\UsersController@logout']);
            });
        });

        $router->group(['prefix' => 'password-reset'], function (Router $router) {
            $router->post('', ['as' => PasswordResetController::ROUTE_NAME_START, 'uses' => 'User\PasswordResetController@start']);
            $router->get('', ['as' => PasswordResetController::ROUTE_NAME_VERIFY_TOKEN, 'uses' => 'User\PasswordResetController@verifyToken']);
            $router->patch('', ['as' => PasswordResetController::ROUTE_NAME_RESET_PASSWORD, 'uses' => 'User\PasswordResetController@resetPassword']);
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
