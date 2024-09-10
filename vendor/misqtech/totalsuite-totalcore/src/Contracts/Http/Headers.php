<?php

namespace TotalPollVendors\TotalCore\Contracts\Http;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface Headers
 * @package TotalPollVendors\TotalCore\Contracts\Http
 */
interface Headers extends \ArrayAccess, Arrayable, \JsonSerializable {

}