@extends('layouts.app')

@section('content')
<br>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard
                  <a class="newclass" href="/newclass">Create New Class</a>
                </div>

                <div class="panel-body">
                    You are logged in!
                </div>
            </div>
        </div>
    </div>
    @if(!empty($classes))
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Classes You Manage
                </div>
                <div class="panel-body">
                    @foreach($classes as $class)
                    <a href="/class/{{$class->id}}">
                    <div class="classblock">
                      <h1>{{$class->name}}</h1>
                      <h2>{{$class->school}}</h2>
                    </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif


    @if(!empty($joined))
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Classes You Joined
                </div>
                <div class="panel-body">
                    @foreach($joined as $class)
                    <a href="/class/{{$class->id}}">
                    <div class="classblock">
                      <h1>{{$class->name}}</h1>
                      <h2>{{$class->school}}</h2>
                    </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
