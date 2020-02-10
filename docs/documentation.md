# Installation

`$ composer require sigma-z/simtt`
`$ ./vendor/bin/simtt --version`


# How it works

The simple time tracker (Simtt) can be used interactively
 or non-interactively. All logs will be stored in simple text files.

**Interactive mode:**
```
$> simtt -i
```

Running Simmt interactively means the command line script will execute
 continuously.

**Non-interactive mode:**
```
$> simtt [options|command]
```


## Screencast tutorials

### Start timer and Status

![Start timer and Status](https://github.com/sigma-z/simtt/raw/master/docs/assets/start_timer_and_status.gif)


### What have I done today?

![day and day sum](https://github.com/sigma-z/simtt/raw/master/docs/assets/day_and_day_sum.gif)


### What did I do yesterday?

![yesterday and yesterday sum](https://github.com/sigma-z/simtt/raw/master/docs/assets/yesterday_and_yesterday_sum.gif)


## How to track time

### Start timer

* `start` starts timer NOW for an anonymous task.
* `start [<time>hhmm|hh:mm]` starts a timer for an anonymous task at a given start time.
* `start [task-name]` starts timer NOW for a named task.
* `start [<time>hhmm|hh:mm] [task-name]` starts a timer a named task at a given start time.
* `start* [<time>hhmm|hh:mm]` Updates the timer start of the last entry


### Stop timer

* `stop` stops running timer NOW.
* `stop [<time>hhmm|hh:mm]` stops a running timer at a given stop time.
* `stop [task-name]` stops timer NOW for a named task (overwrites old name, if has been defined at the timer start).
* `stop [<time>hhmm|hh:mm] [task-name]` stops a timer a named task (overwrites old name, if has been defined at the timer start) at a given stop time.
* `stop* [<time>hhmm|hh:mm]` Updates the timer stop of the last entry


### Status of the timer

You can show the status to see, if time is currently tracked for a task.

Usage `status`


## Tasks

You can add or update a task text.

Usage `task[-n] [<string>task-name]`

When `task` is called, it sets a task text to the currently running task.
If no task is being time tracked, it asks to set the task text to the last task.

When `task 1 [<string>task-name]` is called, it sets the task text to the last task.


## Comments

You can add or update a comment text.

Usage `comment[-n] [<string>comment]`

When `comment` is called, it sets a comment to the currently running task.
If no task is being time tracked, it asks to set the comment to the last task.

When `comment-1 [<string>comment]` is called, it sets a comment to the last task.


## Log & Summary

Simtt can show log entries in a sequence, for a day, a week or a month
 and also summarize entries by tasks.


### Log

Usage `log [<int>range-selection]`

Examples:
* `log` shows the last 15 entries (configurable - see [Configuration](#Configuration)).
* `log all` shows all log entries.
* `log 100` shows the last 100 log entries.
* `log 100-120` shows the log entries from 100 to 120 (21 in total, if available).


### Date log

Usage `[day|yesterday|week|month][-n] [sum]`

Examples:
* `day` shows log entries of today.
* `yesterday` shows log entries of yesterday.
* `day[-num of days]` shows log entries of the day minus *'num of days'*, `day-2` would be the day before yesterday.
* `day sum` shows summarized entries of today.
* `week sum` shows summarized entries of the current week.
* `month sum` shows summarized entries of the current month.
* `month-1 sum` shows summarized entries of the last month.


## Recent tasks

You can show the last 15 task names (configurable - see [Configuration](#Configuration)).

Usage `tasks`


### Want to know about 'now'?

You can see what 'now' is like in simtt (maybe different from current time because of your settings in the configuration - see precision):

Usage `now`


## Motivation

I wanted an easy time tracker for my tasks.

I wanted the tool not using a database for easy editing the log files. (badcrocodile/cltt is using an SQLite database)

I wanted to learn some new technology.

I wanted to build something that would help others.

I wanted the project to be developed using TDD and DDD.
