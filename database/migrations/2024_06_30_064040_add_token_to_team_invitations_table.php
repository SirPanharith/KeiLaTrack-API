<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenToTeamInvitationsTable extends Migration
{
    public function up()
    {
        Schema::table('TeamInvitation', function (Blueprint $table) {
            $table->string('token')->unique()->after('PlayerInfo_ID');
        });
    }

    public function down()
    {
        Schema::table('TeamInvitation', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
}



