<?php
namespace cloudgrayau\cleantalk\models;

use craft\base\Model;
use craft\validators\ArrayValidator;

class Settings extends Model {
  
  /* READ ONLY */
  public bool $enableJS = true;
  
  /* GENERAL */
  public string $apiKey = '';
  public bool $enableBotDetector = true;
  
  /* FIREWALL */
  public bool $enableFirewall = true;
  
  /* INTEGRATIONS */
  public bool $enableUserRegistration = true;
  public array $integrations = [
    'formie',
    'freeform',
    'contact-form',
    'wheelform',
    'express-forms',
    'comments'
  ];
  
  /* MANUAL */
  public array $blockedEmails = [];
  public array $blockedIPs = [];
  public array $allowedEmails = [];
  public array $allowedIPs = [];
  
  // Public Methods
  // =========================================================================

  public function rules(): array {
    return [
      [['apiKey'], 'required'],
      [['apiKey'], 'string'],
      [['enableUserRegistration','enableBotDetector','enableFirewall','enableJS'], 'boolean'],
      [['integrations','blockedEmails','blockedIPs','allowedEmails','allowedIPs'], ArrayValidator::class],
    ];
  }
  
}