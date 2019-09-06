<?php

namespace Drupal\github_cards\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'GitHub Card' Block.
 *
 * @Block(
 *   id = "github_card_block",
 *   admin_label = @Translation("GitHub Card"),
 *   category = @Translation("GitHub Card"),
 * )
 */
class GitHubCardBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity storage for GitHub Cards.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $gitHubCardStorage;

  /**
   * GitHubCardBlock constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param array $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Drupal entity type manager service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->gitHubCardStorage = $entity_type_manager->getStorage('github_card');
  }

  /**
   * {@inheritdoc}}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $card = !empty($config['github_card']) ? $this->gitHubCardStorage->load($config['github_card']) : NULL;

    $form['github_card'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Github Card'),
      '#description' => $this->t('The GitHub Card to use for information.'),
      '#default_value' => $card,
      '#target_type' => 'github_card',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['github_card'] = $values['github_card'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $card = !empty($config['github_card']) ? $this->gitHubCardStorage->load($config['github_card']) : NULL;

    $view = !empty($card) ? $this->entityTypeManager->getViewBuilder('github_card')
      ->view($card) : NULL;

    return [
      'github_card' => $view,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
        'github_card' => NULL,
        'label_display' => FALSE,
      ];
  }

}
