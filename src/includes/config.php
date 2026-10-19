<?php
define('SITE_NAME', 'LAIN // WIRED ARCHIVE');

// Easy: rate limit sangat longgar (nyaris tidak terasa)
define('ADMIN_RATELIMIT_MAX', 1000);
define('ADMIN_RATELIMIT_WINDOW', 300);

define('SQLI_RATELIMIT_MAX', 100000);
define('SQLI_RATELIMIT_WINDOW', 60);
