<?php

namespace Drupal\github_cards;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the GitHub Card entity.
 *
 * @see \Drupal\github_cards\Entity\GitHubCardEntity.
 */
class GitHubCardEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\github_cards\Entity\GitHubCardEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished github card entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published github card entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit github card entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete github card entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add github card entities');
  }

}
