<p align="center">
<a href="https://rtcamp.com/?ref=amp-admanager-repo" target="_blank"><img width="200"src="https://rtcamp.com/wp-content/uploads/2018/04/rtcamp-logo-1.svg"></a>
</p>

# AMP AdManager 1.0.0
[![Project Status: Active – The project has reached a stable, usable state and is being actively developed.](https://www.repostatus.org/badges/latest/active.svg)](https://www.repostatus.org/#active)

AMP ads for all WordPress sites (AMP and Non-AMP). 
This plugin generates `<amp-ad>` code for your ads with breakpoints for responsive display of ad with Single Request Architecture (SRA).
Works without AMP plugin as well for Non-AMP pages. 

**Contributors:** [rtCamp](https://github.com/rtCamp/), [Vishal Dodiya](https://github.com/vishaldodiya), [Pradeep Sonawane](https://github.com/pradeep910), [Sagar Nasit](https://github.com/sagarnasit)

**Tags:** [amp](https://wordpress.org/plugins/tags/amp), [mobile](https://wordpress.org/plugins/tags/mobile)
**Requires at least:** 4.9  
**Tested up to:** 5.1  
**License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)  
**Requires PHP:** 5.4+

# Setup
1. Set network/publisher id in the plugin settings. `Dashboard > AMP AdManager`
2. Select `Load AMP Resources for Non-AMP site` to load `amp-runtime` and `amp-boilerplate` css.

## Usage
Pass different ad sizes as attributes for mobile, tablet, and desktop ads. The plugin uses the width of the specified size to determine which device ad should be displayed. 

1. Width equal to or more than `728` will be considered as desktop sizes eg. `980x250,970x90`.
2. Width between `300` and `600` will be considered as a tablet sizes eg.`320x50,300x100,468x60`.
3. Width less than `300` will be considered as mobile sizes eg. `250x50,150x150`.

In case of unusual sizes for desktop, tablet and mobile specific device size attribute, like

* `mobile-sizes` for mobile.
* `tablet-sizes` for tablet.
* `desktop-sizes` for desktop.


#### As Shortcode

```
[ampad ad-unit="<ad-unit-name>"
sizes="320x50,300x100,300x50,468x60,980x250,970x90"
custom-targeting="key1:value1, key2:value2" ad-refresh="30"]
```

##### Custom sizes attributes example

```
[ampad ad-unit="<ad-unit-name>"
desktop-sizes="320x100,300x100"
tablet-sizes="468x60,300x100"
mobile-sizes="300x100,320x50"
custom-targeting="key1:value1, key2:value2"]
```

##### Custom sizes example sizes with sizes attribute

```
[ampad ad-unit="<ad-unit-name>"
sizes="300x100,320x50,910x150"
desktop-sizes="320x100,300x100"
custom-targeting="key1:value1, key2:value2"]
```

`sizes` attribute and `custom sizes` attrubutes can be used simutanously where `custom sizes` attributes overtake `sizes` attribute.

In above example `300x100` and `320x50` are for tablet and `910x150` is for desktop as per caculation on width. But since we defined `desktop-sizes` as well, it will override previous desktop size of `910x150` and new desktop sizes will be `320x100` and `300x100`. Other sizes for mobile and tablet will remain intact as we have not specified custom sizes for mobile and tablet.

#### In Template

##### With `sizes` attribute example

```php
$attr = [
	'ad-unit'   => '<ad-unit-name>',
	'sizes'     => '320x50,300x100,300x50,468x60,980x250,970x90',
	'layout'    => 'responsive',
	'custom-targeting' => 'key1:value1, key2:value2'
];

AMP_AdManager\AMP_AdManager::get_ads( $attr, true );
```

##### With custom or unusual sizes

```php
$attr = [
	'ad-unit'			=> '<ad-unit-name>',
	'mobile-sizes'		=> '320x50,300x100',
	'tablet-sizes'		=> '300x50,468x60',
	'dekstop-sizes'		=> '980x250,970x90',
	'layout'			=> 'responsive',
	'custom-targeting'	=> 'key1:value1, key2:value2'
];

AMP_AdManager\AMP_AdManager::get_ads( $attr, true );
```

##### Parameters:

1. `$attr` 
(Array) These are passed directly to the `get_ads` via template tag shown above or via shortcode.

	Default value: array
	```
	[
		'network-id'       => '',
		'ad-unit'          => '',
		'mobile-sizes'	   => '320x50,300x100', // consider for mobile
		'tablet-sizes'	   => '300x50,468x60',  // consider for tablet
		'dekstop-sizes'	   => '980x250,970x90', // consider for desktop
		'sizes'            => '320x50,300x50,468x60,980x250,970x90',
		'layout'           => 'fixed',
		'custom-targeting' => '',
		'ad-refresh'       => 30, // refresh ad every 30 seconf.
	]
	```

2. `$echo` 
(Boolean) Outputs amp-ad tag if true, returns amp-ad html as value otherwise.

### Notice:

- `Single Request Architecture(SRA)` is enabled by default and `Ad refresh` is disabled in default ad attributes. But SRA will not work if ad refresh is enabled.

### Available targeting variables

| Targeting Variable | Description |
|--------------------|-------------|
| author             | - post author username (string - lowercase)       |
| contentType 	| - type of content e.g homepage, listingpage, post, page, cpt-name (string - lowercase) |
| authorPage 	| - archive author page name (string - lowercase) |
| categoryPage 	| - archive category page name (string - lowercase) |
| tagPage 	| - archive tag page name (string - lowercase)  |
| postCategories	| - post categories list (array) ["cat1", "cat2"] (all lowercase - slugs) |
| postTags	| - post tags list (array) ["tag1", "tag2"] (all lowercase - slugs) |
| postName	| - post name slug (string - lowercase)  |
| postId	| - post id (numeric) |


## Development Flow

* Create new branch from 'master'.
* Name it appropriately, i.e., `feature/GH-<issue-number>-<name>`,`<context>/GH-<issue-number>-<what_it_is>`.
* Create PR against `master`.
* Request review for your changes and get approvals.

## Change Log

### v1.0.0 (27-09-2019)
* Tablet ads support default sizes between 300px to 600px of width.

### v0.9 (27-09-2019)
* Add missing single page targeting variables.
* Fix SRA tag loading on both AMP and Non-AMP pages.

### v0.8 (20-09-2019)
* Add ad refresh support.
* Add Single Request Architecture (SRA) support.

### v0.7 (03-09-2019)
* Add custom sizes attributes for shortcode.

### v0.6 (28-08-2019)
* Add single function to get output of mobile, tablet, desktop ads.
* Add additional attribute to specify custom or unusual ad sizes.

### v0.5 (17-05-2019)
* PHPCS fixes.
* Doc changes.

### v0.4 (10-05-2019)
* Add `postName` and `postId` targeting variables
* Rename single post targeting variables - `postCategories`, `postTags`

### v0.3 (29-04-2019)
* Add network id support in shortcode.

### v0.2 (14-04-2019)
* Fix shortcode attributes function.
* Add support for user defined targeting with custom-targeting attribute in [ampad]. 
* Type casting for Domain variable.
* Add layout attribute in shortcode for <amp-ad> layout support.

### v0.1
* Basic plugin with ampad shortcode.

# Does this interest you?

<a href="https://rtcamp.com/"><img src="https://rtcamp.com/wp-content/uploads/sites/2/2019/04/github-banner@2x.png" alt="Join us at rtCamp, we specialize in providing high performance enterprise WordPress solutions"></a>
