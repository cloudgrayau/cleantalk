<?php
namespace cloudgrayau\cleantalk\assetbundles;

use cloudgrayau\cleantalk\Cleantalk;
use craft\web\AssetBundle;

class CleantalkAsset extends AssetBundle {
    
    // Public Methods
    // =========================================================================

    public function init(): void {
      $this->sourcePath = "@cloudgrayau/cleantalk/resources";
      if (\Craft::$app->getRequest()->getIsCpRequest()){
        $this->js = ['ct.js'];
      } else {
        $this->js = [(Cleantalk::$plugin->settings->enableBotDetector) ? 'ctb.js' : 'ct.js'];
      }
      parent::init();
    }
}
