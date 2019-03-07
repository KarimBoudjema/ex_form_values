<?php

namespace Drupal\ex_form_values\Controller;

use Drupal\Core\Controller\ControllerBase;

// DI.
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\ex_form_values\MyServices;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use GuzzleHttp\ClientInterface;

// Other.
use Drupal\Core\Url;

/**
 * Target controller of the WithStoreForm.php .
 */
class SimpleController extends ControllerBase {


  /**
   * Tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $clientRequest;


  /**
   * Messenger service.
   *
   * @var \\Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Custom service.
   *
   * @var \Drupal\ex_form_values\MyServices
   */
  private $myServices;

  /**
   * Inject services.
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory,
                              ClientInterface $clientRequest,
                              MessengerInterface $messenger,
                              MyServices $myServices) {

    $this->tempStoreFactory = $tempStoreFactory;
    $this->clientRequest = $clientRequest;
    $this->messenger = $messenger;
    $this->myServices = $myServices;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('http_client'),
      $container->get('messenger'),
      $container->get('ex_form_values.myservices')
    );

  }

  /**
   * Target method of the the WithStoreForm.php.
   *
   * 1. Get the parameters from the tempstore for this user
   * 2. Delete the PrivateTempStore data from the database (not mandatory)
   * 3. Display a simple message with the data retrieved from the tempstore
   * 4. Get the items from the rss file in a renderable array
   * 5. Create a link back to the form
   * 6. Render the array.
   *
   * @return array
   *   An render array.
   */
  public function showRssItems() {

    // 1. Get the parameters from the tempstore.
    $tempstore = $this->tempStoreFactory->get('ex_form_values');
    $params = $tempstore->get('params');
    $url = $params['url'];
    $items = $params['items'];

    // 2. We can now delete the data in the temp storage
    // Its not mandatory since the record in the key_value_expire table
    // will expire normally in a week.
    // We comment this task for the moment, so we can see the values
    // stored in the key_value_expire table.
    /*
    try {
      $tempstore->delete('params');
    }
    catch (\Exception $error) {
      $this->loggerFactory->get('ex_form_values')->alert(t('@err',['@err' => $error]));
    }
    */

    // 3. Display a simple message with the data retrieved from the tempstore
    // Set a cache tag, so when an anonymous user enter in the form
    // we can invalidate this cache.
    $build[]['message'] = [
      '#type' => 'markup',
      '#markup' => t("Url: @url - Items: @items", ['@url' => $url, '@items' => $items]),
      '#cache' => [
        'tags' => [
          'myform',
        ],
      ],
    ];

    // 4. Get the items from the rss file in a renderable array.
    if ($articles = $this->myServices->getItemFromRss($url, $items)) {
      // Create a render array with the results.
      $build[]['data_table'] = $this->myServices->buildTheRender($articles);
    }

    // 5. Create a link back to the form.
    $build[]['back'] = [
      '#type' => 'link',
      '#title' => 'Back to the form',
      '#url' => URL::fromRoute('ex_form_values.with_store_form'),
    ];

    // 6. Render the array.
    return $build;
  }

}
