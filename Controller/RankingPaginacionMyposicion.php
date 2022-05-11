<?php

/**
 * @file
 * @author David Martinez
 * Contains \Drupal\co_bavaria_arcade_150\Controller\GameController.
 * Please place this file under your example(module_root_folder)/src/Controller/
 * Use https://www.drupal.org/docs/8/api/database-api/dynamic-queries/introduction-to-dynamic-queries  para consultes y actualización de data
 */

namespace Drupal\co_bavaria_arcade_150\Controller\pe_mikes_shopkeeper;

use \DateTimeZone;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\co_bavaria_arcade_150\models\Dama;

/**
 * Provides route responses for the Example module.
 */
class RankingPaginacionMyposicion extends ControllerBase
{
  /*
    * Construct a new controller.
    *
    * @param \Drupal\dbtng_example\Services $repository
    *   The repository service.
    */
  function __construct()
  {
    $this->dama =  new Dama();
    $this->dbUser = 'co_bavaria_arcade_150_mikes_shopkeeper_users';
    $this->dbPoints = 'co_bavaria_arcade_150_mikes_data';
    // \Drupal::service('page_cache_kill_switch')->trigger();
  }

  /**
   * ranking
   *
   * @return void
   */
  public function rankingDataNew()
  {
    $uid = \Drupal::currentUser()->id();

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $limit = isset($_GET['records']) ? $_GET['records'] : 10;

    $Data = \Drupal::database()->select($this->dbUser, 'u')
      ->fields('u', ["uid", "name", "code_bees", "points"])
      ->isNotNull('points')
      ->orderBy('points', 'DESC')
      ->orderBy('date_play', 'ASC')
      ->execute()->fetchAllAssoc('uid');

    $myposicion = array_key_exists($uid, $Data);
    $response['myRanking'] = $myposicion;
    $response['dataRanking'] = null;
    $count = 0;
    foreach ($Data as $key => $value) {
      $value->posicion = ++$count;
      $ranking['data'][] = $value;

      if ($myposicion && $key == $uid) {
        $response['myRanking'] = $value;
        $myposicion = false;
      }

      if (!$myposicion && $count >= ($page * $limit) || count($Data) == $count) {
        $response['dataRanking'] =  array_slice($ranking['data'], (($page - 1) * $limit), $limit);
        break;
      }
    }

    return new JsonResponse($response, 200);
  }
}
