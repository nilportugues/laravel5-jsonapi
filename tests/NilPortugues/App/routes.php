<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::group(['prefix' => 'api/v1/'], function () {
        Route::post('employees', ['as' => 'employees.post', 'uses' => 'EmployeesController@postAction']);
        Route::get('employees', ['as' => 'employees.list', 'uses' => 'EmployeesController@listAction']);
        Route::get('employees/{id}', ['as' => 'employees.get', 'uses' => 'EmployeesController@getAction']);
        Route::put('employees/{id}', ['as' => 'employees.put', 'uses' => 'EmployeesController@putAction']);
        Route::patch('employees/{id}', ['as' => 'employees.patch', 'uses' => 'EmployeesController@patchAction']);
        Route::delete('employees/{id}', ['as' => 'employees.delete', 'uses' => 'EmployeesController@deleteAction']);
        Route::get('employees/{employee_id}/orders', ['as' => 'employees.orders', 'uses' => 'EmployeesController@getOrdersByEmployee']);

        Route::get('orders', ['as' => 'orders.list', 'uses' => 'OrdersController@listAction']);
        Route::get('orders/{id}', ['as' => 'orders.get', 'uses' => 'OrdersController@getAction']);
    });
