<?php

namespace Vendor\YbcFramework\Router;

use Vendor\YbcFramework\Utils\Utils;

use RuntimeException;

//TODO: Create upload dir if it does'nt exist
class Upload
{
	private $files;
	private $files_path;
	private $upload_dir;
	private $allow_multiple_files;
	private $allowed_extensions;
	private $max_file_size;

	public function __construct(
		$files,
		$upload_dir = null,
		$allow_multiple_files = true,
		$allowed_extensions = [],
		$max_file_size = 0
	) {
		$this->files = $files;
		$this->upload_dir = $upload_dir;
		$this->allow_multiple_files = $allow_multiple_files;
		$this->allowed_extensions = $allowed_extensions;
		$this->max_file_size = $max_file_size;

		// Create the upload directory if it doesn't exist
		if (!is_null($this->upload_dir) && !file_exists($this->upload_dir)) {
			mkdir($this->upload_dir, 0755, true);
		}
	}

	public function validate()
	{
		$fileCount = count($this->files['name']);

		// Check if multiple files are uploaded without permission
		if (!$this->allow_multiple_files && $fileCount > 1) {
			throw new RuntimeException("Multiple file uploads not allowed.");
		}

		for ($i = 0; $i < $fileCount; $i++) {
			// Check for upload errors
			if ($this->files['error'][$i] !== UPLOAD_ERR_OK) {
				throw new RuntimeException("File upload error: " . $this->files['error'][$i]);
			}

			// Validate file size
			if ($this->max_file_size > 0 && $this->files['size'][$i] > $this->max_file_size) {
				throw new RuntimeException("File size exceeds the allowed limit.");
			}

			// Validate file extension
			$ext = pathinfo($this->files['name'][$i], PATHINFO_EXTENSION);
			if (!empty($this->allowed_extensions) && !in_array($ext, $this->allowed_extensions)) {
				throw new RuntimeException("File type {$ext} is not allowed.");
			}
		}

		return $this->files_path;
	}

	public function upload()
	{
		$this->validate();
		$fileCount = count($this->files['name']);

		for ($i = 0; $i < $fileCount; $i++) {
			$target_file = $this->upload_dir . DIRECTORY_SEPARATOR . Utils::get_uuid() . '.' . Utils::get_file_extension($this->files['name'][$i]);
			$this->files_path[] = $target_file;
			move_uploaded_file($this->files['tmp_name'][$i], $target_file);
		}

		return true;
	}

	public function set_files($files)
	{
		// Handle if one or multiple files are uploaded
		if (array_key_exists('files', $files)) {
			$files = $files['files'];
		}
		$this->files = $files;
	}

	public function get_files()
	{
		return $this->files;
	}
}
