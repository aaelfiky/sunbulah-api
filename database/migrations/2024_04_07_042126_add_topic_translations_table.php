<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTopicTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('topic_card')->nullable();
            $table->bigInteger('topic_id')->unsigned();
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
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
        Schema::table('topic_translations', function (Blueprint $table) {
            //
        });
    }
}
