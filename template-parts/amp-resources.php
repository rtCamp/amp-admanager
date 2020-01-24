<?php
/**
 * Load AMP Resources.
 * This will load amp-boilerplate CSS and amp-runtime script. Ideally at the top of the wp_head.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 0.2
 *
 * @package AMP_AdManager
 */

?>
<style amp-boilerplate>
	body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
</style>
<noscript>
	<style amp-boilerplate>
		body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}
	</style>
</noscript>
<link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">

<?php  //phpcs:disable ?>
<script type="text/javascript" src="https://cdn.ampproject.org/v0.js" async></script>
<script async custom-element="amp-sticky-ad" src="https://cdn.ampproject.org/v0/amp-sticky-ad-1.0.js"></script>
<?php //phpcs:enable ?>
