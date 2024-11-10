<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Models\EmailSMSTemplate;
use App\Utilities\Installer;
use Database\Seeders\UpdateMemberRequestAcceptSeeder;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller {

    public function update_migration() {
        $app_version = '1.7.1';
        
        Artisan::call('migrate', ['--force' => true]);

        //Update Seeder
        $email_template = EmailSMSTemplate::where('slug', 'MEMBER_REQUEST_ACCEPTED')->first();
        if (!$email_template) {
            Artisan::call('db:seed', ['--class' => UpdateMemberRequestAcceptSeeder::class, '--force' => true]);
        }

        //Update Version Number
        Installer::updateEnv([
            'APP_VERSION' => $app_version,
        ]);
        update_option('APP_VERSION', $app_version);
        echo "Migration Updated Sucessfully";
    }
}
