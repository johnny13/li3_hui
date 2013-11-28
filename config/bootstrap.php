<?php

use li3-hui\extensions\storage\FlashMessage;
use lithium\action\Dispatcher;

require __DIR__ . '/bootstrap/session.php';

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {
	$object = $chain->next($self, $params, $chain);

	if (is_a($object, 'lithium\action\Controller')) {
		return FlashMessage::bindTo($object);
	}

	return $object;
});

?>