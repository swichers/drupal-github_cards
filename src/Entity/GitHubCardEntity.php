<?php

namespace Drupal\github_cards\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the GitHub Card entity.
 *
 * @ingroup github_cards
 *
 * @ContentEntityType(
 *   id = "github_card",
 *   label = @Translation("GitHub Card"),
 *   label_collection = @Translation("GitHub Cards"),
 *   label_singular = @Translation("GitHub card"),
 *   label_plural = @Translation("GitHub cards"),
 *   label_count = @PluralTranslation(
 *     singular = "@count GitHub card",
 *     plural = "@count GitHub cards"
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\github_cards\GitHubCardEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\github_cards\Form\GitHubCardEntityForm",
 *       "edit" = "Drupal\github_cards\Form\GitHubCardEntityForm",
 *       "delete" = "Drupal\github_cards\Form\GitHubCardEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\github_cards\GitHubCardEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\github_cards\GitHubCardEntityAccessControlHandler",
 *   },
 *   base_table = "github_cards",
 *   fieldable = FALSE,
 *   translatable = FALSE,
 *   admin_permission = "administer github card entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/github_card/{github_card}",
 *     "add-form" = "/admin/content/github_card/add",
 *     "edit-form" = "/admin/content/github_card/{github_card}/edit",
 *     "delete-form" = "/admin/content/github_card/{github_card}/delete",
 *     "collection" = "/admin/content/github_card"
 *   }
 * )
 */
class GitHubCardEntity extends ContentEntityBase implements GitHubCardEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    $resource_info = $this->getResourceInfo();
    $this->setResourceType($resource_info['type'] ?? 'invalid');

    parent::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setResourceType($resourceType) {
    $this->set('resource_type', $resourceType);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceType() {
    return $this->get('resource_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setResource($resource) {
    $this->set('resource', $resource);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResource() {
    return $this->get('resource')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isRepositoryResource() {
    return $this->getResourceType() === 'repository';
  }

  /**
   * {@inheritdoc}
   */
  public function isUserResource() {
    return $this->getResourceType() === 'user';
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceUser() {
    $resource_info = $this->getResourceInfo();
    return $resource_info['user'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceRepository() {
    $resource_info = $this->getResourceInfo();
    return $resource_info['repository'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchResourceData() {
    $info_service = $this->getGitHubCardsInfoService();
    return $info_service->getInfoByUrl($this->getResource());
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the GitHub Card entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ]);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the GitHub Card entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setRequired(TRUE);

    $fields[$entity_type->getKey('published')]
      ->setDescription(t('A boolean indicating whether the GitHub Card is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 0,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['resource_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Resource Type'))
      ->setDescription(t('The type of GitHub resource being shown.'))
      ->setDefaultValue('invalid')
      ->setSettings([
        'allowed_values' => [
          'invalid' => t('Invalid'),
          'user' => t('User'),
          'repository' => t('Repository'),
        ],
      ])
      ->setRequired(TRUE);

    $fields['resource'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Resource'))
      ->setDescription(t('The GitHub resource URI.'))
      ->addConstraint('UniqueField')
      ->addConstraint('NotBlank')
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'uri',
        'weight' => -8,
      ])
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * Get an instance of the GitHub Cards Info Service.
   *
   * @return \Drupal\github_cards\Service\GitHubCardsInfoServiceInterface
   */
  protected function getGitHubCardsInfoService() {
    return \Drupal::service('github_cards.github_info');
  }

  /**
   * Get the parsed resource information.
   *
   * @return array|bool
   *   An array of resource information or FALSE on failure.
   */
  protected function getResourceInfo() {
    return $this->getGitHubCardsInfoService()
      ->parseResourceUrl($this->getResource());
  }

}
