# Setting up Behat

# Links

* [Moodle development instructions](https://moodledev.io/general/development/tools/behat/running)
* [Moodle browser config profiles](https://github.com/andrewnicols/moodle-browser-config)
* [README for the above - important](https://github.com/andrewnicols/moodle-browser-config/blob/main/README.md)
* [Docker / Selenium / Firefox](https://github.com/SeleniumHQ/docker-selenium)

# Setup browser config

* In the Docker compose directory clone moodle-browser-config so that you end up with a directory of the same name
* Enter moodle-browser-config and copy config-dist.php to config.php
* Edit config.php and change/uncomment setting for 'seleniumUrl' to 'http://selenium-hub:444/wd/hub'

# Setup docker compose

Add the following additional containers to the docker compose file...

```
  firefox:
    image: selenium/node-firefox:beta
    shm_size: 2gb
    depends_on:
      - selenium-hub
    environment:
      - SE_EVENT_BUS_HOST=selenium-hub
      - SE_EVENT_BUS_PUBLISH_PORT=4442
      - SE_EVENT_BUS_SUBSCRIBE_PORT=4443

  selenium-hub:
    image: selenium/hub:latest
    container_name: selenium-hub
    ports:
      - "4442:4442"
      - "4443:4443"
      - "4444:4444"
```

In the 'php' service, add an additional volume, '- ./moodle-browser-config:/var/moodle-browser-config'

Build and start docker containers using the above settings

# Create Moodle database and moodledata area for behat

* Create a new database for behat
* Create a new moodledata area under ./app (e.g., ./app/behat_moodledata)

# Configure Moodle's config.php

Go down to section 11 in config.php and add / edit / uncomment, as required...

```
$CFG->behat_wwwroot = 'http://web:8082';
$CFG->behat_prefix = 'bht_';
$CFG->behat_dataroot = '/var/behat_moodledata';
$CFG->behat_dbname = 'behat44';
$CFG->behat_dbuser = 'root';
$CFG->behat_dbpass = 'purple';
$CFG->behat_dbhost = 'mysql'; 
```

(Adjust settings as required. Note that behat_wwwroot MUST be different to wwwroot)

Go down to the bottom of config.php and add, just before the line to require setup...

```
require_once('/var/moodle-browser-config/init.php');
```

# Setup Behat

This is very similar to PHPUnit. 

Exec into the php docker container, which should look something like this...

```
docker exec -it moodle44-php-1 /bin/bash
```

Then go to the root of the Moodle installation and run the Behat initialisation script...

```
cd /app/public
php admin/tool/behat/cli/init.php
```

When this is complete it should be possible to run Behat tests. The basic test (everything) is as follows...

```
vendor/bin/behat --config /var/behat_moodledata/behatrun/behat/behat.yml --profile=headlessfirefox  --tags=@mod_choice
```

Note: The above uses profile to access the selenium/firefox configuration setup in Docker. The --tags setting is (here) just
accessing the mod_choice activity tests. Any "Frankenstyle" name can be substituted. Running ALL the tests takes forever.


