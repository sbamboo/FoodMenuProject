# FoodMenu - PHP WebService API
## Members
- Simon Kalmi Claesson (@sbamboo)
- Arvid Ersson (@Astikornipal)
- Otis Gustafsson (@bumblebroo)

*NTI Gymnasiet Skövde - 2025 - TE3s*
<br><br>


## Expected endpoints

### Docs
`api.ntigskovde.se/.../gr1/foodmenu/docs`

### Retrieval
Get by date: *(Converts to year and week number)*<br>
`api.ntigskovde.se/.../gr1/foodmenu?date=2025-03-13`

Get by year and week:<br>
`api.ntigskovde.se/.../gr1/foodmenu?year=2025&week=11`

Get by only week number: *(Resolves current year)*<br>
`api.ntigskovde.se/.../gr1/foodmenu?week=11`

Get by only year: *(Returns a list of the entries for the weeks, non-indexed but week-1 is entry 1, number of weeks depends on year)*<br>
`api.ntigskovde.se/.../gr1/foodmenu?year=2025` => `{}`

**Al of the above may add the param ?excludeWeekends and ?excludeRedDays**<br>
**Aswell as ?day being an index from 1-7 if list does not include 7 entries and day-index is the amount the return is empty**
<br><br>


## Functionaliy
1. The dishes for a week are randomised using a seed `<year>;<week>`, which generates 7 entries.
2. If a red-day or weekday filter was applied we filter out thoose.
3. If a day was requested we retrieve the day, if an entire year we return a list of all, else we return the week.
<br><br>

## Returned data

### One day (Found)
```jsonc
{
    "format": 0,
    "endpoint_name": "/foodmenu/day",
    "filters": {
        "weekday": false,
        "redday": false,
        "day": null
    },
    "query_was_valid": false,
    "weeks": {
        "11": {
            "1": "Pankakor; Pankakor med sylt och grädde."
        }
    }
}
```

### One day (Not Found)
```jsonc
{
    "format": 0,
    "endpoint_name": "/foodmenu/day",
    "filters": {
        "weekday": false,
        "redday": false,
        "day": null
    },
    "query_was_valid": false,
    "weeks": {}
}
```

### One week
```jsonc
{
    "format": 0,
    "endpoint_name": "/foodmenu/week",
    "filters": {
        "weekday": false,
        "redday": false,
        "day": null
    },
    "query_was_valid": false,
    "weeks": {
        "11": {
            "1": "Pankakor; Pankakor med sylt och grädde.",
            ...
        }
    }
}
```

### One year
```jsonc
{
    "format": 0,
    "endpoint_name": "/foodmenu/year",
    "filters": {
        "weekday": false,
        "redday": false,
        "day": null
    },
    "query_was_valid": false,
    "weeks": {
        "1": {
            "1": "Pankakor; Pankakor med sylt och grädde.",
            ...
        },
        ...
    }
}
```
<br><br>

## Day strings
Since the `foodslist.json` contains strings in the format `<food_name>; <food_description>` the returned day strings are also in the same format.