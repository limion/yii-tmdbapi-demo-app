<?php

class m160323_132934_create_movie_table extends CDbMigration
{
    public function up()
    {
        $this->createTable('tbl_movie', array(
            'id' => 'pk',
            'title' => 'string NOT NULL',
            'original_title' => 'string NOT NULL',
            'release_date' => 'datetime NOT NULL',
            'runtime' => 'int NOT NULL',
            'overview' => 'text NOT NULL',
            'genres' => 'string NOT NULL',
            'poster_path' => 'string NOT NULL',
            'rating' => 'int NOT NULL default 0',
        ));
    }
 
    public function down()
    {
        $this->dropTable('tbl_movie');
    }

}