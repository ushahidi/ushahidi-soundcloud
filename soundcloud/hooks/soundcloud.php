<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Embed SoundCloud Playback Widgets
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com>
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class soundcloud {

	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'addHooks'));
	}

	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function addHooks()
	{
		// Only add the events if we are on that controller
		if (Router::$controller == 'reports' AND Router::$method == 'view')
		{
			Event::add('ushahidi_filter.report_description', array($this, 'injectPlaybackWidgets'));
		}
	}

	/**
	 * Convert soundcloud text anchors into embedded playback widges.
	 *
	 * @param   string   text to autoembed
	 * @return  string
	 */
	public function injectPlaybackWidgets()
	{
		$text = Event::$data;

		try {
			if (preg_match_all('/<a\s*href=["\']([^"\']+)["\'](.*?)>(.*?)<\/a>/i', $text, $matches))
			{
				foreach ($matches[1] as $index => $match)
				{
					if($parsed = @parse_url($match))
					{
						if(isset($parsed['host']) && $parsed['host'] == 'soundcloud.com')
						{
							$api = curl_init();
							curl_setopt($api, CURLOPT_URL, 'http://soundcloud.com/oembed?format=json&url=' . urlencode($match));
							curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
							$embed = curl_exec($api);
							curl_close($api);

							if(strlen($embed) && $embed = json_decode($embed)) {
								if(isset($embed->html)) {
									$text = str_replace($matches[0][$index], $embed->html, $text);
								}
							}
						}
					}
				}
			}
		} catch(Exception $e) {
			// Silently ignore errors.
		}

		Event::$data = $text;
		return true;
	}
}

new soundcloud;
