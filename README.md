# TebakKata - LINE Chatbot Sample

This is sample of LINE chatbot app. It uses CodeIgniter PHP framework. We only use controller Webhook.php and model Line_model.php for this chatbot. 

## How to install

- clone or download repo, place on your server
- create new MySQL database
- setup database configuration in application/config/database.php
- paste your Channel Access Token on value of property `$accessToken` in application/models/Line_model.php
- open app on your browser to install migration
- open LINE Developer page of your bot. On page Basic Information add https://yourdomain.com/ to Webhook URL field (your domain must be HTTPS). Make sure your webhook is valid by click VERIFY button
- send message to your bot on LINE messenger. You may want to block and unblock the bot first