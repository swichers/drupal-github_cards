<?php

namespace Drupal\github_cards\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Github\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GitHubCardsGitHubService.
 */
class GitHubCardsGitHubService  implements ContainerInjectionInterface, GitHubCardsGitHubServiceInterface {

  /**
   * Drupal\Core\Cache\CacheBackendInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheDefault;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * A GitHub Client instance.
   *
   * @var \Github\Client
   */
  protected $githubClient;

  /**
   * Constructs a new GitHubCardsGitHubService object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_default
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Logger\LoggerChannelInterface|object $logger_channel
   * @param \Github\Client $github_client
   */
  public function __construct(CacheBackendInterface $cache_default, EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger_channel, Client $github_client) {
    $this->cacheDefault = $cache_default;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $logger_channel;
    $this->githubClient = $github_client;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.default'),
      $container->get('entity_type.manager'),
      $container->get('logger.channel.github_cards'),
      $container->get('github_cards.client')
    );
  }

  /**
   * {@inheritdoc}}
   */
  public function getClient() {
    return $this->githubClient;
  }

  /**
   * {@inheritdoc}}
   */
  public function getUserInfo($userName) {
    try {
      return $this->githubClient->users()->show($userName);
    }
    catch (\Exception $x) {
      $this->loggerChannel->error($x->getMessage());
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}}
   */
  public function getRepositoryInfo($userName, $repoName) {
    try {
      return $this->githubClient->repository()->show($userName, $repoName);
    }
    catch (\Exception $x) {
      $this->loggerChannel->error($x->getMessage());
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function parseResourceUrl($url) {

    // Some pre-checks to make sure we have something resembling a full URL
    // because parse_url(1234) will return 1234 as the path.
    if (!\filter_var($url, \FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
      return FALSE;
    }

    // We don't use the Drupal UrlHelper class because it groups the domain
    // with the the path when all we actually want is the path.
    $path = parse_url($url, \PHP_URL_PATH);
    $path = trim($path, '/');
    $path = explode('/', $path);

    $parts = [
      'type' => 'invalid',
      'user' => $path[0] ?? NULL,
      'repository' => $path[1] ?? NULL,
    ];

    if (!empty($parts['user']) && empty($parts['repository'])) {
      $parts['type'] = 'user';
    }
    elseif (!empty($parts['user']) && !empty($parts['repository'])) {
      $parts['type'] = 'repository';
    }

    return $parts['type'] === 'invalid' ? FALSE : $parts;
  }

}
