<?php

namespace Drupal\Tests\github_cards\Kernel\Entity;

use Drupal\github_cards\Entity\GitHubCardEntity;
use Drupal\github_cards\Entity\GitHubCardEntityInterface;
use Drupal\github_cards\Service\GitHubCardsInfoService;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\user\UserInterface;

/**
 * Class GitHubCardEntityTest.
 *
 * @package Drupal\Tests\github_cards\Kernel\Entity
 *
 * @group github_cards
 *
 * @coversDefaultClass \Drupal\github_cards\Entity\GitHubCardEntity
 */
class GitHubCardEntityTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['options', 'github_cards'];

  /**
   * Validate we can set and get the entity name.
   *
   * @covers ::getName
   * @covers ::setName
   */
  public function testName() {
    $card = $this->getCardEntity();
    $this->assertInstanceOf(GitHubCardEntityInterface::class, $card->setName('lorem ipsum'));
    $this->assertEquals('lorem ipsum', $card->getName());
  }

  /**
   * Get a simple GitHubCard entity.
   *
   * @param string $resourceUrl
   *   A default resource to use. Defaults to https://example.com.
   * @param bool $save
   *   TRUE to save the entity before returning. Defaults to FALSE.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\github_cards\Entity\GitHubCardEntity
   *   The simple entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function getCardEntity(string $resourceUrl = NULL, bool $save = FALSE):GitHubCardEntityInterface {
    $user = $this->createUser();

    $container = \Drupal::getContainer();
    $container->get('current_user')->setAccount($user);

    $card = GitHubCardEntity::create([
      'title' => $this->randomString(),
      'resource' => $resourceUrl ?? 'https://example.com',
    ]);

    if ($save) {
      $card->save();
    }

    return $card;
  }

  /**
   * Validate we can set and get the created time.
   *
   * @covers ::getCreatedTime
   * @covers ::setCreatedTime
   */
  public function testCreatedTime() {
    $card = $this->getCardEntity();

    $this->assertEquals(REQUEST_TIME, $card->getCreatedTime());
    $this->assertInstanceOf(GitHubCardEntityInterface::class, $card->setCreatedTime(1234567890));
    $this->assertEquals(1234567890, $card->getCreatedTime());
  }

  /**
   * Validate we can set and get the owner ID.
   *
   * @covers ::getOwnerId
   * @covers ::setOwnerId
   */
  public function testOwnerId() {
    $card = $this->getCardEntity();
    /** @var \Drupal\Core\Session\AccountProxy $user */
    $user = \Drupal::service('current_user');

    $this->assertEquals($user->id(), $card->getOwnerId());

    $second_user = $this->createUser();
    $this->assertInstanceOf(GitHubCardEntityInterface::class, $card->setOwnerId($second_user->id()));
    $this->assertEquals($second_user->id(), $card->getOwnerId());
  }

  /**
   * Validate we can set and get the owner object.
   *
   * @covers ::getOwner
   * @covers ::setOwner
   */
  public function testOwner() {
    $card = $this->getCardEntity();
    $user = \Drupal::service('current_user');

    $this->assertInstanceOf(UserInterface::class, $card->getOwner());
    $this->assertEquals($user->getAccountName(), $card->getOwner()
      ->getAccountName());

    $second_user = $this->createUser();
    $this->assertInstanceOf(GitHubCardEntityInterface::class, $card->setOwner($second_user));
    $this->assertInstanceOf(UserInterface::class, $card->getOwner());
    $this->assertEquals($second_user->id(), $card->getOwnerId());
    $this->assertEquals($second_user->getAccountName(), $card->getOwner()
      ->getAccountName());
  }

  /**
   * Validate we can set and get the resource.
   *
   * @covers ::getResource
   * @covers ::setResource
   */
  public function testResource() {

    $card = $this->getCardEntity();

    $resources = [
      '' => 'invalid',
      'https://example.com' => 'invalid',
      'https://example.com/user-name/repo-name' => 'repository',
      'https://example.com/user-name' => 'user',
    ];

    foreach ($resources as $resource => $expected_type) {
      $this->assertInstanceOf(GitHubCardEntityInterface::class, $card->setResource($resource));
      $this->assertEquals($expected_type, $card->getResourceType(), $resource);
      $this->assertEquals($resource, $card->getResource());
    }
  }

  /**
   * Validate we can determine if a resource is a repository.
   *
   * @covers ::isRepositoryResource
   */
  public function testIsRepositoryResource() {
    $card = $this->getCardEntity('https://example.com/user-name/repo-name', FALSE);
    $this->assertTrue($card->isRepositoryResource());

    $card = $this->getCardEntity('https://example.com/user-name', FALSE);
    $this->assertFalse($card->isRepositoryResource());
  }

  /**
   * Validate we can determine if a resource is a user.
   *
   * @covers ::isUserResource
   */
  public function testIsUserResource() {
    $card = $this->getCardEntity('https://example.com/user-name/repo-name');
    $this->assertFalse($card->isUserResource());

    $card = $this->getCardEntity('https://example.com/user-name');
    $this->assertTrue($card->isUserResource());
  }

  /**
   * Validate we can get the username from a resource.
   *
   * @covers ::getResourceUser
   * @covers ::getGitHubCardsInfoService
   */
  public function testGetResourceUser() {
    $card = $this->getCardEntity('https://example.com/user-name/repo-name');
    $this->assertEquals('user-name', $card->getResourceUser());
  }

  /**
   * Validate we can get the repository from a resource.
   *
   * @covers ::getResourceRepository
   * @covers ::getGitHubCardsInfoService
   */
  public function testGetResourceRepository() {
    $card = $this->getCardEntity('https://example.com/user-name/repo-name');
    $this->assertEquals('repo-name', $card->getResourceRepository());
    $card = $this->getCardEntity('https://example.com/user-name');
    $this->assertFalse($card->getResourceRepository());
  }

  /**
   * Validate we can get resource data.
   *
   * @covers ::fetchResourceData
   * @covers ::getGitHubCardsInfoService
   */
  public function testFetchResourceData() {
    $card = $this->getCardEntity();
    $this->assertFalse($card->fetchResourceData());

    $card->setResource('https://example.com/user-name');
    $this->assertEquals('user-name', $card->fetchResourceData()['login']);
    $this->assertEquals('Username', $card->fetchResourceData()['name']);

    $card->setResource('https://example.com/user-name/repo-name');
    $this->assertEquals('user-name', $card->fetchResourceData()['login']);
    $this->assertEquals('repo-name', $card->fetchResourceData()['name']);
  }

  /**
   * Validate we can get and set the resource type.
   *
   * @covers ::getResourceType
   * @covers ::setResourceType
   */
  public function testResourceType() {
    $card = $this->getCardEntity();
    $card->setResourceType('repository');
    $this->assertEquals('repository', $card->getResourceType());
    $card->setResourceType('user');
    $this->assertEquals('user', $card->getResourceType());
    $card->setResourceType('invalid');
    $this->assertEquals('invalid', $card->getResourceType());
    $card->setResourceType($this->randomString());
    $this->assertEquals('invalid', $card->getResourceType());
  }

  /**
   * Validate preSave functionality.
   *
   * @covers ::preSave
   * @covers ::setResourceTypeFromResource
   */
  public function testPreSave() {
    // preSave() should set the correct value even if something else was set.
    $card = $this->getCardEntity('https://example.com/user-name');
    $card->setResourceType('repository');
    $this->assertEquals('repository', $card->getResourceType(), $card->getResource());
    $card->save();
    $this->assertEquals('user', $card->getResourceType(), $card->getResource());

    $card = $this->getCardEntity('https://example.com/user-name');
    $this->assertEquals('user', $card->getResourceType(), $card->getResource());
    $card->save();
    $this->assertEquals('user', $card->getResourceType(), $card->getResource());

    $card = $this->getCardEntity('https://example.com');
    $this->assertEquals('invalid', $card->getResourceType(), $card->getResource());
    $card->save();
    $this->assertEquals('invalid', $card->getResourceType(), $card->getResource());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('github_card');
    $this->installConfig(['options', 'github_cards']);

    $mock_info = $this->getMockBuilder(GitHubCardsInfoService::class)
      ->disableOriginalConstructor()
      ->setMethods(['getInfoByUrl'])
      ->getMock();
    $mock_info->method('getInfoByUrl')->willReturnMap([
      ['https://example.com', FALSE],
      ['https://example.com/user-name', [
        'name' => 'Username',
        'login' => 'user-name',
      ],
      ],
      ['https://example.com/user-name/repo-name', [
        'name' => 'repo-name',
        'login' => 'user-name',
      ],
      ],
    ]);

    /** @var \Drupal\Component\DependencyInjection\Container|\Drupal\Core\DependencyInjection\ContainerInjectionInterface $container */
    $container = \Drupal::getContainer();
    $container->set('github_cards.github_info', $mock_info);
    \Drupal::setContainer($container);
  }

}
