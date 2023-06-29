Brevo (ex SendInBlue) for Thelia
===
Author: 
- Chabreuil Antoine <achabreuil@openstudio.fr>

This module keep a Brevo contact list of your choice synchronized whith the newsletter subscriptions and unsubscriptions 
on your shop :

- When a user subscribe to your newsletter on your shop, it is automatically added to the Brevo contact list.

- When a user unsubscribe from your list, it is also deleted from the Brevo contact list. The user is nor deleted from Brevo contacts, but is only removed from the contact list.

The module is based on the [APIv3 Documentation of Brevo](https://developers.brevo.com/docs).

0. Prerequisites

You must have a Brevo account and have created a newsletter list.

You'll also need your Secret key. You'll find them in your Brevo account. 
 
1. Installation

There is two ways to install the brevo module:
- Download the zip archive of the file, import it from your backoffice or extract it in ```thelia/local/modules```
- require it with composer:
```
"require": {
    "thelia/brevo-module": "~1.0"
}
```
    
Then go to the configuration panel, give your API key. Then enter your contact list id into the 2nd field.

Save and you're done !