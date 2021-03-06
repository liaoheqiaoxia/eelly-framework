<?php

declare(strict_types=1);

/*
 * This file is part of eelly package.
 *
 * (c) eelly.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shadon\Events\Listener;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

/**
 * @author hehui<hehui@eelly.net>
 */
class AclListener extends AbstractListener
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher): void
    {
    }
}
