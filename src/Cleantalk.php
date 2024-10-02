<?php
namespace cloudgrayau\cleantalk;
use cloudgrayau\cleantalk\models\Settings;
use cloudgrayau\cleantalk\controllers\SettingsController;
use cloudgrayau\cleantalk\services\FirewallService;
use cloudgrayau\cleantalk\services\AntiSpamService;
use cloudgrayau\cleantalk\assetbundles\CleantalkAsset;
use cloudgrayau\utils\UtilityHelper;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\web\Application;
use craft\web\View;
use yii\base\Event;

class Cleantalk extends Plugin {

  public static $plugin;
  public string $schemaVersion = '1.0.1';
  public bool $hasCpSettings = true;
  public bool $hasCpSection = false;
  
  // Public Methods
  // =========================================================================
  
  public function init(): void {
    parent::init();
    self::$plugin = $this;
    $this->_registerComponents();
    $this->_parseSettings();
    $this->_registerInit();
    if (Craft::$app->getRequest()->getIsCpRequest()){
      $this->_registerCpUrlRules();
    }
  }
  
  public function getSettingsResponse(): mixed {
    return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('cleantalk/settings'));
  }
  
  public static function config(): array {
    return [
      'components' => [
        'firewall' => ['class' => FirewallService::class],
        'antiSpam' => ['class' => AntiSpamService::class]
      ],
    ];
  }
  
  public static function checkSpam(array $params): bool {
    if (self::$plugin->settings->apiKey){
      return self::$plugin->antiSpam->checkSpam($params);
    }
    return true;
  }
  
  // Private Methods
  // =========================================================================
  
  private function _registerComponents(): void {
    UtilityHelper::registerModule();
  }
  
  private function _registerInit(): void {    
    Craft::$app->on(Application::EVENT_INIT, function() {
      if (!$this->settings->apiKey || Craft::$app->getRequest()->getIsCpRequest() || Craft::$app->getRequest()->getIsConsoleRequest()){
        return;
      }
      if ($this->settings->enableUserRegistration && Craft::$app->getEdition()){
        $this->antiSpam->initRegistration();
      }
      /*if ($this->settings->enableFirewall){
        $this->firewall->initFirewall();
      }*/
      if (!empty($this->settings->integrations)){
        $this->antiSpam->initIntegrations();
      }
    });
    Event::on(View::class, View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE, function (\craft\events\TemplateEvent $event) {
      if ($this->settings->apiKey && Craft::$app->getRequest()->getIsSiteRequest()){
        $view = Craft::$app->getView();
        $view->registerAssetBundle(CleantalkAsset::class);
      }
    });
  }
  
  private function _registerCpUrlRules(): void {
    Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
      $event->rules += [
        'cleantalk/settings' => 'cleantalk/settings/settings'
      ];
    });
  }
  
  private function _parseSettings(): void { /* Fix table-based settings from config file */
    $settings = Craft::$app->config->getConfigFromFile('cleantalk');
    foreach(['blockedEmails','blockedIPs','allowedEmails','allowedIPs'] as $option){
      if (isset($settings[$option])){
        $data = [];
        foreach($settings[$option] as $row){
          $row = (array)$row;
          if (isset($row[0]) && !empty(trim($row[0]))){
            $data[] = array(trim($row[0]));
          }
        }
        $this->settings[$option] = $data;
      }
    }
  }

  // Protected Methods
  // =========================================================================
  
  protected function createSettingsModel(): Settings {
    return new Settings();
  }
   
}
