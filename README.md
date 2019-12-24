# Simtt - Interactive Simple Command Line Time Tracker in PHP

[![CI Status](https://github.com/sigma-z/simtt/workflows/Continuous%20Integration/badge.svg)](https://github.com/sigma-z/simtt/actions)

Read the full [documentation](https://github.com/sigma-z/simtt/blob/master/docs/documentation.md)

## Project status

This is an early project state. The tool can't do anything at the moment!


## Quick command overview

Usage `./simtt -i` to run the Simple Time Tracker in interactive mode.
 You then can run a lot of commands directly by typing and pressing `<enter>`:

`start [time<hhmm|hh:mm>] [task-title]`
> starts a timer at a given time for a named task. Note: time and task title can be left blank.

`stop [time<hhmm|hh:mm>] [task-title]`
> stops a timer at a given time for a named task. Note: time and task title can be left blank, a given task name will overwrite the task name given at the start.

`status`
> shows status whether a task is running, or not.

`task[-n]`
> sets a task text for a specified or currently running task. See also the documentation.

`comment[-n]`
> sets a comment for a specified or currently running task. See also the documentation.

`tasks`
> shows a list of the latest time tracked tasks

`log [range-selection<int>] [order-direction]`
> shows the latest log entries by range and in the given order direction.

`day [sum]`
> shows the log entries of today. If "sum" is defined, it shows the log entries summarized.

`day-1 [sum]` or `yesterday [sum]`
> shows the log entries of yesterday. If "sum" is defined, it shows the log entries summarized.

`day-n [sum]`
> shows the log entries for n-days before today. If "sum" is defined, it shows the log entries summarized.

You can do the same type of output for `week` and `month`.
