<?php 

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing;

use Syscodes\Components\Support\Arr;

/**
 * Groups attributes according.
 */
class RouteGroup
{
 	/**
	 * Merge the given group attributes.
	 * 
	 * @param  array  $new
	 * @param  array  $old
	 * @param  bool  $existsPrefix
	 * 
	 * @return array
	 */
	public static function mergeGroup($new, $old, $existsPrefix = true): array
	{
		if (isset($new['domain'])) {
			unset($old['domain']);
		}
		
		if (isset($new['controller'])) {
			unset($old['controller']);
		}

		$new = array_merge(static::formatUseAs($new, $old), [
			'namespace' => static::formatUseNamespace($new, $old),
			'prefix' => static::formatUsePrefix($new, $old, $existsPrefix),
			'where' => static::formatUseWhere($new, $old)
		]);
		
		return array_merge_recursive(
			Arr::except($old, array('namespace', 'prefix', 'where', 'as')), $new
		);
	}

	/**
	 * Format the uses namespace for the new group attributes.
	 * 
	 * @param  array  $new
	 * @param  array  $old
	 * 
	 * @return string|null
	 */
	protected static function formatUseNamespace($new, $old): ?string
	{
		if (isset($new['namespace'])) {
			return isset($old['namespace'])
			       ? trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\')
			       : trim($new['namespace'], '\\');
		}

		return $old['namespace'] ?? null;
	}

	/**
	 * Format the prefix for the new group attributes.
	 * 
	 * @param  array  $new
	 * @param  array  $old
	 * @param  bool  $existPrefix
	 * 
	 * @return string|null
	 */
	protected static function formatUsePrefix($new, $old, bool $existsPrefix = true): string|null
	{
		$old = $old['prefix'] ?? null;
		
		if ($existsPrefix) {
			return isset($new['prefix']) ? trim($old, '/').'/'.trim($new['prefix'], '/') : $old;
		}
		
		return isset($new['prefix']) ? trim($new['prefix'], '/').'/'.trim($old, '/') : $old;
	}

	/**
	 * Format the "wheres" for the new group attributes.
	 * 
	 * @param  array  $new
	 * @param  array  $old
	 * 
	 * @return array
	 */
	protected static function formatUseWhere($new, $old): array
	{
		return array_merge(
			$old['where'] ?? [],
			$new['where'] ?? []
		);
	}

	/**
	 * Format the "as" clause of the new group attributes.
	 * 
	 * @param  array  $new
	 * @param  array  $old
	 * 
	 * @return array
	 */
	protected static function formatUseAs($new, $old): array
	{
		if (isset($old['as'])) {
			$new['as'] = $old['as'].($new['as'] ?? '');
		}

		return $new;
	}
}