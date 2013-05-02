# $Id: README.txt,v 1.18.2.2.2.13 2010/02/02 19:25:52 designerbrent Exp $


--- README  -------------------------------------------------------------

Blueprint, Version 1.2

Written by Ted Serbinski, aka, m3avrck
  hello@tedserbinski.com
  http://tedserbinski.com


Co-Maintainer:

Brent Hardinge, aka, designerbrent
 designerbrent@gmail.com
 http://brenthardinge.net/
 
 
Contributors:

Richard Burford, aka, psynaptic
 rich@freestylesystems.co.uk
 http://www.freestylesystems.co.uk

Requirements: Drupal 6.x



--- FEATURES --------------------------------------------------------

- uses Blueprint 0.8 (or greater) CSS framework: http://www.blueprintcss.org/
- normalizes Drupal's CSS to be consistent
- properly aggregates all blueprint CSS files into a single file when this setting is enabled
- put scripts at bottom of page for nice performance gains, read more: http://developer.yahoo.com/performance/rules.html#js_bottom
- flexible layout, from 1 to 3 columns, based on where you configure your blocks to show (left, center, right)
- SEO optimization without the need for heavy modules and additional queries per page
  - automatically adds META description to many pages, read more: http://googlewebmastercentral.blogspot.com/2007/09/improve-snippets-with-meta-description.html
  - automatically adds META keywords if taxonomy exists on that node, read more: http://searchengineland.com/070905-194221.php
  - avoid duplicate titles in search indexes for pager pages: http://www.seo-expert-blog.com/blog/avoiding-duplicate-title-tags-on-pager-pages-in-drupal
- better forum icons, http://drupal.org/node/102743#comment-664157
- improve forum display and performance, http://www.sysarchitects.com/node/70
- prevents duplicate form submissions with jQuery, read more: http://tedserbinski.com/2007/01/11/how_to_prevent_duplicate_posts
  loading animation care of: http://www.ajaxload.info/
- shows the # of comments below a node since Drupal doesn't do this by default (usability)
- add permalinks to each comment (usability)
- highlight any comments by the author of the node
- adds a "you need to login / register" box below all comments if you can't add a comment (usability)
- supports conditional comment subjects, if the setting is off it won't show a chopped off title of the comment
- uses CSSEdit http://macrabbit.com/cssedit/ comments for grouping of styles
- lots of comments and theming tricks in template.php to learn from :)

Drupal 6 version:
- Hover over blocks to reveal admin links to edit and configure the blocks as well as edit the menu blocks.
- Support for sub-themes.

--- INSTALLATION --------------------------------------------------------

1. Place the blueprint folder in your themes directory.

2. Download Blueprint http://www.blueprintcss.org/
   a. Extract folder, creating something like "joshuaclayton-blueprint-css-28c8aa9ae2686442e00a5c7f46dfe2de76b3bd83"
   b. Rename to "blueprint"
   c. Ensure your path looks like themes/blueprint/blueprint/blueprint/screen.css

3. Enable theme under Administer > Site building > Themes



--- TIPS --------------------------------------------------------

- put your custom styles in css/style.css
- put any IE hacks into css/ie.css (conditionally loaded as needed)
- admin/build/themes/settings
  - enable site slogan (add one, good for SEO)
  - enable mission statement (used as META description for homepage in search engines)
  - enable user pictures
- Configure the comments for each node type 
  - admin/content/node-type/<typename> - Flat list - expanded, Date - oldest first, Display below post or comments.
  - You will need to do this for each content type that has comments enabled.
- (performance) remove line 76 in screen.css : .showgrid {background:url(src/grid.png);}  
  this saves an uncessary HTTP request on your server
- (performance) apply system.css.patch to remove uncessary HTTP requests to images that Blueprint overrides already



--- USING BLUEPRINT --------------------------------------------------------

Blueprint aligns designs to a grid. If you add a class "showgrid" to any <div class="container showgrid"> it will
show you the grid it is working with, both in terms of columns and rows. You can read more by following the links
on the homepage: http://www.blueprintcss.org/

To get Blueprint to work with Drupal (because Drupal adds in paddings, borders, and more), we need to override
some values in Drupal and recalculate others so that it more closely follows the grid. Here are how such
calculations work:

# Default values
  - Browsers default font-size: 16px
  - Base font-size: 75% = 12px = 1em
  - Base line-height: 1.5em = 18px

# Calculating font size
  * font-size (em) to font-size (px):
    base * relative = result
    12px * 0.9em    = 10.8px

  * font-size (px) to font-size (em):
    font-size / base font-size = relative font-size
    15px      / 12px           = 1.25em

# Calculating line height
  * font-size (px) to line-height (em):
    base line-height / font-size = line-height
    18px             / 10px      = 1.8em

    * font-size (em) to line-height (em):
    base line-height / (base font-size * font-size) = line-height
    18px             / (12px           * 0.9em    ) = 1.667em

# Calculating pixel size
  1 / font-size = 1 pixel in ems
  1 / 12px      = 0.0834em

# Using horizontal borders
  The box model states that border width is added to the dimensions of the box. This means whenever a top or bottom border
  is used, we must account for the extra height by decreasing the padding.

  Example

  Here we need to adjust the padding-bottom value to account for the extra pixel added by the border-bottom:

    .class {
      border-bottom: 1px solid #ccc;
      padding-bottom: 1.5em;   /* WRONG!! */
    }

  The line height is 18px and putting a padding-bottom of 1.5em gives us a nice full line break below. But because we
  have a border too, we need to do slightly less than 1.5em (e.g., 18px - 1px):

    required pixels / base-font = em value
    17px            / 12px      = 1.4167em

    .class {
      border-bottom: 1px solid #ccc;
      padding-bottom: 1.4167em;   /* CORRECT :) */
    }


--- NOTE: -----------------------------------------------------------
Blueprint theme does not include the Drupal $logo variable. 

The preferred method for adding a logo to the design is to do it in the CSS was a background image. An example of the way I typically do it is as follows:

h1 a{
width: [width of logo];
height: [height of logo];
display:block;
text-indent: -99999em;
background: url(../images/logo.png) no-repeat left top;
}

What this ends up doing is removing the text title of the site, and replacing it with a background image of the logo that is still linked to the homepage of the site.


--- CHANGELOG --------------------------------------------------------

1.x, 2010-02-02
----------------------
- Fixed: Added a temporary fix to the BlueprintCSS that makes the input boxes not aligned properly. [#700464]

1.x, 2008-12-08
----------------------
- Fix sidebar variables so they don't build unnecessary nested divs when using sub-themes.

1.x, 2008-10-01
----------------------
- add admin links in each block on hover to edit and configure the blocks as well as edit the menu blocks.

1.x, xxxx-xx-xx
----------------------
- add permalinks to each comment

1.2, 2008-09-04
----------------------
- add missing header region
- add missing primary and secondary links

1.1, 2008-08-26
----------------------
- Don't allow search engines to index duplicate pages created by the pager
  More: http://www.seo-expert-blog.com/blog/avoiding-duplicate-title-tags-on-pager-pages-in-drupal
- Show node body as meta description if teaser not available


1.0, 2008-06-04
----------------------

- Initial 1.0 release
