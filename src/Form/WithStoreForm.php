<?php

namespace Drupal\ex_form_values\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
// DI.
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Class WithControllerForm.
 *
 * Get the url of a RSS file and the number of items to retrieve
 * from this file.
 * Store those two fields (url and items) to a PrivateTempStore object
 * to use them in a controller for processing and displaying
 * the information of the RSS file.
 */
class WithStoreForm extends FormBase {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Drupal\Core\Cache\CacheTagsInvalidatorInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  private $cacheTagsInvalidator;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * Constructs a new WithControllerForm object.
   */
  public function __construct(
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger_factory,
    CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    PrivateTempStoreFactory $tempStoreFactory
  ) {
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->tempStoreFactory = $tempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('logger.factory'),
      $container->get('cache_tags.invalidator'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'with_state_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Url'),
      '#description' => $this->t('Enter the url of the RSS file'),
      '#default_value' => 'https://www.drupal.org/planet/rss.xml',
      '#weight' => '0',
    ];

    $form['items'] = [
      '#type' => 'select',
      '#title' => $this->t('# of items'),
      '#description' => $this->t('Enter the number of items to retrieve'),
      '#options' => [
        '5' => $this->t('5'),
        '10' => $this->t('10'),
        '15' => $this->t('15'),
      ],
      '#default_value' => 5,
      '#weight' => '0',
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => '0',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Submit the form and redirect to a controller.
   *
   * 1. Save the values of the form into the $params array
   * 2. Create a PrivateTempStore object
   * 3. Store the $params array in the PrivateTempStore object
   * 4. Redirect to the controller for processing.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // 1. Set the $params array with the values of the form
    // to save those values in the store.
    $params['url'] = $form_state->getValue('url');
    $params['items'] = $form_state->getValue('items');

    // 2. Create a PrivateTempStore object with the collection 'ex_form_values'.
    $tempstore = $this->tempStoreFactory->get('ex_form_values');

    // 3. Store the $params array with the key 'params'.
    try {
      $tempstore->set('params', $params);
      // 4. Redirect to the simple controller.
      $form_state->setRedirect('ex_form_values.simple_controller_show_item');
    }
    catch (\Exception $error) {
      // Store this error in the log.
      $this->loggerFactory->get('ex_form_values')->alert(t('@err', ['@err' => $error]));
      // Show the user a message.
      $this->messenger->addWarning(t('Unable to proceed, please try again.'));
    }
  }

}
