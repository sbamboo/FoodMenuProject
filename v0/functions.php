<?php

// Seed: "{year};{week}" where year is "yyyy" and week is week-number
function seededShuffle(array $items, string $seed): array {
    // Convert seed to an integer hash
    $hash = crc32($seed);
    
    // Set seed for randomization
    mt_srand($hash);
    
    // Shuffle the items based on the seed
    shuffle($items);
    
    // Reset the random generator to avoid affecting other parts of the script
    mt_srand();
    
    return $items;
}

// Function to get the seed from the URL
function getSeed(): array {
    // ?date=yyyy-mm-dd (one entry)
    // ?year=yyyy&week=ww (one entry)
    // ?week=ww (current year, one entry)
    // ?year=yyyy (returns seed for all weeks in year)
    // Else return seed for current week of the current year
}

// Function to filter the menu items (?excludeWeekends, ?excludeRedDays, ?day=<int:1-7> if day index is more then entries return empty)
function filterItems(array $items, array $options): array {
    // Filter out weekends
    
    // Filter out red days
    
    // Filter out specific day
}

?>