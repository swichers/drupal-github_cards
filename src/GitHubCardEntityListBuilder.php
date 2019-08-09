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
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\github_cards\Entity\GitHubCardEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = $entity->toLink(NULL, 'edit-form');
    $row['resource_type'] = $entity->getResourceType() == 'user' ?
      $this->t('User') :
      $this->t('Repository');

    $row['resource'] = [
      'data' => [
        '#type' => 'link',
        '#title' => $entity->getResource(),
        '#url' => Url::fromUri($entity->getResource()),
      ],
    ];
    $row['owner'] = $entity->getOwner()->toLink();
    $row['status'] = $entity->isPublished() ?
      $this->t('Active') :
      $this->t('Inactive');
    return $row + parent::buildRow($entity);
  }

}
