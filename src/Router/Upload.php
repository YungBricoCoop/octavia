<?php

namespace ybc\octavia\Router;

use ybc\octavia\Utils\Utils;
use ybc\octavia\{MultipleFilesNotAllowedException, FileUploadErrorException, FileSizeExceededException, FileTypeNotAllowedException};

class Upload
{
	private $files;
	private $uploaded_files;
	private $upload_dir;
	private $allow_multiple_files;
	private $allowed_extensions;
	private $max_file_size;


	/**
	 * Validate the uploaded files
	 * @return array The uploaded files
	 */
	public function validate()
	{
		$file_count = count($this->files['name']);

		// Check if multiple files are uploaded without permission
		if (!$this->allow_multiple_files && $file_count > 1) {
			throw new MultipleFilesNotAllowedException();
		}

		for ($i = 0; $i < $file_count; $i++) {
			$error = $this->files['error'][$i];

			// Check for upload errors
			if ($error !== UPLOAD_ERR_OK && $error !== UPLOAD_ERR_INI_SIZE && $error !== UPLOAD_ERR_FORM_SIZE) {
				throw new FileUploadErrorException();
			}

			// Validate file size
			if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || $this->files['size'][$i] > $this->max_file_size) {
				throw new FileSizeExceededException("File size exceeds {$this->max_file_size} bytes.");
			}

			// Validate file extension
			$ext = pathinfo($this->files['name'][$i], PATHINFO_EXTENSION);
			if (!empty($this->allowed_extensions) && !in_array($ext, $this->allowed_extensions)) {
				throw new FileTypeNotAllowedException("File type not allowed. Allowed types: " . implode(", ", $this->allowed_extensions) . ".");
			}
		}

		return $this->uploaded_files;
	}

	/**
	 * Validate and move the files to the upload directory
	 * @return bool
	 */
	public function upload()
	{
		$this->validate();
		$file_count = count($this->files['name']);

		for ($i = 0; $i < $file_count; $i++) {
			$target_file = $this->upload_dir . DIRECTORY_SEPARATOR . Utils::get_uuid() . '.' . Utils::get_file_extension($this->files['name'][$i]);
			$this->uploaded_files[] = $target_file;
			move_uploaded_file($this->files['tmp_name'][$i], $target_file);
		}

		return true;
	}

	/**
	 * Get all the uploaded files
	 * @return array
	 */
	public function get_uploaded_files()
	{
		return $this->uploaded_files;
	}

	/**
	 * Set the upload params
	 * @param string $upload_dir The upload directory
	 * @param bool $allow_multiple_files If multiple files are allowed
	 * @param array $allowed_extensions The allowed extensions
	 * @param string $max_file_size The max file size
	 * @example set_params("uploads", true, ["jpg", "png"], 10MB)
	 * @return void
	 */
	public function set_params(string $upload_dir, bool $allow_multiple_files, array $allowed_extensions, string $max_file_size)
	{
		// create directory if it doesn't exist
		if (!file_exists($upload_dir)) {
			mkdir($upload_dir, 0777, true);
		}
		$this->upload_dir = $upload_dir;
		$this->allow_multiple_files = $allow_multiple_files;
		$this->allowed_extensions = $allowed_extensions;

		// get the max file size from php.ini if it's not set
		$max_file_size = Utils::convert_to_bytes($max_file_size);
		$ini_max_upload_size = Utils::convert_to_bytes(ini_get('upload_max_filesize'));
		$ini_post_max_size = Utils::convert_to_bytes(ini_get('post_max_size'));
		$ini_memory_limit = Utils::convert_to_bytes(ini_get('memory_limit'));
		$ini_max_upload_size = min($ini_max_upload_size, $ini_post_max_size, $ini_memory_limit);

		$this->max_file_size = $max_file_size && $max_file_size <= $ini_max_upload_size ? $max_file_size : $ini_max_upload_size;
	}

	/**
	 * Set the files that are uploaded
	 * @param array $files
	 * @example set_files($_FILES)
	 * @return void
	 */
	public function set_files(array $files)
	{
		// Handle if one or multiple files are uploaded
		if (array_key_exists('files', $files)) {
			$files = $files['files'];
		}
		$this->files = $files;
	}
}
