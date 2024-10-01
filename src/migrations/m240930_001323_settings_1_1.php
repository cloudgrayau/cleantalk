<?php

namespace cloudgrayau\cleantalk\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240930_001323_settings_1_1 migration.
 */
class m240930_001323_settings_1_1 extends Migration {
  
  public function safeUp(): bool {
    $schemaVersion = Craft::$app->getProjectConfig()->get('plugins.cleantalk.schemaVersion', true);
    if (version_compare($schemaVersion, '1.0.1', '<')) {
      Craft::$app->getProjectConfig()->remove('plugins.cleantalk.settings.enableForms');
      Craft::$app->getProjectConfig()->remove('plugins.cleantalk.settings.enableComments');
      Craft::$app->getProjectConfig()->set('plugins.cleantalk.settings.integrations', [
        'formie',
        'freeform',
        'contact-form',
        'wheelform',
        'express-forms',
        'comments'
      ]);
    }
    return true;
  }

  public function safeDown(): bool {
    echo "m240930_001323_settings_1_1 cannot be reverted.\n";
    return false;
  }
  
}
