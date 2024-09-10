<?php

namespace TotalPoll\Decorators;
! defined( 'ABSPATH' ) && exit();



/**
 * Class StructuredData
 * @package TotalPoll\Decorators
 */
class StructuredData {
	/**
	 * StructuredData constructor.
	 */
	public function __construct() {
		add_filter( 'totalpoll/filters/template/attributes/question/container', [ $this, 'question' ] );
		add_filter( 'totalpoll/filters/template/attributes/question/content', [ $this, 'content' ] );
		add_filter( 'totalpoll/filters/template/attributes/choice/container', [ $this, 'choice' ] );
		add_filter( 'totalpoll/filters/template/attributes/choice/label', [ $this, 'content' ] );
		add_filter( 'totalpoll/filters/template/content/choice', [ $this, 'votes' ], 10, 2 );
	}

	/**
	 * Question schema.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function question( $attributes ) {
		
		$attributes['itemscope'] = '';
		$attributes['itemtype']  = 'http://schema.org/Question';

		

		return $attributes;
	}

	/**
	 * Choice schema.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function choice( $attributes ) {
		
		$attributes['itemscope'] = '';
		$attributes['itemtype']  = 'http://schema.org/Answer';

		

		return $attributes;
	}

	/**
	 * Content schema.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function content( $attributes ) {
		
		$attributes['itemprop'] = 'text';

		

		return $attributes;
	}

	/**
	 * Votes schema.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function votes( $content, $choice ) {
		
		$content .= TotalPoll( 'html.element',
			[
				'meta',
				[ 'itemprop' => 'upvoteCount', 'content' => $choice['votes'] ],
			]
		);
		

		return $content;
	}
}
