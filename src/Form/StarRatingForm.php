<?php
/**
 * @file
 * Contains \Drupal\drupal_ratings\Form\StarRatingForm.
*/
namespace Drupal\drupal_ratings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Database\Connection;
use Exception;
use Drupal\user\Entity\User;
use Drupal\node\NodeInterface;							  

class StarRatingForm extends FormBase {
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
    return 'star_rating_form';
  }



  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    /**
    * handle variables. 
    */
     // Get the current user.
    $current_user = \Drupal::currentUser();
    // Load the user entity.
    $account = User::load($current_user->id());
    // Get the email address.
    $email = $account->getEmail();
    $hs_contact_id = 0; //$account->get('field_hs_contact_id')->value ? $account->get('field_hs_contact_id')->value : 0;
    
    // Get the current route match service.
    $route_match = \Drupal::routeMatch();

    // Get the node from the route.
    $node = $route_match->getParameter('node');

    // Set the node ID. Default to 0 if no node is present.
    $node_id = ($node instanceof NodeInterface) ? $node->id() : 0;
    $content_type = ($node instanceof NodeInterface) ? $node->bundle() : '';

    
    $form['info'] = [
      '#markup' => $this->t('<h5 style="display:flex;justify-content:center;">Do you like the Presentation? Rate it!</h5>'),
    ];

    $form['adr_ip'] = [
      '#type' => 'hidden',
      '#value' => \Drupal::request()->getClientIp(),
    ];
    $form['email'] = [
      '#type' => 'hidden',
      '#value' => $this->encryptData($email), //$email, // $this->t('hello@happy.com'),
    ];
     //obfiscate the hubspot user id with a different name for privacy issues. or load the id via the save but heavy processing.
    $form['hash_hub_id'] = [
      '#type' => 'hidden',
      '#value' =>  $this->encryptData($hs_contact_id), //$current_user->id(), // $this->t('hello@happy.com'),
    ];
    //TODO: get content type to pass to db
    $form['content_type'] = [
      '#type' => 'hidden',
      '#value' => $content_type, // 'technical_presentation', //test page in development default for now
    ];
    
    $form['document_id'] = [
      '#type' => 'hidden',
      '#value' => $node_id, //4519, //test page in development
    ];
    
    
   $form['rating'] = [
      '#type' => 'radios',
      //'#title' => $this->t('Rate this item'),
      '#options' => [
        '0_5' => '0_5',
        '1' => '1',
        '1_5' => '1_5',
        '2' => '2',
        '2_5' => '2_5',
        '3' => '3',
        '3_5' => '3_5',
        '4' => '4',
        '4_5' => '4_5',
        '5' => '5',
      ],
      //'#prefix' => '<div class="rating-group">',
      //'#suffix' => '</div>',
      '#attributes' => [
        'class' => ['rating__input'],
      ],
      '#theme_wrappers' => [],
    ];

   
    $form['feedback'] = [
      '#type' => 'textarea',
      '#title' => $this->t(''),
      '#attributes' => [
        'id' => 'rateResponse',
        // 'class' => ['improveMsg','d-none'],
        'class' => ['improveMsg'],
        'placeholder' => $this->t('What can make this contribution better?'),
        'rows' => 8,
        'cols' => 40,
      ],
      '#theme_wrappers' => [],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Your Feedback'),
    ];

    // Attach the custom theme to the form
    $form['#theme'] = 'star_rating_form_theme'; // Key for the Twig template
    $form['#attached']['library'][] = 'drupal_ratings/star_rating_styles';
    
    return $form;
  
  }
/**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {
     \Drupal::logger('drupal_ratings')->info('Star rating Form hits validation.');
    
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
     \Drupal::logger('drupal_ratings')->info('Star rating Form hits submission.');
     //getALL form values for secondary functions
     $form_values = $form_state->getValues();
    // Get form values.
    $adr_ip = $form_state->getValue('adr_ip');
    $email = $this->decryptData($form_state->getValue('email')); //$this->encryptData
    
    $rating = $form_state->getValue('rating');
    // Convert underscores to decimal points for rating.
    $rating = str_replace('_', '.', $rating);
    // Optionally, cast the rating to a float.
    $rating = (float) $rating;
    $form_values['rating'] = $rating;
    
    
    $feedback = $form_state->getValue('feedback');
    $document_id = (int) $form_state->getValue('document_id');
    
    $content_type = $form_state->getValue('content_type');
  // submission handling: Log the submission.
        \Drupal::logger('drupal_ratings')->info('Star rating Form submitted @adr_ip with email @email, @feedback and rating @rating, docid: @document_id', [
          '@adr_ip' => $adr_ip,
          '@email' => $email,
          '@rating' => $rating,
          '@feedback' => $feedback,
          '@document_id'  => $document_id,
        ]);
        
    
    
    
    try{
        
        // submission handling: Log the submission.
        \Drupal::logger('drupal_ratings')->info('Star rating Form submitted @adr_ip with email @email, @feedback and rating @rating, docid: @document_id', [
          '@adr_ip' => $adr_ip,
          '@email' => $email,
          '@rating' => $rating,
          '@feedback' => $feedback,
          '@document_id'  => $document_id,
        ]);
        

        // if email and node not in table insert else, update the record?
        //$this->manageDataStorage($document_id, $rating, $email,$feedback,$adr_ip);
        // Check if the user has already rated this node.
        $existing_rating = $this->database->select('drupal_ratings', 'r')
          ->fields('r', ['rating'])
          ->condition('r.node_id', $document_id)
          ->condition('r.email', $email)
          ->execute()
          ->fetchField();

        if ($existing_rating) {
          // Update the existing rating.
          $this->database->update('drupal_ratings')
            ->fields(['rating' => $rating, 'adr_ip' => $adr_ip, 'feedback' => $feedback, 'created' => \Drupal::time()->getRequestTime()])
            ->condition('node_id', $document_id)
            ->condition('email', $email)
            ->execute();
            
        } else {
            // Insert the form data into the custom table.
              $this->database->insert('drupal_ratings')
                ->fields([
                  'adr_ip' => $adr_ip,
                  'email' => $email,
                'node_id' => $document_id,
                'content_type' => $content_type,
                'rating' => $rating,
                'feedback' => $feedback,
                'created' => \Drupal::time()->getRequestTime(),
              ])
              ->execute();
      }   
   
        
        if($document_id !== 0){
            //update the node with accurate 
            $this->updateNodeRating($document_id);
            
           
           
        }
        
 
        $this->messenger->addStatus($this->t('Star Rating submitted successfully. Thanks for the feedback!'));
    
    }catch (\Exception $error) {
        // Log the error.
 
        \Drupal::logger('drupal_ratings')->error('Something errored at form level! @error', [ '@error'=>$error->getMessage()]);
    }
     
  }
  

  /*
  * send a note to contact that the user submitted a feedback response.
  * this is actually supposed to be like a star rating response on pages with the comments / feedback displayed. we are not displaying this. 
  * TODO: clean up the calls to server use encryption to decrypt the email and hubspot id
  */
   public function save_to_hubspot($form_values){
   
    //hubspot field with comments against the contact
    $content_type = $form_values['content_type'];
    $rating = $form_values['rating'];
    $document_id = $form_values['document_id'];
    $feedback = $form_values['feedback'];
    $created = \Drupal::time()->getRequestTime();  
    $created =  date('Y-m-d\TH:i:s\Z', $created);
    $hs_contact_id = $this->decryptData($form_values['hash_hub_id']);   
    $note_body =  "User with email: $email rated the document: $document_id of content : $content_type with a rating of $rating with feedback $feedback at $created";
     
    // 201 Contact to note          
    // 202 Note to contact    
    if(!empty($hs_contact_id)){
        $data = [
            'properties' => [
                'hs_timestamp' => $created,
                'hs_note_body' => $note_body,
            ],
            'associations' => [
                [
                    'to' => [
                        'id' => $hs_contact_id, // Contact ID
                    ],
                    'types' => [
                        [
                            'associationCategory' => 'HUBSPOT_DEFINED',
                            'associationTypeId' => 202, // Note to contact
                        ],
                    ],
                ],
            ],
        ];
        
        //todo: centralize this authentication token so that when swapping out every 6 months its just configuration.
        $basic_auth_access_token = 'pat-';
        $hubspot_url = 'https://api.hubapi.com/crm/v3/objects/notes';
        $auth = 'Bearer ' . $basic_auth_access_token;	
        
        \Drupal::logger('drupal_ratings')->info('Created response to hubspot notes @note, @hubspotid',['@note' =>  $note_body , '@hubspotid' => $hs_contact_id]);
        
        try{
           //moved these into the try so handles the errors associated with decryptions 
          $email = $this->decryptData($form_values['email']);
          $hb_contact_id = $this->decryptData($form_values['hash_hub_id']);   
          
          
          
          $response = \Drupal::httpClient()->post($hubspot_url, [
            'verify' => true,
            'json' => $data,  //httpClient handles json encoding/decoding
            'headers' => [
              'Authorization' => $auth,
              'Content-type' => 'application/json',
            ],
          ])->getBody()->getContents();

          \Drupal::logger('drupal_ratings')->info('Response to hubspot finished @hubspotid',[ '@hubspotid' => $hs_contact_id]);
       
       } catch (\GuzzleHttp\Exception\GuzzleException $error) {
          // Get the original response
          $response = $error->getResponse();
          // Get the info returned from the remote server.
          $response_info = $response->getBody()->getContents();
          // Using FormattableMarkup allows for the use of <pre/> tags, giving a more readable log item.
          $message = new \Drupal\Component\Render\FormattableMarkup('API connection error. Error details are as follows:<pre>@response</pre>', ['@response' => print_r(json_decode($response_info), TRUE)]);
          // Log the error
           \Drupal::logger('drupal_ratings')->error('submission remote post failed at guzzle level @error, @message', [ '@error'=>$error->getMessage(), '@message' =>$message]);
        }
        catch (\Exception $error) {
          // Log the error.
           \Drupal::logger('drupal_ratings')->error('Something errored! An unknown error occurred while trying to connect to the remote API. @error', [ '@error'=>$error->getMessage()]);
        }

    }
     
  }

 /**
 * Update the average rating field on the node.
 */
protected function updateNodeRating($node_id) {
  //$connection = Database::getConnection();
// Use the database connection passed to the class.
  $connection = $this->database;

  // Query for the sum and count of ratings in one go.
  $query =  $connection->select('drupal_ratings', 'r');    
  $query->addExpression('SUM(r.rating)', 'total_rating');
  $query->addExpression('COUNT(r.rating)', 'vote_count');
  $query->condition('r.node_id', $node_id);
  // Execute the query and fetch the result.
  $result = $query->execute()->fetchAssoc();
  
  // Check if there are any ratings.
  if ($result['vote_count'] > 0) {
    // Calculate the average rating.
    $average_rating = $result['total_rating'] / $result['vote_count'];

    // Load and update the node.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node_id);
    if ($node) {
      $field_name = $node->hasField('field_rating') ? 'field_rating' : 'field_exchange_rating';
      $vote_name = $node->hasField('field_nb_votes') ? 'field_nb_votes' : 'field_number_of_votes'; //$num_votes = ['field_nb_votes', 'field_number_of_votes'];
      $node->set($field_name, $average_rating);
      $node->set($vote_name, $result['vote_count']);
      $node->save();
    }
  }
}


  private function encryptData($data) {
    $config = $this->config('drupal_ratings.settings');
    $encryption_key = $config->get('encryption_key');
    \Drupal::logger('emtp_star_rating')->info('EncryptData called @data',[ '@data' => $data]);
    //$encryption_key = 'your-secret-key-here'; // Use a secure key
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv) . '::' . $iv);
  }

  private function decryptData($data) {
    $config = $this->config('emtp_star_rating.settings');
    $encryption_key = $config->get('encryption_key');
    \Drupal::logger('emtp_star_rating')->info('DecryptData called @data',[ '@data' => $data]);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
  }
  
  
  
}