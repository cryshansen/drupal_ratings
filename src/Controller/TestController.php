<?php

namespace Drupal\drupal_ratings\Controller;

use Drupal\Core\Controller\ControllerBase;

class TestController extends ControllerBase {

  public function test() {
    // Add a status message.
    $this->messenger()->addMessage($this->t('This is a test message.'));

    // Render a simple response.
    return [
      '#markup' => $this->t('Check if the test message is displayed.'),
    ];
  }
}
