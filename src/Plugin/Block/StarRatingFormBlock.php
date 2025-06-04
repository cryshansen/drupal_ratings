<?php

namespace Drupal\drupal_ratings\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block for Support Feedback form
 *
 * @Block(
 *   id = "star_rating_blocks_block",
 *   admin_label = @Translation("Star Rating Form Block"),
 *   category = @Translation("Custom Forms")
 * )
 */
class StarRatingFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\drupal_ratings\Form\StarRatingForm');
    return $form;
  }
}

