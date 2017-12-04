<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('用户id');
            $table->text('content')->comment('评论内容');
            // 保存文章或者视频的 ID 值
            $table->unsignedInteger('commentable_id');
            // 保存所属模型的类名
            $table->string('commentable_type');
            $table->unsignedInteger('parent_id')->nullable()->comment('上一级评论id');
            $table->smallInteger('level')->default(1)->comment('评论层级');
            $table->unsignedInteger('floor')->default(0)->comment('楼数');
            $table->unsignedInteger('children_count')->default(0)->comment('子评论数量');
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
        Schema::dropIfExists('comments');
    }
}
