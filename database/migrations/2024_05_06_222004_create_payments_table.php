<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('image_name');
            $table->string('payment_type')->nullable()->default('contribution');
            $table->unsignedBigInteger('user_id');
            $table->boolean('verified')->nullable()->default(0);
            $table->unsignedBigInteger('authorised_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }

};
