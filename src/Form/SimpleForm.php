<?php
namespace Drupal\drupal_ratings\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Database\Connection;

/**
*  This is a form to get the call back working. 
*  this form works from its url endpoint not to be implemented in production. 
*/

class SimpleForm extends FormBase {

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

 /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a SimpleForm object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(MessengerInterface $messenger, Connection $database) {
    $this->messenger = $messenger;
    $this->database = $database;
     
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      
    $form['info'] = [
      '#markup' => $this->t('<p>We highly value your opinion!</p>'),
    ];  
    
    $form['adr_ip'] = [
      '#type' => 'hidden',
      '#value' => \Drupal::request()->getClientIp(),
    ];
    
    $form['document_id'] = [
      '#type' => 'hidden',
      '#value' => 4519, //test page in development
    ];
    
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your Email'),
      '#required' => true,
    ];
    
    $form['rating'] = [
      '#type' => 'radios',
      '#title' => $this->t('Rating'),
      '#options' => [
        '0.5' => '0.5',
        '1' => '1',
        '1.5' => '1.5',
        '2' => '2',
        '2.5' => '2.5',
        '3' => '3',
        '3.5' => '3.5',
        '4' => '4',
        '4.5' => '4.5',
        '5' => '5',
      ],
    ];
    
    $form['feedback'] = [
      '#type' => 'textarea',
      '#title' => $this->t('What can we do to improve?'),
      '#attributes' => [
        'id' => 'rateResponse',
        'class' => ['improveMsg'],
        'placeholder' => $this->t('What can make this contribution better? Your rating is 3.5'),
        'rows' => 8,
        'cols' => 40,
      ],
    ];
    
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Example validation: Ensure email is in a valid format.
    $email = $form_state->getValue('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      
    $adr_ip = $form_state->getValue('adr_ip');
    $email = $form_state->getValue('email');
    $rating = $form_state->getValue('rating');
    $feedback = $form_state->getValue('feedback');
    $document_id = (int) $form_state->getValue('document_id');
    
    
    // Example submission handling: Log the submission.
    \Drupal::logger('drupal_ratings')->notice('Form submitted by @name with email @name, @feedback and rating @rating, docid: @node_id', [
      '@adr_ip' => $form_state->getValue('adr_ip'),
      '@email' => $form_state->getValue('email'),
      '@rating' => $rating,
      '@feedback' => $form_state->getValue('feedback'),
      '@node_id'  => $form_state->getValue('document_id'),
    ]);
    
    
    // Insert the form data into the custom table.
    $this->database->insert('drupal_ratings')
      ->fields([
        'adr_ip' => $adr_ip,
        'email' => $email,
        'node_id' => $document_id,
        'content_type' => 'simple-form',
        'rating' => $rating,
        'feedback' => $feedback,
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();
    
    // email, node_id ='4519', rating, feedback, adr_ip, created
    
    
    
    $this->messenger->addMessage($this->t('Form submitted successfully by @name with email @name and @feedback rating @rating.', [
        '@name' => $name,
        '@email' => $email,
        '@rating' => $rating,
        '@feedback' => $feedback,

      ]));
  
    // Add a status message to confirm form submission.
    $this->messenger->addMessage($this->t('Simple Form submitted successfully!'));

    // Redirect to a different page after form submission if needed.
    //$form_state->setRedirect('<front>'); // Redirect to the front page after submission.
  }





}
