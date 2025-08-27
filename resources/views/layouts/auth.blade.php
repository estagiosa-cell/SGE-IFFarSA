@extends('layouts.app')

@section('content')
  <div class="container-fluid">
    <div class="row">
        <div class="col-lg-2 p-0">
            <x-sidebar />
        </div>
      <div class="col-lg-10">
        @yield('main-content')
      </div>
    </div>
  </div>
@endsection
