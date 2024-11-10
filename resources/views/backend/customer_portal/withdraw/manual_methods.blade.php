@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<h4 class="header-title text-center">{{ _lang('Withdraw Methods') }}</h4>
			</div>
			<div class="card-body">
                <div class="row justify-content-md-center">
                @foreach($withdraw_methods as $withdraw_method)
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <img src="{{ asset('public/uploads/media/'.$withdraw_method->image) }}" class="gateway-img"/>
                                <h5>{{ $withdraw_method->name }}</h5>
                                <a href="{{ route('withdraw.manual_withdraw',$withdraw_method->id) }}" data-title="{{ _lang('Withdraw Via').' '.$withdraw_method->name }}" class="btn btn-light mt-3 stretched-link">{{ _lang('Make Withdraw') }}</a>
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