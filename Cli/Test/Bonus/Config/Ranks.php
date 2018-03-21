<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\App\Generic2\Cli\Test\Bonus\Config;


class Ranks
{
    const RANK_GV_QUAL = \Praxigento\App\Generic2\Cli\Test\Bonus\Config::RANK_GV_QUAL;
    const RANK_PSAA_QUAL = \Praxigento\App\Generic2\Cli\Test\Bonus\Config::RANK_PSAA_QUAL;
    const RANK_PV_QUAL = \Praxigento\App\Generic2\Cli\Test\Bonus\Config::RANK_PV_QUAL;
    protected $repoRank;

    public function __construct(
        \Praxigento\BonusBase\Repo\Entity\Rank $repoRank
    ) {
        $this->repoRank = $repoRank;
    }

    public function addRanks()
    {
        $this->repoRank->getIdByCode(self::RANK_PV_QUAL);
    }
}