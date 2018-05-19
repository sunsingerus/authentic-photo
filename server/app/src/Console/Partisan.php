<?php

namespace App\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Slim\Container;

/**
 * Class Partisan
 *
 * @property Container $container
 *
 * @package App\Console
 */
class Partisan extends Application
{
    /**
     * Namespace for commands
     */
    const COMMANDS_NAMESPACE = '\App\Console\Commands';

    /**
     * @var Container
     */
    public $container;

    /**
     * @var string
     */
    private $logo = '
    ____             __  _                
   / __ \____ ______/ /_(_)________ _____ 
  / /_/ / __ `/ ___/ __/ / ___/ __ `/ __ \
 / ____/ /_/ / /  / /_/ (__  ) /_/ / / / /
/_/    \__,_/_/   \__/_/____/\__,_/_/ /_/ 
                                          ';

    /**
     * Partisan constructor.
     *
     * @param Container $container
     * @param string    $name      The name of the application
     * @param string    $version   The version of the application
     * @throws \Exception
     */
    public function __construct(Container $container, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->container = $container;
        $this->registerCommands();
    }

    /**
     * Register commands
     */
    public function registerCommands()
    {
        $finder = new Finder();
        $finder->files()->name('*Command.php')->in(COMMANDS_PATH);

        foreach ($finder as $file) {
            $ns = self::COMMANDS_NAMESPACE;
            /* @var \Symfony\Component\Finder\SplFileInfo $file*/
            $relativePath = $file->getRelativePath();
            if ($relativePath) {
                $ns .= '\\'.strtr($relativePath, '/', '\\');
            }

            $r = new \ReflectionClass($ns.'\\'.$file->getBasename('.php'));
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
                $this->add($r->newInstance());
            }
        }
    }

    /**
     * Returns the long version of the application.
     *
     * @return string The long application version
     *
     * @api
     */
    public function getLongVersion()
    {
        if ('UNKNOWN' !== $this->getName() && 'UNKNOWN' !== $this->getVersion()) {
            return sprintf('<info>%s</info> version <comment>%s</comment>', $this->getName(), $this->getVersion());
        }

        return '<info>'.$this->logo.'</info>';
    }
}
