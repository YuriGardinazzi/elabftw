<?php declare(strict_types=1);
/**
 * @author Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2023 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */

namespace Elabftw\AuditEvent;

class TeamRemoval extends AbstractUsers2TeamsModifiedEvent
{
    public function __construct(private int $teamid, int $userid)
    {
        parent::__construct($userid);
    }

    public function getBody(): string
    {
        return sprintf('User was removed from team with id %d.', $this->teamid);
    }
}
