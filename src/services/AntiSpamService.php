<?php
namespace cloudgrayau\cleantalk\services;

use cloudgrayau\cleantalk\Cleantalk;
use cloudgrayau\cleantalk\helpers\SettingsHelper;

use Craft;
use craft\base\Component;
use craft\helpers\App;
use craft\helpers\StringHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AntiSpamService extends Component {
  
  public const AGENT = 'Craft CMS';
  public string $api_version = '/api2.0';
  public string $error = '';
  
  // Public Methods
  // =========================================================================
  
  public function initRegistration(): void {
    $className = '\cloudgrayau\cleantalk\integrations\UserRegistrationIntegration';
    $obj = new $className();
    $obj->parse();
  }
  
  public function initIntegrations(): void {
    $integrations = SettingsHelper::getIntegrations();
    foreach($integrations as $type => $object){
      $objects = array_keys($object);
      foreach($objects as $integration){
        if (in_array($integration, Cleantalk::$plugin->settings->integrations)){
          if (Craft::$app->plugins->isPluginEnabled($integration)){
            $classes = explode('-',$integration);
            $class = implode('', array_map(function($n){
              return ucfirst($n);
            }, $classes));
            $className = '\cloudgrayau\cleantalk\integrations\\'.$class.'Integration';
            $obj = new $className();
            $obj->parse();
          }
        }
      }
    }
  }
  
  public function checkSpam(array $params): bool {
    $name = $params['name'] ?? '';
    $message = $params['message'] ?? '';    
    $params['name'] = (is_array($name)) ? StringHelper::trim(implode(' ', $name)) : StringHelper::trim($name);
    $params['email'] = StringHelper::trim($params['email'] ?? '');
    $params['message'] = (is_array($message)) ? StringHelper::trim(implode('; ', $message)) : StringHelper::trim($message);
    $params['ip'] = Craft::$app->request->getUserIP();
    $params['token'] = Craft::$app->getRequest()->getBodyParam('ct_bot_detector_event_token');
    
    /* DO MANUAL CHECK */
    $blockedEmails = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->blockedEmails);
    $blockedIPs = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->blockedIPs);
    $allowedEmails = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->allowedEmails);
    $allowedIPs = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->allowedIPs);
    if (in_array($params['email'], $allowedEmails) || in_array($params['ip'], $allowedIPs)){
      return true;
    }
    if (in_array($params['email'], $blockedEmails) || in_array($params['ip'], $blockedIPs)){
      $this->error = 'Blocked by CleanTalk manual rules';
      return false;
    }
    return $this->checkMessage($params);
  }
  
  public function checkRegistration(array $params): bool {
    $params['name'] = StringHelper::trim($params['name'] ?? '');
    $params['email'] = StringHelper::trim($params['email'] ?? '');
    $params['ip'] = Craft::$app->request->getUserIP();
    $params['token'] = Craft::$app->getRequest()->getBodyParam('ct_bot_detector_event_token');
    
    /* DO MANUAL CHECK */
    $blockedEmails = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->blockedEmails);
    $blockedIPs = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->blockedIPs);
    $allowedEmails = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->allowedEmails);
    $allowedIPs = array_map(['\cloudgrayau\cleantalk\helpers\SettingsHelper', 'mapSettings'], Cleantalk::$plugin->settings->allowedIPs);
    if (in_array($params['email'], $allowedEmails) || in_array($params['ip'], $allowedIPs)){
      return true;
    }
    if (in_array($params['email'], $blockedEmails) || in_array($params['ip'], $blockedIPs)){
      $this->error = 'Blocked by CleanTalk manual rules';
      return false;
    }
    return $this->checkUser($params);
  }
  
  // Private Methods
  // =========================================================================
  
  private function checkMessage(array $arg): bool {
    $params = [
      'method_name' => 'check_message',
      'auth_key' => App::parseEnv(Cleantalk::$plugin->settings->apiKey),
      'agent' => self::AGENT,
      'sender_email' => $arg['email'],
      'sender_nickname' => $arg['name'],
      'sender_ip' => $arg['ip'],
      'phone' => StringHelper::trim($arg['phone'] ?? ''),
      'message' => $arg['message'],
      'js_on' => 1,
      'sender_info' => json_encode([
        'user_agent' => Craft::$app->request->getUserAgent() ?? '',
        'referer' => Craft::$app->request->getReferrer() ?? ''
      ]),
      'all_headers' => json_encode(Craft::$app->request->getHeaders()->toArray()),
      'response_lang' => 'en'
    ];  
    if (Cleantalk::$plugin->settings->enableBotDetector){
      $params['event_token'] = $arg['token'] ?? '';
    }
    if (Craft::$app->getRequest()->getBodyParam('ctv_')){
      $dateTime = new \DateTime('now', new \DateTimeZone('Etc/GMT'));
      $params['submit_time'] = intval($dateTime->format('U') - (int)Craft::$app->getRequest()->getBodyParam('ctv_'));
    } else {
      $params['js_on'] = $arg['js'] ?? 0;
    }
    return $this->ctRequest($params);
  }
  
  private function checkUser(array $arg): bool {
    $params = [
      'method_name' => 'check_newuser',
      'auth_key' => Cleantalk::$plugin->settings->apiKey,
      'agent' => self::AGENT,
      'sender_email' => $arg['email'],
      'sender_nickname' => $arg['name'],
      'sender_ip' => $arg['ip'],
      'js_on' => 1,
      'sender_info' => json_encode([
        'user_agent' => Craft::$app->request->getUserAgent() ?? '',
        'referer' => Craft::$app->request->getReferrer() ?? ''
      ]),
      'all_headers' => json_encode(Craft::$app->request->getHeaders()->toArray()),
      'response_lang' => 'en'
    ];
    if (Cleantalk::$plugin->settings->enableBotDetector){
      $params['event_token'] = $arg['token'] ?? null;
    }
    if (Craft::$app->getRequest()->getBodyParam('ctv_')){
      $dateTime = new \DateTime('now', new \DateTimeZone('Etc/GMT'));
      $params['submit_time'] = intval($dateTime->format('U') - (int)Craft::$app->getRequest()->getBodyParam('ctv_'));
    } else {
      $params['js_on'] = $arg['js'] ?? 0;
    }
    return $this->ctRequest($params);
  }
  
  private function ctRequest(array $params): bool {
    $client = new Client([
      'base_uri' => 'https://moderate.cleantalk.org',
    ]);
    try {
      $response = $client->request('POST', ($this->api_version == '/api3.0') ? ($this->api_version.'/'.$params['method_name']) : $this->api_version, [
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
      $this->error = $e->getMessage();
      return false;
    }
  }
  
}