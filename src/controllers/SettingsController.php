<?php
namespace cloudgrayau\cleantalk\controllers;

use cloudgrayau\cleantalk\Cleantalk;
use cloudgrayau\cleantalk\models\Settings;

use Craft;
use craft\web\Controller;

use yii\web\Response;

class SettingsController extends Controller {
    
  // Public Methods
  // =========================================================================

  public function actionSettings(): Response {
    $settings = Cleantalk::$plugin->getSettings();
    return $this->renderTemplate('cleantalk/settings', [
      'settings' => $settings,
    ]);
  }

}