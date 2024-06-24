<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMagnetLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magnet_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('link', 655);
            $table->integer('time_to_keep_minutes')->default(0);
            $table->smallInteger('magnet_link_status_id')->default(0);
            $table->smallInteger('vod_type_id')->default(1);
            $table->timestamps();
        });

        Schema::create('magnet_link_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('magnet_link_id')->constrained('magnet_links','id')->cascadeOnDelete();
            $table->string('filename', 655);
            $table->integer('fileindex');
            $table->foreignUuid('stream_id')->nullable()->constrained('streams','id')->nullOnDelete();
            $table->string('folder', 255)->nullable();
            $table->integer('time_to_keep_minutes')->default(0);
            $table->timestamps();
        });

        DB::statement('CREATE INDEX magnet_link_files_stream_id_index
            ON public.magnet_link_files USING btree
            (stream_id ASC NULLS LAST)
        ');

        DB::statement('CREATE INDEX magnet_link_files_magnet_link_id_index
            ON public.magnet_link_files USING btree
            (magnet_link_id ASC NULLS LAST)
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magnet_link_files');
        Schema::dropIfExists('magnet_links');
    }
}
