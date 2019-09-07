<?php

namespace Drupal\Tests\github_cards\Unit\Service;

use Drupal\github_cards\Service\GitHubCardsClientFactory;
use Drupal\Tests\UnitTestCase;
use Github\Client;

/**
 * Class GitHubCardsClientFactoryTest.
 *
 * @package Drupal\Tests\github_cards\Unit\Service
 * @group github_cards
 */
class GitHubCardsClientFactoryTest extends UnitTestCase {

  /**
   * Validate we can get an instance of a GitHub client.
   */
  public function testCreateGitHubClient() {
    $this->assertInstanceOf(Client::class, GitHubCardsClientFactory::createGitHubClient());
  }

}
