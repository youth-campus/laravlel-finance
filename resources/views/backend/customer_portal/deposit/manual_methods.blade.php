@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<h4 class="header-title text-center">{{ _lang('Manual Deposit Methods') }}</h4>
			</div>
			<div class="card-body">
                <div class="row justify-content-md-center">
                    @foreach($deposit_methods as $deposit_method)
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <img src="{{ asset('public/uploads/media/'.$deposit_method->image) }}" class="gateway-img"/>
                                <h5>{{ $deposit_method->name }}</h5>
                                <a href="{{ route('deposit.manual_deposit',$deposit_method->id) }}" class="btn btn-light mt-3 stretched-link">{{ _lang('Deposit Now') }}</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
			</div>
		</div>
    </div>
</div>
@endsection