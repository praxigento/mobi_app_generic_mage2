<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\App\Generic2\Cli\Init;

/**
 * Post replication routines:
 *  - enable all categories;
 *  - create ACL User for Odoo Replication;
 */
class PostReplicate
    extends \Praxigento\Core\App\Cli\Cmd\Base
{
    /** @var Sub\Categories */
    protected $subCats;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $manObj,
        Sub\Categories $subCats
    ) {
        parent::__construct(
            $manObj,
            'prxgt:odoo:replicate:post',
            'Enable data after replication from Odoo.'
        );
        $this->subCats = $subCats;
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->checkAreaCode();
        /* enable categories after replication */
        $this->subCats->enableForAllStoreViews();
    }

}