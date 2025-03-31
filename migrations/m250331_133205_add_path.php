<?php

namespace istvan0304\ckfilemanager\migrations;

use yii\db\Migration;

class m250331_133205_add_path extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ckfile', 'path', $this->string(255)->after('file_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('ckfile', 'path');
    }
}
