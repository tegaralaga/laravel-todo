<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTodoDetail extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('todo_detail', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->bigInteger('todo_id');
          $table->string('item');
          $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));;
      });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
      Schema::dropIfExists('todo_detail');
  }
}
