<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('gradeId')->index();
            $table->string('classId')->index();
            $table->string('name');
            $table->enum('gender', ['男', '女']);
            $table->date('birthday')->nullable();
            $table->string('eduid')->nullable()->unique();
        });
        // Add two array columns
        DB::statement('ALTER TABLE students ADD COLUMN name_index varchar(255)[]');
        DB::statement('ALTER TABLE students ADD COLUMN pinyin varchar(255)[]');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
