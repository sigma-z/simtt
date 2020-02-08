Interactive command overview:

`start [<time>hhmm|hh:mm] [task-name]`
> starts a timer at a given time for a named task. Note: time and task name can be left blank.

`start* [<time>hhmm|hh:mm] [task-name]`
> updates the start time for a timer at a given time for a named task. Note: time and task name can be left blank.

`stop [<time>hhmm|hh:mm] [task-name]`
> stops a timer at a given time for a named task. Note: time and task name can be left blank, a given task name will overwrite the task name given at the start.

`stop [<time>hhmm|hh:mm] [task-name]`
> updates the stop time for a timer at a given time for a named task. Note: time and task name can be left blank, a given task name will overwrite the task name given at the start.

`status`
> shows status whether a task is running, or not.

`tasks`
> shows a list of the latest time tracked tasks

`log [range-selection<int>]`
> shows the latest log entries by range.

`day [sum]`
> shows the log entries of today. If "sum" is defined, it shows the log entries summarized.

`day-1 [sum]` or `yesterday [sum]`
> shows the log entries of yesterday. If "sum" is defined, it shows the log entries summarized.

`day-n [sum]`
> shows the log entries for n-days before today. If "sum" is defined, it shows the log entries summarized.

You can do the same type of output for `week` and `month`.
