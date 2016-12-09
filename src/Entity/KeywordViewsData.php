<?php

namespace Drupal\alinks\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Keyword entities.
 */
class KeywordViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['alink_keyword']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Keyword'),
      'help' => $this->t('The Keyword ID.'),
    );

    return $data;
  }

}
