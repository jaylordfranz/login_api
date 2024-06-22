@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-success" role="alert">
        A fresh verification link has been sent to your email address.
    </div>

    Before proceeding, please check your email for a verification link.
    If you did not receive the email,
    <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">click here to request another</button>.
    </form>
</div>
@endsection
