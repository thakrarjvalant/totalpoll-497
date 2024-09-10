<?php

namespace TotalPollVendors\League\Container\Inflector;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\League\Container\ImmutableContainerAwareInterface;

interface InflectorAggregateInterface extends ImmutableContainerAwareInterface
{
    /**
     * Add an inflector to the aggregate.
     *
     * @param  string   $type
     * @param  callable $callback
     * @return \TotalPollVendors\League\Container\Inflector\Inflector
     */
    public function add($type, callable $callback = null);

    /**
     * Applies all inflectors to an object.
     *
     * @param  object $object
     * @return object
     */
    public function inflect($object);
}
