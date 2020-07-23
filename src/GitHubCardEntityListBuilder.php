<?php

namespace Drupal\github_cards;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of GitHub Card entities.
 *
 * @ingroup github_cards
 */
class GitHubCardEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('ID');
    $header['name'] = $this->t('Label');
    $header['resource_type'] = $this->t('Type');
    $header['resource'] = $this->t('Resource');
    $header['owner'] = $this->t('Owner');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  public function buildRow(EntityInterface $entity) {

    $row = [];

    /* @var \Drupal\github_cards\Entity\GitHubCardEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = $entity->toLink(NULL, 'canonical');

    $allowed_values = $entity->getFieldDefinition('resource_type')->getSetting('allowed_values');
    $row['resource_type'] = $allowed_values[$entity->getResourceType()] ?? $this->t('Invalid');

    try {
      $row['resource'] = [
        'data' => [
          '#type' => 'link',
          '#title' => $entity->getResource(),
          '#url' => Url::fromUri($entity->getResource()),
        ],
      ];
    }
    catch (\Exception $x) {
      $row['resource'] = $entity->getResource();
    }

    $row['owner'] = $entity->getOwner()->toLink();
    $row['status'] = $entity->isPublished() ?
      $this->t('Active') :
      $this->t('Inactive');
    return $row + parent::buildRow($entity);
  }

}
