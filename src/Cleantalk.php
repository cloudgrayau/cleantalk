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
  public string $schemaVersion = '1.0.0';
  public bool $hasCpSettings = true;
  public bool $hasCpSection = false;
  
  // Public Methods
  // =========================================================================
  
  public function init(): void {
    parent::init();
    self::$plugin = $this;
    $this->_registerComponents();
    $this->_registerInit();
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
  
  public static function checkForm($params): bool {
    if (self::$plugin->settings->apiKey){
      return self::$plugin->antiSpam->checkForm($params);
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
      if (Craft::$app->getRequest()->getIsCpRequest()){$this->_registerCpUrlRules();}
      if (!$this->settings->apiKey){
        return;
      }
      if ($this->settings->enableUserRegistration){$this->antiSpam->initRegistration();}
      if (Craft::$app->getRequest()->getIsCpRequest()){
        return;
      }
      if ($this->settings->enableFirewall){$this->firewall->initFirewall();}
      if ($this->settings->enableForms){$this->antiSpam->initForms();}
      if ($this->settings->enableComments){$this->antiSpam->initComments();}
    });
    Event::on(View::class, View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE, function (\craft\events\TemplateEvent $event) {
      if (Craft::$app->getRequest()->getIsSiteRequest() || Craft::$app->getRequest()->getIsCpRequest()){
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

  // Protected Methods
  // =========================================================================
  
  protected function createSettingsModel(): Settings {
    return new Settings();
  }
   
}
