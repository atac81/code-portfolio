# Scheduled/automatic e-mail system

Scripts that handle sending e-mails that have been scheduled for execution at a specified date/time.

Cronjob is configured on server to check every minute for any e-mail blasts scheduled for execution right now.  If so, a list of e-mails is generated and each e-mail is sent until the complete list has been executed.

Customer requested this feature in 2017 to allow for scheduling reminder e-mails to those users who have yet to complete all of their assigned surveys.
