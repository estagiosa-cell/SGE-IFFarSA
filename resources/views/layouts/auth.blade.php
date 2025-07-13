@extends('layouts.app')

@section('content')
  <div class="container-fluid">
    <div class="row">
      <x-sidebar />
      <div class="col-lg-10">
        @yield('main-content')
      </div>
    </div>
  </div>
@endsection
