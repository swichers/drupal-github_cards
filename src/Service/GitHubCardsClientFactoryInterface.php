<?php

namespace Drupal\github_cards\Service;

/**
 * Interface GitHubCardsClientFactoryInterface.
 */
interface GitHubCardsClientFactoryInterface {

  /**
   * Create a GitHub Client.
   *
   * @return \Github\Client
   *   A GitHub Client instance.
   */
  public static function createGitHubClient();
}
