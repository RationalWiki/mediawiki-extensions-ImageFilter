<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

$wgExtensionCredits['other'][] = array(
	'name' => 'ImageFilter',
	'author' => '[http://mediawiki.org/wiki/User:Nx Nx]',
	'description' => 'Image filter',
	'url' => 'http://www.mediawiki.org/wiki/Extension:ImageFilter'
);

$wgMessagesDirs['ImageFilter'] = __DIR__ . '/i18n';

$wgAutoloadClasses['ImageFilter'] = __DIR__ . '/ImageFilter.class.php';

$wgHooks['PageRenderingHash'][] = 'ImageFilter::onPageRenderingHash';
$wgHooks['ImageBeforeProduceHTML'][] = 'ImageFilter::onImageBeforeProduceHTML';
$wgHooks['ArticleSaveComplete'][] = 'ImageFilter::onArticleSaveComplete';
$wgHooks['GetPreferences'][] = 'ImageFilter::onGetPreferences';