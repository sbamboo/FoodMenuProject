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

// Function taking year as "yyyy" and week number and returns the "dd" part of the dates for al days in the week
function getWeekDays(string $year, string $week): array {
    // Get the date
    $date = new DateTime();
    $date->setISODate($year, $week);

    // Get first day number in the week
    $first_day = $date->format('N');
    // Get the "dd" part of the first_day
    $first_day_number = $date->format('d');
    
    // Based on first_day_number get the day numbers for the entire week
    $days = [];
    for ($i = 0; $i < 7; $i++) {
        $days[] = $first_day_number + $i;
    }

    return $days;
}

// Function to generate a random week (Assembles seed+";<day>")
function generateRandomWeek(string $seed, array $veggy_entries, array $non_veggy_entries): array {
    $days = getWeekDays($seed[0], $seed[1]);

    // Iterate the days and assemble "{seed}+{day}" for each day get seeded-random
    $week = [];
    $i = 1;
    foreach ($days as $day) {
        // Get the entries for the day
        $veggy_entry = seededShuffle($veggy_entries, $seed . ';' . $day);
        $non_veggy_entry = seededShuffle($non_veggy_entries, $seed . ';' . $day);
        
        // Add the entries to the week as {<int:index=1-7>: {"vegetarian": <entry>, "non_vegetarian": <entry>}}
        $week["$i"] = [
            'vegetarian' => explodeDish($veggy_entry[0]),
            'non_vegetarian' => explodeDish($non_veggy_entry[0])
        ];

        // Increment the index
        $i += 1;
    }

    return $week;
}

// Function to get the entries from ./foodslist.json
function getEntries(): array {
    // Read the JSON file
    $json = file_get_contents('./foodslist.json');
    
    // Decode the JSON file
    $lists = json_decode($json, true);
    
    // Return [[...veggy_entries...],[...non_veggy_entries...]]

    // Ensure "veggy" and "non_veggy" fields
    if (!array_key_exists('veggy', $lists)) {
        $lists['veggy'] = [];
    }
    if (!array_key_exists('non_veggy', $lists)) {
        $lists['non_veggy'] = [];
    }

    // Return the entries
    return [$lists['veggy'], $lists['non_veggy']];
}

// Turn "<food_name>;<food_description>" or "<food_name>" into ["<food_name_or_empty>","<food_description_or_empty>"]
function explodeDish(string $dish): array {
    if (strpos($dish, ';') == false) {
        return [$dish, ''];
    } else {
        // Explode and whitespace-trim each part
        $parts = explode(';', $dish);
        foreach ($parts as $part) {
            $part = trim($part);
        }
        return $parts;
    }
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
// $options is ["excludeWeekends"=>bool, "excludeRedDays"=>bool, "day"=>int] where each option is optional
function filterItems(array $items, array $options): array {
    // Filter out weekends
    
    // Filter out red days
    
    // Filter out specific day
}

?>