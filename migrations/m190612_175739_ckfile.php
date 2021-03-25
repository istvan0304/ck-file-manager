<?php

use yii\db\Migration;

/**
 * Class m190612_175739_ckfile
 */
class m190612_175739_ckfile extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM';
        $this->createTable('ckfile', [
            'id' => $this->primaryKey()->comment('Id'),
            'file_name' => $this->string(255)->notNull(),
            'orig_name' => $this->string(255)->notNull(),
            'file_hash' => $this->string(255)->notNull(),
            'mime' => $this->string(255),
            'extension' => $this->string(32),
            'size' => $this->integer(11),
            'type' => $this->tinyInteger(1)->notNull(),
            'create_time' => $this->timestamp()->comment('Létrehozás dátuma'),
            'update_time' => $this->timestamp()->comment('Módosítás dátuma'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('ckimage');
    }
}
