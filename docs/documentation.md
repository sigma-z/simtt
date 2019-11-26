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
$> simtt [options] [arguments]
```

## How to track time

### Start timer 

* `start` starts a timer for an anonymous task.
* `start[:HMM|:HHMM]` starts a timer for an anonymous task at a given start time.
* `start[:task-title]` starts a timer a named task.
* `start[:HMM|:HHMM][:task-title]` starts a timer a named task at a given start time.

### Stop timer

* `stop` stops a running timer.
* `stop[:HMM|:HHMM]` stops a running timer at a given stop time.
* `stop[:task-title]` stops a timer a named task (overwrites old name, if has been defined at the timer start).
* `stop[:HMM|:HHMM][:task-title]` stops a timer a named task (overwrites old name, if has been defined at the timer start) at a given stop time.

### Status of the timer

You can show the status to see, if time is currently tracked for a task.

Usage `status`


## Log & Summary

Simtt can show log entries in a sequence, for a day, a week or a month 
 and also summarize entries by tasks.

### Log 

Usage `log[:selection][:order-direction]`

Examples:
* `log` shows the last 15 entries (configurable with `config:max-log-items` - see [Configuration]). 
* `log:all[:asc|:desc]` shows all log entries in ascending or descending order. 'desc' is the default value.
* `log:100[:asc|:desc]` shows the last 100 log entries by the given order. 
* `log:100-120` shows the log entries from 100 to 120 (21 in total, if available).

### Date log

Usage `[day|yesterday|week|month][-n][:sum]`
  
Examples:
* `day` shows log entries of today.
* `yesterday` shows log entries of yesterday.
* `day-[num of days]` shows log entries of the day minus *'num of days'*, `day-2` would be the day before yesterday.
* `day:sum` shows summarized entries of today.
* `week:sum` shows summarized entries of the current week.
* `month:sum` shows summarized entries of the current month.
* `month-1:sum` shows summarized entries of the last month.

## Tasks

You can show the last 15 task titles (configurable with `config:max-task-items` - see [Configuration]).

Usage `tasks`


## Configuration

Usage for reading a config value `config:[config-name]`

Usage for setting a config value `config:[config-name] [config value]`


**max-log-items**
* `config:max-log-items` reads config 'max-log-items'. 
* `config:max-log-items 20` sets the config 'max-log-items' to 20. Default is 15.

**round-minutes**
* `config:round-minutes` reads config 'round-minutes'.
* `config:round-minutes 5` sets the config 'round-minutes' to 5 minutes. Default is 0.

**max-task-items**
* `config:max-task-items` reads config 'max-task-items'.
* `config:max-task-items 20` sets the config 'max-task-items' to 20. Default is 15.
