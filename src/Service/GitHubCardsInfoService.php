<?php

namespace Drupal\github_cards\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\github_cards\Entity\GitHubCardEntityInterface;
use Github\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GitHubCardsInfoService.
 */
class GitHubCardsInfoService implements ContainerInjectionInterface, GitHubCardsInfoServiceInterface {

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

  /*
   * Drupal Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * A GitHub Client instance.
   *
   * @var \Github\Client
   */
  protected $githubClient;

  /**
   * Constructs a new GitHubCardsInfoService object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_default
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Logger\LoggerChannelInterface|object $logger_channel
   * @param \Drupal\Component\Datetime\TimeInterface|object $time
   * @param \Github\Client $github_client
   */
  public function __construct(CacheBackendInterface $cache_default, EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger_channel, TimeInterface $time, Client $github_client) {
    $this->cacheDefault = $cache_default;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $logger_channel;
    $this->time = $time;
    $this->githubClient = $github_client;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.default'),
      $container->get('entity_type.manager'),
      $container->get('logger.channel.github_cards'),
      $container->get('datetime.time'),
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
   * Provides a cached request wrapper for getting GitHub information.
   *
   * @param string $userName
   *   The user to get information about or that owns the repository provided.
   * @param string $repoName
   *   The repository to get information about.
   *
   * @return array|false
   *   The information about the user or repository. FALSE on failure.
   */
  protected function getRemoteInfo($userName, $repoName = NULL) {
    $cid = implode(':', array_filter(['github_cards', $userName, $repoName]));
    $cached = $this->cacheDefault->get($cid);
    if ($cached) {
      return $cached->data;
    }

    try {
      if (is_null($repoName)) {
        $data = $this->githubClient->users()->show($userName);
      }
      else {
        $data = $this->githubClient->repository()->show($userName, $repoName);
      }
    }
    catch (\Exception $x) {
      $this->loggerChannel->error($x->getMessage());
      $data = NULL;
    }

    // Expire in 1 hour from now.
    $expires = $this->time->getRequestTime() + 3600;

    $tags = ['github_cards'];
    $tags[] = empty($repoName) ? 'github_card_user' : 'github_card_repo';

    $this->cacheDefault->set($cid, $data, $expires, $tags);

    return $data ?? FALSE;
  }

  /**
   * {@inheritdoc}}
   */
  public function getUserInfo($userName) {
    return $this->getRemoteInfo($userName);
  }

  /**
   * {@inheritdoc}}
   */
  public function getRepositoryInfo($userName, $repoName) {
    return $this->getRemoteInfo($userName, $repoName ?: FALSE);
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

  /**
   * {@inheritdoc}
   */
  public function getRepositoryInfoByUrl($url) {
    $parts = $this->parseResourceUrl($url);
    if (empty($parts) || $parts['type'] !== 'repository') {
      return FALSE;
    }

    return $this->getRepositoryInfo($parts['user'], $parts['repository']);
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfoByUrl($url) {
    $parts = $this->parseResourceUrl($url);
    if (empty($parts)) {
      return FALSE;
    }

    return $this->getRemoteInfo($parts['user'], NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function getInfoByUrl($url) {
    $parts = $this->parseResourceUrl($url);
    if (empty($parts)) {
      return FALSE;
    }

    return $this->getRemoteInfo($parts['user'], $parts['repository']);
  }

}
