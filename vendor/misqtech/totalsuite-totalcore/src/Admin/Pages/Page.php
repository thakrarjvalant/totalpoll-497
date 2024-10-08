<?php

namespace TotalPollVendors\TotalCore\Admin\Pages;
! defined( 'ABSPATH' ) && exit();


use TotalPollVendors\TotalCore\Contracts\Admin\Page as PageContract;
use TotalPollVendors\TotalCore\Contracts\Foundation\Environment as EnvironmentContract;
use TotalPollVendors\TotalCore\Contracts\Http\Request as RequestContract;

/**
 * Class Page
 * @package TotalPollVendors\TotalCore\Admin\Pages
 */
abstract class Page implements PageContract {
	/**
	 * @var RequestContract $request
	 */
	protected $request;
	/**
	 * @var EnvironmentContract $env
	 */
	protected $env;

	/**
	 * Page constructor.
	 *
	 * @param RequestContract     $request
	 * @param EnvironmentContract $env
	 */
	public function __construct( RequestContract $request, EnvironmentContract $env ) {
		$this->request = $request;
		$this->env     = $env;
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );
	}

	/**
	 * Enqueue assets.
	 *
	 * @return mixed
	 */
	public function assets() {
		// Implementation of assets method
		return;
	}

	/**
	 * Save page content or settings.
	 *
	 * @return mixed
	 */
	public function save() {
		// Implementation of save method
		return;
	}

	/**
	 * To string.
	 *
	 * @return string
	 */
	final public function __toString() {
		return (string) $this->render();
	}

	/**
	 * Render page.
	 *
	 * @return mixed
	 */
	abstract public function render();
}