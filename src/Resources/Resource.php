<?php

declare(strict_types=1);

namespace Foodticket\Deliveroo\Resources;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

class Resource implements Arrayable
{
    protected array $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->getAttribute($key);
        }

        throw new Exception('Property '.$key.' does not exist on '.get_called_class());
    }

    /**
     * @param  string  $key
     */
    public function __isset($key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Get an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttribute($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Get attributes for the resource.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }
}
