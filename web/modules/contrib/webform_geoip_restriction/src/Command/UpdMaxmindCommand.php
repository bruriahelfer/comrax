<?php

declare(strict_types = 1);

namespace Drupal\webform_geoip_restriction\Command;

use Drupal\webform_geoip_restriction\UpdateMaxmindManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// phpcs:disable Drupal.Commenting.ClassComment.Missing
#[AsCommand(
  name: 'webform_geoip_restriction:updmaxmind',
  description: 'Update the Maxmind Database.',
  aliases: ['updmaxmind'],
)]
final class UpdMaxmindCommand extends Command {

  /**
   * Constructs an UpdMaxmindCommand object.
   */
  public function __construct(
    private readonly UpdateMaxmindManagerInterface $webformGeoipRestrictionUpdateMaxmindManager,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    if ($this->webformGeoipRestrictionUpdateMaxmindManager->updMaxmind() == TRUE) {
      $output->writeln('<info>Maxmind Database updated!</info>');
      return self::SUCCESS;
    }
    else {
      $output->writeln('<error>Maxmind Database update errors!</error>');
      return self::FAILURE;
    }
  }

}
