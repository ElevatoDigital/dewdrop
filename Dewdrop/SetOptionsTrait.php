<?php

namespace Dewdrop;

trait SetOptionsTrait
{
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);

            if (!method_exists($this, $setter)) {
                throw new Exception("No setter method available for {$name} option.");
            } else {
                $this->$setter($value);
            }
        }

        return $this;
    }
}
