<?php

/**
 * Function increments number of redirects into that file
 * 
 */
function incrementClicksCounter() {
    $clicksCounter = trim(file_get_contents("clicks_counter.txt"));

    $clicksCounter++;
    file_put_contents("clicks_counter.txt", $clicksCounter);

    showClicksCounter($clicksCounter);
}

/**
 * Function prints value of clicks counter
 * 
 * @param int current number of clicks
 */
function showClicksCounter($clicksCounter) {
    echo "Number of times clicked: $clicksCounter";
}

incrementClicksCounter();

?>