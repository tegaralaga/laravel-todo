<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Redis;
use App\Http\Requests;
use App\Traits\TodoTrait;
use App\Models\TodoModel;
use App\Models\TodoDetailModel;
use Illuminate\Support\Facades\Cookie;

class TodoController extends Controller
{
  use TodoTrait;

  public function __construct() {

  }

  public function index(Request $request) {
    $key = $request->query('key');
    if ($request->ajax()) {
      $id = Redis::get("todo:key:id:{$key}");
      if ($id == null) {
        $todo = TodoModel::select('id')->where('key', $key)->first();
        if (!($todo == null)) {
          $id = $todo->id;
        }
      } else {
        $todo = TodoModel::select('id')->find($id)->count();
        if ($todo == 0) {
          $id = null;
        }
      }
      $details = [];
      if (!($id == null)) {
        $todo_redis = Redis::get("todo:key:{$key}");
        $data = [];
        if ($todo_redis == null) {
          $details = TodoDetailModel::where('todo_id', $id)->get();
          if (count($details) > 0) {
            $temp = [];
            foreach ($details as $detail) {
              $temp[$detail->id] = [
                'item' => $detail->item,
                'created_at' => $detail->created_at,
              ];
            }
            $details = $temp;
          }
        } else {
          $data = json_decode($todo_redis, true);
          $details = $data['details'];
        }
        $temp = [];
        foreach ($details as $key => $detail) {
          $temp[] = [
            'id' => $key,
            'item' => $detail['item'],
            'created_at' => $detail['created_at'],
          ];
        }
        $details = $temp;
      }
      $this->success = true;
      $this->data = $details;
      return $this->json();
    } else {
      $this->ReloadDatetime();
      if ($key == null) {
        return redirect('/');
      }
      if ($key == 'new') {
        $key = $this->generateTodoKey();
        return redirect("/todo?key={$key}");
      } else {
        setcookie('key', $key, 0, '/');
        if (!(array_key_exists('key', $_COOKIE))) {
          return redirect("/todo?key={$key}");
        } else {
          if (!($key == $_COOKIE['key'])) {
            return redirect("/todo?key={$key}");
          }
        }
      }
      $todo = [
        'id' => 0,
        'key' => $key,
        'name' => null,
        'created_at' => $this->now,
        'updated_at' => $this->now,
      ];
      $todo_temp = Redis::get("todo:key:{$key}");
      if ($todo_temp == null) {
        $id = Redis::get("todo:key:id:{$key}");
        if ($id == null) {
          $todo_temp = TodoModel::where('key', $key)->first();
          if (!($todo_temp == null)) {
            $todo = $todo_temp->toArray();
          }
        } else {
          $todo_temp = $todo = TodoModel::find($id);
          if (!($todo_temp == null)) {
            $todo = $todo_temp->toArray();
          }
        }
      } else {
        $todo_temp = json_decode($todo_temp, true);
        $todo = $todo_temp['todo'];
      }
      $todo = (object)$todo;
      if (!(property_exists($todo, 'name'))) {
        $todo->name = null;
      }
      $todo_title = (($todo->name == null) ? $todo->key : $todo->name);
      $this->title = "Todo | {$todo_title}";
      $pass = [
        'key' => $key,
        'todo' => $todo,
        'todo_title' => $todo_title,
      ];
      return $this->v('todo.index', $pass);
    }
  }

  public function add_item(Request $request) {
    $key = null;
    if (array_key_exists('key', $_COOKIE)) {
      $key = $_COOKIE['key'];
    }
    $item = null;
    if (!($request->input('todo') == null)) {
      $item = $request->input('todo');
      if (strlen(trim($item)) == 0) {
        $item = null;
      }
    }
    if ($request->ajax()) {
      if ($key == null) {
        $this->message = 'Unknown Todo';
      } else {
        if ($item == null) {
          $this->message = 'Todo Cannot Be Empty';
        } else {
          $id = Redis::get("todo:key:id:{$key}");
          $todo = null;
          if ($id == null) {
            $todo = TodoModel::where('key', $key)->first();
            if ($todo == null) {
              $todo = new TodoModel();
              $todo->key = $key;
              $todo->save();
            }
          } else {
            $todo = TodoModel::find($id);
            if ($todo == null) {
              $todo = TodoModel::where('key', $key)->first();
              if ($todo == null) {
                $todo = new TodoModel();
                $todo->key = $key;
                $todo->save();
              }
            }
          }
          $todo_redis = Redis::get("todo:key:{$key}");
          $data = [];
          $details = [];
          if ($todo_redis == null) {
            $details = TodoDetailModel::where('todo_id', $todo->id)->get();
            if (count($details) > 0) {
              $temp = [];
              foreach ($details as $detail) {
                $temp[$detail->id] = [
                  'item' => $detail->item,
                  'created_at' => $detail->created_at,
                ];
              }
              $details = $temp;
            }
          } else {
            $data = json_decode($todo_redis, true);
            $details = $data['details'];
          }
          $this->ReloadDatetime();
          $todo_detail = new TodoDetailModel();
          $todo_detail->todo_id = $todo->id;
          $todo_detail->item = $item;
          $todo_detail->created_at = $this->now;
          $todo_detail->save();
          $details[$todo_detail->id] = [
            'item' => $item,
            'created_at' => $todo_detail->created_at,
          ];
          $todo->updated_at = $todo_detail->created_at;
          $todo->save();
          $todo = $todo->toArray();
          $data['todo'] = $todo;
          $data['details'] = $details;
          Redis::set("todo:key:id:{$key}", $todo['id']);
          Redis::set("todo:key:{$key}", json_encode($data));
          $this->success = true;
          $this->data = $todo_detail->toArray();
        }
      }
      return $this->json();
    } else {
      if ($key == null)
        return redirect('/');
      else
        return redirect("/todo?key={$key}");
    }
  }

  public function delete_item(Request $request) {
    $key = null;
    if (array_key_exists('key', $_COOKIE)) {
      $key = $_COOKIE['key'];
    }
    $item_id = null;
    if (!($request->input('id') == null)) {
      $item_id = $request->input('id');
      if (strlen(trim($item_id)) == 0) {
        $item_id = null;
      }
    }
    if ($request->ajax()) {
      if ($key == null) {
        $this->message = 'Unknown Todo';
      } else {
        if ($item_id == null) {
          $this->message = 'Todo Item Cannot Be Empty';
        } else {
          $id = Redis::get("todo:key:id:{$key}");
          if ($id == null) {
            $todo = TodoModel::select('id')->where('key', $key)->first();
          } else {
            $todo = TodoModel::find($id);
            if ($todo == null) {
              $todo = TodoModel::where('key', $key)->first();
            }
          }
          if ($todo == null) {
            $this->message = 'Todo Not Found!';
          } else {
            $todo_detail = TodoDetailModel::where([
              ['todo_id', '=', $id],
              ['id', '=', $item_id]
            ])->first();
            if ($todo_detail == null) {
              $this->message = 'Todo Item Not Found!';
            } else {
              $todo_redis = Redis::get("todo:key:{$key}");
              $data = [];
              $details = [];
              if ($todo_redis == null) {
                $details = TodoDetailModel::where('todo_id', $todo->id)->get();
                if (count($details) > 0) {
                  $temp = [];
                  foreach ($details as $detail) {
                    $temp[$detail->id] = [
                      'item' => $detail->item,
                      'created_at' => $detail->created_at,
                    ];
                  }
                  $details = $temp;
                }
              } else {
                $data = json_decode($todo_redis, true);
                $details = $data['details'];
              }
              $todo_detail->delete();
              unset($details[$item_id]);
              $this->ReloadDatetime();
              $todo->updated_at = $this->now;
              $todo->save();
              $todo = $todo->toArray();
              $data['todo'] = $todo;
              $data['details'] = $details;
              Redis::set("todo:key:id:{$key}", $todo['id']);
              Redis::set("todo:key:{$key}", json_encode($data));
              $this->success = true;
              $this->data = $todo_detail->toArray();
            }
          }
        }
      }
      return $this->json();
    } else {
      if ($key == null)
        return redirect('/');
      else
        return redirect("/todo?key={$key}");
    }
  }

  public function save_name(Request $request) {
    $key = null;
    if (array_key_exists('key', $_COOKIE)) {
      $key = $_COOKIE['key'];
    }
    $name = null;
    if (!($request->input('name') == null)) {
      $name = $request->input('name');
      if (strlen(trim($name)) == 0) {
        $name = null;
      }
    }
    if ($request->ajax()) {
      if ($key == null) {
        $this->message = 'Unknown Todo';
      } else {
        if ($name == null) {
          $this->message = 'Name Cannot Be Empty';
        } else {
          $id = Redis::get("todo:key:id:{$key}");
          $todo = null;
          if ($id == null) {
            $todo = TodoModel::where('key', $key)->first();
            if ($todo == null) {
              $todo = new TodoModel();
              $todo->key = $key;
            }
            $todo->name = $name;
            $todo->save();
          } else {
            $todo = TodoModel::find($id);
            if ($todo == null) {
              $todo = TodoModel::where('key', $key)->first();
              if ($todo == null) {
                $todo = new TodoModel();
                $todo->key = $key;
              }
            }
            $todo->name = $name;
            $todo->save();
          }
          $todo_redis = Redis::get("todo:key:{$key}");
          $data = [];
          $details = [];
          if ($todo_redis == null) {
            $details = TodoDetailModel::where('todo_id', $todo->id)->get();
            if (count($details) > 0) {

            }
          } else {
            $data = json_decode($todo_redis, true);
            $details = $data['details'];
          }
          $todo = $todo->toArray();
          $data['todo'] = $todo;
          $data['details'] = $details;
          Redis::set("todo:key:id:{$key}", $todo['id']);
          Redis::set("todo:key:{$key}", json_encode($data));
          $this->success = true;
          $this->data = $todo;
        }
      }
      return $this->json();
    } else {
      if ($key == null)
        return redirect('/');
      else
        return redirect("/todo?key={$key}");
    }
  }

  private function generateTodoKey() {
    $key = str_random(42);
    $check = TodoModel::select('id')->where('key', $key)->count();
    if ($check == 1) {
      $key = $this->generateTodoKey();
    }
    return $key;
  }

}
