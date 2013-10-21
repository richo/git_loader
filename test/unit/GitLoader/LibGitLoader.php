<?php

define(TREE, "populate with a tree");

class LibGitLoader_Loads_Test extends PHPUnit_Framework_TestCase
{
    public function testLoadsTreeFromGit()
    {
        $loader = new LibGitLoader(TREE);
        $loader->register();

        $thing = new FooClass();
        $this->assertEquals($thing->butts(), 42);
    }
}
