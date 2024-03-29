# blog-In

## Please note:

_This is an old version of (b)log-In and does not reflect the current system I use (either in range of functionality or security). It can be used freely for reference purposes and forked according to the licence._

### The (b)log-In blogging system.

(b)log-In (a play on blogging, login and inline) is a custom CMS built specifically for blogging that emerged from my efforts to make WordPress a simpler platform.
It is a relatively simple PHP site connecting to a MySQL database. I created it as a way of meeting my owns needs and getting my blog off of WordPress. The concept started as a custom overlay on top of WordPress that didn't require using wp-admin but I converted it to a standalone system.

The core idea of (b)log-In is that each day is a self-contained unit which, by default reads chronologically — top down — opposite to a traditional blog. This can be changed to suit your needs.

The system is indieweb aware from the outset with microformats2 markup and the ability to send and receive webmentions, although not all types of webmention are supported.

Posts are written, edited, updated and even deleted inline in the browser. Markdown Extra is supported along with custom markup to make life easier. More on that later. Comments can be moderated (if the option is enabled) and deleted by the admin inline and file uploads are supported, again all inline, without the need to drop to any back end. An admin page exists but this is only for site settings.

This CMS includes the following external projects:

- Emanuil Rusev's [Parsedown](https://github.com/erusev/parsedown) & [ParsedownExtra](https://github.com/erusev/parsedown-extra)
- [bigfoot.js](http://www.bigfootjs.com/)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)
- [indieweb-mention-client-php](https://github.com/indieweb/mention-client-php)
- [php-mf2](https://github.com/microformats/php-mf2)

It also includes a modification of a [form-caching trick](https://git.jlel.se/jlelse/GoBlog/src/commit/fd844bbfc1c22f96d603546b59a80e271fde5bd0/templates/assets/js/formcache.js) from Jan-Lukas Else so you don't lose your post if the tab is accidentally closed.

### Blogs using (b)log-In

- [Colin Walker](https://colinwalker.blog)

## Prerequisites

- PHP, recommended v7 or later
- MySQL with a database already created
- database accounts
    - it is recommended to have one with write/create permissions (create tables, INSERT, UPDATE, DELETE) and one with read-only (SELECT) permissions
    - just one account _can_ be used for everything but this will be less secure

## Postrequisites

- after installation, a cron job running once a day (at time of choice but ideally around midnight) to build the Daily RSS feed
- replace generic avatar favicons (favicon-16x16.png, favicon-32x32.png, favicon.ico) with your own

## Installation

Upload ALL files and folders to the desired location on your server. The system uses .htaccess files to set the default file for each folder, e.g. admin.php in the /admin/ folder. Alternatively, you could rename `blog.php` and each file in the folders about, admin, colophon, joinme, journal, login, search to `index.php` to avoid the need for the .htaccess files. 

In a browser, navigate to http(s)://your_chosen_blog_address/setup.php — you will be asked to enter an initial set of values:

- blog name
- blog address (this is the base URL)
- admin account username & password
- email address
- database name
- database read account & password
- database write account & password
- desired table prefix

Once submitted, the database tables are automatically created, setup.php is deleted and you will be taken to the admin page for the first time to complete other key pieces of information:

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

When logged in, if no posts exists for that day a 'new post' form will be displayed. Posts can be published immediately or saved as drafts (see below) – draft will be indicated by the word 'DRAFT' ahead of the post content, clicking this word switches that post to edit mode. Once a posts has been published on that day the form is hidden and new posts can be added by clicking/tapping the floating '+' toggle in the top-right corner of the page. This toggle also places the blog in 'edit' mode where existing posts can be modified or deleted using the icons visible by each.

Media uploads are handled using an iframe above the post form which can be toggled using the picture icon by the Post button.

![Media uploads](https://colinwalker.blog/uploads/2021/06/mediauploads.png)

Choosing a file, click upload then clock the 'Copy file path' to have the direct URL placed in the clipboard for easy insertion into a post. Re-uploading a file of the same name with overwrite the original.

Each post is preceeded by a permalink (#) and a comment icon, the comment icon acts as a toggle to show/hide the comments section for that post and will appear 'filled' when a post has comments. When logged in, each comment will have a delete icon by it. Top level comments can be replied to by clicking the reply arrow next to the commenters name. The system will attempt to pull in an avatar for each commenter (either via microformats on the source site or favicon) and default to a monogram if none can be found. When comment moderation is enabled on the admin page the comment form includes a "Comments are moderated" placeholder and all comments will require approval before being displayed. A tick to approve the comment will be shown next to the usual delete icon:

![Comment moderation](https://colinwalker.blog/uploads/2021/06/moderation.png)

## Draft posts

Drafts are inserted into the post list where they are initially created but not given a post/section number. When a draft is changed to published its permalink, date, and post/section number will be updated to ensure it is listed as the most recent post on the blog.

Drafts can be edited within the post list on the main blog page or on the separate `/drafts/` page (only accessible when logged in) which will list all draft posts. If drafts exist, an indicator will show at the top of the page, this is also a link to the `/drafts/` page:

![Drafts indicator](https://colinwalker.blog/uploads/2021/08/drafts_icon.png)

## Markup

In addition to Markdown, custom markup can be used to enhance and simplify posting:

- `[hr]` – a break/horizontal rule of width 33% 
- `~underline~`
- `~~strikethrough~~`
- `^superscript^`
- `!!summary>Summary details!<` – details/summary tag
- `[a[link here]a]` - embed audio
- `[v[link here]v]` - embed video (e.g. self-hosted)
- `[y[video ID "optional title"]y]` - embed YouTube video – you only need to include the YouTube video ID (e.g. A0GgrQXB1tU) but can include an optional title to be displayed in the fallback text if the video iframe can not be displayed
- `::highlighted text::` – a custom highlight option
- `==marked text==` – the HTML mark tag
- `((link to be liked))` - insert properly formatted link to send a webmention Like
- `(r(link to reply to)r)` – insert properly formatted link to send a webmention Reply
- `!(link to bookmark)!` – insert properly formatted link to send a webmention Bookmark

One other piece of markup I use in the journal allows me to divide entries up into sections. Inserting `@@` at the start of a line will add a formatted hash character which looks like the blog permalinks but is purely visual.

Using a feature of MarkdownExtra, images can have classes applied, the format is as below:

`![alt text](image URL){.classname}`

Valid classes:

- .aligncenter
- .left (align left, width 48%)
- .right (align right, width 48%)
- .i50 (align center, width 50%)
- .i60 (align center, width 60%)
- .i75 (align center, width 75%)
- .i80 (align center, width 80%)
- .i90 (align center, width 90%)
- .i100 (align center, width 100%)
- .noradius (all images have a border radius by default, applying this removes it for that image)

The .clickable and .clickabledark classes are a special case and are applied to a link where that link contains a thumbnail of a larger image. This adds an overlay to indicate that the image can be expanded. The format is a markdown link containing a markdown image and is as follows:

`[![image alt text](thumbnail link){.optional_image_class}](image link){.clickable}`

Use .clickabledark if the image is light and the overlay will not be easily visible.

and looks like this:

![clickable image](https://colinwalker.blog/uploads/2021/06/clickable.png)

It works for centred images and not those that are left or right aligned.

## Indented/sub posts

Posts can be indented so as to act as sub posts – ideal for following up to a previous post. To achieve this precede the post content with `>>`
This may technically clash with nested Markdown blockquotes but if nested quotes are required just separate the `>` with a space.

## Linked hashtags

Any hashtags included in post content will automatically be converted into search links for that tag.

## Fragmentions and comment links

(b)log-In supports the original proposal for fragmentions; highlighting a paragraph that includes test passed via the URL in the format `##required+text+here`

Comments can be linked to directly using a URL parameter: `&c=1:3` – where 1 is the first post on the page and 3 is the third comment on that post.
