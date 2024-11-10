<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Branch {

    public static function bootBranch() {
        static::addGlobalScope('branch_id', function (Builder $builder) {
            if (auth()->user()->user_type == 'user') {
                return $builder->where('branch_id', auth()->user()->branch_id);
            }else {
                if (session('branch_id') != '') {
                    $branch_id = session('branch_id') == 'default' ? null : session('branch_id');
                    return $builder->where('branch_id', $branch_id);
                }
            }
        });

    }

}