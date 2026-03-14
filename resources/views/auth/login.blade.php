@extends('layouts.app')

@section('title', 'Login - MediConnect')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>Login to MediConnect</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</div>
@endsection
