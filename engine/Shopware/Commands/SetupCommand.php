<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Commands;

use Shopware\Components\Migrations\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SetupCommand extends ShopwareCommand
{
    private $validSteps = [
        'drop',
        'create',
        'clear',
        'setup',
        'importDemodata',
        'setupShop',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:setup');

        $this->addOption(
            'steps',
            null,
            InputOption::VALUE_REQUIRED,
            sprintf("Valid steps: %s.", implode(', ', $this->validSteps))
        );

        $this->addOption(
            'host',
            null,
            InputOption::VALUE_OPTIONAL
        );

        $this->addOption(
            'path',
            null,
            InputOption::VALUE_OPTIONAL
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbConfig = $this->getContainer()->getParameter('shopware.db');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $connection = $this->createConnection($dbConfig);
        $database = new \Shopware\Components\Install\Database($connection);

        if (!$input->getOption('steps')) {
            echo "Parameter --steps not given\n";
            exit(1);
        }

        $steps = $input->getOption('steps');

        $steps = explode(',', $steps);

        array_walk($steps, function ($step) {
            if (!in_array($step, $this->validSteps)) {
                echo sprintf("Unknown install step (%s). Valid steps: %s\n", $step, implode(', ', $this->validSteps));
                exit(1);
            }
        });

        if (in_array('setupShop', $steps)) {
            $host = $input->getOption('host');
            $path = $input->getOption('path');

            if (!$host) {
                //                echo "Parameter --host not given";
//                exit(1);
                $host = '';
            }


            $path = empty($path) ? $path : '';
            if ($path === '/') {
                $path = '';
            }

            if (!empty($path)) {
                $path = trim($path, '/');
                $path = '/'.$path;
            }
        }

        while ($step = array_shift($steps)) {
            switch ($step) {
                case "drop":
                    echo "Drop database\n";
                    $database->dropDatabase($dbConfig['dbname']);
                    break;

                case "create":
                    echo "Create database\n";
                    $database->createDatabase($dbConfig['dbname']);
                    break;

                case "clear":
                    echo "Clear database\n";
                    $database->emptyDatabase($dbConfig['dbname']);
                    break;

                case "setup":
                    echo "Setup database\n";
                    $database->importFile($dbConfig['dbname'], $rootDir . '/_sql/install/latest.sql');

                    $migrationManger = new Manager($connection, $rootDir . '/_sql/migrations');
                    $migrationManger->run();
                    break;

                case "importDemodata":
                    echo "Import demodata\n";
                    $database->importFile($dbConfig['dbname'], $rootDir . '/_sql/demo/latest.sql');
                    break;

                case "setupShop":
                    echo "Setup Shop\n";
                    $database->setUpShop($host, $path, $dbConfig['dbname']);
                    break;

                default:
                    echo sprintf("Unknown install step (%s). Valid steps: %s\n", $step, implode(', ', $this->validSteps));
                    exit(1);
            }
        }
    }

    /**
     * @param $dbConfig
     * @return string
     */
    private function buildConnectionString($dbConfig)
    {
        if (!isset($dbConfig['host']) || empty($dbConfig['host'])) {
            $dbConfig['host'] = 'localhost';
        }

        $connectionSettings = array(
            'host=' . $dbConfig['host'],
        );

        if (!empty($dbConfig['socket'])) {
            $connectionSettings[] = 'unix_socket=' . $dbConfig['socket'];
        }


        if (!empty($dbConfig['socket'])) {
            $connectionSettings[] = 'unix_socket=' . $dbConfig['socket'];
        }

        if (!empty($dbConfig['charset'])) {
            $connectionSettings[] = 'charset=' . $dbConfig['charset'];
        }

        $connectionString = implode(';', $connectionSettings);

        return $connectionString;
    }

    /**
     * @param array $dbConfig
     * @return \PDO
     */
    private function createConnection(array $dbConfig)
    {
        $password = isset($dbConfig['password']) ? $dbConfig['password'] : '';
        $connectionString = $this->buildConnectionString($dbConfig);

        try {
            $conn = new \PDO(
                'mysql:' . $connectionString,
                $dbConfig['username'],
                $password
            );

            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            // Reset sql_mode "STRICT_TRANS_TABLES" that will be default in MySQL 5.6
            $conn->exec('SET @@session.sql_mode = ""');
        } catch (\PDOException $e) {
            echo 'Could not connect to database: ' . $e->getMessage();
            exit(1);
        }

        return $conn;
    }
}
