=== CleanSave ===
Contributors: johncadams, lucascolin
Donate link: http://www.formatdynamics.com/contact-us
Tags: pdf, print, printing, kindle, widget, email, save, optimize, output, edit, editing, eco-friendly, environmental, sustainable, reader, iPad, tablet, saving, ecological, eco, ink, social, output, plugin, saver, box, box.net, box.com, dropbox, rtf, readlater, instapaper, cloud, google docs, google drive, google cloud print
Requires at least: 2.0.2
Tested up to: 4.1.1
Stable tag: 1.4.7
  
CleanSave - Save web page content to your Kindle, Box, Google Drive, Dropbox, Google Cloud Print, print, PDF, text and email


== Description ==
The best saving tool is now available on WordPress. Join top sites like NBC News, CNN, Disney and Fox Sports and offer your users a simple saving experience that keeps them coming back to your site.

<h4>How CleanSave Works</h4>

Users can easily edit content, save to PDF or a Text file or upload to their Kindle, Dropbox, Box or Google Drive.

1. User activates CleanSave by hitting the save button
2. Content preview appears including editing and output tools for optimization
3. User selects desired output:
   * PDF - Saves content as a PDF document
   * Text - Saves content as a rich text formated file
   * Dropbox - Saves a PDF or text file to your Dropbox account
   * Box - Saves a PDF or text file to your Box account
   * Google Drive - Saves a PDF or text file to your Google Drive account
   * Kindle - Saves the content to your Kindle
   * Google Cloud Print - prints to a remote printer
   * Print - Sends content to your printer
   * Email - Sends content via email
4. Share article link to Facebook, Twitter, LinkedIn, and Google+

<h4>Features and Benefits</h4>

1. Use the CleanSave button set or point your own buttons or text to CleanSave.
2. Lightbox keeps users on your page within their original browser window.
3. Users are in control of font size, images, gray scale of text, and eliminating any unwanted content before saving or uploading.
4. Branded output - Your branding/URL saved so that people can always get back to your site.


== Installation ==

1. Log into your WordPress installation as an administrator.
2. On the navigation on the left hand side, click 'Plugins', then 'Add New' from the menu.
3. Enable the CleanSave plugin.
4. Visit the CleanSave Settings page, select the appropriate options and click 'Save Changes'.


<h4>Using Your Own Buttons</h4>
If you prefer to use your own text links or buttons you may do so but it does
require a deeper understanding of WordPress administration and HTML.  This information can be found in
WordPress documentation found elsewhere:

1. Hide the buttons under Button Styles in the CleanSave Settings page.
2. Insert a hyperlink into your page as per the example below:
   <pre>
      &lt;a href='.' onClick='WpCsCleanSave();             return false' title='Save page' &gt;Save&lt;/a&gt;
      &lt;a href='.' onClick='WpCsCleanPrintSendEmail();   return false' title='Email page'&gt;Email&lt;/a&gt;
      &lt;a href='.' onClick='WpCsCleanPrintGeneratePdf(); return false' title='PDF page'  &gt;PDF&lt;/a&gt;
      &lt;a href='.' onClick='WpCsCleanPrintPrintHtml();   return false' title='Print page'&gt;Print&lt;/a&gt;
   </pre>


<h4>Using Shortcode Buttons</h4>
1. Hide the buttons under Button Styles in the CleanSave Settings page.
2. Activate the button(s) in your HTML content, for example to add all 4 buttons:
   <pre>[cleansave_button save='true' pdf='true' email='true' print='true']</pre>



== Frequently Asked Questions ==

= Can I personalize CleanSave for my site? =

Yes, you can add your own logo in the CleanSave Settings page.  The logo should be no more than 200px wide and 40px tall.

= Can I remove ads from CleanSave? =

Not at the present time. Ads help us pay the bills. CleanPrint is primarily supported by advertising which allows us to cover costs while offering you content output tools that save paper, ink, money and the environment.

= How do remove the Print, Email and PDF buttons leaving only the Save button? =

In the CleanSave Settings page you may choose from a wide variety of button styles.  You may also elect to turn on/off any button.

= How do I remove buttons from my home page? =

Change the Home Page setting from "Include" to "Exclude" in the CleanSave Settings page.

= How do I move the buttons from the upper right corner to the lower left? =

Change the Page Location setting from "Top Right" to "Bottom Left" in the CleanSave Settings page.

= How do I set CleanSave to pre-remove content so the user doesn't have to? =

This can be tricky depending upon your WordPress knowledge and requires you to set certain class names on the element in question.  Visit http://www.formatdynamics.com/cpconfig for details.

= Where can I see CleanSave in action? =

You have two options:
<ol>
   <li>Visit our website and try it out:
   	<ul>
      <li>http://www.formatdynamics.com/cleanprint-4-0</li>      
   </ul></li>
   <li>Install our free browser tool and try it anywhere yourself.
   <ul>
      <li>http://www.formatdynamics.com/bookmarklets.</li>
   </ul></li>
</ol>

= Where can I learn more about CleanSave? =

Visit us at:
<ul>
   <li><a href="http://www.formatdynamics.com/cleanprint-4-0">FormatDynamics.com</a></li>
   <li><a href="http://www.facebook.com/pages/CleanPrint/131304880322920?sk=app_162498273831267">Facebook</a></li>
</ul>


== Screenshots ==

1. CleanSave allows you to insert Save, Print, Email and PDF buttons into your content anywhere you like from a large number of button styles.
2. CleanSave reformats your article content in order to make it easier to read.  Users can edit article content further to get the output they desire.
3. Users can then generate a PDF, save the content to their Kindle, Google Drive, Dropbox or Box accounts (as PDF or text), email it to their friends or send it to the printer.


== Changelog ==

= 1.4.7 =
* Improved visual accessibility 

= 1.4.6 =
* Corrected issue with function names 

= 1.4.5 =
* Non-standard page excludes

= 1.4.4 =
* Improved ID exclusion
* Simplifying shortcode config
* Adding taxonomies

= 1.4.3 =
* Migrates default logo to new location

= 1.4.2 =
* Improved page-load performance
* Added Page excludes

= 1.4.1 =
* HTTPS host changes

= 1.4.0 =
* HTTPS support

= 1.3.0 =
* Kindle support
* Minor bug fixes

= 1.2.1 =
* New shortcode behavior 
* Added no-ad option

= 1.2.0 =
* Added support for Google Cloud Print
* Added support for RTF
* Improved script injection
* Added shortcode support

= 1.1.5 =
* Added support for Box

= 1.1.1 =
* Minor bug fixes

= 1.1.0 =
* Added support for tag page type
* Added support for excluding specific page IDs
* Added support for multiple print buttons per page
* Workaround for Google Analytics for WordPress plug-in defect

= 1.0.0 =
Initial version