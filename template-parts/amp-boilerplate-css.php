<?php
/**
 * Load amp boilerplate style sheet code.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @package AMP_AdManager
 */

if ( function_exists( 'amp_get_boilerplate_code' ) ) {
	echo amp_get_boilerplate_code(); // phpcs:ignore
	return;
}
?>

<style amp-boilerplate>
	body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}
</style>
<noscript>
	<style amp-boilerplate>
		body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}
	</style>
</noscript>
