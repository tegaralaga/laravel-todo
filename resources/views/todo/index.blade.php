<!DOCTYPE html>
<html>
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>{{ $title }}</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <style>
          form#change-todo-name {margin-top: 20px;}
        </style>
    </head>
    <body>
      <div class="container">
        <div class="row">
          <div class="col-1">
            <h1><a href="{{ url('/') }}">Todo</a></h1>
          </div>
            <div class="col-11">
              <h1 id="todo-name">{{ $todo_title }}</h1>
            </div>
        </div>
          <div class="row">
            <div class="col-6">
              <h6 id="todo-created-at">Created At : {{ $todo->created_at }}</h6>
            </div>
            <div class="col-6">
              <h6 id="todo-updated-at">Updated At : {{ $todo->updated_at }}</h6>
            </div>
          </div>
        <div class="row">
          <div class="col-3">
            <form id="add-todo" action="{{url()->current()}}/item/add">
              <div class="form-group">
                <label for="input-todo">Todo</label>
                <input name="todo" type="text" class="form-control" id="input-todo" aria-describedby="help-todo" placeholder="Todo">
                <small id="help-todo" class="form-text text-muted">Write your Todo down!</small>
              </div>
              <button type="submit" class="btn btn-primary">Add Todo</button>
            </form>
            <form id="change-todo-name" action="{{url()->current()}}/name/save">
              <div class="form-group">
                <label for="input-name">Name</label>
                <input name="name" value="{{ $todo->name }}" type="text" class="form-control" id="input-name" aria-describedby="help-name" placeholder="Name">
                <small id="help-name" class="form-text text-muted">Todo's Name</small>
              </div>
              <button type="submit" class="btn btn-primary">Save Name</button>
            </form>
          </div>
          <div class="col-9">
            <table class="table table-striped" id="todo-list">
              <thead>
                <tr>
                  <th scope="col">Item</th>
                  <th scope="col">Created At</th>
                  <th scope="col">Delete</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </body>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script type="text/javascript">
    $(document).ready(function(){
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      var request = $.ajax({
          url: window.location.href,
          timeout:30000,
          datatype:'json',
          type:'GET',
      });
      request.always(function(){
      });
      request.done(function(response){
          if(response.success)
          {
            $html = '';
            $.each(response.data, function( i, val ) {
              $html += '<tr id="item-' + val.id + '">';
              $html += '<td>'+ val.item +'</td>';
              $html += '<td>'+ val.created_at +'</td>';
              $html += '<td><button class="btn btn-danger delete-item" id="delete-' + val.id + '">DELETE</button></td>';
              $html += '</tr>';
            });
            $('table#todo-list tbody').append($html);
          }
          else
          {
            alert('ERROR : ' + response.message);
          }
      });
      request.fail(function(jqXHR,textStatus){
          if(textStatus=='timeout'){
          } else {
          }
      });
      $('#add-todo').submit(function(e){
        e.preventDefault();
        $name = $('#input-todo').val();
        if ($name.trim().length > 0) {
          var request = $.ajax({
              url: $(this).attr('action'),
              timeout:30000,
              datatype:'json',
              type:'POST',
              data:$(this).serialize(),
          });
          request.always(function(){
          });
          request.done(function(response){
              if(response.success)
              {
                $html = '<tr id="item-' + response.data.id + '">';
                $html += '<td>'+ response.data.item +'</td>';
                $html += '<td>'+ response.data.created_at +'</td>';
                $html += '<td><button class="btn btn-danger delete-item" id="delete-' + response.data.id + '">DELETE</button></td>';
                $html += '</tr>';
                $('h6#todo-updated-at').html('Updated At : ' + response.data.created_at);
                $('table#todo-list tbody').append($html);
                $('#input-todo').val('').focus();
              }
              else
              {
                alert('ERROR : ' + response.message);
              }
          });
          request.fail(function(jqXHR,textStatus){
              if(textStatus=='timeout'){
              } else {
              }
          });
        }
      });
      $(document).on('click', 'button.delete-item', function(){
        $item_id = $(this).attr('id');
        $item_id = $item_id.replace('delete-','');
        var request = $.ajax({
            url: location.protocol + '//' + location.host + location.pathname + '/item/delete',
            timeout:30000,
            datatype:'json',
            type:'POST',
            data: {id : $item_id},
        });
        request.always(function(){
        });
        request.done(function(response){
            if(response.success)
            {
              $tr = $('tr#item-' + $item_id);
              $tr.remove();
            }
            else
            {
              alert('ERROR : ' + response.message);
            }
        });
        request.fail(function(jqXHR,textStatus){
            if(textStatus=='timeout'){
            } else {
            }
        });
      });
      $('#change-todo-name').submit(function(e){
        e.preventDefault();
        $name = $('#input-name').val();
        if ($name.trim().length > 0) {
          var request = $.ajax({
              url: $(this).attr('action'),
              timeout:30000,
              datatype:'json',
              type:'POST',
              data:$(this).serialize(),
          });
          request.always(function(){
          });
          request.done(function(response){
              if(response.success)
              {
                $('h1#todo-name').html(response.data.name);
                $('h6#todo-created-at').html('Created At : ' + response.data.created_at);
                $('h6#todo-updated-at').html('Updated At : ' + response.data.updated_at);
                document.title = 'Todo | ' + response.data.name;
              }
              else
              {
                alert('ERROR : ' + response.message);
              }
          });
          request.fail(function(jqXHR,textStatus){
              if(textStatus=='timeout'){
              } else {
              }
          });
        }
      });
    });
    </script>
</html>
