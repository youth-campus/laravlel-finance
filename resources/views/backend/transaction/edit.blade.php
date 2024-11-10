@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<h4 class="header-title">{{ _lang('Update Transaction') }}</h4>
			</div>
			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{ route('transactions.update', $id) }}" enctype="multipart/form-data">
					{{ csrf_field()}}
					<input name="_method" type="hidden" value="PATCH">
					<div class="row">
						<div class="col-lg-8">
							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Date') }}</label>
								<div class="col-xl-9">
									<input type="text" class="form-control datetimepicker" name="trans_date" value="{{ $transaction->getRawOriginal('trans_date') }}"
										required>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Member') }}</label>
								<div class="col-xl-9">
									<select class="form-control auto-select select2" data-selected="{{ $transaction->member_id }}" name="member_id" id="member_id" required>
										<option value="">{{ _lang('Select One') }}</option>
										@foreach(\App\Models\Member::all() as $member)
											<option value="{{ $member->id }}">{{ $member->first_name.' '.$member->last_name }} ({{ $member->member_no }})</option>
										@endforeach
									</select>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Account Number') }}</label>
								<div class="col-xl-9">
									<select class="form-control select2 auto-select" data-selected="{{ $transaction->savings_account_id }}" name="savings_account_id" id="savings_account_id" required>
										@foreach(\App\Models\SavingsAccount::where('member_id', $transaction->member_id)->get() as $account)
										<option value="{{ $account->id }}">{{ $account->account_number }} ({{ $account->savings_type->name.' - '.$account->savings_type->currency->name }})</option>
										@endforeach
									</select>
								</div>
							</div>


							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Amount') }}</label>
								<div class="col-xl-9">
									<input type="text" class="form-control float-field" name="amount" value="{{ $transaction->amount }}" required>
								</div>
							</div>


							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Debit/Credit') }}</label>
								<div class="col-xl-9">
									<select class="form-control auto-select" data-selected="{{ $transaction->dr_cr }}" name="dr_cr" id="dr_cr" disabled>
										<option value="">{{ _lang('Select One') }}</option>
										<option value="dr">{{ _lang('Debit') }}</option>
										<option value="cr">{{ _lang('Credit') }}</option>
									</select>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Transaction Types') }}</label>
								<div class="col-xl-9">
									<select class="form-control auto-select" data-selected="{{ $transaction->type }}" name="type" id="transaction_type" disabled>
										<option value="{{ $transaction->type }}">{{ ucwords(str_replace("_"," ",$transaction->type)) }}</option>
									</select>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Status') }}</label>
								<div class="col-xl-9">
									<select class="form-control auto-select" data-selected="{{ $transaction->status }}" name="status" required>
										<option value="">{{ _lang('Select One') }}</option>
										<option value="0">{{ _lang('Pending') }}</option>
										<option value="1">{{ _lang('Cancelled') }}</option>
										<option value="2">{{ _lang('Completed') }}</option>
									</select>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-xl-3 col-form-label">{{ _lang('Description') }}</label>
								<div class="col-xl-9">
									<textarea class="form-control" name="description" required>{{ $transaction->description }}</textarea>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-xl-9 offset-xl-3">
									<button type="submit" class="btn btn-primary"><i class="ti-check-box"></i>&nbsp;{{ _lang('Update') }}</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
<script>
(function ($) {

	$(document).on('change','#member_id',function(){
		var member_id = $(this).val();
		if(member_id != ''){
			$.ajax({
				url: "{{ url('admin/savings_accounts/get_account_by_member_id/') }}/" + member_id,
				success: function(data){
					var json = JSON.parse(JSON.stringify(data));
					$("#savings_account_id").html('');
					$.each(json['accounts'], function(i, account) {
						$("#savings_account_id").append(`<option value="${account.id}">${account.account_number} (${account.savings_type.name} - ${account.savings_type.currency.name})</option>`);
					});

				}
			});
		}
	});

	$(document).on('change','#dr_cr',function(){
		var dr_cr = $(this).val();
		if(dr_cr != ''){
			$.ajax({
				url: "{{ url('admin/transaction_categories/get_category_by_type/') }}/" + dr_cr,
				success: function(data){
					var json = JSON.parse(JSON.stringify(data));
					$("#transaction_type").html('');
					$.each(json, function(i, category) {
						$("#transaction_type").append(`<option value="${category.value}">${category.name}</option>`);
					});

				}
			});
		}
	});

})(jQuery);
</script>
@endsection



