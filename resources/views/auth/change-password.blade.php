@extends('layouts.app')

@section('title', 'Change Password - MediConnect')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>Change Your Password</h2>
        <p>For security reasons, you must change your default password before continuing.</p>
        
        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" required>
                @error('password')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>
            
            <button type="submit" class="btn btn-warning">Update Password</button>
        </form>
    </div>
</div>
@endsection
