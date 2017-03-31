<?php

namespace OFFLINE\Bootstrapper\October\Config\Entities;

class Plugin extends AbstractEntity
{
    const DEFAULT_BRANCH = 'master';

    static protected $required = ['vendor', 'name'];

    /** @var  string */
    private $vendor;

    /** @var  string */
    private $name;

    /** @var  string The remote url of the repo. If no url, it means a marketplace plugin */
    private $remote;

    /** @var  string */
    private $branch;

    public function __construct(array $plugin)
    {
        $this->validate($plugin);

        $this->vendor = $plugin['vendor'];
        $this->name = $plugin['name'];
        $this->remote = $plugin['remote'] ?? null;
        $this->branch = $plugin['branch'] ?? null;
    }

    /**
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRemote(): string
    {
        return $this->remote;
    }

    /**
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch ?? self::DEFAULT_BRANCH;
    }

    public function hasRemote(): bool
    {
        return $this->remote !== null;
    }

    public function getVendorDir() : string
    {
        return getcwd() . DS . implode(DS, ['plugins', $this->vendor]);
    }

    public function getPluginDir() : string
    {
        return $this->getVendorDir() . DS . $this->getName();
    }

    public function __toString()
    {
        $output = sprintf('%s.%s', $this->vendor, $this->name);

        if ($this->hasRemote()) {
            $output .= sprintf(' (%s#%s)', $this->remote, $this->branch);
        }

        return $output;
    }
}