TODO: 
1. UI: 
	1.1: user request UI （request.html + request_handle.php, can refer to screenshot 3-4-5）
	1.2: admin management UI : if we (manager) do not mind, then we can leave this now, as user may not need to see it. Anyway, the UI can be changed in three functions in auto_aggregate.php file (function ab_queue, ab_proofed, ab_rejected).
2. Email notification: If a RSS request, it should send a notification email to site manager, so he/she can start to check whether to accept or reject the requests. It just needs to add a extra table in database to store the email address who should be notificated and add send email out after user submit their requests. We can set up cron to leave the checking for who day. 

Dependency: 
1. autoblogged plugin database structure: 2.7
2. wordpress core: 3.1

Jianhua Shao
http://cs.nott.ac.uk/~jus