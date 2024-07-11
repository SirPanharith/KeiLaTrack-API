<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateResponseIdDefaultValueInSessionInvitationsTable extends Migration
{
    public function up()
    {
        Schema::table('SessionInvitation', function (Blueprint $table) {
            $table->unsignedBigInteger('Response_ID')->default(2)->change();
        });
    }

    public function down()
    {
        Schema::table('SessionInvitation', function (Blueprint $table) {
            $table->unsignedBigInteger('Response_ID')->default(null)->change();
        });
    }
}
