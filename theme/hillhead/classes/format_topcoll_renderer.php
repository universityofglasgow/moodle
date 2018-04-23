<?php

    class theme_hillhead_format_topcoll_renderer extends format_topcoll_renderer {
        protected function get_row_class() {
            return 'row';
        }
        protected function get_column_class($columns) {
            $colclasses = array(
                1 => 'col-sm-12 col-md-12 col-lg-12',
                2 => 'col-sm-6 col-md-6 col-lg-6',
                3 => 'col-md-4 col-lg-4',
                4 => 'col-lg-3');
            return $colclasses[$columns];
        }
    }

?>