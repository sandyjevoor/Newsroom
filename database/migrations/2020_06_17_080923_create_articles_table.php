<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('ext_article_id');
            $table->string('title');
            $table->string('headline');
            $table->string('kicker');
            $table->string('caption');
            $table->string('tags');
            $table->text('body');
            $table->string('declaration');
            $table->string('location');
            $table->string('language');
            $table->string('district');
            $table->string('state');
            $table->string('reporter_name');
            $table->string('reporter_id');
            $table->string('publish_status');
            $table->string('ingest_status');
            $table->string('ingest_id');
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
        Schema::dropIfExists('articles');
    }
}
