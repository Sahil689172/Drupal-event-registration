<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class EventRegistrationMailer {

  protected $mailManager;
  protected $configFactory;

  public function __construct(
    MailManagerInterface $mail_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
  }

  public function sendUserMail($to, $params, $langcode) {
    return $this->mailManager->mail(
      'event_registration',
      'user_confirmation',
      $to,
      $langcode,
      $params
    );
  }

  public function sendAdminMail($params, $langcode) {
    $config = $this->configFactory->get('event_registration.settings');

    if (!$config->get('enable_admin_notification')) {
      return;
    }

    $this->mailManager->mail(
      'event_registration',
      'admin_notification',
      $config->get('admin_email'),
      $langcode,
      $params
    );
  }
}
