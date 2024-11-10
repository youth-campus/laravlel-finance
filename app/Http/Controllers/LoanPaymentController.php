<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanRepayment;
use App\Models\Transaction;
use App\Utilities\LoanCalculator as Calculator;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class LoanPaymentController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('backend.loan_payment.list');
    }

    public function get_table_data() {
        $loanpayments = LoanPayment::select('loan_payments.*')
            ->with('loan')
            ->orderBy("loan_payments.id", "desc");

        return Datatables::eloquent($loanpayments)
            ->editColumn('repayment_amount', function ($loanpayment) {
                return decimalPlace($loanpayment->repayment_amount - $loanpayment->interest, currency($loanpayment->loan->currency->name));
            })
            ->addColumn('total_amount', function ($loanpayment) {
                return decimalPlace($loanpayment->total_amount, currency($loanpayment->loan->currency->name));
            })
            ->addColumn('action', function ($loanpayment) {
                return '<form action="' . route('loan_payments.destroy', $loanpayment['id']) . '" class="text-center" method="post">'
                . '<a href="' . route('loan_payments.show', $loanpayment['id']) . '" class="btn btn-primary btn-xs"><i class="ti-eye"></i>&nbsp;' . _lang('View') . '</a>&nbsp;'
                . '<a href="' . route('loans.show', $loanpayment['loan_id']) . '" class="btn btn-success btn-xs"><i class="ti-file"></i>&nbsp;' . _lang('Loan Details') . '</a>&nbsp;'
                . csrf_field()
                . '<input name="_method" type="hidden" value="DELETE">'
                . '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-trash"></i>&nbsp;' . _lang('Delete') . '</button>'
                    . '</form>';
            })
            ->setRowId(function ($loanpayment) {
                return "row_" . $loanpayment->id;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $alert_col = 'col-lg-8 offset-lg-2';
        return view('backend.loan_payment.create', compact('alert_col'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'loan_id'          => 'required',
            'paid_at'          => 'required',
            'late_penalties'   => 'nullable|numeric',
            'principle_amount' => 'required|numeric',
            'interest'         => 'required|numeric',
            'due_amount_of'    => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('loan_payments.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();
        $repayment = LoanRepayment::where('loan_id', $request->loan_id)
            ->where('status', 0)
            ->orderBy('id', 'asc')
            ->first();

        if ($repayment->id != $request->due_amount_of) {
            return back()->with('error', _lang('Invalid Operation !'));
        }

        $existing_amount = $repayment->principal_amount;

        $loan = Loan::find($request->loan_id);

        $loanpayment                   = new LoanPayment();
        $loanpayment->loan_id          = $request->loan_id;
        $loanpayment->paid_at          = $request->paid_at;
        $loanpayment->late_penalties   = $request->late_penalties ?? 0; //it's optionals
        $loanpayment->interest         = $repayment->interest;
        $loanpayment->repayment_amount = $request->principle_amount + $repayment->interest;
        $loanpayment->total_amount     = $loanpayment->repayment_amount + $request->late_penalties;
        $loanpayment->remarks          = $request->remarks;
        $loanpayment->repayment_id     = $repayment->id;
        $loanpayment->member_id        = $loan->borrower_id;

        $loanpayment->save();

        //Update Loan Balance
        $loan->total_paid = $loan->total_paid + $request->principle_amount;
        if ($loan->total_paid >= $loan->applied_amount) {
            $loan->status = 2;
        }
        $loan->save();

        //Update Repayment Status
        $repayment->principal_amount = $request->principle_amount;
        $repayment->amount_to_pay    = $request->principle_amount + $repayment->interest;
        $repayment->balance          = $loan->total_payable - ($loan->total_paid + $loan->payments->sum('interest'));
        $repayment->status           = 1;
        $repayment->save();

        //Delete All Upcomming Repayment schedule if payment is done
        if ($loan->total_paid >= $loan->applied_amount) {
            LoanRepayment::where('loan_id', $request->loan_id)->where('status', 0)->delete();
        } else {
            //Update Upcomming Repayment Schedule
            if ($request->principle_amount != $existing_amount) {
                $upCommingRepayments = LoanRepayment::where('loan_id', $request->loan_id)
                    ->where('status', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                if($upCommingRepayments->isEmpty()){
                    return back()->with('error', _lang('You must pay the full repayment because this is your last repayment schedule'));
                }

                // Create Loan Repayments
                $calculator = new Calculator(
                    $loan->applied_amount - $loan->total_paid,
                    $upCommingRepayments[0]->repayment_date,
                    $loan->loan_product->interest_rate,
                    $upCommingRepayments->count(),
                    $loan->loan_product->term_period,
                    $loan->late_payment_penalties
                );

                if ($loan->loan_product->interest_type == 'flat_rate') {
                    $repayments = $calculator->get_flat_rate();
                } else if ($loan->loan_product->interest_type == 'fixed_rate') {
                    $repayments = $calculator->get_fixed_rate();
                } else if ($loan->loan_product->interest_type == 'mortgage') {
                    $repayments = $calculator->get_mortgage();
                } else if ($loan->loan_product->interest_type == 'one_time') {
                    $repayments = $calculator->get_one_time();
                } else if ($loan->loan_product->interest_type == 'reducing_amount') {
                    $repayments = $calculator->get_reducing_amount();
                }

                $index = 0;
                foreach ($repayments as $newRepayment) {
                    $upCommingRepayment                   = $upCommingRepayments[$index];
                    $upCommingRepayment->amount_to_pay    = $newRepayment['amount_to_pay'];
                    $upCommingRepayment->penalty          = $newRepayment['penalty'];
                    $upCommingRepayment->principal_amount = $newRepayment['principle_amount'];
                    $upCommingRepayment->interest         = $newRepayment['interest'];
                    $upCommingRepayment->balance          = $newRepayment['balance'];
                    $upCommingRepayment->save();
                    $index++;
                }
            }
        }

        DB::commit();

        return redirect()->route('loan_payments.index')->with('success', _lang('Loan Payment Made Sucessfully'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $loanpayment = LoanPayment::find($id);
        if (!$request->ajax()) {
            return view('backend.loan_payment.view', compact('loanpayment', 'id'));
        } else {
            return view('backend.loan_payment.modal.view', compact('loanpayment', 'id'));
        }

    }

    public function get_repayment_by_loan_id($loan_id) {
        $repayments = LoanRepayment::where('loan_id', $loan_id)
            ->where('status', 0)
            ->orderBy('id', 'asc')
            ->limit(1)
            ->get();
        echo json_encode($repayments);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        DB::beginTransaction();

        $loanpayment = LoanPayment::find($id);

        $transaction = Transaction::find($loanpayment->transaction_id);
        if ($transaction) {
            $transaction->delete();
        }

        //Update Balance
        $repayment         = LoanRepayment::find($loanpayment->repayment_id);
        $repayment->status = 0;
        $repayment->save();

        $loan             = Loan::find($loanpayment->loan_id);
        $loan->total_paid = $loan->total_paid - $repayment->principal_amount;
        $loan->save();

        $loanpayment->delete();

        DB::commit();

        return back()->with('success', _lang('Deleted Sucessfully'));
    }
}