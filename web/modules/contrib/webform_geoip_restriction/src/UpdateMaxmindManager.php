<?php

declare(strict_types = 1);

namespace Drupal\webform_geoip_restriction;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Service to update Maxmind Database.
 */
final class UpdateMaxmindManager implements UpdateMaxmindManagerInterface {

  private const MAXMIND_URL = 'https://download.maxmind.com/geoip/databases/';

  /**
   * Constructs an UpdateMaxmindManager object.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function updMaxmind(): bool|array {
    $editions = ['GeoLite2-Country'];
    $publicDir = $this->fileSystem->realpath('public://');
    foreach ($editions as $edition) {
      $ch = curl_init(self::MAXMIND_URL . $edition . '/download?' . http_build_query([
        'suffix' => 'tar.gz',
      ]));
      $archiveFile = $publicDir . DIRECTORY_SEPARATOR . $edition . '.tar.gz';
      $fh = fopen($archiveFile, 'wb');
      curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_USERPWD => $this->configFactory->get('webform_geoip_restriction.settings')->get('maxmind_account') . ":" . $this->configFactory->get('webform_geoip_restriction.settings')->get('maxmind_license'),
        CURLOPT_FILE => $fh,
      ]);
      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      fclose($fh);
      if ($response === FALSE || $httpCode !== 200) {
        if (is_file($archiveFile)) {
          unlink($archiveFile);
        }
        return FALSE;
      }
      else {
        $phar = new \PharData($archiveFile);
        if ($phar->current()->isDir()) {
          $dir = $phar->current()->getPathname();
          $dir = basename($dir);

          $phar->extractTo($publicDir, NULL, TRUE);
          rename($publicDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $edition . '.mmdb', $publicDir . DIRECTORY_SEPARATOR . $edition . '.mmdb');
          $files = array_diff(scandir($publicDir . DIRECTORY_SEPARATOR . $dir), [
            '.',
            '..',
          ]);
          foreach ($files as $file) {
            unlink($publicDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file);
          }
          rmdir($publicDir . DIRECTORY_SEPARATOR . $dir);
          unlink($publicDir . DIRECTORY_SEPARATOR . $edition . '.tar.gz');
        }
      }
    }
    return TRUE;
  }

}
