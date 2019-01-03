#Azure Files

Azure Blobs tool to remove older files, useful when use with backups tah will be remove after n days

# Usage

To use adjust your requirements for this example:

    php app.php delete \
      --connectionString 'AccountName=YourAccoutNameHere;AccountKey=YourAccountKey' \
      --containerName 'backup'
      --olderThan '30 days ago' \

## Options

There is a full list of available actions:

    php app.php list
    
    
### Delete action

To show al available options use `--help`:
    
    php app.php delete --help
        
