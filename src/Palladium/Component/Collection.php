<?php

namespace Palladium\Component;

use Palladium\Component\Collection;
use Palladium\Component\Identifiable;

/**
 * Class for handling sets of domain entities.
 * To use it, it needs to be extended with `buildEntity()` method implemented. This method
 * must return an instance of that domain entity.
 */


abstract class Collection implements \Iterator, \ArrayAccess, \Countable
{

    abstract protected function buildEntity();

    private $pool = [];
    private $indexed = [];
    private $volatile = [];

    private $poolMap = [];

    private $position = 0;

    private $total = 0;
    private $offset = 0;
    private $limit = 10;


    /**
     * Add new domain entity, that is constructed using array as values. Each array key
     * will be attempted top match with entity's setter method and provided with
     * the respective array value. It returns the newly created entity.
     *
     * @param array $parameters
     *
     * @return Identifiable
     */
    public function addBlueprint(array $parameters)
    {
        $instance = $this->buildEntity();
        $this->populateEntity($instance, $parameters);

        $this->addEntity($instance);

        return $instance;
    }


    /** code that does the actualy population of data from the given array in blueprint */
    private function populateEntity($instance, $parameters)
    {
        foreach ((array)$parameters as $key => $value) {
            $method = 'set' . str_replace('_', '', $key);
            if (method_exists($instance, $method)) {
                $instance->{$method}($value);
            }
        }
    }


    /**
     * Method for adding already existing domain entity to the collection.
     *
     * @param Identifiable $entity
     */
    public function addEntity(Identifiable $entity)
    {
        $this->pool[] = $entity;

        $key = $entity->getId();

        if ($key === null) {
            $this->volatile[] = $entity;
            return;
        }

        $this->indexed[$key] = $entity;
        $this->poolMap[$key] = $this->retrieveLastPoolKey();
    }


    private function retrieveLastPoolKey()
    {
        end($this->pool);
        return key($this->pool);
    }


    /**
     * Method for getting an ordered list of IDs for items in the collection.
     *
     * @return array
     */
    public function getKeys()
    {
        $keys = array_keys($this->indexed);
        sort($keys);
        return $keys;
    }


    /**
     * Replaces all of the domain entities with a content of some other collection
     *
     * @param Collection $replacement
     */
    public function replaceWith(Collection $replacement)
    {
        $this->pool = [];
        $this->poolMap = [];
        $this->volatile = [];
        $this->indexed = [];

        foreach ($replacement as $entity) {
            $this->addEntity($entity);
        }

    }


    /**
     * Removes an entity from collection.
     *
     * @param Identifiable $entity
     */
    public function removeEntity(Identifiable $entity)
    {
        $key = $entity->getId();

        if ($key !== null) {
            unset($this->indexed[$key]);
            unset($this->pool[$this->poolMap[$key]]);
            unset($this->poolMap[$key]);
        }
    }


    /**
     * Method for retrieving the last added entity from the collection.
     * If collection is empty, it returns `null`
     *
     * @return Identifiable|null
     */
    public function getLastEntity()
    {
        $key = $this->retrieveLastPoolKey();
        if ($key !== null) {
            return $this->pool[$key];
        }

        return $this->buildEntity();
    }


    /**
     * Removes all od the content of the collection and resets it to pristine state.
     */
    public function purge()
    {
        $this->pool = [];
        $this->indexed = [];
        $this->volatile = [];
        $this->poolMap = [];
        $this->position = 0;
    }


    // imeplementing Countable
    public function count()
    {
        return count($this->pool);
    }


    // implementing Iterator
    public function rewind()
    {
        $this->position = 0;
    }


    public function current()
    {
        return $this->pool[$this->position];
    }


    public function key()
    {
        return $this->position;
    }


    public function next()
    {
        ++$this->position;
    }


    public function valid()
    {
        return isset($this->pool[$this->position]);
    }


    // implementing ArrayAccess
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->indexed[$value->getId()] = $value;
            return;
        }

        $this->indexed[$offset] = $value;
    }


    public function offsetExists($offset)
    {
        return isset($this->indexed[$offset]);
    }


    public function offsetUnset($offset)
    {
        unset($this->indexed[$offset]);
    }


    public function offsetGet($offset)
    {
        if (isset($this->indexed[$offset])){
            return $this->indexed[$offset];
        }

        return null;
    }


    // pagination

    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
    }


    public function getOffset()
    {
        return $this->offset;
    }


    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }


    public function getLimit()
    {
        return $this->limit;
    }


    public function setTotal($total)
    {
        $this->total = (int) $total;
    }


    public function getTotal()
    {
        return $this->total;
    }
}
