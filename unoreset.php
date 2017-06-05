<?php
require('config.php');
$fs = get_file_storage();
$fs->delete_area_files(context_system::instance()->id, 'core', 'documentconversion');
