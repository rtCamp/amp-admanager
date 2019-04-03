# amp-admanager
AMP ads for all WordPress sites (AMP and Non-AMP)

## Usage

#### As Shortcode

```
[ampad width="<slot-width>" height="<slot-height>" ad-unit="<ad-unit-name>" min="" max="499" sizes="320x50,300x100,300x50"]
```

#### In Template

```php
$attr = [
    'width'   => '<slot-width>',
    'height'  => '<slot-height>',
    'ad-unit' => '<ad-unit-name>',
    'min'     => '',
    'max'     => '499',
    'sizes'   => '320x50,300x100',
];

AMP_AdManager\AMP_AdManager::get_amp_ad( $attr, true );
```

## Development Flow

* Create new branch from 'master'.
* Name it appropriately, i.e., `feature/GH-<issue-number>-<name>`,`<context>/GH-<issue-number>-<what_it_is>`.
* Create PR against `master`.
* Request review for your changes and get approvals.

## Authors

* **Pradeep Sonawane**
* **Vishal Dodiya**
