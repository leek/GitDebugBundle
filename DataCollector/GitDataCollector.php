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
     * @var string
     */
    protected $gitRootDir;

    /**
     * @var array
     */
    protected $refsInfo;

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
            'git_root' => $this->getGitRootDir(),
            'branch'   => $this->_getCurrentBranch(),
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
     * @return array
     */
    public function getBranches()
    {
        $branches = array_merge($this->_getLocalBranches(), $this->_getBranches());
        $branches = array_unique($branches);
        return $branches;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $tags = array_merge($this->_getLocalTags(), $this->_getTags());
        $tags = array_unique($tags);
        return $tags;
    }

    /**
     * @return string
     */
    public function getGitRootDir()
    {
        if ($this->gitRootDir === null) {
            if (isset($this->data['git_root']) && !empty($this->data['git_root'])) {
                $this->gitRootDir = $this->data['git_root'];
            } else {
                $this->gitRootDir = $this->kernel->getRootDir() . '/../.git';
            }
        }
        return $this->gitRootDir;
    }

    /**
     * @return string
     */
    protected function _getCurrentBranch()
    {
        $headFile = $this->getGitRootDir() . '/HEAD';
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

        return trim($branch);
    }

    /**
     * @return array
     */
    protected function _getBranches()
    {
        $refs     = $this->_getRefsInfo();
        $branches = array();

        foreach ($refs as $ref) {
            if (strpos($ref, 'tags') !== false) {
                continue;
            }
            $ref = str_replace('heads/', '', $ref);
            $branches[] = $ref;
        }

        return $branches;
    }

    /**
     * @return array
     */
    public function _getLocalBranches($ref = null)
    {
        $branches = array();
        $base     = $this->getGitRootDir() . "/refs/heads";
        $path     = $base . (!empty($ref) ? "/{$ref}" : '');
        $files    = glob("{$path}/*");

        foreach ($files as $file) {
            $parts = explode('refs/heads/', $file);
            $name  = $parts[count($parts) - 1];
            if (is_dir($file)) {
                $branches = array_merge($branches, $this->_getLocalBranches($name));
            } else {
                $branches[] = trim($name);
            }
        }

        return $branches;
    }

    /**
     * @return array
     */
    protected function _getTags()
    {
        $refs = $this->_getRefsInfo();
        $tags = array();

        foreach ($refs as $ref) {
            if (strpos($ref, 'tags') === false) {
                continue;
            }
            $ref = str_replace('tags/', '', $ref);
            $tags[] = $ref;
        }

        return $tags;
    }

    /**
     * @return array
     */
    public function _getLocalTags($ref = null)
    {
        $tags  = array();
        $base  = $this->getGitRootDir() . "/refs/tags";
        $path  = $base . (!empty($ref) ? "/{$ref}" : '');
        $files = glob("{$path}/*");

        foreach ($files as $file) {
            $parts = explode('refs/tags/', $file);
            $name  = $parts[count($parts) - 1];
            if (is_dir($file)) {
                $tags = array_merge($tags, $this->_getLocalTags($name));
            } else {
                $tags[] = trim($name);
            }
        }

        return $tags;
    }

    /**
     * @return array
     */
    protected function _getRefsInfo()
    {
        if (!empty($this->refsInfo)) {
            return $this->refsInfo;
        }

        $refsFile = $this->getGitRootDir() . '/packed-refs';
        $refs     = array();

        if (file_exists($refsFile)) {
            $fileHandle = fopen($refsFile, 'r');
            while ($line = fgets($fileHandle)) {
                $parsed = explode('refs/', $line);
                $refs[] = trim($parsed[1]);
            }
            fclose($fileHandle);
        }

        return $refs;
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