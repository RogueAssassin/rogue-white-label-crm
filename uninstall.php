<?php
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
// By default, customer/job data is preserved for safety. To fully remove data, delete the rwlc_* tables manually or enable a future destructive uninstall setting.
delete_option('rwlc_version');
