<?php

class GitLoader
{
    private $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }

    public function getCode($path)
    {
        $cmd = sprintf("git show %s:classes/%s.php 2>/dev/null",
            escapeshellarg($this->tree),
            escapeshellarg($path)
        );
        return shell_exec($cmd);
    }

    public function loadClass($class)
    {
        // Proof of concept implementation. Shelling out to get the code, not exactly ideal.
        $class = str_replace("_", "/", $class);
        $class = str_replace("\\", "/", $class);
        if (($code = $this->getCode($class)) === null) {
            // Ignore hopefully someone else can find it
            return null;
        }

        // Ensure that the open tag doesn't set fire to planet earth
        eval("?>" . $code);
    }

    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'), true, true);
    }
}

class LibGitLoader extends GitLoader
{
    private $repo;
    public function __construct($tree)
    {
        $this->tree = $tree;
        $this->repo = new Git2\Repository(".");
    }

    /* exposes require'like functionality, except that it operates inside the given tree
     */
    public function require_path($path)
    {
        if (($code = $this->getCode($path)) !== null) {
            eval("?>" . $code);
            return true;
        } else {
            return false;
        }
    }

    public function getCode($path)
    {
        // Solve the path of the object
        // TODO: Consider storing a treecache
        $els = explode("/", $path);

        $tree = $this->repo->lookup($this->tree);
        $node = array_shift($els);

        do {
            $tree = $tree->getSubtree($node);
            if ($tree === false) {
                // Not in our tree
                return null;
            }
            $node = array_shift($els);
        } while (!empty($els));

        $blob = $tree->getEntryByName($node . ".php");
        if ($blob === false) {
            return null;
        }

        return $this->repo->lookup($blob->oid);
    }
}
