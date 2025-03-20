# FoodMenu - PHP WebService API
## Members
- Simon Kalmi Claesson (@sbamboo)
- Arvid Ersson (@Astikornipal)
- Otis Gustafsson (@bumblebroo)

*NTI Gymnasiet Skövde - 2025 - TE3s*
<br><br>


## Expected endpoints

### Docs
`api.ntigskovde.se/.../gr1/foodmenu/docs`<br>
It is also possible to request docs for a specific version using<br>
`api.ntigskovde.se/.../gr1/foodmenu/docs/?ver=<ver>` example `api.ntigskovde.se/.../gr1/foodmenu/docs/?ver=v0`<br>
*(If no version is given or the requested one is invalid the latest is used)*

### Retrieval
Get by date: *(Converts to year and week number)*<br>
`api.ntigskovde.se/.../gr1/foodmenu/v0?date=2025-03-13`

Get by year and week:<br>
`api.ntigskovde.se/.../gr1/foodmenu/v0?year=2025&week=11`

Get by only week number: *(Resolves current year)*<br>
`api.ntigskovde.se/.../gr1/foodmenu/v0?week=11`

Get by only year: *(Returns a list of the entries for the weeks, non-indexed but week-1 is entry 1, number of weeks depends on year)*<br>
`api.ntigskovde.se/.../gr1/foodmenu/v0?year=2025` => `{}`

Incase no parameters are given the current year and week is returned.

**Al of the above may add the param ?excludeWeekends and ?excludeRedDays**<br>
**Aswell as ?day being an index from 1-7 if list does not include 7 entries and day-index is the amount the return is empty**
<br><br>


## Functionaliy
### API
1. The dishes for a week are randomised using a seed `<year>;<week>`, which generates 7 entries.
2. If a red-day or weekday filter was applied we filter out thoose.
3. If a day was requested we retrieve the day, if an entire year we return a list of all, else we return the week.
### Docs
For the docs site `/docs/index.php` takes an optional url-param `?ver=` then renders `/docs/{ver}.md`, if no version is given it defaults to the latest.
After loading the markdown it applies specific parsing to codeblocks.
In codeblocks with `api.request` we replace `{url}` with span-formatted string. We also wrap `?` and `&` after the last url-segment with spans.
In codeblocks with `api.response` or `json` we color format the text as JSON using spans.
When `api.request` codeblocks next to `api.response` codeblocks they get styled together.
For codeblocks with `notice.red`/`notice.yellow`/`notice.green`/`notice.orange`/`notice.blue`/`notice.gray`/`notice.purple` they get some padding and coloring to signify them on the site.
### PHP
Randomization
```php
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
```
<br><br>

## Returned data

In each request `format` contains the api-communications format version and `status` contains either `success` or `failed`.

### One day (Found)
```jsonc
{
    "format": 0,
    "status": "success",
    "endpoint_name": "/v0/foodmenu/day",
    "filters": {
        "weekday": false,
        "redday": false,
        "day": 1
    },
    "weeks": {
        "11": {
            "1": {
                "vegetarian": ["Pankakor","Pankakor med sylt och grädde."],
                "non_vegetarian": ["Pankakor","Pankakor med sylt och grädde."]
            }
        }
    }
}
```

### One day (Not Found)
```jsonc
{
    "format": 0,
    "status": "failed",
    "endpoint_name": "/v0/foodmenu/day",
    "filters": {
        "weekday": true,
        "redday": false,
        "day": 7
    },
    "weeks": {}
}
```

### One week
```jsonc
{
    "format": 0,
    "status": "success",
    "endpoint_name": "/v0/foodmenu/week",
    "filters": {
        "weekday": false,
        "redday": false,
        "day": null
    },
    "weeks": {
        "11": {
            "1": {
                "vegetarian": ["Pankakor","Pankakor med sylt och grädde."],
                "non_vegetarian": ["Pankakor","Pankakor med sylt och grädde."]
            },
            ...
        }
    }
}
```

### One year
```jsonc
{
    "format": 0,
    "status": "success",
    "endpoint_name": "/v0/foodmenu/year",
    "filters": {
        "weekday": false,
        "redday": false,
        "day": null
    },
    "weeks": {
        "1": {
            "1": {
                "vegetarian": ["Pankakor","Pankakor med sylt och grädde."],
                "non_vegetarian": ["Pankakor","Pankakor med sylt och grädde."]
            },
            ...
        },
        ...
    }
}
```

### API Errors
Incase the api encounters an error rather then a `failed` request, the return is in the format of:
```jsonc
{
    "error": "<string:error>",
    "msg": "<string:optional>"
}
```

<br><br>

## Day strings
Since the `foodslist.json` contains strings in the format `<food_name>; <food_description>` the api returns both dish name and description. This as a set or two `["<food_name>","<food_description>"]` incase no semi-colon in the list a set of two is still returned but `["<food_name>",""]` *(Here the entire entry is interprited as name)*<br>
**Note! Incase a semicolon is in the list but one side is empty the returned field is also empty, so for example `;<food_description>` gives `["","<food_description>"]**