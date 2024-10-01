<?php
namespace cloudgrayau\cleantalk\helpers;

class SettingsHelper {
  
  private static $integrationList = [
    'forms' => [
      'formie' => [
        'title' => 'Formie',
        'url' => 'https://plugins.craftcms.com/formie'
      ],
      'freeform' => [
        'title' => 'Freeform',
        'url' => 'https://plugins.craftcms.com/freeform'
      ],
      'contact-form' => [
        'title' => 'Contact Form',
        'url' => 'https://plugins.craftcms.com/contact-form'
      ],
      'wheelform' => [
        'title' => 'Wheel Form',
        'information' => '(&gt;= 4.0.2)',
        'url' => 'https://plugins.craftcms.com/wheelform'
      ],
      'express-forms' => [
        'title' => 'Express Forms',
        'information' => '(no longer maintained)',
        'url' => 'https://plugins.craftcms.com/express-forms'
      ]
    ],
    'comments' => [
      'comments' => [
        'title' => 'Comments',
        'url' => 'https://plugins.craftcms.com/comments'
      ],
    ]
  ];
  
  // Public Methods
  // =========================================================================
    
  public static function getIntegrations(): array {
    return self::$integrationList;
  }
  
  public static function mapSettings($n): string {
    return (isset($n[0])) ? $n[0] : '';
  }
  
}