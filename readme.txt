=== Plugin Name ===
Contributors: ryusai
Donate link: https://paypal.me/ryusai
Tags: table of contents
Requires at least: 5.0.3
Tested up to: 5.0.3
Stable tag: 2.1
Requires PHP: 5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically extract H tag and insert table of contents.
multifunction, lightweight, change desgin.

== Description ==

##about CSS
CSSdesign was added in 2.0.
You can select 3 from the setting screen.
-non CSS
-base design
-special design

##Configuration
- You can choose to hide or display on pages, posts, or both.
- You can also choose whether to display in tree structure.
- If there are three or more H tags, we will automatically create a table of contents.
  ###Added in 2.0
  -Home : Insert the TOC on the home
    When the homepage selects a fixed page, You can choose whether to display the table of contents.
  This is not a function of the list tag.
    Please use it with the base design.

  -TOC number : Insert the TOC number
    You can add numbers to each item in the table of contents.

  -Headline number : Insert the headline number
    Add a number to the headline of your article.

  -desgin
    -Non CSS : Non design
    -Base CSS : Apply base design
    -Special CSS : Apply Special design
      You can choose whether to apply design.

  - Title TEXT : TOC title
    You can change the title of the table of contents.
  -Short Code for Widget : [ieitoc]
    You can display the table of contents in the widget using the short code.
    Please copy [ieitoc] and add it to the text widget.

##other
- If the H tags in the article are three or more, the table of contents is automatically inserted.
- We do not leave garbage when uninstalling.
- Use of option values ​​is also minimal.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress


The table of contents should already be displayed in the article.
There should be "ie InsertToc" in the left menu of the administration screen.
So you can make simple settings.


== Frequently Asked Questions ==

= A question that someone might have =

  1. I do not want to display it in a tree structure. what should I do?
    Uncheck the "Tree" item on the setting screen.
    Then press the submit button.

  2. I want to display the table of contents only in the posted article.
    uncheck the "pages" item on the setting screen.
    Then press the submit button.

  3. Where is the setting screen of this plug-in?
    It is in the hierarchy under the setting of the menu on the left of the dashboard.

  4. I want a button to close the table of contents.
    It is none. I think that is a wasteful function.

  5. I want to display the table of contents in the sidebar.
    It's good idea! Please see the setting screen at the bottom.
     -Copy the short code.
     -Then add the custom HTML on the widget sideber.
     -Paste short code in custom HTML.

  6. I want to number the table of contents.
   Please check the "table of contents number" on the setting screen and press submit button.

  7. I want to also add the number to the headlines.
    Please check the "heading number" on the setting screen and press the send button.
    If you are not satisfied, change design.css in the CSS folder.

  8. I want to change the title of the table of contents.
    Please enter up to 20 characters in ”TITLE TEXT” item.

  9. I want to change design.
    You can select the CSS item on the setting screen.

  10. If only the widget is displayed, the heading number is not displayed. How can I see it?
    Please check "Widget to headline number".

  11. How can I support this plugin?
    Thank you!
    Please support from the setting screen or [here](https://www.paypal.me/ryusai).

== Screenshots ==
screenshot-1.png
screenshot-2.png
screenshot-3.png

== Changelog ==
= 2.1 =
  ###add  function
    - fixed bug


= 1.0 =
  ###Release Version
  - changed it for php5.3

= 0.5 =
  ###fixed version
  - Fixed the possibility of conflict.
= 0.3 =
  ###Review Version



== Upgrade Notice ==

= 2.0 =
  ###add  function
    -You can choose to show this on the homepage.
    -Add a number to the table of contents.
    -Add numbers to headlines.
    -Select default design.
    -You can change the table of contents title.
    -Implement short code for widgets.

== Arbitrary section ==
There are already many Table Of Contents plugins in WordPress.
But I was not satisfied with this, so I made it myself.
this plugin became multifunctional, so please try it!

Finally
This plugin will work with php 5.3 - latest version.
However, as of January 2019, php has already finished supporting less than 7.1.
Please use php 7.1 or later whenever possible.

Thank you for using this plugin!
