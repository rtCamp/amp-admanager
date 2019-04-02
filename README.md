# amp-admanager
AMP ads for all WordPress sites (AMP and Non-AMP)

## Shortcode Usage

```
[ampad width="<slot-width>" height="<slot-height>" ad-unit="<ad-unit-name>" breakpoint='{"min":"","max":"499","sizes":"320x50,300x100,300x50"}']
```

## Call Ad in Template

```php
$attr = [
    'width'      => '<slot-width>',
    'height'     => '<slot-height>',
    'ad-unit'    => '<ad-unit-name>',
    'breakpoint' => [
        'min'   => '',
        'max'   => '499',
        'sizes' => '320x50,300x100',
    ],
];

AMP_AdManager\AMP_AdManager::get_amp_ad( $attr, true );
```
