<?php
/**
 * @copyright Copyright (C) 2010-2023, the Friendica project
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace Friendica\Module\Api\Mastodon\Statuses;

use Friendica\Core\System;
use Friendica\DI;
use Friendica\Model\Item;
use Friendica\Model\Post;
use Friendica\Module\BaseApi;
use Friendica\Protocol\Activity;

/**
 * @see https://docs.joinmastodon.org/methods/statuses/
 */
class FavouritedBy extends BaseApi
{
	/**
	 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
	 */
	protected function rawContent(array $request = [])
	{
		$uid = self::getCurrentUserID();

		if (empty($this->parameters['id'])) {
			DI::mstdnError()->UnprocessableEntity();
		}

		$id = $this->parameters['id'];
		if (!Post::exists(['uri-id' => $id, 'uid' => [0, $uid]])) {
			DI::mstdnError()->RecordNotFound();
		}

		$activities = Post::selectPosts(['author-id'], ['thr-parent-id' => $id, 'gravity' => Item::GRAVITY_ACTIVITY, 'verb' => Activity::LIKE, 'deleted' => false]);

		$accounts = [];

		while ($activity = Post::fetch($activities)) {
			$accounts[] = DI::mstdnAccount()->createFromContactId($activity['author-id'], $uid);
		}

		System::jsonExit($accounts);
	}
}
