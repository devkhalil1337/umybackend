<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname'); 
            $table->string('lastname');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->int('package_id')->nullable();
            $table->string('date_of_expiry')->nullable(); 
            $table->string('verification_code')->nullable(); 
            $table->enum('is_verified', ['No', 'Yes']);
            $table->string('gender')->nullable();
            $table->string('time_zone_country')->nullable(); 
            $table->string('time_zone_time')->nullable();
            $table->string('DOB')->nullable();
            $table->string('address')->nullable(); 
            $table->string('company')->nullable();
            $table->string('job_title')->nullable(); 
            $table->string('website')->nullable();
            $table->string('facebook_url')->nullable(); 
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable(); 
            $table->string('youtube_url')->nullable();
            $table->longtext('profile_image')->nullable(); 
            $table->string('refer_code')->nullable();
            $table->string('reffered_from')->nullable(); 
            $table->string('country')->nullable();
            $table->string('payment_id')->nullable(); 
            $table->string('payment_method')->nullable(); 
            $table->enum('user_type', ['user', 'support', 'admin']);
            $table->enum('login', ['false', 'true']);
            $table->string('business_type')->nullable(); 
            $table->string('sub_category')->nullable();
            $table->text('admin_text')->nullable();
            $table->string('balance_transaction')->nullable(); 
            $table->string('balance_transaction_type')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
