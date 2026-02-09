<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\event_registration\Service\EventRegistrationMailer;
use Drupal\event_registration\Service\EventRegistrationStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventRegistrationForm extends FormBase {

  protected EventRegistrationMailer $mailer;
  protected EventRegistrationStorage $storage;

  public function __construct(
    EventRegistrationMailer $mailer,
    EventRegistrationStorage $storage
  ) {
    $this->mailer = $mailer;
    $this->storage = $storage;
  }

public static function create(ContainerInterface $container) {
  return new static(
    $container->get('event_registration.mailer'),
    $container->get('event_registration.database')
  );
}


  public function getFormId() {
    return 'event_registration_form';
  }

  /* ================= BUILD FORM ================= */

  public function buildForm(array $form, FormStateInterface $form_state) {
    $connection = Database::getConnection();
    $today = date('Y-m-d');

    // Check if registration is open
    $active = $connection->select('event_config', 'e')
      ->condition('reg_start', $today, '<=')
      ->condition('reg_end', $today, '>=')
      ->countQuery()
      ->execute()
      ->fetchField();

    if (!$active) {
      return [
        'message' => [
          '#markup' => '<p>Event registration is currently closed.</p>',
        ],
      ];
    }

    /* ---------- BASIC FIELDS ---------- */

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    $form['college'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
    ];

    /* ---------- CATEGORY ---------- */

    $categories = $connection->select('event_config', 'e')
      ->fields('e', ['category'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $category_options = ['' => '- Select -'];
    foreach ($categories as $cat) {
      $category_options[$cat] = ucfirst($cat);
    }

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Category'),
      '#options' => $category_options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::loadEventDates',
        'wrapper' => 'event-date-wrapper',
      ],
    ];

    /* ---------- EVENT DATE ---------- */

    $form['event_date_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-date-wrapper'],
    ];

    $category = $form_state->getValue('category');
    $date_options = ['' => '- Select -'];

    foreach ($this->getEventDateOptions($category) as $d) {
      $date_options[$d] = $d;
    }

    $form['event_date_wrapper']['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $date_options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::loadEventNames',
        'wrapper' => 'event-name-wrapper',
      ],
    ];

    /* ---------- EVENT NAME ---------- */

    $form['event_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-name-wrapper'],
    ];

    $date = $form_state->getValue('event_date');
    $event_options = ['' => '- Select -'] + $this->getEventNameOptions($category, $date);

    $form['event_name_wrapper']['event_config_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => $event_options,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /* ================= AJAX ================= */

  public function loadEventDates(array &$form, FormStateInterface $form_state) {
    return $form['event_date_wrapper'];
  }

  public function loadEventNames(array &$form, FormStateInterface $form_state) {
    return $form['event_name_wrapper'];
  }

  /* ================= HELPERS ================= */

  private function getEventDateOptions($category): array {
    if (empty($category)) {
      return [];
    }

    $today = date('Y-m-d');

    return Database::getConnection()
      ->select('event_config', 'e')
      ->fields('e', ['event_date'])
      ->condition('category', $category)
      ->condition('reg_start', $today, '<=')
      ->condition('reg_end', $today, '>=')
      ->execute()
      ->fetchCol();
  }

  private function getEventNameOptions($category, $date): array {
    if (empty($category) || empty($date)) {
      return [];
    }

    return Database::getConnection()
      ->select('event_config', 'e')
      ->fields('e', ['id', 'event_name'])
      ->condition('category', $category)
      ->condition('event_date', $date)
      ->execute()
      ->fetchAllKeyed();
  }

  /* ================= VALIDATION ================= */

  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!preg_match('/^[a-zA-Z\s]+$/', $form_state->getValue('full_name'))) {
      $form_state->setErrorByName(
        'full_name',
        $this->t('Only letters and spaces are allowed.')
      );
    }

    if ($this->storage->registrationExists(
      $form_state->getValue('email'),
      $form_state->getValue('event_config_id')
    )) {
      $form_state->setErrorByName(
        'email',
        $this->t('You are already registered for this event.')
      );
    }
  }

  /* ================= SUBMIT ================= */

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->storage->saveRegistration([
      'full_name' => $form_state->getValue('full_name'),
      'email' => $form_state->getValue('email'),
      'college' => $form_state->getValue('college'),
      'department' => $form_state->getValue('department'),
      'event_config_id' => $form_state->getValue('event_config_id'),
      'created' => time(),
    ]);

    $langcode = $this->currentUser()->getPreferredLangcode();

    $this->mailer->sendUserMail(
      $form_state->getValue('email'),
      ['body' => 'Registration successful'],
      $langcode
    );

    $this->mailer->sendAdminMail(
      ['body' => 'New registration received'],
      $langcode
    );

    $this->messenger()->addStatus(
      $this->t('Registration successful. Confirmation email sent.')
    );
  }

}
