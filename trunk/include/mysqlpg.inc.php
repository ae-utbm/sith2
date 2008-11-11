<?php

class mysqlpg extends mysql {

  function mysqlpg ($type = "ro") {
    $this->mysql('petitgeni', 'dc75f4d3', 'localhost', 'petitgeni');
  }
}
?>
