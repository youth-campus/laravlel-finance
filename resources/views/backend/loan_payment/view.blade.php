@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<span class="panel-title">{{ _lang('Loan Repayment Details') }}</span>
			</div>
			
			<div class="card-body">
				<table class="table table-bordered">
					<tr>
						<td>{{ _lang('Loan ID') }}</td>
						<td><a href="{{ route('loans.show', $loanpayment->loan->id) }}" target="_blank">{{ $loanpayment->loan->loan_id }}</a></td>
					</tr>
					@if($loanpayment->transaction_id != NULL)
						<tr><td>{{ _lang('Transaction') }}</td><td><a target="_blank" href="{{ route('transactions.show', $loanpayment->transaction_id) }}">{{ _lang('View Transaction Details') }}</a></td></tr>
					@endif
					<tr><td>{{ _lang('Payment Date') }}</td><td>{{ $loanpayment->paid_at }}</td></tr>
					<tr><td>{{ _lang('Principal Amount') }}</td><td>{{ decimalPlace($loanpayment->repayment_amount - $loanpayment->interest, currency($loanpayment->loan->currency->name)) }}</td></tr>
					<tr><td>{{ _lang('Interest') }}</td><td>{{ decimalPlace($loanpayment->interest, currency($loanpayment->loan->currency->name)) }}</td></tr>
					<tr><td>{{ _lang('Late Penalties') }}</td><td>{{ decimalPlace($loanpayment->late_penalties, currency($loanpayment->loan->currency->name)) }}</td></tr>
					<tr><td>{{ _lang('Total Amount') }}</td><td>{{ decimalPlace($loanpayment->total_amount, currency($loanpayment->loan->currency->name)) }}</td></tr>
					<tr><td>{{ _lang('Remarks') }}</td><td>{{ $loanpayment->remarks }}</td></tr>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection


