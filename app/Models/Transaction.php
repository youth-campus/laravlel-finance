<?php

namespace App\Models;

use App\Traits\Member;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    use Member;

    public function member() {
        return $this->belongsTo('App\Models\Member', 'member_id')->withDefault();
    }

    public function account() {
        return $this->belongsTo('App\Models\SavingsAccount', 'savings_account_id')
            ->withoutGlobalScopes()
            ->withDefault();
    }

    public function created_by() {
        return $this->belongsTo('App\Models\User', 'created_user_id')->withDefault();
    }

    public function updated_by() {
        return $this->belongsTo('App\Models\User', 'updated_user_id')->withDefault(['name' => _lang('N/A')]);
    }

    public function gateway() {
        return $this->belongsTo('App\Models\PaymentGateway', 'gateway_id')->withDefault();
    }

    public function parent_transaction() {
        return $this->belongsTo('App\Models\Transaction', 'parent_id')->withDefault();
    }

    public function child_transaction() {
        return $this->hasOne('App\Models\Transaction', 'parent_id')->withDefault();
    }

    public function getTransDateAttribute($value) {
        $date_format = get_date_format();
        $time_format = get_time_format();
        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }

    public function getCreatedAtAttribute($value) {
        $date_format = get_date_format();
        $time_format = get_time_format();
        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }

    public function getUpdatedAtAttribute($value) {
        $date_format = get_date_format();
        $time_format = get_time_format();
        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }

    public function getTransactionDetailsAttribute($value) {
        return json_decode($value);
    }
}