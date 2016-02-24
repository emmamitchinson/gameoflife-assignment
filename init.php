<?php

include 'GameOfLife.php';

$options = [];

if (isset($argv[1])) { // if there is an argument set in the command line...
  parse_str($argv[1], $options); // ...set the arguments as options
}

$game = new GameOfLife($options); // get the class of functions to run the game
$game->loop(); // run the loop function in the class