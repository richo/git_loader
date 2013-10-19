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

    public function getCode($path)
    {
        // Solve the path of the object
        // TODO: Consider storing a treecache
        $tree = $this->tree;
        // Specify multiple paths, eventually
        $els = explode("/", $path);
        $_tree = $this->repo->lookup($tree);

        while(true) {
            $node = array_shift($els);
            if (empty($els)) {
                $blob = $_tree->getEntryByName($node . ".php");
                if ($blob === false) {
                    return null;
                }
                return $this->repo->lookup($blob->oid);
            } else {
                $_tree = $_tree->getSubtree($node);
                if ($_tree === false) {
                    // Not in our tree
                    return null;
                }
                continue;
            }
        }
        return null;
    }
}
