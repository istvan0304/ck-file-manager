<?php

use yii\db\Migration;

/**
 * Class m190612_175739_ckimage
 */
class m190612_175739_ckimage extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM';
        $this->createTable('ckimage', [
            'id' => $this->primaryKey()->comment('Id'),
            'file_name' => $this->string(255)->notNull(),
            'orig_name' => $this->string(255)->notNull(),
            'file_hash' => $this->string(255)->notNull(),
            'mime' => $this->string(255),
            'extension' => $this->string(32),
            'size' => $this->integer(11),
            'cr_date' => $this->dateTime()->comment('Létrehozás dátuma'),
            'mod_date' => $this->dateTime()->comment('Módosítás dátuma'),
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
