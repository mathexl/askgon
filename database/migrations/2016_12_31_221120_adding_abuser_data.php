<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingAbuserData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sections', function (Blueprint $table) {
            //
            $table->Integer("abuser")->default(0);
            $table->Integer("abuser_time")->default(0);
            $table->Integer("abuser_count")->default(0);
            $table->mediumText("abuser_hitlist");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sections', function (Blueprint $table) {
            //
        });
    }
}
