<?php

class GameOfLife {

  public $options = []; // create empty array for all the custom options
  public $iteration = 0; // get frame count (or generation number)
  public $cells = []; // set up empty array for cells

  public function __construct(array $options) {
    $this->defaultValues($options); // set all the default values to the options inputted
    $this->initialState($this->options['random'], $this->options['chanceoflife']); // create initial cell state
    if (!empty($this->options['template'])) { // if a template is set...
      $this->setTemplate($options['template']); // change the option for template and echo it to terminal window
    }
  }

// Define default values and integrate custom values
  public function defaultValues(array $options) { 
    $defaults = [
      'width' => exec('tput cols'), // set width to width of terminal window
      'height' => exec('tput lines') - 3, // set height to height of terminal window but leave room for the info
      'chanceoflife' => 5, // how likely is it that the cell is going to reproduce
      'template' => NULL, // default null template...
      'random' => TRUE, // ...so random is default
      'alive' => 'o', // set symbol for alive cells
      'dead' => ' ', // set symbol for dead cells
      'iterations' => 100 // how many times the loop will run
    ];
    if (isset($options['template']) && !isset($options['random'])) { // if template is set and random isn't...
      $options['random'] = FALSE; // ...turn off random
    }
    $options += $defaults; // add default values to options array
    $this->options += $options; // rewrite defaults with the custom options 
  }

// Generate initial state for random state
  public function initialState($randomize, $chanceoflife = 10) {
    for ($x = 0; $x < $this->options['width']; $x++) { // for each x position on the terminal window 'grid'...
      for ($y = 0; $y < $this->options['height']; $y++) { // and for each y position on the terminal window 'grid'...
        if ($randomize) { // if we're on random mode...
          $state = mt_rand(0, $chanceoflife) === 0; // generate a random number between 0 and the chance of life number to return true/false
          $this->cells[$y][$x] = $state; // ...use random state to determine whether the cell is alive or not (true=1=alive/false=0=dead)
        }
        else { // otherwise...
          $this->cells[$y][$x] = 0; // ...set a blank template so we can use the chosen template
        }
      }
    }
    return $this; // return the construct of the cells
  }

// Gets template from a .txt file and echos the it onto the terminal window
  public function setTemplate($path) {
    $file = fopen($path, 'r'); // get and open template from path
    $centreofX = (int) $this->options['width'] / 2 ; // find centre of grid
    $centreofY = (int) $this->options['height'] / 2 ; // find centre of grid
    $x = $centreofX; // set starting position for x for printing template as centre of grid
    $y = $centreofY; // set starting position for y for printing template as centre of grid 
    while ($c = fgetc($file)) { // loop through all file characters
      if ($c == 'O') { // if character found...
        $this->cells[$y][$x] = 1; // assign a cell to this position on the grid
      }
      if ($c == "\n") { // if there is a return character there...
        $y++; // carry on scanning y
        $x = $centreofX; // x is at the end of the line
      }
      else {
        $x++; // carry on scanning x
      }
    }
    fclose($file); // close template file
  }

// Counts number of alive neighbors for a given cell.
  public function neighborCount($x, $y) {
    $neighbour_Count = 0; // start count at 0
    for ($y2 = $y - 1; $y2 <= $y + 1; $y2++) { // for all of the possible neighbours in the y direction...
      if ($y2 < 0 || $y2 >= $this->options['height']) { // if the neighbours position is off the top of the terminal window...
        continue; // out of range, we ignore it, so there isn't a neighbour there.
      }
      for ($x2 = $x - 1; $x2 <= $x + 1; $x2++) { // and for all the possible neighbours in the x direction...
        if ($x2 == $x && $y2 == $y) { // if the position is on the cell itself...
          continue; // this can't be a neighbour
        }
        if ($x2 < 0 || $x2 >= $this->options['width']) { // if the neighbours position is off the side of the terminal window...
          continue; // out of range, we ignore it, so there isn't a neighbour there.
        }
        if ($this->cells[$y2][$x2]) { // but if there's a cell in any of the possible neighbouring positions...
          $neighbour_Count += 1; // add 1 to the count
        }
      }
    }
    return $neighbour_Count;
  }

// Processes a new generation for all cells.
  public function newGeneration() {
    $cells = &$this->cells;
    $dead_array = []; // create empty array for the new positions of each dead cell
    $alive_array = []; // create empty array for the new positions of each alive cell
    for ($y = 0; $y < $this->options['height']; $y++) { // for each y position on the grid
      for ($x = 0; $x < $this->options['width']; $x++) { // and for each x position for each y ...
        $neighbor_Count = $this->neighborCount($x, $y); // return number of neighbours for cell
        if ($cells[$y][$x] && ($neighbor_Count < 2 || $neighbor_Count > 3)) { // if there are less than 2 (Underpopulation) or more than 3 neighbours (Overcrowding) ...
          $dead_array[] = [$y, $x]; // add this cell to the array to die
        }
        if (!$cells[$y][$x] && $neighbor_Count === 3) { // if an empty space has 3 neighbours (Creation of Life)...
          $alive_array[] = [$y, $x]; // add this cell to the array to generate a living cell
        }
      } // else leave the cell as it is (ie. Survival)
    }
    foreach ($dead_array as $d) { // for each cell in the dead array...
      $cells[$d[0]][$d[1]] = 0; // reset value of cell to be 0 (dead)
    }
    foreach ($alive_array as $a) { // for each cell in the alive array...
      $cells[$a[0]][$a[1]] = 1; // reset value of cell to be 1 (alive)
    }
  }

// Returns the grid in the terminal window.
  public function returnGrid() { 
    foreach ($this->cells as $y => $row) { // for all the y values on the grid...
      foreach ($row as $x => $cell) { // and for each x value...
        print ($cell ? $this->options['alive'] : $this->options['dead']); // if there's a cell, print the chosen alive character, if not, print the chosen dead character
      }
      print "\n"; // go to the next line once we've reached the end of the row
    }
  }

// Counts number of alive cells
  public function cellCount() {
    $cell_Count = 0; // start the counter at 0
    foreach ($this->cells as $y => $row) { // for every row of cells...
      foreach ($row as $x => $cell) { // and for every position on that row...
        if ($cell) { // if there's a cell there...
          $cell_Count++; // add 1 to the counter
        }
      }
    }
    return $cell_Count; // return count for display
  }

// Collates game info and prints it after the game.
  public function showInfo() {
    $iteration = $this->iteration; // get frame count
    $cell_Count = $this->cellCount(); // run counter function throughout grid
    print str_repeat('*', $this->options['width']) . "\n"; // draw a border and go to next line
    echo "\033[K"; // delete the info from the last iteration of the game
    echo "Generation: $iteration . No of cells: $cell_Count \n" ; // return a nice string of data for the new iteration
  }

// Loop through all the functions for every iteration
  public function loop() {
    for ($i = 0; $i < $this->options['iterations']; $i++ ) { // begin loop of ith interations
      $this->iteration++; // add 1 to the frame count
      $this->newGeneration(); // create new generation of cells
      $this->returnGrid(); // print grid of new generation
      $this->showInfo(); // print information
    }
  }

}