<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1;

use Pimple;
use Dewdrop\Zf1\DewdropOptions\ValuePath;

class DewdropOptions
{
    private $options = [];

    public function __construct(array $zfOptions)
    {
        if (isset($zfOptions['dewdrop']) && is_array($zfOptions['dewdrop'])) {
            $this->options = $zfOptions['dewdrop'];
        }
    }

    public function getDebug()
    {
        return isset($this->options['debug']) && $this->options['debug'];
    }

    public function addPimpleResources(Pimple $pimple)
    {
        $values    = $this->findAllScalarOptionValues($this->options);
        $resources = [];

        /* @var $value ValuePath */
        foreach ($values as $value) {
            if (!array_key_exists($value->getPath(), $resources)) {
                $resources[$value->getPath()] = [];
            }

            $resources[$value->getPath()][$value->getKey()]  = $value->getValue();

            /**
             * When we have a non-numeric key, add full path with key as additional resource to support simple strings
             * vs always having to assume that we're dealing with arrays.
             */
            if (!is_numeric($value->getKey())) {
                $resources[$value->getPath() . '.' . $value->getKey()] = $value->getValue();
            }
        }

        foreach ($resources as $name => $value) {
            $pimple[$name] = $value;
        }
    }

    private function findAllScalarOptionValues(array $options, $path = '')
    {
        $values = [];

        foreach ($options as $key => $value) {
            if (!is_array($value)) {
                $values[] = new ValuePath($path, $key, $value);
            } else {
                $subPath = $path . ($path ? '.' : '') . $key;

                foreach ($this->findAllScalarOptionValues($value, $subPath) as $valuePath) {
                    $values[] = $valuePath;
                }
            }
        }

        return $values;
    }
}
