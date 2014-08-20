<?php
/*
Plugin Name: CleanSave
Plugin URI: http://www.formatdynamics.com
Description: Save web page content to your Kindle, Box, Google Drive, Dropbox, print, PDF, and email
Version: 1.4.2
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if( !class_exists( 'WP_Http' ) ) 
   include_once( ABSPATH . WPINC. '/class-http.php' );


// Plug-in parameters (do not change these)
$cleansave_plugin_name       = 'cleansave';
$cleansave_plugin_file       = $cleansave_plugin_name . '/cleansave.php';
$cleansave_plugin_attr       = 'plugin';
$cleansave_print_attr        = 'print';
$cleansave_options_name      = 'CleanSave';

// CleanSave parameters (change these *only* if you know what you're doing)
$cleansave_base_url          = is_ssl() ? 'https://cache-02.cleanprint.net' : 'http://cache-02.cleanprint.net';
$cleansave_edit_buttons      = 'group:edit';
$cleansave_social_buttons    = 'group:share';

// Best not change these (internal-use only)
$cleansave_loader_url        = $cleansave_base_url . '/cpf/cleanprint?polite=no&key=cleansave-wp';
$cleansave_btn_helper_url    = $cleansave_base_url . '/cpf/publisherSignup/js/generateCPFTag.js';
$cleansave_style_url         = $cleansave_base_url . '/media/pfviewer/css/screen.css';
$cleansave_def_btn_style     = 'Btn_white';
$cleansave_def_btn_placement = 'tr';
$cleansave_debug             = false;


// Display the options page
function cleansave_add_options_page() {
   global $cleansave_options_name;
   global $cleansave_plugin_name;
   global $cleansave_style_url;
?>
    <link type="text/css" rel="stylesheet" href="<?php echo $cleansave_style_url ?>" />
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>CleanSave Settings</h2>
		<form action="options.php" method="post">
			<?php settings_fields     ($cleansave_options_name); ?>
			<?php do_settings_sections($cleansave_plugin_name); ?>

			<input name="Submit" type="submit" value="Save Changes" />
		</form>
	</div>
<?php
}


// Outputs a section heading but we do not use it
function cleansave_add_settings_section() {
?>
    <p>Thanks for installing CleanSave on your site! Below are a few options to customize CleanSave and
    make it your own.</p>
    
    <ol>
    <li>You can use our logo or your own<br>- use a <i>http-style</i> image URL with the image size no larger than 200 x 40.</li>
    
    <li>You choose from a variety of button styles or use your own custom buttons<br>
        - please see installation instructions for custom images.</li> 
    
    <li>You may also select the location where the buttons are placed or choose a custom position<br>
        - please see installation instructions for custom locations.</li>
    
    <li>You may select which page types that the buttons appear on.</li>     
    </ol>
    
    <p>NOTE: If you choose to use Google Analytics custom event tracking for CleanPrint your site <b>MUST</b>
    have Google Analytics running.</p>
    </ul>
            
    <?php printf("<tr><td><h2>Logo</h2><hr /></td></tr>");?>
<?php
}


// WP callback for handling the Logo URL (default/custom) option
function cleansave_add_settings_field_logo_url_() {
    global $cleansave_options_name;
    $cleansave_def_logo_url = plugins_url('/CleanSave.png',__FILE__);
    
	$options        = get_option($cleansave_options_name);
	$logoUrl        = isset($options['logoUrl']) ? $options['logoUrl'] : null;
    $customChecked  = isset($logoUrl) && $logoUrl!=$cleansave_def_logo_url;
    $defaultChecked = !$customChecked;

    printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='%s' %s />", $cleansave_options_name, $cleansave_def_logo_url, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='custom' %s />", $cleansave_options_name, $customChecked ?"checked='checked'":"");
	printf( "Custom (fully-qualified URL):");
	printf( "<input type='text'  id='plugin_logoUrl' name='%s[customLogo]' value='%s' /><br>\n", $cleansave_options_name, $customChecked ? $logoUrl : "");
	printf( "<td>Logo Preview<br /><div style='background-color:#DDD; border: 1px solid #BBB; padding: 10px; text-align:center;'><img height='40px' src='%s'></div></td>", $customChecked ? $logoUrl : $cleansave_def_logo_url);
	printf("<tr><td  colspan='3'><h2>Button Styles/Locations</h2><hr /></td></tr>");
}


// WP callback for handling the Print Button URL (default/custom) option
function cleansave_add_settings_field_button_color() {
    global $cleansave_options_name;
    global $cleansave_btn_helper_url;
    global $cleansave_def_btn_style;
    
	$options     = get_option($cleansave_options_name);
	$buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : null;
	$imagesUrl   = plugins_url("/images",__FILE__);    
	
	if(!isset($buttonStyle)) {
        $buttonStyle = $cleansave_def_btn_style;
    }
    
    printf("<script type='text/javascript' src='%s'></script>", $cleansave_btn_helper_url);    
    printf("<script type='text/javascript'>function buildButtonSelect() {");
    printf("var select = document.createElement('select');");
    printf("select.setAttribute('id',       'plugin_buttonStyle');");
    printf("select.setAttribute('name',     '%s[buttonStyle]');", $cleansave_options_name);
    printf("select.setAttribute('onchange', 'changeButtons(this);return false;');");
    printf("var styles = getCPFButtonStyles();");
    printf("for (style in styles) {");
    printf("var label  = styles[style];");
    printf("var option = document.createElement('option');");
    printf("option.setAttribute('value', style);");
    printf("if (style=='%s') option.setAttribute('selected', 'selected');", $buttonStyle);
    printf("option.innerHTML = label;");
    printf("select.appendChild(option);");
    printf("}");
    printf("return select;");
    printf("}");
    
    printf("function changeButtons(select) {");
	printf("var index = select.selectedIndex;");
	printf("var value = select.options[index].value;");
	printf("var saveUrl  = '$imagesUrl/CleanSave'  + value + '.png';");
    printf("var cpUrl    = '$imagesUrl/CleanPrint' + value + '.png';");
	printf("var pdfUrl   = '$imagesUrl/Pdf'        + value + '.png';");
	printf("var emailUrl = '$imagesUrl/Email'      + value + '.png';");
    printf("document.getElementById('pageSaveImg') .src = saveUrl;");
    printf("document.getElementById('pagePrintImg').src = cpUrl;");
	printf("document.getElementById('pagePdfImg')  .src = pdfUrl;");
	printf("document.getElementById('pageEmailImg').src = emailUrl;");
    printf("}");
	
	printf("function changeButton(select,suffix) {");
    printf("var index    = select.selectedIndex;");
    printf("var value    = select.options[index].value;");
    printf("var pageBtn  = 'page'+suffix;");
    printf("var viewBtn  = 'view'+suffix;");
    printf("var pageElem = document.getElementById(pageBtn);");
    printf("var viewElem = document.getElementById(viewBtn);");
    printf("if      (value=='both')   {pageElem.style.display='inline';viewElem.style.display='block';}");
    printf("else if (value=='viewer') {pageElem.style.display='none';  viewElem.style.display='block';}");
    printf("else                      {pageElem.style.display='none';  viewElem.style.display='none';}");
    printf("}</script>");

    printf("<span id='cpf_button_selector'></span>");
    printf("<script>document.getElementById('cpf_button_selector').appendChild(buildButtonSelect());</script>");
    
	
	$PrintInclude    = isset($options['PrintInclude']) ? $options['PrintInclude'] : null;
    $PDFInclude      = isset($options['PDFInclude'  ]) ? $options['PDFInclude'  ] : null;
    $EmailInclude    = isset($options['EmailInclude']) ? $options['EmailInclude'] : null;
    $SaveInclude     = isset($options['SaveInclude' ]) ? $options['SaveInclude' ] : null;
    
    $savePage        = !isset($SaveInclude)  || $SaveInclude =='both';
    $printPage       = !isset($PrintInclude) || $PrintInclude=='both';
    $pdfPage         = !isset($PDFInclude)   || $PDFInclude  =='both';
    $emailPage       = !isset($EmailInclude) || $EmailInclude=='both';

    $saveViewer      = !isset($SaveInclude)  || $SaveInclude =='viewer' || $SaveInclude =='both';
    $printViewer     = !isset($PrintInclude) || $PrintInclude=='viewer' || $PrintInclude=='both';
    $pdfViewer       = !isset($PDFInclude)   || $PDFInclude  =='viewer' || $PDFInclude  =='both';
    $emailViewer     = !isset($EmailInclude) || $EmailInclude=='viewer' || $EmailInclude=='both';
    
    
	printf("<td rowspan='5'>Page Preview<br /><div style='border:1px solid #BBB; padding:10px; text-align:center; width:450px;'>");
	printf("<img id='pageSaveImg'  src='$imagesUrl/CleanSave$buttonStyle.png'  style='padding:0px 1px; %s'/>", ($savePage  ? "" : "display:none"));
    printf("<img id='pagePdfImg'   src='$imagesUrl/Pdf$buttonStyle.png'        style='padding:0px 1px; %s'/>", ($pdfPage   ? "" : "display:none"));
    printf("<img id='pageEmailImg' src='$imagesUrl/Email$buttonStyle.png'      style='padding:0px 1px; %s'/>", ($emailPage ? "" : "display:none"));
    printf("<img id='pagePrintImg' src='$imagesUrl/CleanPrint$buttonStyle.png' style='padding:0px 1px; %s'/>", ($printPage ? "" : "display:none"));
    printf("</div><br />");

    printf("Viewer Preview<br /><div style='border:1px solid #BBB; padding:10px; text-align:center; width:60px;'>");
    printf("<div class='cpf-viewbox-sidebar-buttonGrp'>");
    printf("<div class='cpf-viewbox-sidebar-buttonGrp-heading'>output</div>");
    printf("<div id='viewPrintImg' style='%s'>",                                                                                           ($printViewer ? "" : "display:none"));
    printf(   "<div id='cpf-viewbox-sidebar-button-print'       class='cpf-viewbox-sidebar-button'></div>");
    printf(   "<div id='cpf-viewbox-sidebar-button-googlePrint' class='cpf-viewbox-sidebar-button'></div>");
    printf("</div>");
    printf("<div id='viewPdfImg'   style='%s'>",                                                                                           ($pdfViewer   ? "" : "display:none"));
    printf("   <div id='cpf-viewbox-sidebar-button-pdf' class='cpf-viewbox-sidebar-button'></div>");
    printf("   <div id='cpf-viewbox-sidebar-button-rtf' class='cpf-viewbox-sidebar-button'></div>");
    printf("</div>");
    printf("<div id='viewEmailImg' style='%s'><div id='cpf-viewbox-sidebar-button-email' class='cpf-viewbox-sidebar-button'></div></div>", ($emailViewer ? "" : "display:none"));
    printf("<div id='viewSaveImg'  style='%s'>",                                                                                           ($saveViewer  ? "" : "display:none"));
    printf(   "<div id='cpf-viewbox-sidebar-button-dropbox'    class='cpf-viewbox-sidebar-button'></div>");
    printf(   "<div id='cpf-viewbox-sidebar-button-googleDocs' class='cpf-viewbox-sidebar-button'></div>");
    printf(   "<div id='cpf-viewbox-sidebar-button-boxDotNet'  class='cpf-viewbox-sidebar-button'></div>");
    printf(   "<div class='cpf-viewbox-sidebar-button kindleWidget'></div>");
    printf("</div></div></td>");
}


// WP callback for handling button include
function cleansave_add_settings_field_save_btn() {
    global $cleansave_options_name;
    
    $options         = get_option($cleansave_options_name);
    $SaveInclude     = isset($options['SaveInclude']) ? $options['SaveInclude'] : null;
    $bothChecked     = !isset($SaveInclude) || $SaveInclude == "both";
    $viewChecked     =  isset($SaveInclude) && $SaveInclude == "viewer";
    $noneChecked     = !$bothChecked && !$viewChecked;
    
    printf( "<select id='plugin_SaveInclude' name='%s[SaveInclude]' onchange='changeButton(this,\"SaveImg\"); return false;'>", $cleansave_options_name);
    printf( "<option value='both'    %s>Page & Viewer</option>", ($bothChecked  ?"selected='selected'":""));
    printf( "<option value='viewer'  %s>Viewer</option>",        ($viewChecked  ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Hide</option>",          ($noneChecked  ?"selected='selected'":""));
    printf( "</select>");
}

function cleansave_add_settings_field_pdf_btn() {
    global $cleansave_options_name;
    
	$options         = get_option($cleansave_options_name);
	$PDFInclude      = isset($options['PDFInclude']) ? $options['PDFInclude'] : null;
    $bothChecked     = !isset($PDFInclude)  || $PDFInclude == "both";
    $viewChecked     =  isset($PDFInclude)  && $PDFInclude == "viewer";
    $noneChecked     = !$bothChecked && !$viewChecked;
	
	printf( "<select id='plugin_PDFInclude' name='%s[PDFInclude]' onchange='changeButton(this,\"PdfImg\"); return false;'>", $cleansave_options_name);
	printf( "<option value='both'    %s>Page & Viewer</option>", ($bothChecked  ?"selected='selected'":""));
    printf( "<option value='viewer'  %s>Viewer</option>",        ($viewChecked  ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Hide</option>",          ($noneChecked  ?"selected='selected'":""));
    printf( "</select>");
}

function cleansave_add_settings_field_email_btn() {
    global $cleansave_options_name;
    
	$options         = get_option($cleansave_options_name);
	$EmailInclude    = isset($options['EmailInclude']) ? $options['EmailInclude'] : null;
	$bothChecked     = !isset($EmailInclude) || $EmailInclude == "both";
    $viewChecked     =  isset($EmailInclude) && $EmailInclude == "viewer";
    $noneChecked     = !$bothChecked && !$viewChecked;
	
	printf( "<select id='plugin_EmailInclude' name='%s[EmailInclude]' onchange='changeButton(this,\"EmailImg\"); return false;'>", $cleansave_options_name);
	printf( "<option value='both'    %s>Page & Viewer</option>", ($bothChecked  ?"selected='selected'":""));
    printf( "<option value='viewer'  %s>Viewer</option>",        ($viewChecked  ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Hide</option>",          ($noneChecked  ?"selected='selected'":""));
    printf( "</select>");
}

function cleansave_add_settings_field_print_btn() {
    global $cleansave_options_name;
    
    $options         = get_option($cleansave_options_name);
    $PrintInclude    = isset($options['PrintInclude']) ? $options['PrintInclude'] : null;
    $bothChecked     = !isset($PrintInclude) || $PrintInclude == "both";
    $viewChecked     =  isset($PrintInclude) && $PrintInclude == "viewer";
    $noneChecked     = !$bothChecked && !$viewChecked;
    
    printf( "<select id='plugin_PrintInclude' name='%s[PrintInclude]' onchange='changeButton(this,\"PrintImg\"); return false;'>", $cleansave_options_name);
    printf( "<option value='both'    %s>Page & Viewer</option>", ($bothChecked  ?"selected='selected'":""));
    printf( "<option value='viewer'  %s>Viewer</option>",        ($viewChecked  ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Hide</option>",          ($noneChecked  ?"selected='selected'":""));
    printf( "</select>");
}


function cleansave_add_settings_field_btn_placement() {
    global $cleansave_options_name;
    global $cleansave_def_btn_placement;
    
	$options         = get_option($cleansave_options_name);
	$ButtonPlacement = isset($options['ButtonPlacement']) ? $options['ButtonPlacement'] : null;
	
	if (!isset($ButtonPlacement)) {
	   $ButtonPlacement = $cleansave_def_btn_placement;
	}
	
	$trChecked  = $ButtonPlacement=="tr";
    $tlChecked  = $ButtonPlacement=="tl";
	$blChecked  = $ButtonPlacement=="bl";
	$brChecked  = $ButtonPlacement=="br";
	
	
    printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='tl' %s />", $cleansave_options_name, $tlChecked ?"checked='checked'":"");
	printf( "Top Left<br />\n");

	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='tr' %s />", $cleansave_options_name, $trChecked  ?"checked='checked'":"");
	printf( "Top Right<br />\n");
	
	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='bl' %s />", $cleansave_options_name, $blChecked  ?"checked='checked'":"");
	printf( "Bottom Left<br />\n");
	
	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='br' %s />", $cleansave_options_name, $brChecked  ?"checked='checked'":"");
	printf( "Bottom Right<br />\n");
	printf("<tr><td colspan='3'><h2>Page Types:</h2><hr /></td></tr>");  
}


// WP callback for handling page type
function cleansave_add_settings_field_homepage() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $homepage    = isset($options['HomepageInclude']) ? $options['HomepageInclude'] : null;
    $isChecked   = !isset($homepage) || $homepage=="include";
    
    printf( "<select id='plugin_homepage' name='%s[HomepageInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_home()</i>");  
}


function cleansave_add_settings_field_frontpage() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $frontpage   = isset($options['FrontpageInclude']) ? $options['FrontpageInclude'] : null;
    $isChecked   = !isset($frontpage) || $frontpage=="include";
    
    printf( "<select id='plugin_frontpage' name='%s[FrontpageInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_front_page()</i>");
}


function cleansave_add_settings_field_category() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $category    = isset($options['CategoryInclude']) ? $options['CategoryInclude'] : null;
    $isChecked   = !isset($category) || $category=="include";
    
    printf( "<select id='plugin_category' name='%s[CategoryInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_category()</i>");
}


function cleansave_add_settings_field_posts() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $posts       = isset($options['PostsInclude']) ? $options['PostsInclude'] : null;
    $isChecked   = !isset($posts) || $posts=="include";
    
    printf( "<select id='plugin_posts' name='%s[PostsInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_single()</i>");
}


function cleansave_add_settings_field_pages() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $pages       = isset($options['PagesInclude']) ? $options['PagesInclude'] : null;
    $isChecked   = !isset($pages) || $pages=="include";
    
    printf( "<select id='plugin_pages' name='%s[PagesInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_page()</i>");
}


function cleansave_add_settings_field_tags() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $tags        = isset($options['TagsInclude']) ? $options['TagsInclude'] : null;
    $isChecked   = !isset($tags) || $tags=="include";
    
    printf( "<select id='plugin_tags' name='%s[TagsInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_tag()</i>");
}


function cleansave_add_settings_field_excludes() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $excludes    = isset($options['PagesExcludes']) ? $options['PagesExcludes'] : "";
    
    printf( "<input type='text' id='plugin_excludes' name='%s[PagesExcludes]' value='%s' /><br>\n", $cleansave_options_name, $excludes);
    printf( "<i>(comma separated)</i>");
    printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");  
}


// WP callback for handling the Google Analytics option
function cleansave_add_settings_field_ga() {
    global $cleansave_options_name;
    
	$options         = get_option($cleansave_options_name);
	$GASetting       = isset($options['GASetting']) ? $options['GASetting'] : null;
	$disabledChecked = !isset($GASetting) || $GASetting=="false";
    $enabledChecked  = !$disabledChecked;
    
    printf( "<input type='radio' id='plugin_gaOption' name='%s[GASetting]' value='true' %s />", $cleansave_options_name, $enabledChecked?"checked='checked'":"");
	printf( "Enabled<br />\n");

	printf( "<input type='radio' id='plugin_gaOption' name='%s[GASetting]' value='false' %s />", $cleansave_options_name, $disabledChecked ?"checked='checked'":"");
	printf( "Disabled<br /><br />\n");
}


function cleansave_add_query_vars($vars) {
	global $cleansave_plugin_attr;
	global $cleansave_print_attr;
		
	array_push($vars, $cleansave_print_attr,$cleansave_plugin_attr);
    return $vars;
}


// Clean up the DB properties
function cleansave_sanitize_options($options) {
   $cleansave_def_logo_url = plugins_url('/CleanSave.png',__FILE__);
   global $optionsVersion;
   
   // Map the customLogo into logoUrl
   $logoUrl    = isset($options['logoUrl'])    ? $options['logoUrl']    : null;
   $customLogo = isset($options['customLogo']) ? $options['customLogo'] : null;
   if (isset($logoUrl) && isset($customLogo) && $logoUrl!=$cleansave_def_logo_url) {
      $options['logoUrl'] = $customLogo;            
   }   
   unset($options['customLogo']);
   
   return $options;
}


function cleansave_is_pagetype() {
    global $post;
    global $page_id;
	global $cleansave_options_name;

    $options       = get_option($cleansave_options_name);
    $homepage      = isset($options['HomepageInclude' ]) ? $options['HomepageInclude' ] : null;
    $frontpage     = isset($options['FrontpageInclude']) ? $options['FrontpageInclude'] : null;
    $category      = isset($options['CategoryInclude' ]) ? $options['CategoryInclude' ] : null;
    $posts         = isset($options['PostsInclude'    ]) ? $options['PostsInclude'    ] : null;
    $pages         = isset($options['PagesInclude'    ]) ? $options['PagesInclude'    ] : null;
    $tags          = isset($options['TagsInclude'     ]) ? $options['TagsInclude'     ] : null;
    $excludes      = isset($options['PagesExcludes'   ]) ? $options['PagesExcludes'   ] : null;
    
	if (is_page() && isset($excludes) && isset($page_id)) {
       $IDs = explode(",", $excludes);
       foreach ($IDs as $id) {
          if ($page_id == $id) return false;
       }
    }
 
    $isHomeChecked = !isset($homepage)  || $homepage =='include';
    $isFrntChecked = !isset($frontpage) || $frontpage=='include';
    $isCatgChecked = !isset($category)  || $category =='include';
    $isPostChecked = !isset($posts)     || $posts    =='include';
    $isPageChecked = !isset($pages)     || $pages    =='include';
    $isTagChecked  = !isset($tags)      || $tags     =='include';
    
    if (is_home()       && $isHomeChecked) return true;
    if (is_front_page() && $isFrntChecked) return true;              
    if (is_category()   && $isCatgChecked) return true;
    if (is_single()     && $isPostChecked) return true;
    if (is_page()       && $isPageChecked) return true;
    if (is_tag()        && $isTagChecked ) return true;
    
    return false;
}

// Add the hooks for print functionality
function cleansave_add_content($content) {
	global $post;
    global $cleansave_options_name;
	global $cleansave_def_btn_style;
	global $cleansave_def_btn_placement;
	 	    
	$options         = get_option($cleansave_options_name);
	$buttonStyle     = isset($options['buttonStyle']    ) ? $options['buttonStyle']     : null;
    $ButtonPlacement = isset($options['ButtonPlacement']) ? $options['ButtonPlacement'] : null;
    
    $showSaveBtn     = !isset($options['SaveInclude' ]) || $options['SaveInclude' ]=='both';
    $showPrintBtn    = !isset($options['PrintInclude']) || $options['PrintInclude']=='both';
    $showPdfBtn      = !isset($options['PDFInclude'  ]) || $options['PDFInclude'  ]=='both';
    $showEmailBtn    = !isset($options['EmailInclude']) || $options['EmailInclude']=='both';    
    $buttons         = "";
    
    if (!isset($ButtonPlacement)) {
       $ButtonPlacement = $cleansave_def_btn_placement;
    }
    
    
	if (cleansave_is_pagetype()) {
		$postId    = isset($post) && isset($post->ID) ? sprintf("'post-%s'",$post->ID) : null;
		$imagesUrl = plugins_url("/images",__FILE__);	
	
	   	if (!isset($buttonStyle)) {
            $buttonStyle = $cleansave_def_btn_style;
        }

        if ($showSaveBtn) {
            $buttons .= "<a href=\".\" onClick=\"WpCsCleanSave($postId);return false\" title=\"Save page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanSave$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
        }

        if ($showPdfBtn) {
            $buttons .= "<a href=\".\" onClick=\"WpCsCleanPrintGeneratePdf($postId);return false\" title=\"PDF page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Pdf$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
        }

        if ($showEmailBtn) {
            $buttons .= "<a href=\".\" onClick=\"WpCsCleanPrintSendEmail($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Email$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
        }
        
        if ($showPrintBtn) {
            $buttons .= "<a href=\".\" onClick=\"WpCsCleanPrintPrintHtml($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
        }

        

        if (isset($buttons)) {
            if ($ButtonPlacement=="tl") {
                $content = sprintf("%s<br />%s", $buttons, $content);

            } else if ($ButtonPlacement=="tr") {
                $content = sprintf("<div style='text-align:right;'>%s</div><br />%s", $buttons, $content);

            } else if($ButtonPlacement=="bl") {
                $content = sprintf("%s<br />%s", $content, $buttons);

            } else {
                $content = sprintf("%s<br /><div style='text-align:right;'>%s</div>", $content, $buttons);
            }
        }
    }
	return $content;
}


// Adds the CleanSave save button for use by a shortcode
function cleansave_add_save_button() {
	global $post;
    global $cleansave_options_name;
    global $cleansave_def_btn_style;

	if (cleansave_is_pagetype()) {	 	    
		$postId      = isset($post) && isset($post->ID) ? sprintf("'post-%s'",$post->ID) : null;
    	$options     = get_option($cleansave_options_name);
    	$buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : null; 
    	$imagesUrl   = plugins_url("/images",__FILE__);
        
    	if (!isset($buttonStyle)) {
        	$buttonStyle = $cleansave_def_btn_style;
    	}

    	return "<a href=\".\" onClick=\"WpCsCleanSave($postId);return false\" title=\"Save page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanSave$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
	}
}


// Adds any CleanSave button for use by a shortcode
function cleansave_add_button($atts, $content, $tag) {
	global $post;
    global $cleansave_options_name;
    global $cleansave_def_btn_style;
	 	    
    extract( shortcode_atts( array(
		'save'  => 'true',
        'pdf'   => 'false',
        'email' => 'false',
        'print' => 'false',        
	), $atts ) );
	 	    
	if (cleansave_is_pagetype()) {
		$postId      = isset($post) && isset($post->ID) ? sprintf("'post-%s'",$post->ID) : null;
    	$options     = get_option($cleansave_options_name);
    	$buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : null;
    	$imagesUrl   = plugins_url("/images",__FILE__);    
    	$rtn         = ""; 
        
    	if (!isset($buttonStyle)) {
        	$buttonStyle = $cleansave_def_btn_style;
    	}
    
    	if ("{$save}" =="true") $rtn .= "<a href=\".\" onClick=\"WpCsCleanSave($postId);            return false\" title=\"Save page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanSave$buttonStyle.png\" style=\"padding:0px 1px;\" /></a>";
    	if ("{$pdf}"  =="true") $rtn .= "<a href=\".\" onClick=\"WpCsCleanPrintGeneratePdf($postId);return false\" title=\"PDF page\"  class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Pdf$buttonStyle.png\" style=\"padding:0px 1px;\"       /></a>";
    	if ("{$email}"=="true") $rtn .= "<a href=\".\" onClick=\"WpCsCleanPrintSendEmail($postId); return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Email$buttonStyle.png\" style=\"padding:0px 1px;\"     /></a>";
    	if ("{$print}"=="true") $rtn .= "<a href=\".\" onClick=\"WpCsCleanPrintPrintHtml($postId); return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
                                                                                                                                                                      
    	return $rtn;
	}
}


// Adds the CleanPrint script tags to the head section
function cleansave_wp_head() {
    global $page_id;
    global $cleansave_options_name;
    global $cleansave_loader_url;
	$cleansave_def_logo_url = plugins_url('/CleanSave.png',__FILE__);
    global $cleansave_edit_buttons;
    global $cleansave_social_buttons;
    global $cleansave_debug;

    $options      = get_option($cleansave_options_name);
	$GASetting    = isset($options['GASetting']) ? $options['GASetting'] : null;
    $logoUrl      = isset($options['logoUrl'])   ? $options['logoUrl']   : null;
		
    $showPrintBtn = !isset($options['PrintInclude']) || $options['PrintInclude']!='exclude';
    $showPdfBtn   = !isset($options['PDFInclude'  ]) || $options['PDFInclude'  ]!='exclude';
    $showEmailBtn = !isset($options['EmailInclude']) || $options['EmailInclude']!='exclude';
    $showSaveBtn  = !isset($options['SaveInclude' ]) || $options['SaveInclude' ]!='exclude';
    $buttons      = '';

	if ($cleansave_debug) {
		printf("\n\n\n<!-- CleanSave Debug\n\t\t%s\n\t\tpage_id:%s, the_ID:%d, home:%d, front:%d, category:%d, single:%d, page:%d, tag:%d\n-->\n\n\n",
					               http_build_query($options,"","\n\t\t"), $page_id, the_ID(), is_home(), is_front_page(), is_category(), is_single(), is_page(), is_tag());
	}
		
    
    if (cleansave_is_pagetype() == false) {
       // Disabled page type
       return;
    }

    if (!($showPrintBtn || $showPdfBtn || $showEmailBtn || $showSaveBtn)) {
       // All the buttons are excluded
       return;
    }

    if ($showPrintBtn) $buttons .= ',print,gcp';
    if ($showPdfBtn  ) $buttons .= ',pdf,rtf';
    if ($showEmailBtn) $buttons .= ',email';
    if ($showSaveBtn ) $buttons .= ',dropbox,googleDocs,boxDotNet,kindle';

    $buttons = sprintf("help,%s,%s,%s", substr($buttons,1), $cleansave_edit_buttons, $cleansave_social_buttons);
    
    printf( "<script id='cpf_wp_cs' type='text/javascript'>\n");
    printf( "   function WpCsCleanSave(articleId) {\n");
    printf( "   	CleanPrintPrintHtml(null,articleId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Save']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function WpCsCleanPrintSendEmail(articleId) {\n");
    printf( "   	CleanPrintSendEmail(null,articleId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Email']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function WpCsCleanPrintGeneratePdf(articleId) {\n");
    printf( "   	CleanPrintGeneratePdf(null,articleId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'PDF']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function WpCsCleanPrintPrintHtml(articleId) {\n");
    printf( "       CleanPrintPrintHtml(null,articleId);\n");
                        if ($GASetting=="true") {
                            printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Print']); } catch(e) {}\n");
                        }
    printf( "   }\n");
    printf( "</script>\n");
		
	$loader = $cleansave_loader_url;
	if ($buttons) $loader = "$loader&buttons=" . urlencode($buttons);
	if ($logoUrl) $loader = "$loader&logo="    . urlencode($logoUrl);
	printf( "<script id='cpf_loader' type='text/javascript' src='%s'></script>\n", $loader);
}



// Add the Settings menu link to the plugin page
function cleansave_add_action_links($links, $file) {
	global $cleansave_plugin_name;
    global $cleansave_plugin_file;
    
    if ($file == $cleansave_plugin_file) {
        $links[] = sprintf("<a href='options-general.php?page=%s'>Settings</a>", $cleansave_plugin_name);
	}
	return $links;
}


// Activate CleanSave, migrate any old options here
function cleansave_activate() {
   // cannot use the global, chicken/egg problem
   $options            = get_option('CleanSave');
   $optionsVersion     = '1.0';
      
   if (isset($options)) {
      $options['version'] = $optionsVersion;      

      // Unset the logoUrl if we have the older default URL      
      $logoUrl = isset($options['logoUrl']) ? $options['logoUrl'] : null;      
      if (isset($logoUrl) && $logoUrl=="http://cache-02.cleanprint.net/media/logos/Default.png") {
         unset($options['logoUrl']); // Not sure this is working but its getting called
      }

      update_option('CleanSave', $options);
   }
}


// Remove the CleanSave options from the database
function cleansave_uninstall() {
    // cannot use the global, chicken/egg problem
	delete_option('CleanSave');
}


// WP callback for initializing the options menu
function cleansave_admin_init() {
    global $cleansave_plugin_name;
    global $cleansave_plugin_file;
    global $cleansave_options_name;
    
    register_setting       ($cleansave_options_name, $cleansave_options_name, 'cleansave_sanitize_options');
    register_uninstall_hook($cleansave_plugin_file, 'cleansave_uninstall');

    add_settings_section   ('plugin_main', '', 'cleansave_add_settings_section', $cleansave_plugin_name);
    add_settings_field     ('plugin_logoUrl',         '<strong>Image:</strong>',                     'cleansave_add_settings_field_logo_url_',     $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_buttonStyle',     '<strong>Size / Color:</strong>',              'cleansave_add_settings_field_button_color',  $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_SaveInclude',     '<strong>Save Button:</strong>',               'cleansave_add_settings_field_save_btn',      $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_PDFInclude',      '<strong>PDF Button:</strong>',                'cleansave_add_settings_field_pdf_btn',       $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_EmailInclude',    '<strong>Email Button:</strong>',              'cleansave_add_settings_field_email_btn',     $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_PrintInclude',    '<strong>Print Button:</strong>',              'cleansave_add_settings_field_print_btn',     $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_buttonplacement', '<strong>Page Location:</strong>',             'cleansave_add_settings_field_btn_placement', $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_homepage',        '<strong>Home Page:</strong>',                 'cleansave_add_settings_field_homepage',      $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_frontpage',       '<strong>Front Page:</strong>',                'cleansave_add_settings_field_frontpage',     $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_category',        '<strong>Categories:</strong>',                'cleansave_add_settings_field_category',      $cleansave_plugin_name, 'plugin_main');    
    add_settings_field     ('plugin_posts',           '<strong>Posts:</strong>',                     'cleansave_add_settings_field_posts',         $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_pages',           '<strong>Pages:</strong>',                     'cleansave_add_settings_field_pages',         $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_tags',            '<strong>Tags:</strong>',                      'cleansave_add_settings_field_tags',          $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_excludes',        '<strong>Excluded Page IDs:</strong>',         'cleansave_add_settings_field_excludes',      $cleansave_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_gaOption',        '<strong>CleanPrint Event Tracking:</strong>', 'cleansave_add_settings_field_ga',            $cleansave_plugin_name, 'plugin_main');
}


// WP callback for launching the options menu
function cleansave_admin_menu() {
   global $cleansave_plugin_name;
   add_options_page('CleanSave Settings', 'CleanSave', 'manage_options', $cleansave_plugin_name, 'cleansave_add_options_page');
}


// Activate
register_activation_hook(__FILE__, 'cleansave_activate');

// Actions
add_action('admin_init',          'cleansave_admin_init');
add_action('admin_menu',          'cleansave_admin_menu');
add_action('wp_head',             'cleansave_wp_head', 1);

// Filters
add_filter('plugin_action_links', 'cleansave_add_action_links', 10, 2);
add_filter('the_content',         'cleansave_add_content');
add_filter('query_vars',          'cleansave_add_query_vars');

?>