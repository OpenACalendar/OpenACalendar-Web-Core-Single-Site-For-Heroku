# OpenACalendar for Heroku

## Installing

Set up a Heroku app.

Add a Postgres database.

    heroku addons:add heroku-postgresql

Add Mandrill for email sending.

    heroku addons:add mandrill

Add the following config variables:

 * SITE_NAME
 * ADMIN_EMAIL - email of admin.
 * ADMIN_USERNAME - username admin wants.
 * ADMIN_PASSWORD - password admin wants. This will only be used when the account is set up - after this they should change their password as normal.
 * URL
 * SYSADMIN_EXTRA_PASSWORD

Deploy the Heroku app.

Run this to install the database tables, import static data and set up the first user. 

    heroku run php /app/setup.php

The app can now be used in a web browser.

Call /cronWeb.php regularly to update caches.



