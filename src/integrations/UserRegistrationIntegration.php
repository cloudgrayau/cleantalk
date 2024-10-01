<?php
namespace cloudgrayau\cleantalk\integrations;
use cloudgrayau\cleantalk\Cleantalk;

use craft\elements\User;
use craft\events\ModelEvent;
use yii\base\Event;

class UserRegistrationIntegration {
  
  public function parse(): void {
    Event::on(User::class, User::EVENT_BEFORE_SAVE, function (ModelEvent $e){
      $user = $e->sender;
      if ($user->firstSave){
        $params = [
          'name' => $e->sender->fullName ?? '',
          'email' => $e->sender->email ?? ''
        ];
        if (!Cleantalk::$plugin->antiSpam->checkRegistration($params)){
          $e->isValid = false;
        }
      }
    });
  }
  
}

?>