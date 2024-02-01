Automatic extension - Moodle assignsubmission plugin
====================
![GitHub Workflow Status (branch)](https://img.shields.io/github/actions/workflow/status/catalyst/moodle-assignsubmission_automaticextension/ci.yml?branch=main&label=ci)

This plugin allows students to request an automatic extension. A "Request extension" button will be presented on the assignment view page, pressing the button will take the student to a confirmation page, after confirming an extension will be applied using the site settings.

## Site settings
- **Enabled by default | default** - if the plugin should be enabled by default for new and existing assignments (created before this plugin was installed), allowing students to make extension requests
- **Condition details | conditions** - HTML that will be presented to the student on the confirmation screen when the student requests an extension
- **Maximum requests | maximumrequests** - the number of extension requests a user can make for each assignment (setting this to 0 will disable the plugin)
- **Extension length | extensionlength** - the length of each extension period, each subsequent request will increase the extension by this amount (setting this to 0 will disable the plugin)

## Permissions
There is a single permission, **assignsubmission/automaticextension:requestextension** determines if a user can request an automatic extension. By default this permission is given to the student archetype.