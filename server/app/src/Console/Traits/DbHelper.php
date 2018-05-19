<?php

namespace App\Console\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

trait DbHelper
{
    /**
     * @var string Table name where migrations info is kept
     */
    private $migrationsTable = 'migrations';

    /**
     * @var string Table name where seeds info is kept
     */
    private $seedsTable = 'seeds';

    /**
     * Return class name by file basename
     * @param string $baseName
     *
     * @return string
     */
    private function getClassName($baseName)
    {
        $filenameParts = explode('_', $baseName);
        $class         = '';

        array_shift($filenameParts);

        foreach ($filenameParts as $key => $filenamePart) {
            $class .= ucfirst($filenamePart);
        }

        return $class;
    }

    /**
     * @param string $tableName
     */
    private function safeCreateTable($tableName)
    {
        if (!Capsule::schema()->hasTable($tableName)) {
            Capsule::schema()->create($tableName, function($table) {
                $table->string('version');
                $table->timestamp('apply_time')->useCurrent();
                $table->primary('version');
            });
        }
    }

    /**
     * @param string $name
     * @param string $table
     * @return bool
     */
    private function isRowExist($name, $table)
    {
        $item = Capsule::table($table)->where('version', $name)->first();
        return !is_null($item);
    }

    /**
     * @param string $name
     * @param string $table
     */
    private function insertRow($name, $table)
    {
        Capsule::table($table)->insert([
            'version' => $name,
        ]);
    }

    /**
     * @param string $name
     * @param string $table
     */
    private function deleteRow($name, $table)
    {
        Capsule::table($table)->where([
            'version' => $name,
        ])->delete();
    }

    /**
     * Run list of commands in files
     *
     * @param string          $path
     * @param OutputInterface $output
     * @param string          $tableName
     * @param string          $method
     *
     * @return void
     */
    private function runAction($path, OutputInterface $output, $tableName, $method)
    {
        if (!is_dir($path) || !is_readable($path)) {
            throw new \RunTimeException(sprintf('Path `%s` is not good', $path));
        }

        $output->writeln([
            '<info>Run command</info>',
            sprintf('Ensure table `%s` presence', $tableName)
        ]);

        try {
            $this->safeCreateTable($tableName);
        } catch (\Exception $e) {
            $output->writeln([
                sprintf('Can\'t ensure table `%s` presence. Please verify DB connection params and presence of database named', $tableName),
                sprintf('Error: `%s`', $e->getMessage()),
            ]);
        }

        $finder = new Finder();
        $finder->files()->name('*.php')->in($path);

        foreach ($finder as $file) {
            $baseName = $file->getBasename('.php');
            $class    = $this->getClassName($baseName);

            if ($this->isRowExist($baseName, $tableName)) {
                $output->writeln([sprintf('`%s` - already exists.', $baseName)]);
                continue;
            }

            require_once($file);

            $obj = new $class();
            $obj->$method();

            $this->insertRow($baseName, $tableName);
            $output->writeln([sprintf('`%s` - done.', $baseName)]);
        }

        $output->writeln(['<info>Completed.</info>']);

        return;
    }
}
