<?php declare(strict_types=1);

namespace DmitriiKoziuk\yii2Pages\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `dk_pages`.
 */
class m190122_143041_create_dk_pages_table extends Migration
{
    private $pagesTable = '{{%dk_pages}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->pagesTable, [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(0),
            'meta_title' => $this->string(255)->notNull(),
            'meta_description' => $this->string(255)->notNull(),
            'content' => $this->text()->null()->defaultValue(NULL),
        ]);
        $this->createIndex(
            'idx_dk_pages_name',
            $this->pagesTable,
            'name',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->pagesTable);
    }
}
