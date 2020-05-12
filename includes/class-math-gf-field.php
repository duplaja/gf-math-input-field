<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Math_GF_Field extends GF_Field {

	/**
	 * @var string $type The field type.
	 */
	public $type = 'math';

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Math', 'mathfieldaddon' );
	}

	/**
	 * Assign the field button to the Advanced Fields group.
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'description_setting',
			'rules_setting',
			'placeholder_setting',
			'input_class_setting',
			'css_class_setting',
			'admin_label_setting',
			'default_value_setting',
			'conditional_logic_field_setting',
		);
	}

	/**
	 * Enable this field for use with conditional logic.
	 *
	 * @return bool
	 */
	public function is_conditional_logic_supported() {
		return true;
	}

	/**
	 * The scripts to be included in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {

		// set the default field label for the math type field
		$script = sprintf( "function SetDefaultValues_math(field) {field.label = '%s';}", $this->get_form_editor_field_title() ) . PHP_EOL;

		// initialize the fields custom settings
		$script .= "jQuery(document).bind('gform_load_field_settings', function (event, field, form) {" .
		           "var inputClass = field.inputClass == undefined ? '' : field.inputClass;" .
		           "jQuery('#input_class_setting').val(inputClass);" .
		           "});" . PHP_EOL;

		// saving the math setting
		$script .= "function SetInputClassSetting(value) {SetFieldProperty('inputClass', value);}" . PHP_EOL;

		$script.= "jQuery( 'm' ).each(function() {MQ.StaticMath(jQuery( this )[0]);});". PHP_EOL;

		return $script;
	}

	/**
	 * Define the fields inner markup.
	 *
	 * @param array $form The Form Object currently being processed.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$id              = absint( $this->id );
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		// Prepare the value of the input ID attribute.
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$value = esc_attr( $value );

		// Get the value of the inputClass property for the current field.
		$inputClass = $this->inputClass;

		// Prepare the input classes.
		$size         = $this->size;
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $class_suffix . ' ' . $inputClass;

		// Prepare the other input attributes.
		$tabindex              = $this->get_tabindex();
		$logic_event           = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

		$icon_folder = plugin_dir_url( __DIR__ ).'icons/';

		// Prepare the input tag for this field.
		$input = "
		<span id='span_{$id}' class='math-input' style='display:inline-block;";
		
		if($is_form_editor || $is_entry_detail) {
			$input.= 'min-width:200px;padding:5px 5px 5px 5px;';
		}

		$input .="'>{$value}</span><script>
		var answerSpan_{$id} = document.getElementById('span_{$id}');
		var answerMathField_{$id} = MQ.MathField(answerSpan_{$id}, {
		handlers: {
			edit: function() {
				var enteredMath_{$id} = answerMathField_{$id}.latex(); // Get entered math in LaTeX format
			//checkAnswer(enteredMath_{$id});
				jQuery('#{$field_id}').val(enteredMath_{$id});
			}
		}
		});

		</script>";

		if(!$is_form_editor && !$is_entry_detail) {

			$input .= "<button type='button' class='btn btn-calc-math' style='margin-bottom:0px;margin-right:0px;padding:2px 2px 2px 2px!important;vertical-align:bottom' onClick=\"togglebuttons({$id})\"><img src='{$icon_folder}calc.png' alt='Toggle Entry Buttons'></button>
		<script>
				function input{$id}(str) {

			//var selection = window.getSelection().anchorNode.textContent.substring(window.getSelection().extentOffset, window.getSelection().anchorOffset)

			//alert(selection);
			//str = str+selection

			if(str == 'backspace') {
			
				answerMathField_{$id}.keystroke('Backspace');
			} else {

				answerMathField_{$id}.cmd(str)
				answerMathField_{$id}.focus()
			}
		}
		</script>	
		<div id='keyboard_{$id}' style='display:none'>

	<div role='group' aria-label='common math functions' style='float:left; margin-right:30px'>

		Common
		<br>
	
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('1')\">1</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('2')\">2</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('3')\">3</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('backspace')\">&#x2B05;</button>
		<br>
				
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('3')\">3</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('4')\">4</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('5')\">5</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\\\\frac')\"><img src='{$icon_folder}fract.png' alt='Fraction'></button>

		<br>

		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('7')\">7</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('8')\">8</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('9')\">9</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('0')\">0</button>

		<br>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('+')\"><img src='{$icon_folder}plus.png' alt='Plus'></button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('-')\"><img src='{$icon_folder}minus.png' alt='Minus'></button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\\\\times')\"><img src='{$icon_folder}multiply.png' alt='Multiply'></button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\div')\"><img src='{$icon_folder}divide.png' alt='Divide'></button>
		<br>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('(')\">( )</button>

		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('x')\">x</button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('^')\">x<sup>n</sup></button>
		<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('=')\"><img src='{$icon_folder}equal.png' alt='Equals'></button>
		
		</div>
		<div role='group' aria-label='less common math functions' style='float:left;clear:right'>

			Less Common

			<br>

			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\pi')\"><img src='{$icon_folder}pi.png' alt='Pi'></button>
			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\infinity')\"><img src='{$icon_folder}inf.png' alt='Infinity'></button>
			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\sqrt')\"><img src='{$icon_folder}sqrt.png' alt='Square Root'></button>
			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\\\\nthroot')\"><sup>n</sup>√</button>

			<br>
			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\lt')\"><img src='{$icon_folder}lt.png' alt='Less Than'></button>

			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\gt')\"><img src='{$icon_folder}gt.png' alt='Greater Than'></button>

			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\le')\"><img src='{$icon_folder}lte.png' alt='Less Than or Equal'></button>

			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\ge')\"><img src='{$icon_folder}gte.png' alt='Greater Than or Equal'></button>

			<br>

			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('=')\"><img src='{$icon_folder}is-not-equal.png' alt='Does Not Equal'></button>
			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('\pm')\"><img src='{$icon_folder}pm.png' alt='Plus Minus'></button>
			<button type='button' class='btn btn-calc-math' onClick=\"input{$id}('{')\">{ }</button>


		</div>
		<div style='clear:both'>
		Some Icons by <a href='https://www.flaticon.com/authors/freepik' target='_blank'>Freepik</a> from <a href='https://flaticon.com' target='_blank'>flaticon.com</a>
		</div>
</div>";
	}
		$input.= "<input name='input_{$id}' id='{$field_id}' type='hidden' value='{$value}' class='{$class}' {$tabindex} {$logic_event} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text}/>";

		return sprintf( "<div class='ginput_container ginput_container_%s'>%s</div>", $this->type, $input );
	}
}

GF_Fields::register( new Math_GF_Field() );