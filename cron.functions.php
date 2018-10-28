<?php

// @TODO move this to class

include(__DIR__ . "/inc/Cli/GF_CLI.php");

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    // add cli commands
    \WP_CLI::add_command('fixItems', 'fixItems');

}



function fixItems() {
    $cli = new \GF\Cli();

    $cli->fixItems();
}

function updateItems() {

}






