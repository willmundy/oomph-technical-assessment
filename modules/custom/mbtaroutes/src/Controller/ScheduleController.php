<?php
/**
 * @file
 * Contains \Drupal\mbtaroutes\Controller\RoutesController
 */

namespace Drupal\mbtaroutes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Carbon\Carbon;
class ScheduleController extends ControllerBase
{
  public function schedule($route_id)
  {
    $content['message'] = [
      '#markup' => "Below is the schedule for the " . $route_id .  " route."
    ];
    $schedules = $this->getTodaysScheduleData($route_id);
    $rows = [];
    $headers = [
      t('Stop'),
      t('Arrival Time'),
      t('Departure Time'),
      t('Direction')
    ];
    foreach ($schedules as $schedule) {
      $rows[] = [
        $schedule['relationships']['stop']['data']['id'],
        $schedule['attributes']['arrival_time'] ?? '-',
        $schedule['attributes']['departure_time'] ?? '-',
        $schedule['attributes']['direction_id'],
      ];
    }
    $content['table'] = array(
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No entries available.')
    );
    $content['#cache']['max-age'] = 0;
    return $content;
  }

  private function getTodaysScheduleData($route_id)
  {
    // For a production build of this I'd probably inject this client as a dependency
    $url = 'https://api-v3.mbta.com/schedules?page[limit]=50&include=stop&filter[route]=' . $route_id;

    $client = \Drupal::httpClient();
    $request = $client->get($url)->getBody()->getContents();
    $data = json_decode($request, true)['data'];
    return $data;
  }

  // IDEA - too many requests per minute makes it not viable
  // The APIs return a lot IDs with limited ability to connect them to human-readable info,
  // which means to do this for real we'd need to pull this all into a DB rather
  // than trying to pull all of this stuff on the fly
  private function getStopReadableName($stop_id): string
  {
    $url = 'https://api-v3.mbta.com/stops/' . $stop_id;

    $client = \Drupal::httpClient();
    $request = $client->get($url)->getBody()->getContents();
    $name = json_decode($request, true)['data']['attributes']['description'];
    return $name;
  }
}
