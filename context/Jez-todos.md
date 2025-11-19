# Jez Notices these issues:
- Command center takes a really long time to load from the dashboard, it's css looks wonky, no notifications load, it should be a live scrolling view of all notifications as they come in. 
- hero header alert area should be a header area displaying small alert cards relevant to that customer in real time.
- Device lifecycle for device CRON testing is broken / will not load
- Cache refresh cron currently logs OAuth timeout for page 17 even though column mismatch is fixed; follow-up is to add retries + quiet mode before marking resolved.
- Create `/home/resolut7/logs/refresh-cache-chunked.log` (already uploaded) and keep the cron appending there so AI agents can inspect the raw JSON without email spamming.

# Jez's Wishlist - do not work on these without permission.

[ ] When command center is working correctly, create a mobile-friendly version for smart phone monitoring and alerts.
[ ] desktop widget for alerts (after everything else is working)
