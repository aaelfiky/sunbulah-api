<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_translations', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("slug");
            $table->integer("preparation_time")->nullable();
            $table->integer("serves")->nullable();
            $table->integer("cooking_time")->nullable();
            $table->string("video")->nullable();
            $table->string("video_link")->nullable();
            $table->string("image_desktop")->nullable();
            $table->string("image_mobile")->nullable();
            $table->json('instructions')->nullable();
            $table->json('ingredients')->nullable();
            $table->json('notes')->nullable();
            $table->json('recipe_card')->nullable();
            $table->integer('main_product_id')->unsigned()->nullable();
            $table->foreign('main_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->bigInteger('recipe_id')->unsigned();
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
            $table->json('seo')->nullable();
            $table->string('locale')->index();
            $table->integer('locale_id')->nullable()->unsigned();
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');
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
        Schema::dropIfExists('recipe_translations');
    }
}
