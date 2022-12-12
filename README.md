# Laminas-Cron

This module provides the possibility to configure the applications crontabs via Laminas config.

The only crontab which has to be run "outside" on the host machine is 

`* * * * * vendor/bin/laminas-cron process`

The processable jobs are being executed in parallel (sub processes) thanks to `amphp/process`.

Use `vendor/bin/laminas-cron help` to show all possible commands.