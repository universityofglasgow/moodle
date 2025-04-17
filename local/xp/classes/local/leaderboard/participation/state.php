<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\local\leaderboard\participation;

/**
 * Participation state.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class state {

    /** @var bool */
    protected $isparticipating;
    /** @var ?\DateTimeImmutable */
    protected $lockeduntil;

    /**
     * Constructor.
     *
     * @param bool $isparticipating
     * @param \DateTimeImmutable|null $lockeduntil
     */
    public function __construct(bool $isparticipating, ?\DateTimeImmutable $lockeduntil = null) {
        $this->isparticipating = $isparticipating;
        $this->lockeduntil = $lockeduntil;
    }

    /**
     * Get the locked date.
     *
     * @return \DateTimeImmutable|null
     */
    public function get_state_locked_until(): ?\DateTimeImmutable {
        return $this->lockeduntil;
    }

    /**
     * Is participating.
     *
     * @return bool
     */
    public function is_participating(): bool {
        return $this->isparticipating;
    }

    /**
     * Is state locked?
     *
     * @return bool
     */
    public function is_state_locked(): bool {
        return $this->lockeduntil && $this->lockeduntil > new \DateTimeImmutable();
    }

    /**
     * Is state locked until date.
     *
     * @param \DateTimeImmutable $dt
     * @return bool
     */
    public function is_state_locked_until(\DateTimeImmutable $dt): bool {
        return $this->lockeduntil && $this->lockeduntil && $this->lockeduntil >= $dt;
    }


}
