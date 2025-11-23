<?php
namespace cloudgrayau\cleantalk\integrations;
use cloudgrayau\cleantalk\Cleantalk;

use yii\base\Event;

class FormieIntegration {

  public function parse(): void {
    Event::on(\verbb\formie\services\Submissions::class, \verbb\formie\services\Submissions::EVENT_AFTER_SPAM_CHECK, function(\verbb\formie\events\SubmissionSpamCheckEvent $e){
      $params = [
        'message' => []
      ];
      foreach($e->submission->form->getCustomFields() as $field){
        switch(get_class($field)){
          case 'verbb\formie\fields\formfields\Name':
          case 'verbb\formie\fields\Name':
            $params['name'] = (string)$e->submission->getFieldValue($field->handle);
            break;
          case 'verbb\formie\fields\formfields\Email':
          case 'verbb\formie\fields\Email':
            $params['email'] = (string)$e->submission->getFieldValue($field->handle);
            break;
          case 'verbb\formie\fields\formfields\Phone':
          case 'verbb\formie\fields\Phone':
            $params['phone'] = (string)$e->submission->getFieldValue($field->handle);
            break;
          case 'verbb\formie\fields\formfields\MultiLineText':
          case 'verbb\formie\fields\MultiLineText':
            $params['message'][] = (string)$e->submission->getFieldValue($field->handle);
            break;
        }
      }
      if (!Cleantalk::$plugin->antiSpam->checkSpam($params)){
        $e->submission->isSpam = true;
      }
    });
  }
  
}

?>