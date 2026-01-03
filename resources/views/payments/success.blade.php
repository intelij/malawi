@extends('layouts.app')

@section('content')
<div class="container text-center">
    <h2 class="text-success">Payment Successful 🎉</h2>
    <p>Your payment has been processed successfully.</p>
    {{-- <p><strong>Payment Intent ID:</strong> {{ $paymentIntent }}</p> --}}

    <a href="{{ url('/') }}" class="btn btn-primary mt-3">Back to Home</a>
</div>
@endsection
