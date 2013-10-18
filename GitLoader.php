<?php

class GitLoader
{
    private $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }

    private function getCode($path)
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
