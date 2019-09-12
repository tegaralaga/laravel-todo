<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('/', 'HomeController@index')->name('index');
Route::group([
    'prefix' => 'todo'
], function () {
  Route::get('/', 'TodoController@index')->name('todo_index');
  Route::post('/name/save', 'TodoController@save_name')->name('todo_save_name');
  Route::post('/item/add', 'TodoController@add_item')->name('todo_add_item');
  Route::post('/item/delete', 'TodoController@delete_item')->name('todo_delete_item');
});
