@extends('layouts.app')

@section('content')
<br>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">New Class Creation</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/newclass') }}">
                        {{ csrf_field() }}

                        <div >
                            <label for="name" class="col-md-4 control-label">Class Name</label>

                            <div class="col-md-6">
                                <input id="email" class="form-control" name="name" required autofocus>


                            </div>
                        </div>
                        <br>                        <br>

                        <div >
                            <label for="school" class="col-md-4 control-label">School Name</label>

                            <div class="col-md-6">
                                <input id="school" class="form-control" name="school" required>


                            </div>
                        </div>
                        <br>
                        <br>
                        <br>


                            <input type="submit" value="Create Class" style="float:right;">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
