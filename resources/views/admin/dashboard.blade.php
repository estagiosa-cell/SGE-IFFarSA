@extends('layouts.auth')

@section('title', 'Dashboard')

@section('main-content')
    <div class="container-fluid mt-4 mx-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Dashboard</h2>
            <form method="POST" action="{{ route('admin.sync.data') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat me-2"></i>Sincronizar Dados
                </button>
            </form>
        </div>
    </div>
@endsection
