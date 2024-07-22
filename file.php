<?php

/**
 * Function increments number of redirects into that file
 * 
 */
function incrementClicksCounter() {
    $counterFile = "clicks_counter.txt";

    // If file not exists - create it and put "0"
    if (!file_exists($counterFile)) {
        file_put_contents($counterFile, "0");
    }

    // Get saved value and increment it
    $clicksCounter = trim(file_get_contents($counterFile));

    showClicksCounter($clicksCounter);

    $clicksCounter++;

    // Save new value
    file_put_contents("clicks_counter.txt", $clicksCounter);
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