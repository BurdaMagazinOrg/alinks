<?php

namespace Drupal\alinks\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class KeywordSettingsForm.
 *
 * @package Drupal\alinks\Form
 *
 * @ingroup alinks
 */
class KeywordSettingsForm extends ConfigFormBase  {

  protected $entityTypeManager;

  protected $entityDisplayRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entityTypeManager, EntityDisplayRepositoryInterface $entityDisplayRepository) {

    parent::__construct($config_factory);

    $this->entityTypeManager = $entityTypeManager;
    $this->entityDisplayRepository = $entityDisplayRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return 'alinks.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alinks_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $displays = $this->configFactory()->getEditable('alinks.settings')->get('displays');

    $displays[] = [
      'entity_type' => $form_state->getValue('entityType'),
      'entity_bundle' => $form_state->getValue('entityBundle'),
      'entity_display' => $form_state->getValue('entityDisplay'),
    ];

    $this->configFactory()->getEditable('alinks.settings')
      ->set('displays', $displays)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $form['actions']['submit']['#value'] = $this->t('Add');

    $form = $this->buildTable($form, $form_state);


    $entityTypes = [];
    foreach ($this->entityTypeManager->getDefinitions() as $definition) {
      if ($definition instanceof ContentEntityTypeInterface) {
        $entityTypes[$definition->id()] = $definition->getLabel();
      }
    }
    asort($entityTypes);

    $form['entityType'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => $entityTypes,
      '#empty_value' => '',
      '#empty_option' => $this->t('-- None --'),
      '#ajax' => [
        'callback' => array($this, 'populateEntitySettings'),
        'wrapper' => 'entity-wrapper',
      ],
    ];

    $form['entity'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="entity-wrapper">',
      '#suffix' => '</div>',
    );

    $form['entity']['entityBundle'] = array(
      '#type' => 'select',
      '#title' => $this->t('Entity bundle'),
      '#empty_value' => '',
      '#empty_option' => $this->t('-- None --'),
      '#options' => [],
    );

    $form['entity']['entityDisplay'] = array(
      '#type' => 'select',
      '#title' => $this->t('Entity display'),
      '#empty_value' => '',
      '#empty_option' => $this->t('-- None --'),
      '#options' => [],
    );

    $entityType = $form_state->getValue('entityType');
    if ($entityType) {
      $bundleType = $this->entityTypeManager->getDefinition($entityType)->getBundleEntityType();
      if ($bundleType) {
        $bundles = $this->entityTypeManager->getStorage($bundleType)->loadMultiple();

        if ($bundles) {
          foreach ($bundles as $bundle) {
            $form['entity']['entityBundle']['#options'][$bundle->id()] = $bundle->label();
          }
        }
        asort($form['entity']['entityBundle']['#options']);
      }

      $options = $this->entityDisplayRepository->getViewModeOptions($entityType);
      if ($options) {
        asort($options);
        $form['entity']['entityDisplay']['#options'] += $options;
      }
    }

    return $form;
  }

  /**
   * Fills forms.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */
  public function populateEntitySettings(array &$form, FormStateInterface $form_state) {
    return $form['entity'];
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array
   */
  protected function buildTable(array &$form, FormStateInterface $form_state) {

    $form['mytable'] = array(
      '#type' => 'table',
      '#header' => array(t('Entity type'), t('Entity bundle'), t('Display'), t('Operations')),
    );

    $displays = $this->configFactory()->getEditable('alinks.settings')->get('displays');

    foreach ($displays as $key => $display) {

      $form['mytable'][$key]['entity_type'] = array(
        '#plain_text' => $display['entity_type'],
      );

      $form['mytable'][$key]['entity_bundle'] = array(
        '#plain_text' => $display['entity_bundle'],
      );

      $form['mytable'][$key]['entity_display'] = array(
        '#plain_text' => $display['entity_display'],
      );

      $form['mytable'][$key]['operations'] = array(
        '#type' => 'operations',
        '#links' => array(
          'delete' => array(
            'title' => t('Delete'),
            'url' => Url::fromRoute('alinks.alinks_controller_delete', array('id' => $key)),
          ),
        ),
      );
    }

    return $form;
  }

}
