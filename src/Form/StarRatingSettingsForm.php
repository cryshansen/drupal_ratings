<?php
/**
 * @file
 * Contains \Drupal\drupal_ratings\Form\StarRatingSettingsForm.
 * purpose is to integrate encryption keys to hide or obscure data in form when rendered. 
*/
namespace Drupal\drupal_ratings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;					  

class StarRatingSettingsForm extends ConfigFormBase  {
 
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['drupal_ratings.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupal_ratings_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('drupal_ratings.settings');

    $form['encryption_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Encryption Key'),
      '#default_value' => $config->get('encryption_key'),
      '#description' => $this->t('Enter the encryption key for securing sensitive data.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('drupal_ratings.settings')
      ->set('encryption_key', $form_state->getValue('encryption_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
