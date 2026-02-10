<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\Response;

class EventRegistrationAdminController extends ControllerBase {

  /**
   * Admin listing page
   */
  public function listing() {
    $header = [
      'id' => $this->t('ID'),
      'name' => $this->t('Name'),
      'email' => $this->t('Email'),
      'college' => $this->t('College'),
      'department' => $this->t('Department'),
      'event' => $this->t('Event'),
      'date' => $this->t('Date'),
    ];

    $rows = [];

    $query = Database::getConnection()->select('event_registration', 'r');
    $query->join('event_config', 'e', 'r.event_config_id = e.id');
    $query->fields('r', ['id', 'full_name', 'email', 'college', 'department']);
    $query->fields('e', ['event_name', 'event_date']);

    $results = $query->execute();

    foreach ($results as $row) {
      $rows[] = [
        $row->id,
        $row->full_name,
        $row->email,
        $row->college,
        $row->department,
        $row->event_name,
        $row->event_date,
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No registrations found'),
    ];
  }

  /**
   * CSV Export
   */
  public function exportCsv() {
    $connection = Database::getConnection();

    $query = $connection->select('event_registration', 'r');
    $query->join('event_config', 'e', 'r.event_config_id = e.id');
    $query->fields('r', ['full_name', 'email', 'college', 'department']);
    $query->fields('e', ['event_name', 'event_date']);

    $results = $query->execute();

    $csv = "Name,Email,College,Department,Event,Date\n";

    foreach ($results as $row) {
      $csv .= "\"{$row->full_name}\",\"{$row->email}\",\"{$row->college}\",\"{$row->department}\",\"{$row->event_name}\",\"{$row->event_date}\"\n";
    }

    $response = new Response($csv);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="event_registrations.csv"');

    return $response;
  }
}
