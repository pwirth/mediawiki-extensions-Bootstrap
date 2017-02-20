<?php

namespace Bootstrap\Tests\Hooks;

use Bootstrap\Hooks\SetupAfterCache;

/**
 * File holding the SetupAfterCacheTest class
 *
 * @copyright (C) 2013-2017, Stephan Gambke
 * @license       http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension Bootstrap.
 * The Bootstrap extension is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Bootstrap extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       Bootstrap
 */

/**
 * @uses \Bootstrap\Hooks\SetupAfterCache
 *
 * @ingroup Test
 *
 * @group extension-bootstrap
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v3+
 * @since 1.0
 *
 * @author mwjames
 */
class SetupAfterCacheTest extends \PHPUnit_Framework_TestCase {

	protected $localBasePath = null;
	protected $localBootstrapVendorPath = null;

	protected function setUp() {
		parent::setUp();
		if ( is_readable( __DIR__ . '/../../../vendor' ) ) {
			$this->localBootstrapVendorPath = __DIR__ . '/../../../vendor/twbs/bootstrap';
		} else {
			$this->localBootstrapVendorPath = __DIR__ . '/../../../../../vendor/twbs/bootstrap';
		}
	}

	public function testCanConstruct() {

		$configuration = array();

		$this->assertInstanceOf(
			'\Bootstrap\Hooks\SetupAfterCache',
			new SetupAfterCache( $configuration )
		);
	}

	public function testProcessWithAccessibilityOnBootstrapVendorPath() {

		$configuration = array(
			'localBasePath'  => $this->localBootstrapVendorPath,
			'remoteBasePath' => '',
			'IP' => 'someIP',
		);

		$instance = new SetupAfterCache( $configuration );

		$this->assertTrue( $instance->process() );
	}

	public function testProcess_setsDefaultCacheTriggers() {

		$configuration = array(
			'localBasePath'  => $this->localBootstrapVendorPath,
			'remoteBasePath' => '',
			'IP' => 'someIP',
		);

		$this->resetGlobals();

		$instance = new SetupAfterCache( $configuration );

		$this->assertTrue( $instance->process() );

		$this->assertEquals(
			'someIP/LocalSettings.php',
			$GLOBALS[ 'wgResourceModules' ][ 'ext.bootstrap.styles' ][ 'cachetriggers' ][ 'LocalSettings.php' ]
		);

		$this->assertEquals(
			'someIP/composer.lock',
			$GLOBALS[ 'wgResourceModules' ][ 'ext.bootstrap.styles' ][ 'cachetriggers' ][ 'composer.lock' ]
		);
	}

	public function testProcessWithAccessibilityOnAddedLocalResourcePaths() {

		$configuration = array(
			'localBasePath'  => $this->localBootstrapVendorPath,
			'remoteBasePath' => '',
			'IP' => 'someIP',
		);

		$instance = new SetupAfterCache( $configuration );
		$instance->process();

		$this->assertThatPathIsReadable(
			$GLOBALS[ 'wgResourceModules' ][ 'ext.bootstrap.styles' ]['localBasePath']
		);

		$this->assertThatPathIsReadable(
			$GLOBALS[ 'wgResourceModules' ][ 'ext.bootstrap.scripts' ]['localBasePath']
		);
	}

	/**
	 * @dataProvider invalidConfigurationProvider
	 */
	public function testProcessOnInvalidConfigurationThrowsException( $configuration ) {

		$instance = new SetupAfterCache( $configuration );

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->process();
	}

	public function testProcessOnInvalidLocalPathThrowsException() {

		$configuration = array(
			'localBasePath'  => 'Foo',
			'remoteBasePath' => '',
			'IP' => 'someIP',
		);

		$instance = new SetupAfterCache( $configuration );

		$this->setExpectedException( 'RuntimeException' );
		$instance->process();
	}

	public function invalidConfigurationProvider() {

		$provider = array();

		$provider[] = array(
			array()
		);

		$provider[] = array(
			array(
				'localBasePath' => 'Foo'
			)
		);

		$provider[] = array(
			array(
				'remoteBasePath' => 'Foo'
			)
		);

		return $provider;
	}

	protected function assertThatPathIsReadable( $path ) {
		$this->assertTrue( is_readable( $path ) );
	}

	private function resetGlobals() {
		$GLOBALS[ 'wgResourceModules' ][ 'ext.bootstrap.styles' ] = array (
			'class'         => 'Bootstrap\ResourceLoaderBootstrapModule',
			'styles'        => array (),
			'variables'     => array (),
			'dependencies'  => array (),
			'cachetriggers' => array (
				'LocalSettings.php' => null,
				'composer.lock'     => null,
			),
		);
	}

}
