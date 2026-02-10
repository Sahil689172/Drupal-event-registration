<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class AdminRegistrationListForm extends FormBase {

  public function getFormId() {
    return 'admin_event_registration_list_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $connection = Database::getConnection();

    /* ---------------- FILTERS ---------------- */

    // Event Date dropdown
    $dates = $connection->select('event_config', 'e')
      ->fields('e', ['event_date'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $date_options = ['' => '- Select Date -'];
    foreach ($dates as $date) {
      $date_options[$date] = $date;
    }

    $form['filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Filter Registrations'),
    ];

    $form['filters']['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $date_options,
      '#ajax' => [
        'callback' => '::updateEventNames',
        'wrapper' => 'event-name-wrapper',
      ],
    ];

    /* ---------------- EVENT NAME ---------------- */

    $form['filters']['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $event_name_options = ['' => '- Select Event -'];
    $selected_date = $form_state->getValue('event_date');

    if ($selected_date) {
      $event_name_options += $connection->select('event_config', 'e')
        ->fields('e', ['id', 'event_name'])
        ->condition('event_date', $selected_date)
        ->execute()
        ->fetchAllKeyed();
    }

    $form['filters']['event_name_wrapper']['event_config_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => $event_name_options,
      '#ajax' => [
        'callback' => '::updateTable',
        'wrapper' => 'table-wrapper',
      ],
    ];

    /* ---------------- TABLE ---------------- */

    $form['table_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'table-wrapper'],
    ];

    $form['table_wrapper'] += $this->buildTable($form_state);

    return $form;
  }

  /* ================= AJAX CALLBACKS ================= */

  public function updateEventNames(array &$form, FormStateInterface $form_state) {
    return $form['filters']['event_name_wrapper'];
  }

  public function updateTable(array &$form, FormStateInterface $form_state) {
    return $form['table_wrapper'];
  }

  /* ================= TABLE + COUNT ================= */

  private function buildTable(FormStateInterface $form_state) {

    $connection = Database::getConnection();
    $event_id = $form_state->getValue('event_config_id');

    $header = [
      'name' => $this->t('Name'),
      'email' => $this->t('Email'),
      'college' => $this->t('College'),
      'department' => $this->t('Department'),
      'date' => $this->t('Event Date'),
      'created' => $this->t('Submission Date'),
    ];

    $rows = [];
    $count = 0;

    if ($event_id) {
      $query = $connection->select('event_registration', 'r')
        ->fields('r', ['full_name', 'email', 'college', 'department', 'created'])
        ->condition('event_config_id', $event_id);

      $results = $query->execute();

      foreach ($results as $row) {
        $rows[] = [
          'name' => $row->full_name,
          'email' => $row->email,
          'college' => $row->college,
          'department' => $row->department,
          'date' => date('Y-m-d', $row->created),
          'created' => date('Y-m-d H:i', $row->created),
        ];
        $count++;
      }
    }

    return [
      'count' => [
        '#markup' => '<p><strong>Total Participants: ' . $count . '</strong></p>',
      ],
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No registrations found.'),
      ],
    ];
  }
   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No submit action needed.
    // This form is used only for filtering and AJAX updates.
  }


}
