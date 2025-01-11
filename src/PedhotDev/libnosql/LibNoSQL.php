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

use function basename;
use function is_dir;
use function is_file;
use function mkdir;
use function rmdir;
use function scandir;
use function unlink;
use const DIRECTORY_SEPARATOR;

class LibNoSQL
{
	/**
	 * You can use LibNoSQL::setDatabase( dbname ) to set database without edit the code.
	 */

	/**
	 * File Extension .ns (nosql) default
	 */
	const FILE_EXTENSION = ".ns";

	private static string $path;

	/**
	 * Set database
	 */
	public static function setDatabaseDirectory(string $dbName) : void {
		self::$path = $dbName . DIRECTORY_SEPARATOR;
	}

	/**
	 * Optional, create database directory if it doesn't exist
	 */
	public static function init() : void {
		@mkdir(self::getPath());
		@mkdir(self::getPath() . DIRECTORY_SEPARATOR . "tables");
	}

	public static function getTable(string $table) : Table {
		return new Table($table);
	}

	public static function getPath() : string {
		return self::$path;
	}

	public static function removeFile(string $path) : int {
		unlink($path);
		return 1;
	}

	public static function removeDir(string $dirPath) : int {
		$files = 1;
		if(basename($dirPath) == "." || basename($dirPath) == "..") {
			return 0;
		}
		foreach (scandir($dirPath) as $item) {
			if($item != "." || $item != "..") {
				if(is_dir($dirPath . DIRECTORY_SEPARATOR . $item)) {
					$files += self::removeDir($dirPath . DIRECTORY_SEPARATOR . $item);
				}
				if(is_file($dirPath . DIRECTORY_SEPARATOR . $item)) {
					$files += self::removeFile($dirPath . DIRECTORY_SEPARATOR . $item);
				}
			}

		}
		rmdir($dirPath);
		return $files;
	}
}
