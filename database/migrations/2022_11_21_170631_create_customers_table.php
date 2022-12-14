<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('mobile_no')->nullable();
            $table->integer('birthday_date')->nullable();
            $table->integer('month_id')->nullable();
            $table->date('anniversary_date')->nullable();
            $table->integer('state_type')->nullable();
            $table->string('gst_no')->default(0);
            $table->integer('city_id')->default(0);
            // $table->integer('employee_id')->default(0);
            $table->date('date')->nullable();
            $table->string('time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
