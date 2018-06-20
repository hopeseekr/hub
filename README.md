# BrainActs HUB for Magento 2



[![Latest Stable Version](https://poser.pugx.org/brainacts/hub/v/stable)](https://packagist.org/packages/brainacts/hub)
[![Total Downloads](https://poser.pugx.org/brainacts/hub/downloads)](https://packagist.org/packages/brainacts/hub)


## How to install & upgrade BrainActs_Hub


### 1. Install via composer (recommend)

We recommend you to install BrainActs_Hub module via composer. It is easy to install, update and maintenance.

Run the following command in Magento 2 root folder.

#### 1.1 Install

```
composer require brainacts/hub
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

#### 1.2 Upgrade

```
composer update brainacts/hub
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

Run compile if your store in Product mode:

```
php bin/magento setup:di:compile
```

### 2. Copy and paste

If you don't want to install via composer, you can use this way. 

- Download [the latest version here](https://github.com/brainacts/hub/archive/master.zip) 
- Extract `master.zip` file to `app/code/BrainActs/Hub` ; You should create a folder path `app/code/BrainActs/Hub` if not exist.
- Go to Magento root folder and run upgrade command line to install `BrainActs_Hub`:

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```