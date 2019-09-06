<?php

namespace Drupal\Tests\github_cards\Unit\Service;

use Drupal;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\github_cards\Service\GitHubCardsInfoService;
use Drupal\Tests\UnitTestCase;
use Github\Api\Repo;
use Github\Api\User;
use Github\Client;

/**
 * Class GitHubCardsInfoServiceTest.
 *
 * @package Drupal\Tests\github_cards\Unit\Service
 */
class GitHubCardsInfoServiceTest extends UnitTestCase {

  /**
   * Container builder helper.
   *
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * The randomly generated username to use for testing.
   *
   * @var string
   */
  protected $testUserName;

  /**
   * The randomly generated repository name to use for testing.
   *
   * @var string
   */
  protected $testRepoName;

  /**
   * Validate we can properly parse a resource URL.
   */
  public function testParseResourceUrl() {
    $ghc = GitHubCardsInfoService::create($this->container);

    $user_name = $this->randomMachineName();
    $repo_name = $this->randomMachineName();

    $checks = [
      1234 => FALSE,
      '' => FALSE,
      'http://example.com/' => FALSE,
      'http://example.com/ test' => FALSE,
      sprintf('http://example.com/%s', $user_name) => [
        'type' => 'user',
        'user' => $user_name,
        'repository' => NULL,
      ],
      sprintf('http://example.com/%s/', $user_name) => [
        'type' => 'user',
        'user' => $user_name,
        'repository' => NULL,
      ],
      sprintf('http://example.com/%s/%s', $user_name, $repo_name) => [
        'type' => 'repository',
        'user' => $user_name,
        'repository' => $repo_name,
      ],
      sprintf('http://example.com/%s/%s/', $user_name, $repo_name) => [
        'type' => 'repository',
        'user' => $user_name,
        'repository' => $repo_name,
      ],
      sprintf('http://example.com/%s/%s/anything', $user_name, $repo_name) => [
        'type' => 'repository',
        'user' => $user_name,
        'repository' => $repo_name,
      ],
    ];

    foreach ($checks as $url => $expected) {
      $this->assertEquals($expected, $ghc->parseResourceUrl($url), 'Failure checking ' . $url);
    }
  }

  /**
   * Validate we can get resource information from a URL.
   */
  public function testGetInfoByUrl() {
    $ghc = GitHubCardsInfoService::create($this->container);

    $this->assertFalse($ghc->getInfoByUrl(''));

    $url = sprintf('http://example.com/%s', $this->testUserName);
    $this->assertEquals($this->getUserInfo($this->testUserName), $ghc->getInfoByUrl($url));

    $url = sprintf('http://example.com/%s/%s', $this->testUserName, $this->testRepoName);
    $this->assertEquals($this->getRepoInfo($this->testUserName, $this->testRepoName), $ghc->getInfoByUrl($url));
  }

  /**
   * Validate we can get user information by URL.
   */
  public function testGetUserInfoByUrl() {
    $ghc = GitHubCardsInfoService::create($this->container);

    $this->assertFalse($ghc->getUserInfoByUrl(''));

    $url = sprintf('http://example.com/%s', $this->testUserName);
    $this->assertEquals($this->getUserInfo($this->testUserName), $ghc->getUserInfoByUrl($url));

    // Make sure that even with a repo URL we are getting the proper user.
    $url = sprintf('http://example.com/%s/%s', $this->testUserName, $this->testRepoName);
    $this->assertEquals($this->getUserInfo($this->testUserName), $ghc->getUserInfoByUrl($url));
  }

  /**
   * Validate we can get repository information by URL.
   */
  public function testGetRepoInfoByUrl() {
    $ghc = GitHubCardsInfoService::create($this->container);

    $this->assertFalse($ghc->getRepositoryInfoByUrl(''));

    $url = sprintf('http://example.com/%s/%s', $this->testUserName, $this->testRepoName);
    $this->assertEquals($this->getRepoInfo($this->testUserName, $this->testRepoName), $ghc->getRepositoryInfoByUrl($url));
  }

  /**
   * Validate we can get the expected user information.
   */
  public function testGetUserInfo() {
    $ghc = GitHubCardsInfoService::create($this->container);

    $this->assertEquals($this->getUserInfo($this->testUserName), $ghc->getUserInfo($this->testUserName));
    $this->assertFalse($ghc->getUserInfo(''));
    $this->assertFalse($ghc->getUserInfo(FALSE));
  }

  /**
   * Validate we can get the expected repository information.
   */
  public function testGetRepositoryInfo() {
    $ghc = GitHubCardsInfoService::create($this->container);

    $this->assertEquals($this->getRepoInfo($this->testUserName, $this->testRepoName), $ghc->getRepositoryInfo($this->testUserName, $this->testRepoName));
    $this->assertFalse($ghc->getRepositoryInfo('', ''));
    $this->assertFalse($ghc->getRepositoryInfo('', NULL));
    $this->assertFalse($ghc->getRepositoryInfo($this->testUserName, NULL));
    $this->assertFalse($ghc->getRepositoryInfo($this->testUserName, ''));
    $this->assertFalse($ghc->getRepositoryInfo($this->testUserName, FALSE));
  }

  /**
   * Validate we can get a GitHub client.
   */
  public function testGetClient() {
    $ghc = GitHubCardsInfoService::create($this->container);
    $this->assertInstanceOf(Client::class, $ghc->getClient());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->testUserName = $this->randomMachineName();
    $this->testRepoName = $this->randomMachineName();

    $cache_default_bin = $this->getMockBuilder(CacheBackendInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $entity_type_manager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $logger_channel = $this->getMockBuilder(LoggerChannelInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $time = $this->getMockBuilder(TimeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->container = new ContainerBuilder();
    $this->container->set('cache.default', $cache_default_bin);
    $this->container->set('entity_type.manager', $entity_type_manager);
    $this->container->set('logger.channel.github_cards', $logger_channel);
    $this->container->set('datetime.time', $time);
    $this->container->set('github_cards.client', $this->getMockedGitHubClient($this->testUserName, $this->testRepoName));
    Drupal::setContainer($this->container);
  }

  /**
   * Get a mocked GitHub Client for testing with.
   *
   * @param string $userName
   *   The username to set for the client response.
   * @param string $repoName
   *   The repository name to set for the client response.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject|\Github\Client
   *   A mocked GitHub Client.
   */
  protected function getMockedGitHubClient($userName, $repoName) {
    $github_client = $this->getMockBuilder(Client::class)
      ->disableOriginalConstructor()
      ->setMethods(['users', 'repository'])
      ->getMock();

    $github_repo = $this->getMockBuilder(Repo::class)
      ->disableOriginalConstructor()
      ->getMock();
    $github_repo->method('show')->willReturnMap([
      [$userName, $repoName, $this->getRepoInfo($userName, $repoName)],
      [$userName, NULL, FALSE],
      [$userName, '', FALSE],
      ['', '', FALSE],
      ['', NULL, FALSE],
    ]);

    $github_users = $this->getMockBuilder(User::class)
      ->disableOriginalConstructor()
      ->getMock();
    $github_users->method('show')->willReturnMap([
      [$userName, $this->getUserInfo($userName)],
      ['', FALSE],
    ]);

    $github_client->method('users')->willReturn($github_users);

    $github_client->method('repository')->willReturn($github_repo);

    return $github_client;
  }

  /**
   * Provide minimal user information for testing against.
   *
   * @param string $userName
   *   The GitHub username to use.
   *
   * @return array
   *   An array of minimal user information to fake a response.
   */
  protected function getUserInfo($userName) {
    return [
      'login' => $userName,
      'id' => 1234,
      'public_repos' => 24,
      'public_gists' => 24,
      'followers' => 7,
      'following' => 3,
    ];
  }

  /**
   * Provide minimal repository information for testing against.
   *
   * @param string $userName
   *   The repository owner's name.
   * @param string $repoName
   *   The repository name.
   *
   * @return array
   *   An array of minimal repository information to fake a response.
   */
  protected function getRepoInfo($userName, $repoName) {
    return [
      'id' => 7890,
      'name' => $repoName,
      'full_name' => $userName . '/' . $repoName,
      'forks_count' => 13,
      'stargazers_count' => 3,
      'watchers_count' => 2,
    ];
  }

}
