# blog-In
The (b)log-In blogging system.

(b)log-In (a play on blogging, login and inline) is a custom CMS built specifically for blogging that emerged from my efforts to make WordPress a simpler platform.
It is a relatively simple PHP site connecting to a MySQL database.
The core idea of (b)log-In is that each day is a self-contained unit which, by default reads chronologically — top down — opposite to a traditional blog. This can be changed to suit your needs.

The system is indieweb aware from the outset with microformats2 markup and the ability to send and receive webmentions, although not all types of webmention are supported.

Posts are written, edited, updated and even deleted inline in the browser. Markdown Extra is supported along with custom markup to make life easier. More on that later. Comments can be deleted by the admin inline and file uploads are supported, again all inline, without the need to drop to any back end. An admin page exists but this is only for site settings.

This CMS includes the following external projects:

- Emanuil Rusev's [Parsedown](https://github.com/erusev/parsedown) & [ParsedownExtra](https://github.com/erusev/parsedown-extra)
- [bigfoot.js](http://www.bigfootjs.com/)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)
- [indieweb-mention-client-php](https://github.com/indieweb/mention-client-php)
- [php-mf2](https://github.com/microformats/php-mf2)

## Prerequisites

- PHP, recommended v7 or later
- MySQL with a database already created
- database accounts
    - it is recommended to have one with write/create permissions and one with read-only (SELECT) permissions
    - just one account can be used for everything but this will be less secure

## Postrequisites

- after installation, a cron job running once a day (at time of choice but ideally around midnight) to build the Daily RSS feed

## Installation

Upload ALL files and folders to the desired location on your server. The system uses .htaccess files to set the default file for each folder, e.g. admin.php in the /admin/ folder.

In a browser, navigate to http(s)://your_chosen_blog_address/setup.php — you will asked to enter an initial set of values:

- blog name
- blog address (this is the base URL)
- admin account username & password
- email address
- database name
- database read account & password
- database write account & password
- desired table prefix

Once submitted, the database tables are automatically created and you will be taken to the admin page for the first time to complete other key pieces of information:

- URL to an avatar image
- description (is inserted into the meta description tag)
- about text - appears on the About page, supports Markdown
- SMTP server details
    - host address
    - username
    - password
    - port number
- post order – ASC (chronological) or DESC (reverse chron)
- post titles (on or off)
- post meta (on or off, shows if post order is DESC)
- comment moderation (on or off)
- date format (UK or US format)
- timezone (manual free entry, must be in standard format, e.g. Europe/London)
- journal (on or off – shows/hides the streak/link in header)

Saving these details successfully takes you straight to the blog.

Admin settings can be changed at any time when logged in – a floating cog icon is shown in the bottom-right corner of the page.

## Usage

(b)log-In uses PHP sessions to manage logging in. If not logged in the (b)log-In link top-right goes to the login page, entering the correct credentials creates a random hash of the admin password which is valid ONLY for that browser session. Closing the browser or logging in via another browser/device invalidates any existing session.

When logged in, if no posts exists for that day a 'new post' form will be displayed. Posts can be published immediately or saved as drafts. Once a posts has been published on that day the form is hidden and new posts can be added by clicking/tapping the floating '+' toggle in the top-right corner of the page. This toggle also places the blog in 'edit' mode where existing posts can be modified or deleted using the icons visible by each.

![media uploads](https://colinwalker.blog/images/image_light.png)





