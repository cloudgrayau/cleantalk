<?php
namespace cloudgrayau\cleantalk\integrations;
use cloudgrayau\cleantalk\Cleantalk;

use Craft;
use craft\helpers\StringHelper;
use yii\base\Event;

class WheelformIntegration {

  public function parse(): void {
    $plugin = Craft::$app->plugins->getPlugin('wheelform');
    if ((int)StringHelper::replace($plugin->getVersion(), '.', '') >= 402){
      Event::on(\wheelform\controllers\MessageController::class, \wheelform\controllers\MessageController::EVENT_BEFORE_SAVE, function(\wheelform\events\MessageEvent $e){
        $params = [
          'name' => [],
          'message' => []
        ];
        foreach($e->message as $obj) {
          switch($obj->field->type){
            case 'text':
              switch(true){
                case (stristr($obj->field->name, 'name')):
                  $params['name'][] = $obj->value;
                  break;
                case (stristr($obj->field->name, 'phone')):
                  $params['phone'] = $obj->value;
                  break;
                case (stristr($obj->field->name, 'message')):
                  $params['message'] = $obj->value;
                  break;
              }
              break;
            case 'email':
              $params['email'] = $obj->value;
              break;
            case 'textarea':
              $params['message'][] = $obj->value;
              break;
          }
        }
        if (!Cleantalk::$plugin->antiSpam->checkSpam($params)){
          $e->sendMessage = false;
          $e->saveMessage = false;
        }
      });
    }
  }
  
}

?>