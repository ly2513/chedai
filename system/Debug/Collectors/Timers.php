<?php
/**
 * User: yongli
 * Date: 17/4/22
 * Time: 00:13
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\Services;

/**
 * Timers collector
 */
class Timers extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var bool
	 */
	protected $hasTimeline = true;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var bool
	 */
	protected $hasTabContent = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Timers';

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * @return mixed
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		$benchmark = Services::timer(true);
		$rows = $benchmark->getTimers(6);

		foreach ($rows as $name => $info)
		{
			if ($name == 'total_execution') continue;

			$data[] = [
				'name' => ucwords(str_replace('_', ' ', $name)),
			    'component' => 'Timer',
			    'start'     => $info['start'],
			    'duration'  => $info['end'] - $info['start']
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

}
