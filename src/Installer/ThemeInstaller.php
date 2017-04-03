<?php

namespace OFFLINE\Bootstrapper\October\Installer;


use GitElephant\Repository;
use OFFLINE\Bootstrapper\October\Config\Entities\Theme;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class ThemeInstaller
 * @package OFFLINE\Bootstrapper\October\BaseInstaller
 */
class ThemeInstaller extends BaseInstaller
{
    /**
     * Install a theme via git or artisan.
     *
     * @throws LogicException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function install()
    {
        if (! isset($this->config->cms['theme'])) {
            return false;
        }

        $theme = new Theme($this->config->cms['theme']);

        if (! $theme->hasRemote()) {
            return $this->installViaArtisan($theme);
        }

        $this->mkdir($theme->getThemeDir());

        $repo = Repository::open($theme->getThemeDir());
        try {
            if ($this->isEmpty($theme->getThemeDir())) {
                $repo->cloneFrom($theme->getRemote(), $theme->getThemeDir());
            } else {
                $repo->pull('origin', 'master');
            }
        } catch (RuntimeException $e) {
            throw new RuntimeException('Error while cloning theme repo: ' . $e->getMessage());
        }

        // $this->cleanup($theme->getThemeDir());

        return true;
    }

    /**
     * Parse the theme's name and remote path out of the
     * given theme declaration.
     *
     * @param $theme
     *
     * @return mixed
     */
    protected function parse($theme)
    {
        // Theme (Remote)
        preg_match("/([^ ]+)(?: ?\(([^\)]+))?/", $theme, $matches);

        array_shift($matches);

        if (count($matches) < 2) {
            $matches[1] = false;
        }

        return $matches;
    }

    /**
     * Installs a theme via artisan command.
     *
     * @param $theme
     *
     * @return bool
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function installViaArtisan(Theme $theme)
    {
        $exitCode = (new Process("php artisan theme:install {$theme->getName()}"))->run();

        if ($exitCode !== $this::EXIT_CODE_OK) {
            throw new RuntimeException(sprintf('Error while installing theme "%s" via artisan.', $theme->getName()));
        }

        return true;
    }
}