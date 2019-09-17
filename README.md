# Processor add metadata

[![Build Status](https://travis-ci.com/keboola/processor-add-metadata.svg?branch=master)](https://travis-ci.com/keboola/processor-add-metadata)

Processor for adding specified metadata to table manifest file

# Usage

The configuration requires following properties:

- `tables` - array (required): list of objects including table name and metadata to add
    - `table` - string (required): target table name
    - `metadata` - array (required): list of metadata key => value pairs
        - `key` - string (required): metadata key
        - `value` - string (required): metadata value for key

```
{
    "parameters": {
        "tables": [
            {
                "table": "my-table",
                "metadata": [
                    {
                        "key": "bdm.scaffold.table.tag",
                        "value": "bdm.keboola.test-app.my-table-tag1"
                    },
                    {
                        "key": "bdm.scaffold.table.tag",
                        "value": "bdm.keboola.test-app.my-table-tag2"
                    }
                ]
            }
        ]
    }
}

```

## Development

Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/processor-add-metadata
cd my-component
docker-compose build
docker-compose run --rm dev composer install --no-scripts
```

Run the test suite using this command:

```
docker-compose run --rm dev composer tests
```

# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/)
