<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMauticContactIdToCompanyInfo extends Migration
{
    public function up()
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->string('mautic_contact_id')->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('company_info', function (Blueprint $table) {
            $table->dropColumn('mautic_contact_id');
        });
    }
}