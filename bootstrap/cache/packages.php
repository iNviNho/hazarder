<?php return array (
  'fideloper/proxy' => 
  array (
    'providers' => 
    array (
      0 => 'Fideloper\\Proxy\\TrustedProxyServiceProvider',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'laravelcollective/html' => 
  array (
    'providers' => 
    array (
      0 => 'Collective\\Html\\HtmlServiceProvider',
    ),
    'aliases' => 
    array (
      'Form' => 'Collective\\Html\\FormFacade',
      'Html' => 'Collective\\Html\\HtmlFacade',
    ),
  ),
  'sentry/sentry-laravel' => 
  array (
    'providers' => 
    array (
      0 => 'Sentry\\SentryLaravel\\SentryLaravelServiceProvider',
    ),
    'aliases' => 
    array (
      'Sentry' => 'Sentry\\SentryLaravel\\SentryFacade',
    ),
  ),
);