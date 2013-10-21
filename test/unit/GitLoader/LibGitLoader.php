<?php

// classes/ on branch testclasses
define(TREE, "eceafd14a0f3240fbe24b58486741d7e718627eb");

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
