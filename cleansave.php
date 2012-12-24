<?php
/*
Plugin Name: CleanSave
Plugin URI: http://www.formatdynamics.com
Description: Save web page content to Box.net, Google Docs, Dropbox, print, PDF, and email
Version: 1.2.0
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
$cleansave_base_url          = 'http://cache-02.cleanprint.net';
$cleansave_publisher_key     = 'cleansave-wp';
$cleansave_edit_buttons      = 'group:edit';
$cleansave_social_buttons    = 'group:share';

// Best not change these (internal-use only)
$cleansave_loader_url        = $cleansave_base_url . '/cpf/cleanprint';
$cleansave_images_base_url   = $cleansave_base_url . '/media/pfviewer/images';
$cleansave_btn_helper_url    = $cleansave_base_url . '/cpf/publisherSignup/js/generateCPFTag.js';
$cleansave_style_url         = $cleansave_base_url . '/media/pfviewer/css/screen.css';
$cleansave_def_logo_url      = $cleansave_base_url . '/media/logos/CleanSave.png';
$cleansave_def_btn_style     = 'Btn_white';
$cleansave_def_btn_placement = 'tr';
$cleansaveDebug              = false;


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
    make it your own.  You can use your logo and choose from a variety of button styles or use your own 
    buttons. You may also select the location within the page where the button(s) are placed.</p>
    
    <p>You may select which page types that the button(s) should appear on.  <!-- You may also exclude specific
    pages by entering their comma separated IDs.  NOTE: The ID is visible in the URL when you navigate to
    that page.--></p>
    
    <p>If you would like to place the button(s) in a custom position please see installation instructions.
    Also, if you choose to use Google Analytics custom event tracking for CleanSave your site <b>MUST</b>
    have Google Analytics running.</p>
    <?php printf("<tr><td><h2>Logo</h2><hr /></td></tr>");?>
<?php
}


// WP callback for handling the Logo URL (default/custom) option
function cleansave_add_settings_field_logo_url_() {
    global $cleansave_options_name;
    global $cleansave_def_logo_url;
    
	$options        = get_option($cleansave_options_name);
	$logoUrl        = $options['logoUrl'];
    $customChecked  = isset($logoUrl) && $logoUrl!=$cleansave_def_logo_url;
    $defaultChecked = !$customChecked;

    printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='%s' %s />", $cleansave_options_name, $cleansave_def_logo_url, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='custom' %s />", $cleansave_options_name, $customChecked ?"checked='checked'":"");
	printf( "Custom:");
	printf( "<input type='text'  id='plugin_logoUrl' name='%s[customLogo]' value='%s' /><br>\n", $cleansave_options_name, $customChecked ? $logoUrl : "");
	printf( "<td>Logo Preview<br /><div style='background-color:#DDD; border: 1px solid #BBB; padding: 10px; text-align:center;'><img height='40px' src='%s'></div></td>", $customChecked ? $logoUrl : $cleansave_def_logo_url);
	printf("<tr><td  colspan='3'><h2>Button Styles</h2><hr /></td></tr>");
}


// WP callback for handling the Print Button URL (default/custom) option
function cleansave_add_settings_field_button_color() {
    global $cleansave_options_name;
    global $cleansave_images_base_url;
    global $cleansave_btn_helper_url;
    global $cleansave_def_btn_style;
    
	$options     = get_option($cleansave_options_name);
	$buttonStyle = $options['buttonStyle'];
	
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
	printf("var saveUrl  = '$cleansave_images_base_url/CleanSave'  + value + '.png';");
    printf("var cpUrl    = '$cleansave_images_base_url/CleanPrint' + value + '.png';");
	printf("var pdfUrl   = '$cleansave_images_base_url/Pdf'        + value + '.png';");
	printf("var emailUrl = '$cleansave_images_base_url/Email'      + value + '.png';");
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
    
	
	$PrintInclude    = $options['PrintInclude'];
    $PDFInclude      = $options['PDFInclude'  ];
    $EmailInclude    = $options['EmailInclude'];
    $SaveInclude     = $options['SaveInclude' ];
    
    $savePage        = !isset($SaveInclude)  || $SaveInclude =='both';
    $printPage       = !isset($PrintInclude) || $PrintInclude=='both';
    $pdfPage         = !isset($PDFInclude)   || $PDFInclude  =='both';
    $emailPage       = !isset($EmailInclude) || $EmailInclude=='both';

    $saveViewer      = !isset($SaveInclude)  || $SaveInclude =='viewer' || $SaveInclude =='both';
    $printViewer     = !isset($PrintInclude) || $PrintInclude=='viewer' || $PrintInclude=='both';
    $pdfViewer       = !isset($PDFInclude)   || $PDFInclude  =='viewer' || $PDFInclude  =='both';
    $emailViewer     = !isset($EmailInclude) || $EmailInclude=='viewer' || $EmailInclude=='both';
    
    
	printf("<td rowspan='5'>Page Preview<br /><div style='border:1px solid #BBB; padding:10px; text-align:center; width:450px;'>");
	printf("<img id='pageSaveImg'  src='$cleansave_images_base_url/CleanSave$buttonStyle.png'  style='padding:0px 1px; %s'/>", ($savePage  ? "" : "display:none"));
    printf("<img id='pagePdfImg'   src='$cleansave_images_base_url/Pdf$buttonStyle.png'        style='padding:0px 1px; %s'/>", ($pdfPage   ? "" : "display:none"));
    printf("<img id='pageEmailImg' src='$cleansave_images_base_url/Email$buttonStyle.png'      style='padding:0px 1px; %s'/>", ($emailPage ? "" : "display:none"));
    printf("<img id='pagePrintImg' src='$cleansave_images_base_url/CleanPrint$buttonStyle.png' style='padding:0px 1px; %s'/>", ($printPage ? "" : "display:none"));
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
    printf(   "<div id='cpf-viewbox-sidebar-button-dropbox'     class='cpf-viewbox-sidebar-button'></div>");
    printf(   "<div id='cpf-viewbox-sidebar-button-googleDocs'  class='cpf-viewbox-sidebar-button'></div>");
    printf(   "<div id='cpf-viewbox-sidebar-button-boxDotNet'   class='cpf-viewbox-sidebar-button'></div>");
    printf("</div></div></td>");
}


// WP callback for handling button include
function cleansave_add_settings_field_save_btn() {
    global $cleansave_options_name;
    
    $options         = get_option($cleansave_options_name);
    $SaveInclude     = $options['SaveInclude'];
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
	$PDFInclude      = $options['PDFInclude'];
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
	$EmailInclude    = $options['EmailInclude'];
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
    $PrintInclude    = $options['PrintInclude'];
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
	$ButtonPlacement = $options['ButtonPlacement'];
	
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
	printf("<tr><td colspan='3'><h2>Display Button(s) on the Following:</h2><hr /></td></tr>");  
}


// WP callback for handling page type
function cleansave_add_settings_field_homepage() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $homepage    = $options['HomepageInclude'];
    $isChecked   = $homepage=="include" || !isset($homepage);
    
    printf( "<select id='plugin_homepage' name='%s[HomepageInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_home()</i>");  
}


function cleansave_add_settings_field_frontpage() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $frontpage   = $options['FrontpageInclude'];
    $isChecked   = $frontpage=="include" || !isset($frontpage);
    
    printf( "<select id='plugin_frontpage' name='%s[FrontpageInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_front_page()</i>");
}


function cleansave_add_settings_field_category() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $category    = $options['CategoryInclude'];
    $isChecked   = $category=="include" || !isset($category);
    
    printf( "<select id='plugin_category' name='%s[CategoryInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_category()</i>");
}


function cleansave_add_settings_field_posts() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $posts       = $options['PostsInclude'];
    $isChecked   = $posts=="include" || !isset($posts);
    
    printf( "<select id='plugin_posts' name='%s[PostsInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_single()</i>");
}


function cleansave_add_settings_field_pages() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $pages       = $options['PagesInclude'];
    $isChecked   = $pages=="include" || !isset($pages);
    
    printf( "<select id='plugin_pages' name='%s[PagesInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<br><i> - i.e. is_page()</i>");
}


function cleansave_add_settings_field_tags() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $tags        = $options['TagsInclude'];
    $isChecked   = $tags=="include" || !isset($tags);
    
    printf( "<select id='plugin_tags' name='%s[TagsInclude]'>", $cleansave_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_tag()</i>");
    printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");
}


function cleansave_add_settings_field_excludes() {
    global $cleansave_options_name;
    
    $options     = get_option($cleansave_options_name);
    $excludes    = $options['PagesExcludes'];
    
    printf( "<input type='text' id='plugin_excludes' name='%s[PagesExcludes]' value='%s' /><br>\n", $cleansave_options_name, $excludes);
//  printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");  
}


// WP callback for handling the Google Analytics option
function cleansave_add_settings_field_ga() {
    global $cleansave_options_name;
    
	$options         = get_option($cleansave_options_name);
	$GASetting       = $options['GASetting'];
	$disabledChecked = !isset($GASetting) || $GASetting=="false";
    $enabledChecked  = $GASetting;
    
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
   global $cleansave_def_logo_url;
   global $optionsVersion;
   
   // Map the customLogo into logoUrl
   $logoUrl    = $options['logoUrl'];
   $customLogo = $options['customLogo'];
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
    $homepage      = $options['HomepageInclude'];
    $frontpage     = $options['FrontpageInclude'];
    $category      = $options['CategoryInclude'];
    $posts         = $options['PostsInclude'];
    $pages         = $options['PagesInclude'];
    $tags          = $options['TagsInclude'];
    $excludes      = $options['PagesExcludes'];
/*    
    if (isset($excludes)) {
       $IDs = explode(",", $excludes);
       $len = count($IDs);
       for ($i=0; $i<$len; $i++) {
          if ($page_id == $IDs[$i]) return false;
       }
    }
*/    
    $isHomeChecked = $homepage =='include' || !isset($homepage);
    $isFrntChecked = $frontpage=='include' || !isset($frontpage);
    $isCatgChecked = $category =='include' || !isset($category);
    $isPostChecked = $posts    =='include' || !isset($posts);
    $isPageChecked = $pages    =='include' || !isset($pages);
    $isTagChecked  = $tags     =='include' || !isset($tags);
    
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
	global $cleansave_images_base_url;
	global $cleansave_def_btn_style;
	global $cleansave_def_btn_placement;
	 	    
	$options         = get_option($cleansave_options_name);
	$buttonStyle     = $options['buttonStyle'];
    $ButtonPlacement = $options['ButtonPlacement'];
    
    $showSaveBtn     = $options['SaveInclude' ]=='both' || !isset($options['SaveInclude' ]);
    $showPrintBtn    = $options['PrintInclude']=='both' || !isset($options['PrintInclude']);
    $showPdfBtn      = $options['PDFInclude'  ]=='both' || !isset($options['PDFInclude'  ]);
    $showEmailBtn    = $options['EmailInclude']=='both' || !isset($options['EmailInclude']);
    $postId          = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : ""; 
    
    if (!isset($ButtonPlacement)) {
       $ButtonPlacement = $cleansave_def_btn_placement;
    }
    
    
	if (cleansave_is_pagetype()) {
	   if (!isset($buttonStyle)) {
            $buttonStyle = $cleansave_def_btn_style;
        }

        if ($showSaveBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanSave($postId);return false\" title=\"Save page\" class=\"cleanprint-exclude\"><img src=\"$cleansave_images_base_url/CleanSave$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
        }

        if ($showPdfBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanPDF($postId);return false\" title=\"PDF page\" class=\"cleanprint-exclude\"><img src=\"$cleansave_images_base_url/Pdf$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
        }

        if ($showEmailBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanEmail($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$cleansave_images_base_url/Email$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
        }
        
        if ($showPrintBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanPrint($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$cleansave_images_base_url/CleanPrint$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
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
function cleansave_add_save_button($content) {
    global $post;
    global $cleansave_options_name;
    global $cleansave_images_base_url;
    global $cleansave_def_btn_style;
	 	    
    $options     = get_option($cleansave_options_name);
    $buttonStyle = $options['buttonStyle'];
    $postId      = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : ""; 
        
    if (!isset($buttonStyle)) {
        $buttonStyle = $cleansave_def_btn_style;
    }

    return "<a href=\".\" onClick=\"CleanSave($postId);return false\" title=\"Save page\" class=\"cleanprint-exclude\"><img src=\"$cleansave_images_base_url/CleanSave$buttonStyle.png\" style=\"padding:0px 1px;\"/></a>";
}


// Adds the CleanPrint script tags to the head section
function cleansave_wp_head() {
    global $page_id;
    global $cleansave_options_name;
    global $cleansave_loader_url;
    global $cleansave_publisher_key;
	global $cleansave_def_logo_url;
    global $cleansave_edit_buttons;
    global $cleansave_social_buttons;
    global $cleansaveDebug;

    $options      = get_option($cleansave_options_name);
	$GASetting    = $options['GASetting'];
	$logoUrl      = $options['logoUrl'];
		
    $showPrintBtn = !isset($options['PrintInclude']) || $options['PrintInclude']!='exclude';
    $showPdfBtn   = !isset($options['PDFInclude'  ]) || $options['PDFInclude'  ]!='exclude';
    $showEmailBtn = !isset($options['EmailInclude']) || $options['EmailInclude']!='exclude';
    $showSaveBtn  = !isset($options['SaveInclude' ]) || $options['SaveInclude' ]!='exclude';
    $buttons      = '';

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
    if ($showSaveBtn ) $buttons .= ',dropbox,googleDocs,boxDotNet';

    $buttons = sprintf("&buttons=help,%s,%s,%s", substr($buttons,1),$cleansave_edit_buttons,$cleansave_social_buttons);
    
    if ($cleansaveDebug) {
		printf("\n\n\n<!-- CleanSave Debug\n\t\t%s\n\t\tpage_id:%s, home:%d, front:%d, category:%d, single:%d, page:%d, tag:%d\n-->\n\n\n",
					               http_build_query($options,"","\n\t\t"), $page_id, is_home(), is_front_page(), is_category(), is_single(), is_page(), is_tag());
	}
		
    printf( "<script id='cpf_wp' type='text/javascript'>\n");
    printf( "   function CleanSave(postId) {\n");
    printf( "   	CleanPrintPrintHtml(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Save']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function CleanEmail(postId) {\n");
    printf( "   	CleanPrintSendEmail(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Email']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function CleanPDF(postId) {\n");
    printf( "   	CleanPrintGeneratePdf(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'PDF']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function CleanPrint(postId) {\n");
    printf( "       CleanPrintPrintHtml(null,postId);\n");
                        if ($GASetting=="true") {
                            printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Print']); } catch(e) {}\n");
                        }
    printf( "   }\n");
    printf( "</script>\n");
	
	printf( "<script id='cpf_loader' type='text/javascript' src='%s?key=%s&logo=%s%s'></script>\n", 
	           $cleansave_loader_url, urlencode($cleansave_publisher_key), urlencode($logoUrl), $buttons);
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
   $options        = get_option('CleanSave');
   $optionsVersion = '1.0';
   
   if (isset($options)) {
      $version  = $options['version'];   
   
      // Don't know what version we looking at (0.97, 1.0.0, 1.0.1, or 2.0.0) so there is only
      // so much we can do.  The biggest issue of the logoUrl which was hijacked in 2.0.0 and
      // now we cannot tell it use apart from earlier releases.
      if (!isset($version)) {      
         $logoUrl = $options['logoUrl'];
         // Get rid of the old CP3/WP leader board header
         if (isset($logoUrl) && $logoUrl == 'http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg') {      
            unset($options['logoUrl']);
         }
         
         $buttonColor = $options['buttonColor'];
         if (isset($buttonColor)) {
            $options['buttonStyle'] = 'Btn_' . $buttonColor;
         }
   
         // Get rid of the old options
         unset($options['printSpecId']);
         unset($options['activationKey']);
         unset($options['buttonUrl']);
         unset($options['customButton']);
         unset($options['customLogo']);
         unset($options['buttonColor']);
      }
   
      // Set the version and commit the changes
      $options['version'] = $optionsVersion;      
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
//  add_settings_field     ('plugin_excludes',        '<strong>Excluded Page IDs:</strong>',         'cleansave_add_settings_field_excludes',      $cleansave_plugin_name, 'plugin_main');
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