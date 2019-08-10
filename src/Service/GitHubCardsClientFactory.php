<?php

namespace Drupal\github_cards\Service;

use Github\Client;

/**
 * Class GitHubCardsClientFactory.
 */
class GitHubCardsClientFactory implements GitHubCardsClientFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function createGitHubClient() {
    return new Client();
  }

}
