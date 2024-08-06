
# Introduction
Using AppSumo's Licensing gives your customers a seamless way of activating their accounts with your product after purchasing your product on AppSumo.

## - This plugin has embedded JWT token system.
- JNAMESPACE AND ENDPOINTS
When the plugin is activated, a new namespace is added

`/jwt-auth/v1`
Also, two new endpoints are added to this namespace

`Endpoint | HTTP Verb
/wp-json/jwt-auth/v1/token | POST
/wp-json/jwt-auth/v1/token/validate | POST`

- USAGE
`/WP-JSON/JWT-AUTH/V1/TOKEN`
This is the entry point for the JWT Authentication.

Validates the user credentials, username and password, and returns a token to use in a future request to the API if the authentication is correct or error if the authentication fails.

Codes can be found at `core/AppSumoApi.php:69`

# Installation
- Change the values from this config file: `appsumo__gutenkit__licensing/core/config-globals.php`
- Add `<?php do_action('appsumo__gutenkit__footer_userform'); ?>` into your footer to show the user form modal.


# Appsumo Setup
Go to https://appsumo.com/partners/profile/ and fillup the following fields.
- Token URL: https://WEBSITE/wp-json/jwt-auth/v1/token
- Notification URL: https://WEBSITE/wp-json/appsumo__gutenkit__licensing/v1/notification
- Username: WPUSER
- Secret: PASSWORD_FOR_THE_WPUSER
