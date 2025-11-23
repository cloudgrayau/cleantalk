<?php
namespace cloudgrayau\cleantalk\integrations;
use cloudgrayau\cleantalk\Cleantalk;

use yii\base\Event;

class ContactFormIntegration {

  public function parse(): void {
    Event::on(\craft\contactform\Mailer::class, \craft\contactform\Mailer::EVENT_BEFORE_SEND, function(\craft\contactform\events\SendEvent $e){
      $submission = $e->submission;
      $params = [
        'email' => $submission['fromEmail'] ?? '',
        'name' => $submission['fromName'] ?? '',
        'phone' => $submission['phone'] ?? '',
        'message' => $submission['message'] ?? ''
      ];
      if (!Cleantalk::$plugin->antiSpam->checkSpam($params)){
        $e->isSpam = true; 
      }
    }, append: false);
  }
  
}

?>