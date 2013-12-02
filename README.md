# li3-hui [ lithium ui plugin ]

## Installation

Checkout the code to your library directory:

    cd libraries
    git clone git@github.com:/johnny13/li3-hui.git

Include the library in in your `/app/config/bootstrap/libraries.php`

    Libraries::add('li3-hui');
	define('HUI_PATH', dirname(LITHIUM_APP_PATH) . '/libraries/li3_hui' );
	
The li3-hui (`li3-hui`) plugin provides a straightforward interface for displaying status messages to the user, a number of html builders, and stuff and thangs.


## Integration

```
<?php

// config/bootstrap/libraries.php:

Libraries::add('li3-hui');
define('HUI_PATH', dirname(LITHIUM_APP_PATH) . '/libraries/li3_hui' );
?>
```

### Custom Views

Once you have the library installed and included, you probably want to use it.
Calling custom layouts from libraries can be tricky. It basically requires you setup each controller.
There needs to be an init() where you define the library's path.

Your Controller Should have this Function

```
<?php

public function _init() {
    parent::_init();
    $this->controller = $this->request->controller;
    $this->library = $this->request->library;

    $this->_render['paths'] = array(
            'template' => array(
                    LITHIUM_APP_PATH . '/views/{:controller}/{:template}.{:type}.php',
                    HUI_PATH . '/views/{:controller}/{:template}.{:type}.php',
                    '{:library}/views/{:controller}/{:template}.{:type}.php',
            ),
            'layout' => array(
                    LITHIUM_APP_PATH . '/views/layouts/{:layout}.{:type}.php',
                    HUI_PATH . '/views/layouts/{:layout}.{:type}.php',
                    '{:library}/views/layouts/{:layout}.{:type}.php',
            ),
            'element' => array(
                    LITHIUM_APP_PATH . '/views/elements/{:template}.{:type}.php',
                    HUI_PATH . '/views/elements/{:template}.{:type}.php',
                    '{:library}/views/elements/{:template}.{:type}.php',
            ),
    );
}

?>
```

Then later in the SAME Controller you would render a view like this

```
<?php

public function index() {
	$title = "whatevah";
	$this->set(array('title' => $title));
	return $this->render(array('layout' => 'hui', 'library' => 'li3_hui'));
}

?>
```