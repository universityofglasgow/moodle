<nav role="navigation" class="navbar navbar-default">
    <div class="container-fluid">
    <div class="navbar-header pull-left">
        <a class="navbar-brand" href="<?php echo $CFG->wwwroot;?>"><div id="logo"></div></a>
        <div id="moodle-logo"></div>
    </div>
    <div class="navbar-header pull-right">
        <?php if ($PAGE->pagelayout != 'login') {echo $OUTPUT->user_menu();} ?>
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#moodle-navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <div id="moodle-navbar" class="navbar-collapse collapse navbar-right">
        <?php echo $OUTPUT->custom_menu(); ?>
        <ul class="nav pull-right">
            <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
        </ul>
    </div>
    
    </div>
</nav>
