<?php

namespace OFFLINE\Bootstrapper\October\Installer;


use GitElephant\Repository;
use OFFLINE\Bootstrapper\October\Config\Entities\Plugin;
use OFFLINE\Bootstrapper\October\Util\Gitignore;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class PluginInstaller
 * @package OFFLINE\Bootstrapper\October\BaseInstaller
 */
class PluginInstaller extends BaseInstaller
{
    /**
     * Install a plugin via git or artisan.
     *
     * @param Gitignore $gitignore
     *
     * @return bool
     */
    public function install()
    {
        try {
            $config = $this->config->plugins;
        } catch (\RuntimeException $e) {
            $this->writeInfo('- Nothing to install');

            // No plugin set
            return false;
        }

        $isBare     = (bool)$this->config->git['bareRepo'];
        $exceptions = [];

        foreach ($config as $pluginConfig) {

            $plugin = new Plugin($pluginConfig);

            $this->writeInfo(' - ' . $plugin);

            $this->mkdir($plugin->getVendorDir());
            $this->mkdir($plugin->getPluginDir());

            if (! $plugin->hasRemote()) {
                $this->handleMarketPlacePlugin($plugin);
                continue;
            }

            $this->handleGithubPlugin($plugin);

            if ($isBare) {
                $this->gitignore->addPlugin($plugin);
            }

            // $this->cleanup($plugin->getPluginDir());
        }

        return true;
    }

    /**
     * Parse the Vendor, Plugin and Remote values out of the
     * given plugin declaration.
     *
     * @param $plugin
     *
     * @return mixed
     */
    protected function parse($plugin)
    {
        // Vendor.Plugin (Remote)
        preg_match("/([^\.]+)\.([^ #]+)(?: ?\(([^\#)]+)(?:#([^\)]+)?)?)?/", $plugin, $matches);

        array_shift($matches);

        if (count($matches) < 3) {
            $matches[2] = false;
        }

        if (count($matches) < 4) {
            $matches[3] = false;
        }

        $matches[0] = strtolower($matches[0]);
        $matches[1] = strtolower($matches[1]);

        return $matches;
    }

    /**
     * Create the plugin's vendor directory.
     *
     * @param $vendor
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function createVendorDir($vendor)
    {
        $pluginDir = getcwd() . DS . implode(DS, ['plugins', $vendor]);

        return $this->mkdir($pluginDir);
    }

    /**
     * Installs a plugin via artisan command.
     *
     * @param $vendor
     * @param $plugin
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function installViaArtisan(Plugin $plugin)
    {
        $exitCode = (new Process("php artisan plugin:install {$plugin->getVendor()}.{$plugin->getName()}"))->run();

        if ($exitCode !== $this::EXIT_CODE_OK) {
            throw new RuntimeException(
                sprintf('Error while installing plugin %s via artisan. Is your database set up correctly?',
                    $plugin
                )
            );
        }
    }

    private function handleMarketPlacePlugin(Plugin $plugin)
    {
        if ( ! $this->isEmpty($plugin->getPluginDir())) {
            $this->writeComment('   -> ' . sprintf('Plugin "%s" already installed. Skipping.', $plugin));
            return;
        }

        $this->installViaArtisan($plugin);
    }

    private function handleGithubPlugin(Plugin $plugin)
    {
        $repo = Repository::open($plugin->getPluginDir());
        try {
            if ($this->isEmpty($plugin->getPluginDir())) {
                $repo->cloneFrom($plugin->getRemote(), $plugin->getPluginDir());
                $this->checkout($plugin->getBranch(), $repo);
            } else {
                $this->checkout($plugin->getBranch(), $repo);
                $repo->pull('origin', $plugin->getBranch());
            }
        } catch (RuntimeException $e) {
            $this->writeError(' - ' . 'Error while cloning plugin repo: ' . $e->getMessage());
        }
    }

    /**
     * @param $branch
     * @param $repo
     */
    private function checkout($branch, $repo)
    {
        if ($branch !== false) {
            $this->writeComment('   -> ' . sprintf('Checkout "%s" ...', $branch));
            $repo->checkout($branch);
        }
    }
}