<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTccFailTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tcc_fail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('iid', 80)->comment('事务ID')->unique();
            $table->longText('options')->comment('操作代码序列化');
            $table->longText('exception')->comment('异常信息');
            $table->dateTime('created_at')->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcc_fail');
    }
}
