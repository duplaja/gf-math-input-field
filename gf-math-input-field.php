<?php
/*
Plugin Name: Gravity Forms Math Field Add-On
Plugin URI: https://dandulaney.com
Description: A math input field add-on, built using MathQuill.
Version: 1.6
Author: Dan Dulaney
Author URI: https://dandulaney.com
Text Domain: mathfieldaddon
Domain Path: /languages
GitHub Plugin URI: https://github.com/duplaja/gf-math-input-field

------------------------------------------------------------------------
Copyright 2020 Dan Dulaney.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'GF_MATH_FIELD_ADDON_VERSION', '1.6' );

add_action( 'gform_loaded', array( 'GF_Math_Field_AddOn_Bootstrap', 'load' ), 5 );

class GF_Math_Field_AddOn_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfmathfieldaddon.php' );

        GFAddOn::register( 'GFMathFieldAddOn' );
    }

}

add_filter( 'gform_field_content', function ( $field_content, $field, $value ) {
    // Change 2 to the id number of your field.
    if ( $field->type == 'math' ) {

        if ( $field->is_entry_detail_edit() ) {
            $value = esc_attr( $value );
            $name  = 'input_' . esc_attr( $field->id );
 
            if(!empty($field->description)) {

                $description = "<div id='description_{$field->id}' style='margin-bottom:10px'>".$field->description."</div>";


                $description .= "<script>
                jQuery('#description_{$field->id} > m').each(function() {MQ.StaticMath(jQuery( this )[0]);});
                </script>";

                $field_content = str_replace('</label>','</label>'."$description",$field_content);

            }
            

            //return "<input type='text' name='{$name}' value='{$value}'>";
        } elseif ( $field->is_entry_detail() ) {
            $field_id = $field->id;

            error_log("$field_content");

            $replacement_string = "
            Answer: <span id='display_span_{$field_id}' style='font-size:18px'>$value</span>
            <script>

                var problemSpan_{$field_id} = document.getElementById('display_span_{$field_id}');
                MQ.StaticMath(problemSpan_{$field_id});
                jQuery('#description_{$field->id} > m').each(function() {MQ.StaticMath(jQuery( this )[0]);});
            </script>
            ";
            $field_content = str_replace( "$value", $replacement_string, $field_content );

            $description = "<tr><td colspan='2' class='entry-view-field-value' id='description_{$field->id}'>".$field->description."</td></tr>";

            $field_content = str_replace('<td colspan="2" class="entry-view-field-value',"$description".'<td colspan="2" class="entry-view-field-value',$field_content);

        }
    }
 
    return $field_content;
}, 10, 3 );