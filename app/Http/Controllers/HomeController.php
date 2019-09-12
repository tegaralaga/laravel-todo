<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Traits\TodoTrait;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
  use TodoTrait;

  public function __construct() {

  }

  public function index(Request $request) {
    $this->title = "Todo";
    $key = '';
    if (array_key_exists('key', $_COOKIE)) {
      $key = $_COOKIE['key'];
    }
    $pass = [
      'key' => $key
    ];
    return $this->v('welcome', $pass);
  }

}
