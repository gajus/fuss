<?php
namespace Gajus\Puss;

class User {
	private
		/**
		 * @var Gajus\Puss\App
		 */
		$app;
	
	/**
	 * @param Gajus\Puss\App $app
	 */
	public function __construct (\Gajus\Puss\App $app) {
		$this->app = $app;
	}
}