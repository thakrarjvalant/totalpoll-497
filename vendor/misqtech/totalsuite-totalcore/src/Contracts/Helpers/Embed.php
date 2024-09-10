<?php

namespace TotalPollVendors\TotalCore\Contracts\Helpers;
! defined( 'ABSPATH' ) && exit();



interface Embed {

	public function getProviderName( $url );

	public function getProviderThumbnail( $url );

	public function getProviderHtml( $url );

	public function getProvider( $url );

}