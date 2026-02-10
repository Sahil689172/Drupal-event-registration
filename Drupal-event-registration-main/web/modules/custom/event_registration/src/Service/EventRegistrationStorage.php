<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Database\Connection;

class EventRegistrationStorage {

  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public function registrationExists($email, $event_config_id) {
    return $this->database->select('event_registration', 'r')
      ->condition('email', $email)
      ->condition('event_config_id', $event_config_id)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  public function saveRegistration(array $data) {
    $this->database->insert('event_registration')
      ->fields($data)
      ->execute();
  }

}
