<?php
define('NELLIEL_VERSION', 'v0.9.17'); // Version
define('NELLIEL_COPYRIGHT', '2010-2018 Nelliel Project'); // Copyright line
define('NELLIEL_PACKAGE', 'Nelliel'); // Package
define('BASE_PATH', realpath('./') . '/'); // Base path for script
define('FILES_PATH', BASE_PATH . 'board_files/'); // Base board files path
define('INCLUDE_PATH', FILES_PATH . 'include/'); // Base include files path
define('LIBRARY_PATH', FILES_PATH . 'libraries/'); // Libraries path

require_once INCLUDE_PATH . 'autoload.php';
require_once INCLUDE_PATH . 'initializations.php';
require_once LIBRARY_PATH . 'portable-utf8/portable-utf8.php';
require_once LIBRARY_PATH . 'random_compat/lib/random.php';
require_once INCLUDE_PATH . 'database.php';
require_once INCLUDE_PATH . 'accessors.php';

nel_plugins()->loadPlugins();

// A demo point. Does nothing.
nel_plugins()->processHook('nel-plugin-example', array(5));
$out = nel_plugins()->processHook('nel-plugin-example-return', array('string'), 5);
unset($out);

require_once INCLUDE_PATH . 'general_functions.php';

// Check if we're just returning a CAPTCHA image
if(isset($_GET['get-captcha']))
{
    nel_get_captcha();
}

require_once INCLUDE_PATH . 'output/header.php';
require_once INCLUDE_PATH . 'output/footer.php';
require_once INCLUDE_PATH . 'derp.php';

$language = new \Nelliel\Language\Language(new \Nelliel\Auth\Authorization(nel_database()));
$language->loadLanguage(LOCALE_FILE_PATH . DEFAULT_LOCALE . '/LC_MESSAGES/nelliel.po');
unset($language);

require_once INCLUDE_PATH . 'crypt.php';
nel_set_password_algorithm(NEL_PASSWORD_PREFERRED_ALGORITHM);

if (RUN_SETUP_CHECK)
{
    $setup = new \Nelliel\Setup\Setup();
    $board_id = (isset($_GET['board_id'])) ? $_GET['board_id'] : '';
    $setup->checkAll($board_id);
    unset ($setup);
}

require_once CONFIG_FILE_PATH . 'generated.php';

if (nel_setup_stuff_done())
{
    if (USE_INTERNAL_CACHE)
    {
        $regen = new \Nelliel\Regen();
        $regen->siteCache(new \Nelliel\Domain('', new \Nelliel\CacheHandler(), nel_database()));
        unset($regen);
    }
}

// IT'S GO TIME!
ignore_user_abort(true);

require_once INCLUDE_PATH . 'dispatch/central_dispatch.php';

nel_central_dispatch();
nel_clean_exit();

