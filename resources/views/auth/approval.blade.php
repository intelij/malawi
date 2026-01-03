@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Waiting Approval') }}</h5>

                        {{ __('Your account is waiting approval from administrators.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
