<?php
namespace cloudgrayau\cleantalk\assetbundles;

use Craft;
use cloudgrayau\cleantalk\Cleantalk;
use craft\web\AssetBundle;

class CleantalkAsset extends AssetBundle {

  public function init(): void {
    $this->sourcePath = "@cloudgrayau/cleantalk/resources";
    $this->js = [];
    if (Cleantalk::$plugin->settings->enableJS){
      $this->js[] = 'ct.js';
    }
    if (Cleantalk::$plugin->settings->enableBotDetector){
      $this->js[] = 'https://moderate.cleantalk.org/ct-bot-detector-wrapper.js';
    }
    parent::init();
  }
  
}