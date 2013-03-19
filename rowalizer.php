<?php
/* * * * * 
    The data Row-A-Lizer 1.2
	by Double D Photo & Design
	
	
*/
// Redefine as required.
$container_shortcode_call = 'resume';
//[resume] shortcode will signal the Row-A-Lizer to start
$rowalizer_shortcode_call = 'cols';
//[cols] will start a new section of rows and columns
$section_title = 'title';
//[cols title="THE TITLE"] will define the title of the section for print and css
$class_additions = 'style';
//[cols style="css_class"] will add a class to the section

//Pretty HTML: comment out the next lines for gross, but concise code.
$DD_line_breaks = "\n";
$DD_tabs = "    ";



//  ******* leave the rest alone unless you know what you are doing... not that I did...

//Will normalize line breaks from user entry
function DD_line_breaks($atts,$content = null) {
	$output = str_replace(array('<p>','<br />','<br>'),"\n",$content);
	$output = str_replace('</p>','',$output);
	while (stristr($output,"\n\n")){
		$output = str_replace("\n\n","\n",$output);
		}
	return $output;
}


//The row-a-lizer shortcodes
add_shortcode("$container_shortcode_call", 'rowalizer_container_shortcode');
add_shortcode("$rowalizer_shortcode_call", 'rowalizer_shortcode');

function rowalizer_container_shortcode($atts,$content = null) {
	global $container_shortcode_call, $DD_line_breaks, $DD_tabs;
	$r = $DD_line_breaks;
	$t = $DD_tabs;
	$output = $r.'<div class="'.$container_shortcode_call.'">' . do_shortcode($content) .$t.'<div class="anchor"></div>'.$r.$t.'</div><!--'.$container_shortcode_call.'-->';
	$output = str_replace(array('<p>','</p>'),'',$output);
	return $output;
}
function rowalizer_shortcode($atts,$content = null) {
	global  $section_title,
			$class_additions, 
			$container_shortcode_call, 
			$DD_line_breaks, 
			$DD_tabs; 
	
	$r = $DD_line_breaks;
	$t = $DD_tabs;
	
	static $col_instance = 0;
	$col_instance++;

	extract(shortcode_atts(array(
		$section_title		=> '',
		$class_additions	=> ''
	), $atts));
	
	//add a break to begining for use later
	$begining = "\n";
	$content = $begining . $content;
	
	//Turn all forms of line break into a carriage return 
	//and turn all multiple carriage returns into SINGLE carriage return
	$content = DD_line_breaks('',$content);
	
	//Get rid of that first carriage return... and the last one
	$content = trim($content);
	
	//Break into rows
	$content = explode("\n", $content);

	//Break into columns (separated from rows currently)
	$i=0; while($content[$i]) {
		$content[$i] = explode("|", $content[$i]);
		$i++;
	}
	
	//creating a multi-dimensional foreach. By specifying keys ($rows and $cols) I retain NULL values.
	foreach ($content as $rows=>$row) {
		foreach ($row as $cols=>$cell) {
			if($tot_rows<$rows)$tot_rows = $rows;
			if($tot_cols<$cols)$tot_cols = $cols;
		}
	}
	++$tot_rows; ++$tot_cols;
	//echo "number of rows is ".$tot_rows.", and number of cols is ".$tot_cols.".<br/>";
	
	$row=0;
	$cell=0;
	while($tot_rows>0){
		$temp_tot_cols = $tot_cols;
		$output .=  $t . '<div class="row row-'.++$row.(($row%2) ? ' odd' : ' even').'">' . $r;
		while($tot_cols>0){
			$cell++;
			
			//is there content in the cell?
			if( trim($content[($row-1)][($cell-1)]) ){
				$contents = trim($content[($row-1)][($cell-1)]);
				$css_empty = '';
			}else{
				$contents = '&nbsp;';
				$css_empty = ' empty ';
			}
			
			$output .= $t.$t. '<span class="cell cell-'.$cell.(($cell%2) ? ' odd' : ' even').$css_empty.'">'.
							  $contents.
							  '</span>'.$r;
			$tot_cols--;
		}
		$output .= $t.$t.'<div class="anchor"></div>'.$r;
		$output .= $t.$t.'</div><!-- row-'.$row.' -->'.$r;
		$tot_rows--;
		$tot_cols = $temp_tot_cols;
		$cell=0;
	}
	
	$output = str_replace(array('<p>','</p>'),'',$output);
	if($atts[$section_title]){
		$safe_title = str_replace(' ','_',strtolower(trim($atts[$section_title])));
		$preCols =  '<div class="section section-'.$col_instance.' section_title-'.$safe_title.' '.$atts[$class_additions].'">' . $r . 
					$t . '<div class="section_heading"><span>' . $atts[$section_title] . '</span></div>' . $r .
					$t . '<div class="section_content">' . $r;
	}else{
		$preCols =  '<div class="section section-'.$col_instance.' '.$atts[$class_additions].'">' . $r;
	}
	$postCols = $t . '</div><!--section'.$col_instance.'-->' . $r .
			$t . '</div><!--section_content-->' . $r;

	return $preCols . $output . $postCols;
}
// End Row-A-Lizer
