@extends('emails.layout')
@section('content')
    <div>
        <div style="background-color: #176435;padding: 10px;display: flex;gap: 1rem;align-items: center;">
            <h3  style="margin: 0; color: white;">Welcome to CPSU Grad School App</h3>
        </div>
        <div style="padding: 20px;">
            <p style="margin-top: 0;">You are now successfully registered. Here is your temporary password:</p>
            <div style="padding: 15px; font-size: 18pt; background-color: lightgray; width: auto; display: inline-block; border-radius: 3px;">
                12345678
            </div>
            <p style="margin-bottom: 0;">
                <small><i>Note: Do not share password, one-time-pin, birthday or any sensitive information.</i></small>
            </p>
        </div>
    </div>
@endsection