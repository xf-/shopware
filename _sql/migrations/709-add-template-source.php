<?php

class Migrations_Migration709 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE `s_core_templates`
ADD `source` varchar(255) NULL;
EOD;
        $this->addSql($sql);
    }
}
