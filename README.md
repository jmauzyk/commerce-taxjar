<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="TaxJar icon"></p>

<h1 align="center">TaxJar for Craft Commerce</h1>

This plugin provides a tax integration between [Craft Commerce](https://craftcms.com/commerce) and [TaxJar](https://www.taxjar.com/).

## Requirements

This plugin requires Craft CMS 3.1.20 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “TaxJar”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/commerce-taxjar

# tell Craft to install the plugin
./craft install/plugin commerce-taxjar
```

## Configuration

This plugin will replace the built-in tax engine in Craft Commerce. 
Any current tax rates you have configured will be ignored (hidden), as well as tax zones.

Tax categories are still used but you can not create any manually, you must sync the tax categories 
from TaxJar. Press the sync button at the top of the tax categories index page to bring down the latest categories.

Once you have the TaxJar tax categories, you can decide to edit them and add them to your product types.

The plugin will use your Store Location as the 'From' address supplied to TaxJar, so make 
sure to set that up.

To see all the data from the TaxJar API response, take a look in the `sourceSnapshot` variable 
within the adjustment created. 