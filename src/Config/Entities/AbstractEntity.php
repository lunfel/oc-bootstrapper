<?php


namespace OFFLINE\Bootstrapper\October\Config\Entities;

abstract class AbstractEntity
{
    protected static $required = [];

    protected function validate($params)
    {
        foreach (self::$required as $field) {
            if (! isset($params[$field])) {
                throw new \Exception(sprintf('Configuration requires the "%s" configuration key', $field));
            }
        }
    }
}