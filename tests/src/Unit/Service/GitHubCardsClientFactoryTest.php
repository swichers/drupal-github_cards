<?php

namespace Drupal\Tests\github_cards\Unit\Service;

use Drupal\github_cards\Service\GitHubCardsClientFactory;
use Drupal\Tests\UnitTestCase;
use Github\Client;

class GitHubCardsClientFactoryTest extends UnitTestCase {

  public function testCreateGitHubClient() {
    $this->assertInstanceOf(Client::class, GitHubCardsClientFactory::createGitHubClient());
  }

}
