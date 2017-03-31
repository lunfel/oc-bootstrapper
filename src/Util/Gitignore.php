<?php

namespace OFFLINE\Bootstrapper\October\Util;


use OFFLINE\Bootstrapper\October\Config\Entities\Plugin;

class Gitignore
{
    /**
     * @var array
     */
    private $contents;
    private $file;

    public function __construct($file)
    {
        $this->file     = $file;
        $this->contents = file($file);
    }

    public function write()
    {
        file_put_contents($this->file, $this->contents);
    }

    public function add($line)
    {
        if ($this->hasLine($line)) {
            return;
        }

        $this->contents[] = $line . PHP_EOL;
    }

    public function hasLine($line)
    {
        foreach ($this->contents as $entry) {
            if (strtolower($line) === strtolower($entry)) {
                return true;
            }
        }

        return false;
    }

    protected function newLine()
    {
        $this->contents[] = PHP_EOL . PHP_EOL;
    }

    public function addPlugin(Plugin $plugin)
    {
        $header = sprintf("# %s.%s", $plugin->getVendor(), $plugin->getName());
        if ($this->hasLine($header)) {
            return;
        }

        $this->newLine();
        $this->add($header);
        $this->add('!plugins/' . $plugin->getVendor());
        $this->add('!plugins/' . $plugin->getVendor() . '/' . $plugin->getName());
        $this->add('!plugins/' . $plugin->getVendor() . '/' . $plugin->getName() . '/**/*');
    }


}