<?php

namespace TotalPollVendors\TotalCore\Filesystem;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\TotalCore\Contracts\Filesystem\Base as FilesystemContract;

/**
 * Class Local
 * @package TotalPollVendors\TotalCore\Filesystem
 */
class Local extends \WP_Filesystem_Direct implements FilesystemContract {

}