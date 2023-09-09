<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductAndCategoriesNeededColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->index('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('strapi_slug')->nullable();
            $table->index('strapi_slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('slug');
            $table->dropColumn('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('slug');
            $table->dropColumn('slug');
        });
    }
}
