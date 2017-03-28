# Yii2 component for sending SMS messages via SMS.RU service
## Install by composer
composer require sem-soft/yii2-smsru
## Or add this code into require section of your composer.json and then call composer update in console
"sem-soft/yii2-smsru": "*"
## Usage
In configuration file do
```php
return[
  ...
  'components' => [
      ...
      'sms' =>  [
        'class' =>  'sem\components\smsru\Sms',
        'api_id'=>  '<api_code_hash>',
        'oneSmsCost'=>	0.94
      ],
      ...
  ],
  ...
];
 ```
 In code use example
 ```php
 $smsResponse = Yii::$app->sms->send("8903XXXXXXX",  "Hello, Guy!");
 ```
