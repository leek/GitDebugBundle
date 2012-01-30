<?php

namespace Leek\GitDebugBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GitDataCollector extends DataCollector
{
    /**
     * @var \AppKernel
     */
    protected $kernel;

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'branch' => $this->_getGitBranch()
        );
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->data['branch'];
    }

    /**
     * @return string
     */
    protected function _getGitBranch()
    {
        $headFile = $this->kernel->getRootDir() . '/../.git/HEAD';
        $branch   = null;

        if (file_exists($headFile)) {
            // Read first line
            $fileHandle = fopen($headFile, 'r');
            $firstLine = fgets($fileHandle);
            fclose($fileHandle);

            // Determine branch name
            $parsed = explode('/', $firstLine);
            unset($parsed[0]);
            unset($parsed[1]);
            $branch = implode('/', $parsed);
        }

        return $branch;
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName()
    {
        return 'leek_git_debug';
    }

    /**
     * @param \AppKernel $kernel
     */
    public function setKernel(\AppKernel $kernel = null)
    {
        $this->kernel = $kernel;
    }
}