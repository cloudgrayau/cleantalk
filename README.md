# CleanTalk for Craft CMS 4+

CleanTalk Anti-Spam for Craft CMS.

![Screenshot](resources/cleantalk.png)

## Requirements

This plugin requires Craft CMS 4.0.0 or later.

## Installation

`composer require cloudgrayau/cleantalk`

## CleanTalk Overview

CleanTalk is an anti-spam utility for protecting forms, user registrations and comments in Craft CMS. A valid API key from the CleanTalk service is required to use this plugin.

## Protection

The CleanTalk plugin protects the following services from spam and includes an option bot detector script that offers greater spam detection.

### User Registration Protection

Protects user registrations from spam.

### Form Protection

Protects form submissions from spam. The current form plugins/systems are automatically protected:

**Formie** - [https://plugins.craftcms.com/formie](https://plugins.craftcms.com/formie) 
**Freeform** - [https://plugins.craftcms.com/freeform](https://plugins.craftcms.com/freeform) 
**Express Forms** - [https://plugins.craftcms.com/express-forms](https://plugins.craftcms.com/express-forms) 
**Contact Form** - [https://plugins.craftcms.com/contact-form](https://plugins.craftcms.com/contact-form) 
**Custom Forms** - requires additional programming

### Comment Protection

Protects comment submissions from spam. The current comment plugins/systems are automatically protected:

**Comments** - [https://plugins.craftcms.com/comments](https://plugins.craftcms.com/comments) 
**Custom Comments** - requires additional programming

### Firewall Protection

Coming soon in a later release.

## Custom Protection

Any custom form or comment logic can be protected by CleanTalk.

    <?php    
    $params = array(
      'name' => '<NAME>',
      'email' => '<EMAIL>',
      'phone' => '<PHONE>',
      'message' => '<MESSAGE>'
    );
    if (\cloudgrayau\cleantalk\Cleantalk::checkForm($params)){
    
      /* passed */
      
    } else {
    
      /* failed */
      $error = \cloudgrayau\cleantalk\Cleantalk::$plugin->antiSpam->error;
      
    }
    ?>

Brought to you by [Cloud Gray Pty Ltd](https://cloudgray.com.au/)