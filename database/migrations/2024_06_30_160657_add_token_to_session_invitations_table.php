<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenToSessionInvitationsTable extends Migration
{
    public function up()
    {
        Schema::table('SessionInvitation', function (Blueprint $table) {
            $table->string('token')->unique()->after('Response_ID');
        });
    }

    public function down()
    {
        Schema::table('SessionInvitation', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
}



