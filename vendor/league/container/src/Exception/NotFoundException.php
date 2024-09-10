<?php

namespace TotalPollVendors\League\Container\Exception;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
