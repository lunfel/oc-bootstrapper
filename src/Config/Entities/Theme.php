<?php


namespace OFFLINE\Bootstrapper\October\Config\Entities;

use OFFLINE\Bootstrapper\October\Util\Slug;

class Theme extends AbstractEntity
{
    static protected $required = ['name'];

    /** @var string */
    private $name;

    /** @var string */
    private $remote;

    public function __construct(array $theme)
    {
        $this->validate($theme);

        $this->name = $theme['name'];
        $this->remote = $theme['remote'] ?? null;

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

    public function hasRemote()
    {
        return $this->remote !== null;
    }

    public function getThemeDir() : string
    {
        return getcwd() . DS . implode(DS, ['themes', Slug::slugify($this->name)]);
    }

    public function __toString()
    {
        $output = $this->name;

        if ($this->hasRemote()) {
            $output .= sprintf(' (%s)', $this->remote);
        }

        return $output;
    }
}