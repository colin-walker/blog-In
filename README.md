# blog-In
The (b)log-In blogging system.

(b)log-In (a play on blogging, login and inline) is a custom CMS built specifically for blogging that emerged from my efforts to make WordPress a simpler platform.
It is a relatively simple PHP site connecting to a MySQL database.
The core idea of (b)log-In is that each day is a self-contained unit which, by default reads chronologically — top down — opposite to a traditional blog. This can be changed to suit your needs.

The system is indieweb aware from the outset with microformats2 markup and the ability to send and receive webmentions, although not all types of webmention are supported.

Posts are written, edited, updated and even deleted inline in the browser. Markdown Extra is supported along with custom markup to make life easier. More on that later.

This CMS includes the following external projects:

- Emanuil Rusev's [Parsedown](https://github.com/erusev/parsedown) & [ParsedownExtra](https://github.com/erusev/parsedown-extra)
- [bigfoot.js](http://www.bigfootjs.com/)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)
- [indieweb-mention-client-php](https://github.com/indieweb/mention-client-php)
- [php-mf2](https://github.com/microformats/php-mf2)
