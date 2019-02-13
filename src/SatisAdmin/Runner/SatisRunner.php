<?php

namespace SatisAdmin\Runner;

use Monolog\Logger;
use SatisAdmin\Model\ModelManager;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class SatisRunner implements RunnerInterface
{
    /**
     * @var ModelManager
     */
    protected $manager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * @var string
     */
    protected $binDir;
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $phpBinaryPath;

    /**
     * @param ModelManager $manager
     * @param Logger       $logger
     * @param string       $outputDir
     * @param string       $binDir
     * @param string       $cacheDir
     * @param string       $phpBinaryPath
     */
    public function __construct(ModelManager $manager, Logger $logger, $outputDir, $binDir, $cacheDir, $phpBinaryPath)
    {
        $this->manager   = $manager;
        $this->logger    = $logger;
        $this->outputDir = $outputDir;
        $this->binDir    = $binDir;
        $this->cacheDir  = $cacheDir;
        $this->phpBinaryPath = $phpBinaryPath;
    }

    public function run()
    {
        $configFile = @tempnam($this->cacheDir . '/satis', 'satis-admin');
        file_put_contents($configFile, $this->manager->getJson());
        $process = ProcessBuilder::create(
            [
                $this->phpBinaryPath,
                $this->binDir.'/satis',
                'build',
                $configFile,
                $this->outputDir
            ]
        )
            ->setTimeout(null)
            ->addEnvironmentVariables(['HOME' => $this->cacheDir])
            ->getProcess();

        $this->logger->addInfo('Building config...', ['command-line' => $process->getCommandLine()]);
        if (0 === $process->run()) {
            unlink($configFile);
            $this->logger->addInfo('Config built.');
        } else {
            $this->logger->addError(
                'Config not build',
                [
                    'stdout' => $process->getOutput(),
                    'stderr' => $process->getErrorOutput()
                ]
            );
        }
    }
}
