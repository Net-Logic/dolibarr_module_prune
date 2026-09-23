<?php
/**
 * Composer post-install/post-update cleanup of vendor/ (run by composer.json "scripts").
 *
 * Removes microsoft/microsoft-graph's Beta API models (~34 MB, about half of vendor/):
 * no module uses the Graph Beta endpoint any more (microsoftgraph is on v1.0 only), and
 * the SDK autoloads them through PSR-4, so nothing references the missing files. Remove
 * this rule if a module ever needs Beta\Microsoft\Graph classes.
 */

// This file sits under the web root: never let a request trigger it.
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit;
}

$dirs = [
	__DIR__ . '/../vendor/microsoft/microsoft-graph/src/Beta',
];

foreach ($dirs as $dir) {
	if (!is_dir($dir)) {
		continue;
	}
	$items = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($items as $item) {
		$item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
	}
	rmdir($dir);
	echo "composer_cleanup: removed " . realpath(dirname($dir)) . "/" . basename($dir) . "\n";
}
