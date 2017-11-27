<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string("title")->comment("文章标题");
            $table->text("body")->comment("文章内容"); 
            $table->integer("user_id")->unsigned()->comment("作者id");
            $table->integer("last_comment_user_id")->unsigned()->default(0)->index()->comment("最后评论用户id");
            $table->integer("category_id")->unsigned()->default(0)->index()->comment("所属类别");
            $table->integer("view_count")->unsigned()->default(0)->index()->comment("查看此次数");
            $table->integer("comments_count")->unsigned()->default(0)->comment("评论数");
            $table->integer("likes_count")->unsigned()->default(0)->comment("点赞数");
            $table->enum("close_comment", [0, 1])->default(0)->index()->comment("是否关闭评论");
            $table->enum("is_public", [0, 1])->default(1)->index()->comment("是否公开");
            $table->enum("is_top", [0, 1])->default(0)->index()->comment("是否置顶");
            $table->enum("is_excellent", [0, 1])->default(0)->index()->comment("是否为精华");
            $table->timestamp('last_comment_time')->comment('最后评论时间');
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
