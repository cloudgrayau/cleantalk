<?php
namespace cloudgrayau\cleantalk\services;

use cloudgrayau\cleantalk\Cleantalk;

use Craft;
use craft\base\Component;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\elements\User;
use craft\events\ModelEvent;

use yii\base\Event;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AntiSpamService extends Component {
  
  public const AGENT = 'Craft CMS';
  public string $error = '';
  
  // Public Methods
  // =========================================================================
  
  public function initForms(): void {
    if (Craft::$app->plugins->isPluginEnabled('formie')){
      Event::on(\verbb\formie\services\Submissions::class, \verbb\formie\services\Submissions::EVENT_AFTER_SPAM_CHECK, function(\verbb\formie\events\SubmissionSpamCheckEvent $e) {
        $submission = $e->submission;
        $params = array();
        foreach($submission->form->getCustomFields() as $field){
          switch(get_class($field)){
            case 'verbb\formie\fields\formfields\Name':
              $params['name'] = (string)$submission->getFieldValue($field->handle);
              break;
            case 'verbb\formie\fields\formfields\Email':
              $params['email'] = (string)$submission->getFieldValue($field->handle);
              break;
            case 'verbb\formie\fields\formfields\Phone':
              $params['phone'] = (string)$submission->getFieldValue($field->handle);
              break;
            case 'verbb\formie\fields\formfields\MultiLineText':
              $params['message'][] = (string)$submission->getFieldValue($field->handle);
              break;
          }
        }
        if (isset($_POST['ct_bot_detector_event_token'])){
          $params['token'] = $_POST['ct_bot_detector_event_token'];
        }
        if (!$this->checkMessage($params)){
          $e->submission->isSpam = true;
        }
      });
    }
    if (Craft::$app->plugins->isPluginEnabled('freeform')){
      Event::on(\Solspace\Freeform\Form\Form::class, \Solspace\Freeform\Form\Form::EVENT_BEFORE_VALIDATE, function (\Solspace\Freeform\Events\Forms\ValidationEvent $e){
        $form = $e->getForm();
        $params = array();
        foreach($form->getFields() as $field){
          switch(get_class($field)){
            case 'Solspace\Freeform\Fields\Implementations\TextField':
              switch(true){
                case (strstr($field->getHandle(), 'name')):
                  $params['name'][] = $field->getValue();
                  break;
                case (strstr($field->getHandle(), 'phone')):
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
        if (isset($_POST['ct_bot_detector_event_token'])){
          $params['token'] = $_POST['ct_bot_detector_event_token'];
        }
        if (!$this->checkMessage($params)){
          $form->markAsSpam('Cleantalk', $this->error);
        }
      });
    }
    if (Craft::$app->plugins->isPluginEnabled('express-forms')){
      Event::on(\Solspace\ExpressForms\models\Form::class, \Solspace\ExpressForms\models\Form::EVENT_VALIDATE_FORM, function(\Solspace\ExpressForms\events\forms\FormValidateEvent $e){
        $form = $e->getForm();
        $params = array();
        foreach($form->getFields() as $field){
          switch(get_class($field)){
            case 'Solspace\ExpressForms\fields\Text':
              switch(true){
                case (strstr($field->handle, 'name')):
                  $params['name'][] = $field->getValue();
                  break;
                case (strstr($field->handle, 'phone')):
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
        if (isset($_POST['ct_bot_detector_event_token'])){
          $params['token'] = $_POST['ct_bot_detector_event_token'];
        }
        if (!$this->checkMessage($params)){
          $form->markAsSpam();
        }
      });
    }
    /*if (Craft::$app->plugins->isPluginEnabled('wheelform')){
      Event::on(\wheelform\controllers\MessageController::class, \wheelform\controllers\MessageController::EVENT_BEFORE_SAVE, function($e){
        $e->sender->returnModel()->save_entry = false;
        $e->sender->returnModel()->send_email = false;
      });  
    }*/
    if (Craft::$app->plugins->isPluginEnabled('contact-form')){
      Event::on(\craft\contactform\Mailer::class, \craft\contactform\Mailer::EVENT_BEFORE_SEND, function(\craft\contactform\events\SendEvent $e){
        $submission = $e->submission;
        $params = array(
          'email' => $submission['fromEmail'] ?? '',
          'name' => $submission['fromName'] ?? '',
          'phone' => $submission['phone'] ?? '',
          'subject' => $submission['subject'] ?? '',
          'message' => $submission['message'] ?? ''
        );
        if (isset($_POST['ct_bot_detector_event_token'])){
          $params['token'] = $_POST['ct_bot_detector_event_token'];
        }
        if (!$this->checkMessage($params)){
          $e->isSpam = true; 
        }
      });
    }
  }
  
  public function initRegistration(): void {
    Event::on(User::class, User::EVENT_BEFORE_SAVE, function (ModelEvent $e){
      if ($e->sender->firstSave){
        $params = array(
          'email' => $e->sender->email ?? '',
          'name' => $e->sender->fullName ?? ''
        );
        if (isset($_POST['ct_bot_detector_event_token'])){
          $params['token'] = $_POST['ct_bot_detector_event_token'];
        }
        if (!$this->checkUser($params)){
          $e->isValid = false;
        }
      }
    });
  }
  
  public function initComments(): void {
    if (Craft::$app->plugins->isPluginEnabled('comments')){
      Event::on(\verbb\comments\elements\Comment::class, \verbb\comments\elements\Comment::EVENT_BEFORE_SAVE, function(ModelEvent $e){
        $comment = $e->sender;
        if ($comment->firstSave){
          $params = array();
          if ($comment->userId){
            $identity = Craft::$app->getUser()->getIdentity();
            $params['name'] = $identity->fullName;
            $params['email'] = $identity->email;
          } else {
            $params['name'] = $comment->name;
            $params['email'] = $comment->email;
          }
          $params['message'] = $comment->getComment();
          if (isset($_POST['ct_bot_detector_event_token'])){
            $params['token'] = $_POST['ct_bot_detector_event_token'];
          }
          if (!$this->checkMessage($params)){
            $e->isValid = false; 
          }
        }
      });
    }
  }
  
  public function checkForm($params): bool {
    if (isset($_POST['ct_bot_detector_event_token'])){
      $params['token'] = $_POST['ct_bot_detector_event_token'];
    }
    return $this->checkMessage($params);
  }
  
  // Private Methods
  // =========================================================================
  
  private function checkMessage($arg): bool {
    $name = $arg['name'] ?? '';
    if (is_array($name)){
      $name = implode(' ', $arg['name']);
    }
    $message = $arg['message'] ?? '';
    if (is_array($message)){
      $message = implode(' ', $arg['message']);
    }
    $params = array(
      'method_name' => 'check_message',
      'auth_key' => Cleantalk::$plugin->settings->apiKey,
      'agent' => self::AGENT,
      'sender_email' => $arg['email'] ?? '',
      'sender_nickname' => $name,
      'sender_ip' => $arg['ip'] ?? Craft::$app->getRequest()->userIP,
      'phone' => $arg['phone'] ?? '',
      'message' => $message,
      'js_on' => 1,
      'sender_info' => json_encode(array(
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'referer' => $_SERVER['HTTP_REFERER'] ?? ''
      )),
      'all_headers' => json_encode(Craft::$app->getRequest()->getHeaders()->toArray()),
      'response_lang' => 'en'
    );
    if (Cleantalk::$plugin->settings->enableBotDetector){
      $params['event_token'] = $arg['token'] ?? '';
    }
    if (isset($_POST['ctv_'])){
      $dateTime = new \DateTime('now', new \DateTimeZone('Etc/GMT'));
      $params['submit_time'] = intval($dateTime->format('U') - (int)$_POST['ctv_']);
    } else {
      $params['js_on'] = 0;
    }
    return $this->ctRequest($params);
  }
  
  private function checkUser($arg): bool {
    $params = array(
      'method_name' => 'check_newuser',
      'auth_key' => Cleantalk::$plugin->settings->apiKey,
      'agent' => self::AGENT,
      'sender_email' => $arg['email'] ?? '',
      'sender_nickname' => $arg['name'],
      'sender_ip' => $arg['ip'] ?? Craft::$app->getRequest()->userIP,
      'js_on' => 1,
      'sender_info' => json_encode(array(
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'referer' => $_SERVER['HTTP_REFERER'] ?? ''
      )),
      'all_headers' => json_encode(Craft::$app->getRequest()->getHeaders()->toArray()),
      'response_lang' => 'en'
    );
    if (Cleantalk::$plugin->settings->enableBotDetector){
      $params['event_token'] = $arg['token'] ?? null;
    }
    if (isset($_POST['ctv_'])){
      $dateTime = new \DateTime('now', new \DateTimeZone('Etc/GMT'));
      $params['submit_time'] = intval($dateTime->format('U') - (int)$_POST['ctv_']);
    } else {
      $params['js_on'] = 0;
    }
    return $this->ctRequest($params);
  }
  
  private function ctRequest($params): bool {
    $client = new Client([
      'base_uri' => 'https://moderate.cleantalk.org',
    ]);
    try {
      $response = $client->request('POST', '/api2.0', [
        'json' => $params,
        'headers' => [
          'Content-Type' => 'application/json'
        ]
      ]);
      $result = json_decode($response->getBody()->getContents(), true);
      if ($result['allow'] == 1){
        return true;
      } else {
        $this->error = $result['comment'];
      }
      return false;
    } catch (GuzzleException $e) {
      return true;
    }
  }
  
}
