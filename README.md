# Laminas-Cron

This module provides the possibility to configure the applications crontabs via Laminas config.

The only crontab which has to be run "outside" on the host machine is

`* * * * * vendor/bin/laminas-cron process`

The processable jobs are being executed in parallel (sub processes) thanks to `amphp/process`.

You can enable a second crontab for monitoring purposes. Adapt the execution time to your needs:

`? ? ? ? ? vendor/bin/laminas-cron monitoring`

The monitoring job is separate to not interfer with errors which are happening during processing. Monitoring would be useless if the monitoring job dies within exectuion.

Use `vendor/bin/laminas-cron help` to show all possible commands.