# CleanTalk for Craft CMS

CleanTalk Anti-Spam for Craft CMS.

![Screenshot](resources/cleantalk.png)

## Requirements

This plugin requires Craft CMS 4.0.0 or later.

## Installation

`composer require cloudgrayau/cleantalk`

## CleanTalk Overview

CleanTalk is an anti-spam utility for protecting forms, user registrations and comments in Craft CMS.

A valid API key from the [CleanTalk Service](https://cleantalk.org/) is required to use this plugin.

## Protection

The CleanTalk plugin protects the following services from spam and includes an optional bot detector script that offers greater spam detection.

### User Registration Protection

Protects user registrations from spam.

### Form Protection

Protects form submissions from spam. The current form integrations are protected:

**✓ Formie** - [https://plugins.craftcms.com/formie](https://plugins.craftcms.com/formie)  
**✓ Freeform** - [https://plugins.craftcms.com/freeform](https://plugins.craftcms.com/freeform)
**✓ Contact Form** - [https://plugins.craftcms.com/contact-form](https://plugins.craftcms.com/contact-form) 
**✓ Wheel Form** (> 4.0.2) - [https://plugins.craftcms.com/wheelform](https://plugins.craftcms.com/wheelform) 
**✓ Express Forms** (no longer maintained) - [https://plugins.craftcms.com/express-forms](https://plugins.craftcms.com/express-forms)  
**✓ Custom Forms** - requires custom programming

### Comment Protection

Protects comment submissions from spam. The current comment integrations are protected:

**✓ Comments** - [https://plugins.craftcms.com/comments](https://plugins.craftcms.com/comments)  
**✓ Custom Comments** - requires custom programming

### Firewall Protection

Coming soon in a later release.

## Custom Protection

Any form or comment logic can be protected by CleanTalk via a custom plugin/module controller.

    <?php    
    $params = [
      'name' => '<NAME>',
      'email' => '<EMAIL>',
      'phone' => '<PHONE>',
      'message' => '<MESSAGE>'
    ];
    if (\cloudgrayau\cleantalk\Cleantalk::checkSpam($params)){ /* passed */
    } else { /* failed */
      $errormsg = \cloudgrayau\cleantalk\Cleantalk::$plugin->antiSpam->error;
    }
    ?>

Brought to you by [Cloud Gray Pty Ltd](https://cloudgray.com.au/)