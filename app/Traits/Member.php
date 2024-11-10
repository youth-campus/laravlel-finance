<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Member {

    public static function bootMember() {
        static::addGlobalScope('member_id', function (Builder $builder) {
            if (auth()->user()->user_type == 'user') {
                return $builder->whereHas('member', function (Builder $query) {
                    $query->where('branch_id', auth()->user()->branch_id);
                });
            }else {
                if (session('branch_id') != '') {
                    $branch_id = session('branch_id') == 'default' ? null : session('branch_id');
                    return $builder->whereHas('member', function (Builder $query) use($branch_id) {
                        $query->where('branch_id', $branch_id);
                    });
                }
            }
        });
    }

}