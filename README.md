# supervisor-bundle
Symfony bundle to create/update supervisor configurations and control supervisor process

## Installation
Install with composer:
```sh
composer require imper86/supervisor-bundle
```

Add bundle to your bundles.php:
```php
Imper86\SupervisorBundle\Imper86SupervisorBundle::class => ['all' => true]
```

## Configuration
To start using this bundle you must configure your supervisor "instances".
Instances are groups of commands that are controlled together.

So, if you want some commands to be started/stopped/etc separately it's
possible by using another instance.

Example config:
```yaml
imper86_supervisor:
    # you can define workspace dir for bundle, this is default:
    workspace_directory: '%kernel.project_dir%/var/imper86supervisor/%kernel.environment%'

    # to start using bundle you must configure your instances and worker commands
    instances:
        default:
            commands:
                messenger:
                    command: 'messenger:consume async_high async_low --limit=50'
                    numprocs: 1
                messenger_multiproc:
                    command: 'messenger:consume async_multiproc --limit=50'
                    numprocs: 4
                crawler:
                    command: 'app:crawl'
                    numprocs: 1
        enqueue:
            commands:
                enqueue:
                    command: 'enqueue:consume --message-limit=10'
                    numprocs: 4
        foo_instance:
            commands:
                foo:
                    command: 'app:foo'
                bar:
                    command: 'app:bar'
```

## Console commands

* **i86:supervisor:rebuild** - this command will:
    * stop running workers
    * create config files using your configuration
    * ask do you want to start workers
* **i86:supervisor:control** - use this command with argument:
    * **stop** - to stop running workers
    * **status** - to get supervisor's current status
    * **start/restart** - starts workers with current configuration
* **i86:supervisor:clean:dirs** - Removes every directory in workspace, which is not currently configured
* **i86:supervisor:clean:logs** - removes all .log files related to the bundle 

## Contributing
Any help will be very appreciated :)
