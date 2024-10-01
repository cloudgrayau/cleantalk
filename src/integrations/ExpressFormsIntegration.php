<?php
namespace cloudgrayau\cleantalk\integrations;
use cloudgrayau\cleantalk\Cleantalk;

use yii\base\Event;

class ExpressFormsIntegration {

  public function parse(): void {
    Event::on(\Solspace\ExpressForms\models\Form::class, \Solspace\ExpressForms\models\Form::EVENT_VALIDATE_FORM, function(\Solspace\ExpressForms\events\forms\FormValidateEvent $e){
      if (!$e->getForm()->isValid()){
        return;
      }
      $params = [
        'name' => [],
        'message' => []
      ];
      foreach($e->getForm()->getFields() as $field){
        switch(get_class($field)){
          case 'Solspace\ExpressForms\fields\Text':
            switch(true){
              case (stristr($field->handle, 'name')):
                $params['name'][] = $field->getValue();
                break;
              case (stristr($field->handle, 'phone')):
                $params['phone'] = $field->getValue();
                break;
            }
            break;
          case 'Solspace\ExpressForms\fields\Email':
            $params['email'] = $field->getValue();
            break;
          case 'Solspace\ExpressForms\fields\Textarea':
            $params['message'][] = $field->getValue();
            break;
        }
      }
      if (!Cleantalk::$plugin->antiSpam->checkSpam($params)){
        $e->getForm()->markAsSpam();
      }
    });
  }
  
}

?>