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

