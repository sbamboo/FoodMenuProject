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
        $new_parts = [];
        // Explode and whitespace-trim each part
        $parts = explode(';', $dish);
        foreach ($parts as $part) {
            $new_parts[] = trim($part);
        }
        return $new_parts;
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

    /*
    req_context:
        /v0/foodmenu/date
        /v0/foodmenu/year
        /v0/foodmenu/week
    */

    // If ?date seed is year and week-nr for that date, also set $_REQUEST["day"] to string-of-int for the days index in the week

    $req_context = "/v0/foodmenu/";

    if(array_key_exists("date", $_REQUEST)){
        $date = $_REQUEST["date"];
    }
    $year;
    $week;

    $seeds = [];
    if(!empty($date)){
        // ?date=yyyy-mm-dd
        $year = explode("-", $date)[0];

        $dateTime = new DateTime($date);
        $week = $dateTime->format("W");

        $req_context = $req_context . "date";
    }
    else{
        // ?year=yyyy&week=ww
        if(array_key_exists("year", $_REQUEST)){
            $year = $_REQUEST["year"];
        }

        if(array_key_exists("week", $_REQUEST)){
            $week = $_REQUEST["week"];
        }

        if(empty($year) && !empty($week)){
            $year = date("Y");
            $req_context = $req_context . "week";
        } else if(!empty($year) && empty($week)){
            // ?year=yyyy
            for ($i=0; $i < 52; $i++) {
                array_push($seeds, $year . ";" . $i + 1);
            }
        } else{
            //Missing values ????
        }
    }
    
    if($seeds == []){
        $seeds[0] = $year . ";" . $week;
    }
    return [$req_context, $seeds];
}

function getOptionsFromURL(array $params): array {
    $options = [
        "excludeWeekends" => false,
        "excludeHolidays" => false,
        "day" => null
    ];

    // ?excludeWeekends url parameter
    if (array_key_exists('excludeWeekends', $params)) {
        $options['excludeWeekends'] = true;
    }

    // ?excludeHolidays url parameter
    if (array_key_exists('excludeHolidays', $params)) {
        $options['excludeHolidays'] = true;
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
    $easterSunday = easter_date((int)$year);
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

//TODO: Make this return ["string_of_int:year"=> [ "string_of_int:week" => ["string_of_int:dayindex"=><entry>....],...],... ]
function convertDatesToYearWeekDay(array $dateStrings): array {
    $result = [];

    // Fill yearWeekDay
    $weekAndDays = [];
    foreach ($dateStrings as $dateString) {
        try {
            $dateTime = new DateTime($dateString);
            $week = (int)$dateTime->format('W');  // ISO-8601 week number
            $dayIndex = (int)$dateTime->format('N'); // ISO-8601 numeric representation of the day (1-7)

            $weekAndDays[] = [
                'week' => $week,
                'day' => $dayIndex,
            ];
        } catch (Exception $e) {
            // Handle invalid date strings. You might want to log this error.
            continue;  // Skip to the next date string
        }
    }

    // For each entry in weekAndDays, add it to the result array as [ "string_of_int:week" => ["string_of_int:dayindex"],...]
    foreach ($weekAndDays as $entry) {
        $week = $entry['week'];
        $dayIndex = $entry['day'];

        if (!array_key_exists($week, $result)) {
            $result[$week] = [];
        }

        // Append the dayIndex as a string to the week's array
        array_push($result[$week], "$dayIndex");
    }

    return $result;
}

// weekAndDays   : [ "string_of_int:week" => ["string_of_int:dayindex"=><entry>....],... ]
// toFilterArray : ["string_of_int:dayindex"=><entry>....]
function filterDateArrays(array $weekAndDays, string $weekNumber, array $toFilterArray): array {
    $filteredArray = [];

    // if weekNumber is not a key in weekAndDays just return it
    if (!array_key_exists($weekNumber, $weekAndDays)) {
        return $toFilterArray;
    } else {
        $filteredArray = [];
        // Iterate the weekAndDays array
        foreach ($toFilterArray as $dayIndex => $day) {
            // If the $dayIndex is not a key in $weekAndDays[$weekNumber] add it
            if (!in_array("$dayIndex", $weekAndDays[$weekNumber])) {
                $filteredArray[$dayIndex] = $day;
            }
        }
    }

    return $filteredArray;
}


// Function to filter the menu items for a week (?excludeWeekends, ?excludeHolidays, ?day=<int:1-7> if day index is more then entries return empty)
// $options is ["excludeWeekends"=>bool, "excludeHolidays"=>bool, "day"=>int] where each option is optional
function filterItems(string $year, string $weekNumber, array $items, array $options): array {
    $filters = getHolidays($year);
    $holidays = convertDatesToYearWeekDay($filters[0]);
 
    // Filter out weekends by removing the 6 8 keys from this weeks array
    if (array_key_exists('excludeWeekends', $options) && $options['excludeWeekends'] == true) {
        foreach ($items as $day => $week) {
            if ("$day" == "6" || "$day" == "7") {
                unset($items[$day]);
            }
        }
    }
    
    // Filter out holidays
    if (array_key_exists('excludeHolidays', $options) && $options['excludeHolidays'] == true) {
        $items = filterDateArrays($holidays, $weekNumber, $items);
    }
    
    // Filter out specific day (day is index 1-7 of a week)
    if (array_key_exists('day', $options) && $options['day'] !== null) {
        $new_items = [];
        // Iterate the keys of the $items and if $key is not $options['day'] add it to $new_items
        foreach ($items as $key => $item) {
            if ($key == $options['day']) {
                $new_items[$key] = $item;
            }
        }
        $items = $new_items;
    }

    return $items;
}

// Function to get {"weekday":bool, "holiday":bool, "day":int/null} depending on options
function getOptionFilters(array $options): array {
    $filters = [
        "no_weekdays" => false,
        "no_holidays" => false,
        "day" => null
    ];

    if (array_key_exists('excludeWeekends', $options) && $options['excludeWeekends'] == true) {
        $filters['no_weekdays'] = true;
    }

    if (array_key_exists('excludeHolidays', $options) && $options['excludeHolidays'] == true) {
        $filters['no_holidays'] = true;
    }

    if (array_key_exists('day', $options)) {
        $filters['day'] = $options['day'];
    }

    return $filters;
}

?>