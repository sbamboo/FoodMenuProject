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
// Return ["<entrypoint_name>", [...seeds...]]
function getSeed(): array {
    // ?date=yyyy-mm-dd (one entry)
    // ?year=yyyy&week=ww (one entry)
    // ?week=ww (current year, one entry)
    // ?year=yyyy (returns seed for all weeks in year)
    // Else return seed for current week of the current year
}

function getOptionsFromURL(array $params): array {
    $options = [
        "excludeWeekends" => false,
        "excludeRedDays" => false,
        "day" => null
    ];

    // ?excludeWeekends url parameter
    if (array_key_exists('excludeWeekends', $params)) {
        $options['excludeWeekends'] = true;
    }

    // ?excludeRedDays url parameter
    if (array_key_exists('excludeRedDays', $params)) {
        $options['excludeRedDays'] = true;
    }

    // ?day=<int:1-7> url parameter
    if (array_key_exists('day', $params)) {
        $options['day'] = intval($params['day']);
    }

    // If ?date get day from it
    if (array_key_exists('date', $params)) {
        $date = new DateTime($params['date']);
        $options['day'] = intval($date->format('N'));
    }

    return $options;
}

function getHolidays($year) {
    $holidays = [];
    $weekendDays = [];

    // Fixed-date holidays
    $fixedHolidays = [
        "$year-01-01", // Nyårsdagen
        "$year-01-06", // Trettondedag jul
        "$year-05-01", // Första maj
        "$year-06-06", // Sveriges nationaldag
        "$year-12-24", // Julafton
        "$year-12-25", // Juldagen
        "$year-12-26", // Annandag jul
        "$year-12-31", // Nyårsafton
    ];

    // Easter-based holidays
    $easterSunday = easter_date($year);
    $holidays[] = date("Y-m-d", $easterSunday - 3 * 86400); // Skärtorsdagen
    $holidays[] = date("Y-m-d", $easterSunday - 2 * 86400); // Långfredagen
    $holidays[] = date("Y-m-d", $easterSunday - 1 * 86400); // Påskafton
    $holidays[] = date("Y-m-d", $easterSunday);             // Påskdagen
    $holidays[] = date("Y-m-d", $easterSunday + 1 * 86400); // Annandag påsk
    $holidays[] = date("Y-m-d", $easterSunday + 39 * 86400); // Kristi himmelsfärdsdag
    $holidays[] = date("Y-m-d", $easterSunday + 49 * 86400); // Pingstafton
    $holidays[] = date("Y-m-d", $easterSunday + 50 * 86400); // Pingstdagen

    // Midsummer (Friday & Saturday between June 19-25)
    for ($d = 19; $d <= 25; $d++) {
        $date = strtotime("$year-06-$d");
        if (date("N", $date) == 5) { // Friday
            $holidays[] = date("Y-m-d", $date); // Midsommarafton
            $holidays[] = date("Y-m-d", $date + 86400); // Midsommardagen (Saturday)
            break;
        }
    }

    // All Saints' Day (Saturday between October 31 and November 6)
    $startDate = strtotime("$year-10-31");
    $endDate = strtotime("$year-11-06");

    for ($date = $startDate; $date <= $endDate; $date += 86400) {
        if (date("N", $date) == 6) { // Saturday
            $holidays[] = date("Y-m-d", $date); // Alla helgons dag
            $holidays[] = date("Y-m-d", $date - 86400); // Allhelgonaafton (Friday)
            break;
        }
    }

    // Collect all weekends (Saturdays & Sundays)
    $start = strtotime("$year-01-01");
    $end = strtotime("$year-12-31");
    for ($date = $start; $date <= $end; $date += 86400) {
        if (date("N", $date) >= 6) { // Saturday (6) or Sunday (7)
            $weekendDays[] = date("Y-m-d", $date);
        }
    }

    // Merge fixed holidays and sort all dates
    $holidays = array_merge($holidays, $fixedHolidays);
    sort($holidays);

    // Ensure uniqueness for holidays and weekends
    $holidays = array_values(array_unique($holidays));
    $weekendDays = array_values(array_unique($weekendDays));

    return [$holidays, $weekendDays]; // Return both arrays
}

// Function to filter the menu items (?excludeWeekends, ?excludeHolidays, ?day=<int:1-7> if day index is more then entries return empty)
// $options is ["excludeWeekends"=>bool, "excludeHolidays"=>bool, "day"=>int] where each option is optional
function filterItems(string $year, array $items, array $options): array {
    $filters = getHolidays($year);
    $holidays = $filters[0];
    $weekendDays = $filters[1];
 
    // Filter out weekends use array_key_exists
    if (array_key_exists('excludeWeekends', $options) && $options['excludeWeekends'] == true) {
        $items = array_diff($items, $weekendDays);
    }
    
    // Filter out holidays
    if (array_key_exists('excludeHolidays', $options) && $options['excludeHolidays'] == true) {
        $items = array_diff($items, $holidays);
    }
    
    // Filter out specific day (day is index 1-7 of a week)
    if (array_key_exists('day', $options) && $options['day'] !== null) {
        $items = array_filter($items, function($item) use ($options) {
            return date("N", strtotime($item)) == $options['day'];
        });
    }

    return $items;
}

// Function to get {"weekday":bool, "holiday":bool, "day":int/null} depending on options
function getOptionFilters(array $options): array {
    $filters = [
        "weekday" => true,
        "holiday" => true,
        "day" => null
    ];

    if (array_key_exists('excludeWeekends', $options)) {
        $filters['weekday'] = $options['excludeWeekends'];
    }

    if (array_key_exists('excludeHolidays', $options)) {
        $filters['holiday'] = $options['excludeHolidays'];
    }

    if (array_key_exists('day', $options)) {
        $filters['day'] = $options['day'];
    }

    return $filters;
}

?>