<?php

/**
 * Image to Text
 *
 * Takes a given system image and recreates it with a given string
 * Function accepts arbitrary elements to use for markup
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	string
 * @return	string
 */	

	function image_to_text($data, $txt, $new_width = NULL, $new_height = NULL, $inline_element = 'span')
	{
		$img = imagecreatefromstring($data);
		$width = imagesx($img);
		$height = imagesy($img);

		// add abiilty to resize on the fly
		if ($new_width AND $new_height)
		{
			$new = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($new, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			unset($img);
			$img = $new;
			$width = $new_width;
			$height = $new_height;
			unset($new);
		}
		
		$counter=0;
		$output = "";
		
		for($i=0; $i<$height; $i++) {
			for($j=0; $j<$width; $j++) {
				$counter = $counter%strlen($txt);
				$cindex = ImageColorAt($img, $j, $i);
				$rgb = ImageColorsForIndex($img, $cindex);
				$hex = rgb2hex(array($rgb['red'], $rgb['green'], $rgb['blue']));
				$output .= '<' . $inline_element . ' style="color:#' . $hex . ';">' . substr($txt,$counter,1) . '</' . $inline_element . '>';
				$counter++;
			}
			$output .=  "<br />";
		}
		return $output;
	}

	function rgb2hex($rgb)
	{
		for($i=0; $i<count($rgb); $i++){
			if(strlen($hex[$i] = dechex($rgb[$i])) == 1) {
				$hex[$i] = "0" . $hex[$i];
			}
		}
		return implode($hex);
	}