# Simtt - Interactive Simple Time Tracker for the CLI in PHP

[![Latest Stable Version](https://img.shields.io/packagist/v/sigma-z/simtt.svg?style=flat-square)](https://packagist.org/packages/sigma-z/simtt)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square)](https://php.net/)
[![CI Status](https://github.com/sigma-z/simtt/workflows/Continuous%20Integration/badge.svg)](https://github.com/sigma-z/simtt/actions)

Read the full [documentation](https://github.com/sigma-z/simtt/blob/master/docs/documentation.md)

The project was inspired by [badcrocodile/cltt](https://github.com/badcrocodile/cltt).


## Getting started

### Start timer and Status

![Start timer and Status](https://github.com/sigma-z/simtt/raw/master/docs/assets/start_timer_and_status.gif)


### What have I done today?

![day and day sum](https://github.com/sigma-z/simtt/raw/master/docs/assets/day_and_day_sum.gif)


### What did I do yesterday?

![yesterday and yesterday sum](https://github.com/sigma-z/simtt/raw/master/docs/assets/yesterday_and_yesterday_sum.gif)


## Installation

`$ composer create-project sigma-z/simtt`

**Linux/MacOS**

`$ ./simtt --version`

**Windows**

`$ php simtt --version`


## Implemented features

This is an early project state. At the moment the tool can do:
- start a timer
- update the start of a timer
- stop a timer
- update the stop of a timer
- status whether a timer is running or not


## Limitations

- it is not possible to track times across days


## Quick feature overview

Usage `./simtt -i` to run the Simple Time Tracker in interactive mode.
 You then can run a lot of commands directly by typing and pressing `<enter>`:

`start [time<hhmm|hh:mm>] [task-name]`
> starts a timer at a given time for a named task. Note: time and task name can be left blank.
- [x] implemented

`start* [time<hhmm|hh:mm>] [task-name]`
> updates the start of last log entry. Note: time and task name can be left blank.
- [x] implemented

`stop [time<hhmm|hh:mm>] [task-name]`
> stops a timer at a given time for a named task. Note: time and task name can be left blank, a given task name will overwrite the task name given at the start.
- [x] implemented

`stop* [time<hhmm|hh:mm>] [task-name]`
> updates the stopping time of last log entry. Note: time and task name can be left blank.
- [x] implemented

`status`
> shows status whether a task is running, or not.
- [x] implemented

`now`
> shows current time (which can be different because of your configuration - see config precision)
- [x] implemented

`task[-offset] [<string>task-name]`
> updates a task text for a specified or currently running task. See also the documentation.
- [x] implemented

`comment[-offset] [<string>comment]`
> updates a comment for a specified or currently running task. See also the documentation.
- [x] implemented

`tasks`
> shows a list of the latest time tracked tasks
- [x] implemented

`log [range-selection<int>]`
> shows the latest log entries by range.
- [x] implemented

`day [sum]`
> shows the log entries of today. If "sum" is defined, it shows the log entries summarized.
- [x] implemented

`day-1 [sum]` or `yesterday [sum]`
> shows the log entries of yesterday. If "sum" is defined, it shows the log entries summarized.
- [x] implemented

`day-n [sum]`
> shows the log entries for n-days before today. If "sum" is defined, it shows the log entries summarized.
- [x] implemented

You can do the same type of output for `week` and `month`.
- [ ] week implemented, scheduled for version 1.1.0
- [ ] month implemented, scheduled for version 1.2.0

