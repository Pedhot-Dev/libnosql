<?php

/*
 *
 * Copyright 2024 PedhotDev
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * 	http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

declare(strict_types=1);

namespace PedhotDev\libnosql;

use function array_values;
use function count;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function is_dir;
use function is_file;
use function mkdir;
use function serialize;
use function unlink;
use function unserialize;
use const DIRECTORY_SEPARATOR;

class Table
{
	/**
	 * Table Name.
	 */
	private string $table;

	/**
	 * Table constructor.
	 */
	public function __construct(string $table)
	{
		$this->table = $table;
		@mkdir($this->getPath());
	}

	public function getPath() : string
	{
		return LibNoSQL::getPath() . "tables" . DIRECTORY_SEPARATOR . $this->getName() . DIRECTORY_SEPARATOR;
	}

	public function getName() : string
	{
		return $this->table;
	}

	/**
	 * You can use this function to delete table
	 */
	public function drop() : void
	{
		LibNoSQL::removeDir($this->getPath());
	}

	public function getString(string $key) : ?string
	{
		$val = $this->getValue($key);
		if($val === null) return null;
		return $val;
	}

	public function getValue(string $key) : ?string
	{
		if (($path = $this->isExists($key)) !== false) {
			return file_get_contents($path);
		}
		return null;
	}

	public function isExists(string $key) : string|bool
	{
		$dir = $this->getPath() . $key[0] . DIRECTORY_SEPARATOR;
		if (is_dir($dir)) {
			$file = $dir . $key . LibNoSQL::FILE_EXTENSION;
			if (is_file($file)) {
				return $file;
			}
		}
		return false;
	}

	public function getObject(string $key) : ?object
	{
		$val = $this->getValue($key);
		if($val === null) return null;
		$val = unserialize($val);
		if ($val === false) {
			return null;
		}
		return (object) $val;
	}

	public function getInt(string $key) : ?int
	{
		$val = $this->getValue($key);
		if($val === null) return null;
		return (int) $val;
	}

	public function getFloat(string $key) : ?float
	{
		$val = $this->getValue($key);
		if($val === null) return null;
		return (float) $val;
	}

	public function setInt(string $key, int $value) : void
	{
		$this->setValue($key, $value);
	}

	public function setValue(string $key, $value) : void
	{
		$path = $this->getPath() . $key[0] . DIRECTORY_SEPARATOR;
		if ($this->isExists($key) === false) {
			@mkdir($path);
		}
		$file = $path . $key . LibNoSQL::FILE_EXTENSION;
		file_put_contents($file, $value);
	}

	public function setFloat(string $key, float $value) : void
	{
		$this->setValue($key, $value);
	}

	public function setObject(string $key, object $object) : void
	{
		$serializedObj = serialize($object);
		if (!$serializedObj) {
			return;
		}
		$this->setString($key, $serializedObj);
	}

	public function setString(string $key, string $value) : void
	{
		$this->setValue($key, $value);
	}

	public function pushArray(string $key, mixed $value, mixed $index = null) : void
	{
		$arr = $this->getArray($key);
		if ($index === null) {
			$arr[] = $value;
		} else {
			$arr[$index] = $value;
		}
		$this->setArray($key, $arr);
	}

	public function getArray(string $key) : ?array
	{
		$val = unserialize($this->getValue($key));
		return $val !== null && $val !== false ? $val : null;
	}

	public function setArray(string $key, array $value) : void
	{
		$value = serialize($value);
		$this->setValue($key, $value);
	}

	public function inArray(string $key, mixed $value) : bool
	{
		if (in_array($value, $this->getArray($key), true)) {
			return true;
		}
		return false;
	}

	public function countArray(string $key) : int
	{
		return count($this->getArray($key));
	}

	public function existsArray(string $key, mixed $index) : bool
	{
		return isset($this->getArray($key)[$index]);
	}

	public function reindexArray(string $key) : void
	{
		$ar = $this->getArray($key);
		$output = array_values($ar);
		$this->setArray($key, $output);
	}

	public function unsetArray(string $key, mixed $index) : void
	{
		$arr = $this->getArray($key);
		if (isset($arr[$index])) {
			unset($arr[$index]);
			$this->setArray($key, $arr);
		}
	}

	public function unset(string $key) : void
	{
		$dir = $this->getPath() . $key[0] . DIRECTORY_SEPARATOR;
		if (is_dir($dir)) {
			$file = $dir . $key . LibNoSQL::FILE_EXTENSION;
			if (is_file($file)) {
				@unlink($file);
			}
		}
	}

}
