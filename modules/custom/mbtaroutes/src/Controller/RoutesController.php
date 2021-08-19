<?php
/**
 * @file
 * Contains \Drupal\mbtaroutes\Controller\RoutesController
 */

namespace Drupal\mbtaroutes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;


class RoutesController extends ControllerBase
{

  public function display(): array
  {
    $route_data = $this->getAllMBTARouteData();
    $content['message'] = [
      '#markup' => "Below is a list of MBTA Routes sorted by type."
    ];
    $row_group = [];
    foreach ($route_data as $route) {
      $row_group[$route['attributes']['description']][] = [
        'data' => [
        $this->buildLink($route['id'], $route['attributes']['long_name'])
        ],
        'style' => [
          'background-color:#' . $route['attributes']['color']
        ]
      ];
    }
    foreach ($row_group as $type => $rows) {
      $content['table_' . $type] = array(
        '#type' => 'table',
        '#header' => [$type],
        '#rows' => $rows,
        '#empty' => t('No entries available.')
      );
    }
    $content['#cache']['max-age'] = 0;
    return $content;
  }

  private function getAllMBTARouteData()
  {
    // For a production build of this I'd probably inject this client as a dependency
    $client = \Drupal::httpClient();
    $request = $client->get('https://api-v3.mbta.com/routes')->getBody()->getContents();
    $data = json_decode($request, true)['data'];
    return $data;
  }

  private function buildLink($id, $long_name)
  {
    return Markup::create('<a style="color:black" href="/mbtaroutes/' . $id . '">' . $long_name . '</a>');
  }
}
