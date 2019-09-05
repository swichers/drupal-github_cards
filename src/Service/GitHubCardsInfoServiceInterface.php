<?php

namespace Drupal\github_cards\Service;

/**
 * Interface GitHubCardsInfoServiceInterface.
 */
interface GitHubCardsInfoServiceInterface {

  /**
   * A GitHub Client object.
   *
   * @return \Github\Client
   *   The GitHub Client object.
   */
  public function getClient();

  /**
   * Get information about the given user.
   *
   * @param string $userName
   *   The user to get information about.
   *
   * @return array|false
   *   The information about the user or FALSE on failure.
   */
  public function getUserInfo($userName);

  /**
   * Get information about the given repository.
   *
   * @param string $userName
   *   The user the repository belongs to.
   * @param string $repoName
   *   The repository to get information about.
   *
   * @return array|false
   *   The information about the repository or FALSE on failure.
   */
  public function getRepositoryInfo($userName, $repoName);

  /**
   * Parse a resource URL into useful information.
   *
   * @param string $url
   *   The URL to parse for information.
   *
   * @return array|bool
   *   An array of resource information, or FALSE on a bad resource URL.
   */
  public function parseResourceUrl($url);

  /**
   * Get User information by a Resource URL.
   *
   * @param string $url
   *   The Resource URL.
   *
   * @return array|false
   *   An array of User information or FALSE on failure.
   */
  public function getUserInfoByUrl($url);

  /**
   * Get Repository information by a Resource URL.
   *
   * @param string $url
   *   The Resource URL.
   *
   * @return array|false
   *   An array of Repository information or FALSE on failure.
   */
  public function getRepositoryInfoByUrl($url);

  /**
   * Gets resource information by the given URL.
   *
   * @param string $url
   *   The Resource URL.
   *
   * @return array|false
   *   An array of resource information or FALSE on failure.
   */
  public function getInfoByUrl($url);

}
