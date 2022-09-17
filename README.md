# Notion API script to create recurring tasks
by Colette Snow <colettesnow.com>

This is unsupported - use at your own risk.

You need a database with these fields at a minimum:

- Title
- Done (checkbox)
- Due Date (date)
- Recurring Unit (Single select - day, week, month)
- Recurring Interval (number)
- Next Due Date (Formula):

```
empty(prop("Recurring Interval")) ? prop("Due Date") : dateAdd(prop("Due Date"), prop("Recurring Interval"), prop("Recurring Unit"))
```

It loops through your tasks, finds those marked as done, creates a new task with the same properties
(except done is unchecked) due on the date in Next Due Date, and archives the original task.

If you have relation fields that are going to empty, you need to add logic or unset them.
See example of how to do this at line 73. The API will complain if you try to add these and
they are empty.

Fill $secret with your Notion API secret, and database ID with the ID of your task database.
Be sure to share your database with your Notion API integration (the three dots -> add connections).

You will need to find some way to run this regulary such as cron or IFTTT.
