# Simtt - Interactive Simple Time Tracker for the CLI in PHP

[![CI Status](https://github.com/sigma-z/simtt/workflows/Continuous%20Integration/badge.svg)](https://github.com/sigma-z/simtt/actions)

Read the full [documentation](https://github.com/sigma-z/simtt/blob/master/docs/documentation.md)

## Project status

The project is inspired by [badcrocodile / cltt](https://github.com/badcrocodile/cltt).


### Implemented features

This is an early project state. At the moment the tool can do:
- start a timer
- update the start of a timer
- stop a timer
- update the stop of a timer
- status whether a timer is running or not

### Limitations

- at the moment it is not possible to track times across days


## Quick command overview

Usage `./simtt -i` to run the Simple Time Tracker in interactive mode.
 You then can run a lot of commands directly by typing and pressing `<enter>`:

`start [time<hhmm|hh:mm>] [task-title]`
> starts a timer at a given time for a named task. Note: time and task title can be left blank.
- [x] implemented

`start* [time<hhmm|hh:mm>] [task-title]`
> updates the start of last log entry. Note: time and task title can be left blank.
- [x] implemented

`stop [time<hhmm|hh:mm>] [task-title]`
> stops a timer at a given time for a named task. Note: time and task title can be left blank, a given task name will overwrite the task name given at the start.
- [x] implemented

`stop* [time<hhmm|hh:mm>] [task-title]`
> updates the stopping time of last log entry. Note: time and task title can be left blank.
- [x] implemented

`status`
> shows status whether a task is running, or not.
- [x] implemented

`now`
> shows current time (which can be different because of your configuration - see config precision)
- [x] implemented

`task [offset]`
> updates a task text for a specified or currently running task. See also the documentation.
- [ ] implemented

`comment [offset]`
> updates a comment for a specified or currently running task. See also the documentation.
- [ ] implemented

`tasks`
> shows a list of the latest time tracked tasks
- [ ] implemented

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
- [ ] week implemented
- [ ] month implemented

## Motivation

I wanted an easy time tracker for my tasks.

I wanted the tool not using a database for easy editing the log files.

I wanted to learn some new technology.

I wanted to build something that could be of use for others.

I wanted the project to be developed by TDD and DDD.
