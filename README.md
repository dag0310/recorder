# recorder

Record data line by line (CSV mostly).

Optional: In `data/ids.json` you can define where your data should be stored as a file - By default a .csv file will be generated in the `data` directory with the name of the id GET parameter.

## Usage

### Request

`POST /api.php?id=food`

#### JSON body

```json
{ "fields": [3.14, "Ate a PI"] }
```

### Result

data/food.csv:

`2021-01-29T08:30:00+01:00,3.14,Ate a PI`

## Requirements

- PHP 7
