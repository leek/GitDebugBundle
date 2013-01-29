<?php

namespace Leek\GitDebugBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GitDataCollector extends DataCollector
{
    /**
     * @var string
     */
    private $gitRootDir;

    /**
     * @var array
     */
    private $refsInfo;

    /**
     * Constructor
     *
     * @param string $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->gitRootDir = realpath($kernelRootDir . '/../.git');
    }

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
            'git_root' => $this->gitRootDir,
            'branch'   => $this->getCurrentBranch(),
        );
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->getCurrentBranch();
    }

    /**
     * @return array
     */
    public function getBranches()
    {
        $branches = array_merge($this->getLocalBranches(), $this->getRefsBranches());
        $branches = array_unique($branches);
        return $branches;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $tags = array_merge($this->getLocalTags(), $this->getRefsTags());
        $tags = array_unique($tags);
        return $tags;
    }

    /**
     * @return string
     */
    protected function getCurrentBranch()
    {
        $headFile = $this->data["git_root"] . '/HEAD';
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
    protected function getRefsBranches()
    {
        $refs     = $this->getRefsInfo();
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
    protected function getLocalBranches($ref = null)
    {
        $branches = array();
        $base     = $this->data["git_root"] . '/refs/heads';
        $path     = $base . (!empty($ref) ? "/{$ref}" : '');
        $files    = glob("{$path}/*");

        foreach ($files as $file) {
            $parts = explode('refs/heads/', $file);
            $name  = $parts[count($parts) - 1];
            if (is_dir($file)) {
                $branches = array_merge($branches, $this->getLocalBranches($name));
            } else {
                $branches[] = trim($name);
            }
        }

        return $branches;
    }

    /**
     * @return array
     */
    protected function getRefsTags()
    {
        $refs = $this->getRefsInfo();
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
    protected function getLocalTags($ref = null)
    {
        $tags  = array();
        $base  = $this->data["git_root"] . '/refs/tags';
        $path  = $base . (!empty($ref) ? "/{$ref}" : '');
        $files = glob("{$path}/*");

        foreach ($files as $file) {
            $parts = explode('refs/tags/', $file);
            $name  = $parts[count($parts) - 1];
            if (is_dir($file)) {
                $tags = array_merge($tags, $this->getLocalTags($name));
            } else {
                $tags[] = trim($name);
            }
        }

        return $tags;
    }

    /**
     * @return array
     */
    protected function getRefsInfo()
    {
        if (!empty($this->refsInfo)) {
            return $this->refsInfo;
        }

        $refsFile = $this->data["git_root"] . '/packed-refs';
        $refs     = array();

        if (file_exists($refsFile)) {
            $fileHandle = fopen($refsFile, 'r');
            while ($line = fgets($fileHandle)) {
                $parsed = explode('refs/', $line);
                if (isset($parsed[1])) {
                    $refs[] = trim($parsed[1]);
                }
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