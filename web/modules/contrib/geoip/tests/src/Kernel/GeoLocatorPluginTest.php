<?php

namespace Drupal\Tests\geoip\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the default GeoLocator plugins.
 *
 * @group geoip
 */
class GeoLocatorPluginTest extends KernelTestBase {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   *   A file system instance
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'system',
    'geoip',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->fileSystem = $this->container->get('file_system');
  }

  /**
   * Tests the CDN plugin.
   */
  public function testCdn() {
    $this->config('geoip.geolocation')
      ->set('plugin_id', 'cdn')
      ->save();

    $_SERVER['HTTP_CF_IPCOUNTRY'] = 'US';
    $this->assertEquals('US', $this->container->get('geoip.geolocation')->geolocate('127.0.0.1'));
    unset($_SERVER['HTTP_CF_IPCOUNTRY']);

    $_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY'] = 'CA';
    $this->assertEquals('CA', $this->container->get('geoip.geolocation')->geolocate('192.168.1.1'));
  }

  /**
   * Tests the Local plugin (MaxMindDB).
   */
  public function testLocal() {
    $this->fileSystem->copy(__DIR__ . '/../../fixtures/files/GeoLite2-Country.mmdb', 'public://GeoLite2-Country.mmdb');
    $this->config('geoip.geolocation')
      ->set('plugin_id', 'local')
      ->save();
    $this->assertEquals('US', $this->container->get('geoip.geolocation')->geolocate('75.86.161.59'));
  }

}
