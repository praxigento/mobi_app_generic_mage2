<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\App\Generic2\Cli\Cmd\Test\Downline\Init;

use Praxigento\App\Generic2\Config as Cfg;

/**
 * Create downline tree;
 */
class CreateDownline
{
    const A_ENTRIES = \Praxigento\App\Generic2\Cli\Cmd\Test\Downline\Init\CreateCustomers::A_ENTRIES;
    const A_MAP_BY_MLM_ID = \Praxigento\App\Generic2\Cli\Cmd\Test\Downline\Init\CreateCustomers::A_MAP_BY_MLM_ID;
    /** @var \Praxigento\Downline\Service\ISnap */
    protected $callDwnlSnap;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var \Praxigento\Downline\Repo\Entity\IChange */
    protected $repoDwnlChange;
    /** @var \Praxigento\Downline\Repo\Entity\ICustomer */
    protected $repoDwnlCust;
    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resource;
    /** @var  \Praxigento\Core\Tool\IFormat */
    protected $hlpFormat;
    public function __construct(
        \Praxigento\Core\Fw\Logger\App $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Tool\IFormat $hlpFormat,
        \Praxigento\Downline\Repo\Entity\IChange $repoDwnlChange,
        \Praxigento\Downline\Repo\Entity\ICustomer $repoDwnlCust,
        \Praxigento\Downline\Service\ISnap $callDwnlSnap
    ) {
        $this->logger = $logger;
        $this->resource = $resource;
        $this->hlpFormat = $hlpFormat;
        $this->repoDwnlChange = $repoDwnlChange;
        $this->repoDwnlCust = $repoDwnlCust;
        $this->callDwnlSnap = $callDwnlSnap;
    }

    public function do($data)
    {
        $tree = $data[self::A_ENTRIES];
        $mapByMlmId = $data[self::A_MAP_BY_MLM_ID];
        /* expand minimal tree: populate tree with depth & path */
        $reqExpand = new \Praxigento\Downline\Service\Snap\Request\ExpandMinimal();
        $reqExpand->setTree($tree);
        $reqExpand->setKeyCustomerId(\Praxigento\App\Generic2\Cli\Cmd\Test\Downline\Init\ReadCsv\Downline::A_CUST_ID);
        $reqExpand->setKeyParentId(\Praxigento\App\Generic2\Cli\Cmd\Test\Downline\Init\ReadCsv\Downline::A_PARENT_ID);
        $respExpand = $this->callDwnlSnap->expandMinimal($reqExpand);
        $expandedTree = $respExpand->getSnapData();
        /* order tree by depth */
        uasort($expandedTree, function ($a, $b) {
            $result = $a[\Praxigento\Downline\Data\Entity\Snap::ATTR_DEPTH] - $b[\Praxigento\Downline\Data\Entity\Snap::ATTR_DEPTH];
            return $result;
        });
        /* save customer data into repo */
        $dtChanged = \DateTime::createFromFormat('Ymd', '20170101');
        $timeStarted = $dtChanged->getTimestamp();
        foreach ($expandedTree as $item) {
            $custMlmId = $item[\Praxigento\Downline\Data\Entity\Snap::ATTR_CUSTOMER_ID];
            $parentMlmId = $item[\Praxigento\Downline\Data\Entity\Snap::ATTR_PARENT_ID];
            $depth = $item[\Praxigento\Downline\Data\Entity\Snap::ATTR_DEPTH];
            $path = $item[\Praxigento\Downline\Data\Entity\Snap::ATTR_PATH];
            $country = 'ES';
            /* get Mage IDs for MLM IDs */
            $cusMageId = $mapByMlmId[$custMlmId];
            $parentMageId = $mapByMlmId[$parentMlmId];
            $parts = explode(Cfg::DTPS, $path);
            $partsMapped = array_map(function ($mlmId) use ($mapByMlmId) {
                $result = isset($mapByMlmId[$mlmId]) ? $mapByMlmId[$mlmId] : '';
                return $result;
            }, $parts);
            $pathIds = implode(Cfg::DTPS, $partsMapped);
            $timeStarted += 600;
            $dtChanged->setTimestamp($timeStarted);
            $dateChanged = $this->hlpFormat->dateTimeForDb($dtChanged);
            /* add record to downline tree */
            $eCust = new \Praxigento\Downline\Data\Entity\Customer();
            $eCust->setCustomerId($cusMageId);
            $eCust->setParentId($parentMageId);
            $eCust->setDepth($depth);
            $eCust->setPath($pathIds);
            $eCust->setHumanRef($custMlmId);
            $eCust->setReferralCode($custMlmId);
            $eCust->setCountryCode($country);
            $this->repoDwnlCust->create($eCust);
            /* add record to change log */
            $eChange = new \Praxigento\Downline\Data\Entity\Change();
            $eChange->setCustomerId($cusMageId);
            $eChange->setParentId($parentMageId);
            $eChange->setDateChanged($dateChanged);
            $this->repoDwnlChange->create($eChange);
        }
    }
}