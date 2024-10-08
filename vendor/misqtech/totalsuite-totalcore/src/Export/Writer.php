<?php

namespace TotalPollVendors\TotalCore\Export;
! defined( 'ABSPATH' ) && exit();


/**
 * Class Writer
 * @package TotalPollVendors\TotalCore\Export
 */
abstract class Writer {
	/**
	 * @var bool $includeColumnHeaders
	 */
	public $includeColumnHeaders = false;

	/**
	 * Get content.
	 *
	 * @param array $columns
	 * @param array $data
	 *
	 * @return mixed
	 */
	abstract public function getContent( array $columns, array $data );

	/**
	 * Get content type.
	 *
	 * @return string
	 */
	abstract public function getContentType();

	/**
	 * File extension.
	 *
	 * @return string
	 */
	abstract public function getDefaultExtension();
}
