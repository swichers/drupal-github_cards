<?php

namespace Drupal\github_cards\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining GitHub Card entities.
 *
 * @ingroup github_cards
 */
interface GitHubCardEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the GitHub Card name.
   *
   * @return string
   *   Name of the GitHub Card.
   */
  public function getName();

  /**
   * Sets the GitHub Card name.
   *
   * @param string $name
   *   The GitHub Card name.
   *
   * @return \Drupal\github_cards\Entity\GitHubCardEntityInterface
   *   The called GitHub Card entity.
   */
  public function setName($name);

  /**
   * Gets the GitHub Card creation timestamp.
   *
   * @return int
   *   Creation timestamp of the GitHub Card.
   */
  public function getCreatedTime();

  /**
   * Sets the GitHub Card creation timestamp.
   *
   * @param int $timestamp
   *   The GitHub Card creation timestamp.
   *
   * @return \Drupal\github_cards\Entity\GitHubCardEntityInterface
   *   The called GitHub Card entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Get the GitHub Card owner user ID.
   *
   * @return int
   *   The GitHub Card owner user ID.
   */
  public function getOwnerId();

  /**
   * Set the GitHub Card owner user ID.
   *
   * @param int $uid
   *  The GitHub Card owner user ID.
   *
   * @return \Drupal\github_cards\Entity\GitHubCardEntityInterface
   *   The called GitHub Card entity.
   */
  public function setOwnerId($uid);

  /**
   * Get the GitHub Card owner account.
   *
   * @return UserInterface
   *   The GitHub Card owner account.
   */
  public function getOwner();

  /**
   * Set the GitHub Card owner account.
   *
   * @param UserInterface $account
   *   The GitHub Card owner account.
   *
   * @return \Drupal\github_cards\Entity\GitHubCardEntityInterface
   *   The called GitHub Card entity.
   */
  public function setOwner(UserInterface $account);

  /**
   * Get the GitHub Card resource type.
   *
   * @return string
   *   The GitHub Card resource type.
   */
  public function getResourceType();

  /**
   * Set the GitHub Card resource type.
   *
   * @param string $resourceType
   *   The resource type. One of 'user' or 'repo'.
   *
   * @return \Drupal\github_cards\Entity\GitHubCardEntityInterface
   *   The called GitHub Card entity.
   */
  public function setResourceType($resourceType);

  /**
   * Get the GitHub Card resource.
   *
   * @return string
   *   The GitHub Card resource.
   */
  public function getResource();

  /**
   * Set the GitHub Card resource URI.
   *
   * @param string $resource
   *   The resource URI to use.
   *
   * @return \Drupal\github_cards\Entity\GitHubCardEntityInterface
   *   The called GitHub Card entity.
   */
  public function setResource($resource);

}
