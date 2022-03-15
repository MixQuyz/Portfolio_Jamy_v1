<?php

namespace MetForm\Core\Entries;

defined('ABSPATH') || exit;

class File_Data_Validation {

	public static function validate($fields, $file_data) {

		$response = [];

		foreach ($file_data as $key => $data) {

			$field = $fields[$key];

			$limit_status = isset($field->mf_input_file_size_status) && $field->mf_input_file_size_status == 'on' ? true : false;
			$file_size_limit = isset($field->mf_input_file_size_limit) ? $field->mf_input_file_size_limit : 128;

			$size = intval($data['size']) / 1024;

			if($limit_status === true && $size > $file_size_limit) {
				$response[$key] = esc_html__('File size cannot exceed ' . $file_size_limit.'kb', 'metform');
			} else {
				$mf_input_file_types = isset($field->mf_input_file_types) ? $field->mf_input_file_types : ['.jpg', '.jpeg', '.gif', '.png'];
				$result = false;
				$path = $_FILES[$key]['name'];
				$upload_extension = '.'.pathinfo($path, PATHINFO_EXTENSION);
				$upload_extension = strtolower($upload_extension);

				if(in_array($upload_extension, $mf_input_file_types) === true && array_key_exists($upload_extension, self::mimes()) === true) {

                    if(!function_exists('finfo_open')){
                        $result = true;
                    }else{
    					$mime_type = self::mimes()[$upload_extension];
    
    					$finfo = finfo_open(FILEINFO_MIME);
    					$mime = finfo_file($finfo, $_FILES[$key]['tmp_name']);
    					finfo_close($finfo);
    
    					if(strpos($mime, $mime_type['mime']) !== false) {
    						$result = true;
    					}
                    }
				}
				if($result == false){
					$extensions = implode(', ', $mf_input_file_types);

					$response[$key] = esc_html__('We only accept'. $extensions.' types file', 'metform');

				}
			} 
		}
		return $response;
	}

	public static function mimes() {
		
		$mimes = array(
			'.docx' => [
				'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
			],
			'.png' => [
				'mime' => 'image/png'
			],
			'.jpg' => [
				'mime' => 'image/jpeg'
			],
			'.jpeg' => [
				'mime' => 'image/jpeg'
			],
			'.gif' => [
				'mime' => 'image/gif'
			],
			'.pdf' => [
				'mime'	=> 'application/pdf'
			],
			'.doc' => [
				'mime'	=> 'application/msword'
			],
			'.icon' => [
				'mime' => 'image/x-icon'
			],
			'.pptx' => [
				'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
			],
			'.ppt' => [
				'mime' => 'application/vnd.ms-powerpoint'
			],
			'.pps' => [
				'mime' => 'application/vnd.ms-powerpoint'
			],
			'.ppsx' => [
				'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
			],
			'.odt' => [
				'mime' => 'application/vnd.oasis.opendocument.text'
			],
			'.xls' => [
				'mime' => 'application/vnd.ms-excel'
			],
			'.xlsx' => [
				'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
			],
			'.psd' => [
				'mime' => 'image/vnd.adobe.photoshop'
			],
			'.mp3' => [
				'mime' => 'audio/mpeg'
			],
			'.m4a' => [
				'mime' => 'audio/x-m4a'
			],
			'.ogg' => [
				'mime' => 'audio/ogg'
			],
			'.wav' => [
				'mime' => 'audio/x-wav',
			],
			'.mp4' => [
				'mime' => 'video/mp4'
			],
			'.m4v' => [
				'mime' => 'video/x-m4v'
			],
			'.mov' => [
				'mime' => 'video/quicktime'
			],
			'.wmv' => [
				'mime' => 'video/x-ms-asf'
			],
			'.avi' => [
				'mime' => 'video/x-msvideo'
			],
			'.mpg' => [
				'mime' => 'video/mpeg'
			],
			'.ogv' => [
				'mime' => 'video/ogg'
			],
			'.3gp' => [
				'mime' => 'video/3gpp',
			],
			'.3g2' => [
				'mime' => 'video/3gpp2' 
			],
			'.zip' => [
				'mime' => 'application/zip'
			],
			'.csv' => [
				'mime' => 'text/plain'
			]
		);

		return $mimes;
	}
}