<?php
namespace cloudgrayau\cleantalk\models;

use Craft;
use craft\base\Model;

class Settings extends Model {
  
  // Editable Variables
  // =========================================================================
  
  public string $apiKey = '';
  public bool $enableForms = true;
  public bool $enableUserRegistration = true;
  public bool $enableComments = true;
  public bool $enableBotDetector = true;
  public bool $enableFirewall = true;
  
  // Public Methods
  // =========================================================================

  public function rules(): array {
    return [
      [['apiKey'], 'string'],
      [['enableForms','enableUserRegistration','enableComments','enableBotDetector','enableFirewall'], 'boolean']
    ];
  }
  
}