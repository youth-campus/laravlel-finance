@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-12">
		<form method="post" action="{{ route('interest_calculation.interest_posting') }}">
			@csrf
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<span class="panel-title">{{ _lang('Interest Review') }}</span>
					<button class="btn btn-primary btn-xs float-right" type="submit">{{ _lang('POST INTEREST') }}</button>
				</div>

				<input type="hidden" name="account_type_id" value="{{ $account_type_id }}"/>
				<input type="hidden" name="start_date" value="{{ $start_date }}"/>
				<input type="hidden" name="end_date" value="{{ $end_date }}"/>
				<input type="hidden" name="posting_date" value="{{ $posting_date }}"/>

				@php  $date_format = get_date_format(); @endphp

				<div class="card-body">		
					<div class="alert alert-info">
						<span>{{ _lang('Amount greater than zero will post to user account') }}</span>
					</div> 
					<table class="table table-bordered data-table">
						<thead>
						<tr>
							<th>{{ _lang('User') }}</th>
							<th>{{ _lang('Account') }}</th>
							<th>{{ _lang('Type') }}</th>
							<th>{{ _lang('Interest') }}</th>
							<th>{{ _lang('Date Range') }}</th>
						</tr>
						</thead>
						<tbody>
							@foreach($users as $user)
							<tr>
								<td>{{ $user['member']->name }}</td>
								<td>{{ $user['account']->account_number }}</td>
								<td>{{ $user['account']->savings_type->name }}</td>
								<td>{{ decimalPlace($user['interest'], currency($user['account']->savings_type->currency->name)) }}</td>
								<td>
									{{ date($date_format, strtotime($start_date)) . ' - ' . date($date_format, strtotime($end_date))  }}
									@if($user['interest'] > 0)
									<input type="hidden" name="member_id[]" value="{{ $user['member_id'] }}"/>
									<input type="hidden" name="interest[]" value="{{ $user['interest'] }}"/>
									<input type="hidden" name="account_id[]" value="{{ $user['account']->id }}"/>
									@endif
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
@endsection


