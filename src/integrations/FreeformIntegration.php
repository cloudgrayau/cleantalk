<?php
namespace cloudgrayau\cleantalk\integrations;
use cloudgrayau\cleantalk\Cleantalk;

use yii\base\Event;

class FreeformIntegration {

  public function parse(): void {
    Event::on(\Solspace\Freeform\Form\Form::class, \Solspace\Freeform\Form\Form::EVENT_SUBMIT, function (\Solspace\Freeform\Events\Forms\SubmitEvent $e){      
      $params = [
        'name' => [],
        'message' => []
      ];
      foreach($e->getForm()->getFields() as $field){
        switch(get_class($field)){
          case 'Solspace\Freeform\Fields\Implementations\TextField':
            switch(true){
              case (stristr($field->getHandle(), 'name')):
                $params['name'][] = $field->getValue();
                break;
              case (stristr($field->getHandle(), 'phone')):
                $params['phone'] = $field->getValue();
                break;
            }
            break;
          case 'Solspace\Freeform\Fields\Implementations\EmailField':
            $params['email'] = $field->getValue();
            break;
          case 'Solspace\Freeform\Fields\Implementations\TextareaField':
            $params['message'][] = $field->getValue();
            break;
        }
      }
      if (!Cleantalk::$plugin->antiSpam->checkSpam($params)){
        $e->getForm()->markAsSpam('Cleantalk', Cleantalk::$plugin->antiSpam->error);
      }
    });
  }
  
}

?>